<?php

namespace Modules\IndianPayroll\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\FullFinalSettlement;
use Modules\IndianPayroll\Entities\GratuitySetting;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Services\Attendance\AttendanceDataResolver;
use Modules\IndianPayroll\Services\Attendance\LossOfPayCalculator;
use Modules\IndianPayroll\Services\DTO\TaxCalculationInput;
use Modules\IndianPayroll\Services\Statutory\GratuityCalculator;
use Modules\IndianPayroll\Services\Tax\AnnualTaxProjectionBuilder;
use Modules\IndianPayroll\Services\Tax\IncomeTaxCalculator;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;

class SettlementController extends Controller
{
    public function __construct(
        private GratuityCalculator $gratuityCalculator,
        private AnnualTaxProjectionBuilder $taxProjectionBuilder,
        private IncomeTaxCalculator $taxCalculator,
        private AttendanceDataResolver $attendanceResolver,
        private LossOfPayCalculator $lopCalculator,
    ) {
        view()->share('activeLink', 'indian-payroll.settlements');
    }

    public function index()
    {
        canPerform('Manage Full Final Settlement');

        $settlements = FullFinalSettlement::with('user')->orderByDesc('id')->paginate(20);

        return view('indianpayroll::settlement.index', compact('settlements'));
    }

    public function create(User $user)
    {
        canPerform('Manage Full Final Settlement');

        $profile = EmployeeProfile::where('user_id', $user->id)->firstOrFail();
        $structure = EmployeeSalaryStructure::where('user_id', $user->id)->where('is_active', true)->with('components.component')->firstOrFail();
        $leaveTypes = LeaveType::where('is_paid', true)->get();
        $leaveBalances = LeaveBalance::where('user_id', $user->id)->where('year', now()->year)->get()->keyBy('leave_type_id');

        return view('indianpayroll::settlement.create', compact('user', 'profile', 'structure', 'leaveTypes', 'leaveBalances'));
    }

