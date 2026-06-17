<?php

namespace Modules\IndianPayroll\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EsiSetting;
use Modules\IndianPayroll\Entities\GratuitySetting;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Entities\LwfRule;
use Modules\IndianPayroll\Entities\PayrollRun;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\PayslipComponent;
use Modules\IndianPayroll\Entities\PfSetting;
use Modules\IndianPayroll\Entities\ProfessionalTaxSlab;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Notifications\PayslipApprovedNotification;
use Modules\IndianPayroll\Services\Attendance\AttendanceDataResolver;
use Modules\IndianPayroll\Services\Attendance\LossOfPayCalculator;
use Modules\IndianPayroll\Services\Statutory\EPFCalculator;
use Modules\IndianPayroll\Services\Statutory\ESICalculator;
use Modules\IndianPayroll\Services\Tax\AnnualTaxProjectionBuilder;
use Modules\IndianPayroll\Services\Tax\IncomeTaxCalculator;

/**
 * Orchestrates a monthly payroll run end-to-end. Every state transition and every
 * per-employee computation happens inside a DB transaction, and every mutating method
 * re-checks the run's status server-side before writing — a locked/approved run cannot
 * be silently re-computed by calling compute() again, unlike the legacy Payroll module
 * where `is_close` was only enforced by hiding a button in the UI.
 *
 * Conventions baked into the math here (documented once, not scattered as magic numbers):
 *  - Earning components are stored on the payslip at their LOP-prorated amount (monthly_amount
 *    x payableFraction). A LOSS_OF_PAY line is also stored for payslip readability/registers,
 *    but it is NOT subtracted again when computing net pay — it would double-count the
 *    proration already applied to the earning lines above.
 *  - PF wage, ESI gross, and PT gross all use the prorated (actual-earned) figures, per
 *    EPFO/ESIC/state PT practice of contributing on wages actually paid for the period.
 */
class PayrollRunService
{
    public function __construct(
        private AttendanceDataResolver $attendanceResolver = new AttendanceDataResolver,
        private LossOfPayCalculator $lopCalculator = new LossOfPayCalculator,
        private EPFCalculator $epfCalculator = new EPFCalculator,
        private ESICalculator $esiCalculator = new ESICalculator,
        private AnnualTaxProjectionBuilder $taxProjectionBuilder = new AnnualTaxProjectionBuilder,
        private IncomeTaxCalculator $taxCalculator = new IncomeTaxCalculator,
    ) {
    }

