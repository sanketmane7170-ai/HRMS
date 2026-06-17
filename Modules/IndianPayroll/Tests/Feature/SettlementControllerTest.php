<?php

namespace Modules\IndianPayroll\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Modules\IndianPayroll\Database\Seeders\DefaultStatutoryRatesSeeder;
use Modules\IndianPayroll\Database\Seeders\SalaryComponentSeeder;
use Modules\IndianPayroll\Database\Seeders\StatesOfIndiaSeeder;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructureComponent;
use Modules\IndianPayroll\Entities\FullFinalSettlement;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Http\Controllers\SettlementController;
use Tests\TestCase;

/**
 * Exercises SettlementController::store end-to-end through real HTTP-layer objects
 * (not just the calculators) — this is the layer that previously shipped with an
 * unfixed DateTimeInterface/string type mismatch that only surfaced on first actual
 * execution, and an unwrapped multi-write transaction.
 */
class SettlementControllerTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatesOfIndiaSeeder::class);
        $this->seed(SalaryComponentSeeder::class);
        $this->seed(DefaultStatutoryRatesSeeder::class);

        $this->user = User::factory()->create();
        $this->seed(\Modules\IndianPayroll\Database\Seeders\IndianPayrollPermissionsSeeder::class);
        $this->user->givePermissionTo('Manage Full Final Settlement');

        EmployeeProfile::create([
            'user_id' => $this->user->id,
            'state_id' => IpState::where('code', 'MH')->first()->id,
            'pf_applicable' => true,
            'date_of_joining' => Carbon::now()->subYears(6), // gratuity-eligible
            'gender' => 'male',
        ]);

        $structure = EmployeeSalaryStructure::create([
            'user_id' => $this->user->id,
            'annual_ctc' => 600000,
            'monthly_ctc' => 50000,
            'effective_from' => Carbon::now()->subYears(6),
            'is_active' => true,
        ]);

        $componentIds = SalaryComponent::pluck('id', 'code');
        EmployeeSalaryStructureComponent::create([
            'structure_id' => $structure->id,
            'salary_component_id' => $componentIds[SalaryComponent::CODE_BASIC],
            'monthly_amount' => 30000,
            'annual_amount' => 360000,
        ]);
        EmployeeSalaryStructureComponent::create([
            'structure_id' => $structure->id,
            'salary_component_id' => $componentIds[SalaryComponent::CODE_HRA],
            'monthly_amount' => 20000,
            'annual_amount' => 240000,
        ]);
    }

    public function test_store_computes_gratuity_and_auto_calculated_pending_salary_without_throwing(): void
    {
        $this->actingAs($this->user);

        $request = Request::create('', 'POST', [
            'last_working_day' => now()->format('Y-m-d'),
            'notice_pay_recovery' => 0,
            'other_deductions' => 0,
        ]);

        $controller = app(SettlementController::class);
        $response = $controller->store($request, $this->user);

        $this->assertEquals(302, $response->getStatusCode());

        $settlement = FullFinalSettlement::where('user_id', $this->user->id)->first();
        $this->assertNotNull($settlement);

        // 6 years of service, well past the 5-year vesting minimum.
        $this->assertGreaterThan(0, (float) $settlement->gratuity_amount);

        // date_of_exit must be set as part of the same write — this is exactly the
        // invariant the missing DB::transaction() previously put at risk.
        $profile = EmployeeProfile::where('user_id', $this->user->id)->first();
        $this->assertNotNull($profile->date_of_exit);
    }

    public function test_explicit_pending_salary_overrides_auto_calculation(): void
    {
        $this->actingAs($this->user);

        $request = Request::create('', 'POST', [
            'last_working_day' => now()->format('Y-m-d'),
            'pending_salary_amount' => 12345.67,
        ]);

        app(SettlementController::class)->store($request, $this->user);

        $settlement = FullFinalSettlement::where('user_id', $this->user->id)->first();
        $this->assertEquals(12345.67, (float) $settlement->pending_salary_amount);
    }
}
