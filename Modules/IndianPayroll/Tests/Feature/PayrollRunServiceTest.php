<?php

namespace Modules\IndianPayroll\Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Attendance\Entities\Attendance;
use Modules\IndianPayroll\Database\Seeders\DefaultStatutoryRatesSeeder;
use Modules\IndianPayroll\Database\Seeders\SalaryComponentSeeder;
use Modules\IndianPayroll\Database\Seeders\StatesOfIndiaSeeder;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructureComponent;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Services\PayrollRunService;
use Tests\TestCase;

class PayrollRunServiceTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private PayrollRunService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatesOfIndiaSeeder::class);
        $this->seed(SalaryComponentSeeder::class);
        $this->seed(DefaultStatutoryRatesSeeder::class);

        $dept = Department::firstOrCreate(['name' => 'Test Department'], ['short_name' => 'TEST']);
        $desig = Designation::firstOrCreate(['name' => 'Test Designation']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
        ]);

        $maharashtra = IpState::where('code', 'MH')->first();

        EmployeeProfile::create([
            'user_id' => $this->user->id,
            'state_id' => $maharashtra->id,
            'pf_applicable' => true,
            'esi_applicable' => false,
            'pt_applicable' => true,
            'lwf_applicable' => false,
            'date_of_joining' => Carbon::create(1998, 1, 1),
            'gender' => 'male',
        ]);

        $structure = EmployeeSalaryStructure::create([
            'user_id' => $this->user->id,
            'annual_ctc' => 600000,
            'monthly_ctc' => 50000,
            'effective_from' => Carbon::create(1999, 1, 1),
            'is_active' => true,
        ]);

        $componentIds = SalaryComponent::pluck('id', 'code');

        EmployeeSalaryStructureComponent::create([
            'structure_id' => $structure->id,
            'salary_component_id' => $componentIds[SalaryComponent::CODE_BASIC],
            'monthly_amount' => 25000,
            'annual_amount' => 300000,
        ]);
        EmployeeSalaryStructureComponent::create([
            'structure_id' => $structure->id,
            'salary_component_id' => $componentIds[SalaryComponent::CODE_HRA],
            'monthly_amount' => 12500,
            'annual_amount' => 150000,
        ]);
        EmployeeSalaryStructureComponent::create([
            'structure_id' => $structure->id,
            'salary_component_id' => $componentIds[SalaryComponent::CODE_SPECIAL_ALLOWANCE],
            'monthly_amount' => 12500,
            'annual_amount' => 150000,
        ]);

        // Mark every day of the run period as Present so the payable fraction is 1.0
        // and the test exercises the full calculation path, not a degenerate LOP case.
        $month = 4;
        $year = 2099; // far-future year — never collides with live data; seeded statutory rates (effective 2025-04-01) apply
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            Attendance::create([
                'date' => $date->toDateString(),
                'status' => 'present',
                'user_id' => $this->user->id,
                'created_by_id' => $this->user->id,
            ]);
        }

        $this->service = new PayrollRunService;
    }

    public function test_compute_creates_a_payslip_with_consistent_totals(): void
    {
        $run = $this->service->createRun(4, 2099, $this->user->id);

        $this->service->compute($run);

        $payslip = Payslip::where('run_id', $run->id)->where('user_id', $this->user->id)->first();

        $this->assertNotNull($payslip);
        $this->assertEquals('computed', $run->fresh()->status);

        // Invariant: net pay must equal prorated gross minus statutory deductions —
        // this is the core correctness check independent of exact PF/ESI/tax figures.
        $componentsSum = $payslip->components->sum(fn ($c) => match ($c->type) {
            'earning' => (float) $c->amount,
            'deduction' => -(float) $c->amount,
            default => 0.0,
        });

        // earnings already exclude the LOSS_OF_PAY deduction's double counting per design,
        // so reconstruct prorated gross as gross_earnings minus the LOP line.
        $lop = $payslip->components->first(fn ($c) => $c->component->code === SalaryComponent::CODE_LOSS_OF_PAY);
        $proratedGross = (float) $payslip->gross_earnings - (float) ($lop->amount ?? 0);

        $this->assertEqualsWithDelta($proratedGross - $payslip->total_statutory_deductions, (float) $payslip->net_pay, 0.01);

        // Full attendance this month — no loss of pay.
        $this->assertEquals(0.0, (float) $payslip->loss_of_pay_days);
    }

    public function test_epf_is_computed_at_twelve_percent_of_basic_capped_at_ceiling(): void
    {
        $run = $this->service->createRun(4, 2099, $this->user->id);
        $this->service->compute($run);

        $payslip = Payslip::where('run_id', $run->id)->where('user_id', $this->user->id)->with('components.component')->first();
        $epf = $payslip->components->first(fn ($c) => $c->component->code === SalaryComponent::CODE_EPF_EMPLOYEE);

        // Basic is 25,000 but PF wage is capped at the statutory ceiling of 15,000.
        $this->assertEquals(1800.0, (float) $epf->amount); // 15000 * 12%
    }

    public function test_recomputing_a_draft_run_replaces_components_instead_of_duplicating(): void
    {
        $run = $this->service->createRun(4, 2099, $this->user->id);

        $this->service->compute($run);
        $firstCount = Payslip::where('run_id', $run->id)->where('user_id', $this->user->id)->first()->components()->count();

        $this->service->compute($run);
        $payslipsAfterSecondRun = Payslip::where('run_id', $run->id)->where('user_id', $this->user->id)->count();
        $secondCount = Payslip::where('run_id', $run->id)->where('user_id', $this->user->id)->first()->components()->count();

        $this->assertEquals(1, $payslipsAfterSecondRun); // unique(run_id, user_id) — no duplicate payslip row
        $this->assertEquals($firstCount, $secondCount); // components replaced, not appended
    }

    public function test_locked_run_rejects_recomputation(): void
    {
        $run = $this->service->createRun(4, 2099, $this->user->id);
        $this->service->compute($run);
        $this->service->approve($run, $this->user->id);
        $this->service->lock($run->fresh(), $this->user->id);

        $this->expectException(\RuntimeException::class);
        $this->service->compute($run->fresh());
    }

    public function test_duplicate_run_for_same_month_and_year_is_rejected_by_unique_constraint(): void
    {
        $this->service->createRun(4, 2099, $this->user->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->service->createRun(4, 2099, $this->user->id);
    }
}