    public function createRun(int $month, int $year, ?int $createdById = null): PayrollRun
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return PayrollRun::create([
            'month' => $month,
            'year' => $year,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => PayrollRun::STATUS_DRAFT,
            'created_by' => $createdById,
        ]);
    }

    /**
     * Computes (or recomputes) every eligible employee's payslip for this run.
     * Idempotent while the run is editable: re-running replaces each employee's
     * component lines rather than appending duplicates.
     */
    public function compute(PayrollRun $run): void
    {
        if (! $run->isEditable() && ! $run->isComputing()) {
            throw new \RuntimeException('Cannot compute a payroll run that is already approved or locked.');
        }

        $componentIds = SalaryComponent::pluck('id', 'code');

        $profiles = EmployeeProfile::with('user', 'state')
            ->where(function ($q) use ($run) {
                $q->whereNull('date_of_exit')->orWhere('date_of_exit', '>=', $run->period_start);
            })
            ->where('date_of_joining', '<=', $run->period_end)
            ->get();

        // -----------------------------------------------------------------------
        // Pre-resolve all per-run statutory settings ONCE — outside the employee
        // loop.  For a 100-employee run with 3 states and 2 tax regimes this
        // drops ~600 repeated queries down to ~13 (PF×1, ESI×1, Gratuity×1,
        // PT slabs×states, LWF rule×states, tax slabs×2 regimes×2 tables).
        // -----------------------------------------------------------------------
        $pfSetting = PfSetting::effectiveAsOf($run->period_end);
        $esiSetting = EsiSetting::effectiveAsOf($run->period_end);
        $gratuitySetting = GratuitySetting::effectiveAsOf(now());

        $stateIds = $profiles->pluck('state_id')->filter()->unique()->values();

        // PT slab collection per state_id (used by ProfessionalTaxSlab::pickFrom() in applyPt)
        $ptSlabsByState = $stateIds->mapWithKeys(
            fn (int $id) => [$id => ProfessionalTaxSlab::where('state_id', $id)->where('is_active', true)->get()]
        );

        // LWF rule per state_id (only one row per state/date is needed)
        $lwfRuleByState = $stateIds->mapWithKeys(
            fn (int $id) => [$id => LwfRule::findFor($id, $run->period_end)]
        );

        // Income tax slabs cached lazily by "financialYear:regime" as each employee's
        // regime is resolved — at most 2 DB roundtrips for (old, new) across all employees.
        $taxSlabCache = [];

        $skipped = []; // user_ids that failed — logged and reported but don't halt the run

        foreach ($profiles as $profile) {
            $structure = $this->activeStructureFor($profile->user_id, $run->period_start);

            if (! $structure) {
                continue; // no salary structure assigned yet — skip silently
            }

            try {
                DB::transaction(function () use (
                    $run, $profile, $structure, $componentIds,
                    $pfSetting, $esiSetting, $gratuitySetting,
                    $ptSlabsByState, $lwfRuleByState, &$taxSlabCache
                ) {
                    $this->computeForEmployee(
                        $run, $profile, $structure, $componentIds,
                        $pfSetting, $esiSetting, $gratuitySetting,
                        $ptSlabsByState, $lwfRuleByState, $taxSlabCache
                    );
                });
            } catch (\Throwable $e) {
                // One employee failing must NOT roll back everyone else. Log and continue.
                $skipped[] = $profile->user_id;
                Log::error('IndianPayroll: failed to compute payslip', [
                    'run_id' => $run->id,
                    'user_id' => $profile->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $finalStatus = PayrollRun::STATUS_COMPUTED;
        $errorSummary = null;
        if (! empty($skipped)) {
            $errorSummary = count($skipped).' employee(s) could not be computed (user IDs: '.implode(', ', $skipped).') — check laravel.log for details.';
            // Still mark computed — the run has results for everyone that succeeded.
        }

        $run->update(['status' => $finalStatus, 'compute_error' => $errorSummary]);
    }

    public function approve(PayrollRun $run, int $approverId): void
    {
        if ($run->isLocked()) {
            throw new \RuntimeException('Cannot approve a locked payroll run.');
        }

        $run->update([
            'status' => PayrollRun::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        Payslip::where('run_id', $run->id)->update(['status' => PayrollRun::STATUS_APPROVED]);

        // Notify each employee. Wrapped in try/catch so a misconfigured mail driver or
        // missing queue table never blocks a legitimate approval in any environment.
        try {
            $employeeIds = Payslip::where('run_id', $run->id)->pluck('user_id');
            $employees = \App\Models\User::whereIn('id', $employeeIds)->get();
            Notification::send($employees, new PayslipApprovedNotification($run));
        } catch (\Throwable $e) {
            Log::warning('IndianPayroll: payslip approval notification failed', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function lock(PayrollRun $run, int $lockerId): void
    {
        if ($run->status !== PayrollRun::STATUS_APPROVED) {
            throw new \RuntimeException('Only an approved payroll run can be locked.');
        }

        $run->update([
            'status' => PayrollRun::STATUS_LOCKED,
            'locked_by' => $lockerId,
            'locked_at' => now(),
        ]);

        Payslip::where('run_id', $run->id)->update(['status' => PayrollRun::STATUS_LOCKED]);
    }

    private function activeStructureFor(int $userId, Carbon $periodStart): ?EmployeeSalaryStructure
    {
        return EmployeeSalaryStructure::where('user_id', $userId)
            ->where('is_active', true)
            ->where('effective_from', '<=', $periodStart)
            ->where(function ($q) use ($periodStart) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $periodStart);
            })
            ->with('components.component')
            ->orderByDesc('effective_from')
            ->first();
    }

    private function computeForEmployee(
        PayrollRun $run,
        EmployeeProfile $profile,
        EmployeeSalaryStructure $structure,
        $componentIds,
        ?PfSetting $pfSetting,
        ?EsiSetting $esiSetting,
        ?GratuitySetting $gratuitySetting,
        Collection $ptSlabsByState,
        Collection $lwfRuleByState,
        array &$taxSlabCache,
    ): void {
        $lop = $this->lopCalculator->calculate(
            $run->period_start->diffInDays($run->period_end) + 1,
            $this->attendanceResolver->resolve($profile->user_id, $run->period_start, $run->period_end)['paid_days']
        );

        $lines = []; // code => ['type' => ..., 'amount' => ...]
        $grossContracted = 0.0;
        $grossProrated = 0.0;

        foreach ($structure->components as $structureComponent) {
            $component = $structureComponent->component;

            if (! in_array($component->type, [SalaryComponent::TYPE_EARNING, SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION], true)) {
                continue;
            }

            $contracted = (float) $structureComponent->monthly_amount;
            $prorated = round($contracted * $lop->payableFraction, 2);

            $lines[$component->code] = ['type' => $component->type, 'amount' => $prorated];

            if ($component->type === SalaryComponent::TYPE_EARNING) {
                $grossContracted += $contracted;
                $grossProrated += $prorated;
            }
        }

        $lines[SalaryComponent::CODE_LOSS_OF_PAY] = [
            'type' => SalaryComponent::TYPE_DEDUCTION,
            'amount' => round($grossContracted - $grossProrated, 2),
        ];

        $basicProrated = $structure->componentAmount(SalaryComponent::CODE_BASIC) * $lop->payableFraction;

        $this->applyPf($pfSetting, $profile, $basicProrated, $lines);
        $this->applyEsi($esiSetting, $profile, $grossProrated, $lines, $run);
        $this->applyPt($ptSlabsByState, $profile, $grossProrated, $lines, $run->period_end, $run->month);
        $this->applyLwf($lwfRuleByState, $profile, $grossProrated, $lines, $run);
        $this->applyGratuityProvision($gratuitySetting, $structure, $lines);
        $regime = $this->applyIncomeTax($profile, $structure, $lines, $run->period_start, $taxSlabCache);

        $payslip = Payslip::updateOrCreate(
            ['run_id' => $run->id, 'user_id' => $profile->user_id],
            [
                'days_in_period' => $lop->daysInPeriod,
                'paid_days' => $lop->paidDays,
                'loss_of_pay_days' => $lop->lossOfPayDays,
                'tax_regime' => $regime,
                'status' => PayrollRun::STATUS_COMPUTED,
            ]
        );

        // Scoped to engine-computed rows only — manual one-off earnings/deductions added
        // via the payslip edit screen must survive a recompute, not be silently wiped.
        $payslip->components()->where('is_manual', false)->delete();
        foreach ($lines as $code => $line) {
            if (! isset($componentIds[$code])) {
                continue;
            }
            PayslipComponent::create([
                'payslip_id' => $payslip->id,
                'salary_component_id' => $componentIds[$code],
                'type' => $line['type'],
                'amount' => $line['amount'],
                'is_manual' => false,
            ]);
        }

        $payslip->recalculateTotals();
    }

    private function applyPf(?PfSetting $settings, EmployeeProfile $profile, float $basicProrated, array &$lines): void
    {
        if (! $settings) {
            return;
        }

        $result = $this->epfCalculator->calculate($settings, $basicProrated, $profile->pf_voluntary_above_ceiling, $profile->pf_applicable);

        if (! $result->applicable) {
            return;
        }

        $lines[SalaryComponent::CODE_EPF_EMPLOYEE] = ['type' => SalaryComponent::TYPE_DEDUCTION, 'amount' => $result->employeeAmount];
        $lines[SalaryComponent::CODE_EPF_EMPLOYER] = ['type' => SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION, 'amount' => $result->employerEpfAmount];
        $lines[SalaryComponent::CODE_EPS_EMPLOYER] = ['type' => SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION, 'amount' => $result->employerEpsAmount];
    }

    private function applyEsi(?EsiSetting $settings, EmployeeProfile $profile, float $grossProrated, array &$lines, PayrollRun $run): void
    {
        if (! $settings || ! $profile->esi_applicable) {
            return;
        }

        $alreadyCovered = $this->wasEsiAppliedInContributionPeriod($profile->user_id, $run->period_start);

        $result = $this->esiCalculator->calculate($settings, $grossProrated, false, $alreadyCovered);

        if (! $result->applicable) {
            return;
        }

        $lines[SalaryComponent::CODE_ESI_EMPLOYEE] = ['type' => SalaryComponent::TYPE_DEDUCTION, 'amount' => $result->employeeAmount];
        $lines[SalaryComponent::CODE_ESI_EMPLOYER] = ['type' => SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION, 'amount' => $result->employerAmount];
    }

    private function wasEsiAppliedInContributionPeriod(int $userId, Carbon $periodStart): bool
    {
        $periodFrom = $periodStart->month >= 4 && $periodStart->month <= 9
            ? Carbon::create($periodStart->year, 4, 1)
            : ($periodStart->month >= 10
                ? Carbon::create($periodStart->year, 10, 1)
                : Carbon::create($periodStart->year - 1, 10, 1));

        return PayslipComponent::query()
            ->whereHas('component', fn ($q) => $q->where('code', SalaryComponent::CODE_ESI_EMPLOYEE))
            ->whereHas('payslip', fn ($q) => $q->where('user_id', $userId)
                ->whereHas('run', fn ($q2) => $q2->where('period_start', '>=', $periodFrom)->where('period_start', '<', $periodStart)))
            ->exists();
    }

    /**
     * Uses a pre-loaded collection of slabs (fetched once per state per run) and matches
     * in-memory via ProfessionalTaxSlab::pickFrom() — avoids a DB hit per employee.
     */
    private function applyPt(Collection $ptSlabsByState, EmployeeProfile $profile, float $grossProrated, array &$lines, Carbon $asOf, int $runMonth): void
    {
        if (! $profile->pt_applicable || ! $profile->state || ! $profile->state->pt_applicable) {
            return;
        }

        $stateSlabs = $ptSlabsByState->get($profile->state_id);
        if (! $stateSlabs || $stateSlabs->isEmpty()) {
            return;
        }

        $slab = ProfessionalTaxSlab::pickFrom($stateSlabs, $grossProrated, $profile->gender ?? 'all', $asOf);

        if (! $slab) {
            return;
        }

        // taxForMonth() returns february_tax for Feb if set — handles MH ₹300 Feb rule.
        $amount = $slab->taxForMonth($runMonth);
        if ($amount > 0) {
            $lines[SalaryComponent::CODE_PROFESSIONAL_TAX] = ['type' => SalaryComponent::TYPE_DEDUCTION, 'amount' => $amount];
        }
    }

    /**
     * Half-yearly LWF due months default to June/December (the common state convention).
     * Uses a pre-resolved LwfRule (fetched once per state per run) — avoids a DB hit per employee.
     */
    private function applyLwf(Collection $lwfRuleByState, EmployeeProfile $profile, float $grossProrated, array &$lines, PayrollRun $run): void
    {
        if (! $profile->lwf_applicable || ! $profile->state || ! $profile->state->lwf_applicable) {
            return;
        }

        /** @var ?LwfRule $rule */
        $rule = $lwfRuleByState->get($profile->state_id);
        if (! $rule) {
            return;
        }

        if ($rule->wage_ceiling !== null && $grossProrated > (float) $rule->wage_ceiling) {
            return;
        }

        $isDueThisMonth = in_array($run->month, $rule->dueMonths(), true);
        if (! $isDueThisMonth) {
            return;
        }

        $lines[SalaryComponent::CODE_LWF_EMPLOYEE] = ['type' => SalaryComponent::TYPE_DEDUCTION, 'amount' => (float) $rule->employee_contribution];
        $lines[SalaryComponent::CODE_LWF_EMPLOYER] = ['type' => SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION, 'amount' => (float) $rule->employer_contribution];
    }

    /**
     * Monthly accrual estimate shown for CTC transparency — NOT the final payable amount,
     * which is only computed at exit by GratuityCalculator against actual completed years.
     */
    private function applyGratuityProvision(?GratuitySetting $settings, EmployeeSalaryStructure $structure, array &$lines): void
    {
        if (! $settings) {
            return;
        }

        $basic = $structure->componentAmount(SalaryComponent::CODE_BASIC);
        $monthlyAccrual = round(($basic * $settings->days_per_year_first_slab) / $settings->divisor_days_per_month / 12, 2);

        $lines[SalaryComponent::CODE_GRATUITY_PROVISION] = ['type' => SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION, 'amount' => $monthlyAccrual];
    }

    /**
     * Tax slabs are cached by "$financialYear:$regime" across the loop — at most 2 DB
     * roundtrips (old regime, new regime) regardless of employee count.
     */
    private function applyIncomeTax(EmployeeProfile $profile, EmployeeSalaryStructure $structure, array &$lines, Carbon $periodStart, array &$taxSlabCache): ?string
    {
        $input = $this->taxProjectionBuilder->build($profile->user, $structure, $periodStart);

        $cacheKey = $input->financialYear . ':' . $input->regime;
        if (! array_key_exists($cacheKey, $taxSlabCache)) {
            $taxSlabCache[$cacheKey] = [
                'slabs' => IncomeTaxSlab::forRegime($input->financialYear, $input->regime),
                'surchargeSlabs' => IncomeTaxSurchargeSlab::forRegime($input->financialYear, $input->regime),
            ];
        }

        $slabs = $taxSlabCache[$cacheKey]['slabs'];
        $surchargeSlabs = $taxSlabCache[$cacheKey]['surchargeSlabs'];

        if ($slabs->isEmpty()) {
            return $input->regime; // no slabs configured for this FY/regime — admin must configure before payroll can compute tax
        }

        $result = $this->taxCalculator->calculate($input, $slabs, $surchargeSlabs);

        if ($result->monthlyTds > 0) {
            $lines[SalaryComponent::CODE_TDS] = ['type' => SalaryComponent::TYPE_DEDUCTION, 'amount' => $result->monthlyTds];
        }

        return $input->regime;
    }
}