    public function store(Request $request, User $user)
    {
        canPerform('Manage Full Final Settlement');

        $data = $request->validate([
            'last_working_day' => 'required|date',
            'pending_salary_amount' => 'nullable|numeric|min:0', // blank = auto-calculate from attendance
            'notice_pay_recovery' => 'nullable|numeric|min:0',
            'asset_recovery' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'encash_leave_type_ids' => 'nullable|array',
            'encash_leave_type_ids.*' => 'integer',
            'is_death_or_disablement' => 'boolean',
        ]);

        $profile = EmployeeProfile::where('user_id', $user->id)->firstOrFail();
        $structure = EmployeeSalaryStructure::where('user_id', $user->id)->where('is_active', true)->with('components.component')->firstOrFail();
        $basicMonthly = $structure->componentAmount(SalaryComponent::CODE_BASIC);
        $lastWorkingDay = Carbon::parse($data['last_working_day']);

        $gratuitySettings = GratuitySetting::effectiveAsOf($lastWorkingDay);
        $gratuity = $this->gratuityCalculator->calculate(
            $gratuitySettings,
            $basicMonthly,
            $profile->date_of_joining,
            $lastWorkingDay,
            (bool) ($data['is_death_or_disablement'] ?? false)
        );

        $leaveEncashment = $this->computeLeaveEncashment($user, $data['encash_leave_type_ids'] ?? [], $basicMonthly, $gratuitySettings->divisor_days_per_month ?? 26);

        $finalTds = $this->computeIncrementalTax(
            $user, $structure, $lastWorkingDay,
            $gratuity->taxableAmount + $leaveEncashment['taxable']
        );

        $pendingSalary = $data['pending_salary_amount'] ?? $this->computePendingSalary($user, $profile, $structure, $lastWorkingDay);

        // Outstanding balance of the employee's active loans/advances, recovered in full at exit.
        $loanRecovery = round((float) \Modules\IndianPayroll\Entities\EmployeeLoan::where('user_id', $user->id)
            ->where('status', \Modules\IndianPayroll\Entities\EmployeeLoan::STATUS_ACTIVE)
            ->get()
            ->sum(fn ($loan) => $loan->outstandingBalance()), 2);

        $assetRecovery = (float) ($data['asset_recovery'] ?? 0);

        $netPayable = round(
            $pendingSalary
            + $gratuity->grossAmount
            + $leaveEncashment['gross']
            - ($data['notice_pay_recovery'] ?? 0)
            - $assetRecovery
            - $loanRecovery
            - ($data['other_deductions'] ?? 0)
            - $finalTds,
            2
        );

        $settlement = DB::transaction(function () use ($user, $profile, $data, $pendingSalary, $gratuity, $leaveEncashment, $finalTds, $netPayable, $assetRecovery, $loanRecovery) {
            $settlement = FullFinalSettlement::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'last_working_day' => $data['last_working_day'],
                    'pending_salary_amount' => $pendingSalary,
                    'gratuity_amount' => $gratuity->grossAmount,
                    'gratuity_taxable_amount' => $gratuity->taxableAmount,
                    'leave_encashment_amount' => $leaveEncashment['gross'],
                    'leave_encashment_taxable_amount' => $leaveEncashment['taxable'],
                    'notice_pay_recovery' => $data['notice_pay_recovery'] ?? 0,
                    'asset_recovery' => $assetRecovery,
                    'loan_recovery' => $loanRecovery,
                    'other_deductions' => $data['other_deductions'] ?? 0,
                    'final_tds' => $finalTds,
                    'net_payable' => $netPayable,
                    'status' => 'draft',
                ]
            );

            // Close the recovered loans so they don't keep deducting from payroll.
            \Modules\IndianPayroll\Entities\EmployeeLoan::where('user_id', $user->id)
                ->where('status', \Modules\IndianPayroll\Entities\EmployeeLoan::STATUS_ACTIVE)
                ->update(['status' => \Modules\IndianPayroll\Entities\EmployeeLoan::STATUS_CLOSED]);

            $profile->update(['date_of_exit' => $data['last_working_day']]);

            return $settlement;
        });

        return redirect()->route('backend.indian-payroll.settlements.show', $settlement)
            ->with('success', createFlashMessage('Full & Final Settlement', 'computed'));
    }

    public function show(FullFinalSettlement $settlement)
    {
        canPerform('Manage Full Final Settlement');

        $settlement->load('user');

        return view('indianpayroll::settlement.show', compact('settlement'));
    }

    public function approve(FullFinalSettlement $settlement)
    {
        canPerform('Manage Full Final Settlement');

        $settlement->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return redirect()->route('backend.indian-payroll.settlements.show', $settlement)->with('success', 'Settlement approved.');
    }

    public function download(FullFinalSettlement $settlement)
    {
        canPerform('Manage Full Final Settlement');

        $settlement->load('user');

        $pdf = Pdf::loadView('indianpayroll::settlement.pdf', compact('settlement'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('settlement_'.str_replace(' ', '_', strtolower($settlement->user->name)).'.pdf');
    }

    /**
     * Pays for the days actually worked in the exit month, using the same attendance
     * data and proration math as the regular monthly payroll run — so F&F doesn't
     * require HR to hand-calculate a partial month while every other figure here is
     * engine-computed.
     */
    private function computePendingSalary(User $user, EmployeeProfile $profile, EmployeeSalaryStructure $structure, \DateTimeInterface $lastWorkingDay): float
    {
        $lastWorkingDay = Carbon::parse($lastWorkingDay);
        $periodStart = $lastWorkingDay->copy()->startOfMonth()->max($profile->date_of_joining);
        $periodEnd = $lastWorkingDay->copy();

        if ($periodStart->gt($periodEnd)) {
            return 0.0;
        }

        $attendance = $this->attendanceResolver->resolve($user->id, $periodStart, $periodEnd);
        $lop = $this->lopCalculator->calculate($attendance['days_in_period'], $attendance['paid_days']);

        $monthlyGross = $structure->components
            ->filter(fn ($c) => $c->component->type === SalaryComponent::TYPE_EARNING)
            ->sum(fn ($c) => (float) $c->monthly_amount);

        return round($monthlyGross * $lop->payableFraction, 2);
    }

    /**
     * @return array{gross: float, taxable: float}
     */
    private function computeLeaveEncashment(User $user, array $leaveTypeIds, float $basicMonthly, int $divisorDays): array
    {
        if (empty($leaveTypeIds)) {
            return ['gross' => 0.0, 'taxable' => 0.0];
        }

        $perDayRate = round($basicMonthly / $divisorDays, 2);

        $balances = LeaveBalance::where('user_id', $user->id)
            ->where('year', now()->year)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->get();

        $gross = round($balances->sum('available') * $perDayRate, 2);

        // Section 10(10AA): non-government employees get a lifetime exemption ceiling;
        // anything beyond it (or beyond the cash-equivalent of the leave itself, capped at 10 months of average basic salary) is taxable.
        $exemptCeiling = (float) config('indianpayroll.leave_encashment.exemption_ceiling');
        $tenMonthsSalary = $basicMonthly * 10;
        $exempt = min($gross, $exemptCeiling, $tenMonthsSalary);
        $taxable = max(0.0, round($gross - $exempt, 2));

        return ['gross' => $gross, 'taxable' => $taxable];
    }

    private function computeIncrementalTax(User $user, EmployeeSalaryStructure $structure, \DateTimeInterface $lastWorkingDay, float $lumpSumTaxable): float
    {
        if ($lumpSumTaxable <= 0) {
            return 0.0;
        }

        $baseInput = $this->taxProjectionBuilder->build($user, $structure, $lastWorkingDay);

        $slabs = \Modules\IndianPayroll\Entities\IncomeTaxSlab::forRegime($baseInput->financialYear, $baseInput->regime);
        $surchargeSlabs = \Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab::forRegime($baseInput->financialYear, $baseInput->regime);

        if ($slabs->isEmpty()) {
            return 0.0;
        }

        $baseResult = $this->taxCalculator->calculate($baseInput, $slabs, $surchargeSlabs);

        $lumpSumInput = new TaxCalculationInput(
            financialYear: $baseInput->financialYear,
            regime: $baseInput->regime,
            annualTaxableSalaryProjected: $baseInput->annualTaxableSalaryProjected + $lumpSumTaxable,
            annualBasicPlusDa: $baseInput->annualBasicPlusDa,
            annualHraReceived: $baseInput->annualHraReceived,
            annualRentPaid: $baseInput->annualRentPaid,
            isMetro: $baseInput->isMetro,
            oldRegimeDeductions: $baseInput->oldRegimeDeductions,
            incomeFromPreviousEmployer: $baseInput->incomeFromPreviousEmployer,
            tdsDeductedByPreviousEmployer: $baseInput->tdsDeductedByPreviousEmployer,
            tdsAlreadyDeductedThisYear: $baseInput->tdsAlreadyDeductedThisYear,
            remainingMonthsInYear: $baseInput->remainingMonthsInYear,
        );

        $lumpSumResult = $this->taxCalculator->calculate($lumpSumInput, $slabs, $surchargeSlabs);

        return max(0.0, round($lumpSumResult->annualTaxLiability - $baseResult->annualTaxLiability, 2));
    }
}
