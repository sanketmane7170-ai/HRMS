<?php

namespace Modules\IndianPayroll\Services\Tax;

use App\Models\User;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\Payslip;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Services\DTO\TaxCalculationInput;

/**
 * Assembles the inputs IncomeTaxCalculator needs from current DB state:
 *  - actual taxable earnings already paid this financial year (from prior payslips),
 *  - the current salary structure's run-rate projected over the remaining months,
 *  - the employee's regime choice and verified investment declarations.
 *
 * Projection method: this is the standard employer-TDS approach (actual-to-date +
 * projected-remainder), recomputed every run so it self-corrects for revisions —
 * no separate "true-up" pass is needed.
 */
class AnnualTaxProjectionBuilder
{
    public function build(User $user, EmployeeSalaryStructure $structure, \DateTimeInterface $periodStart): TaxCalculationInput
    {
        $financialYear = FinancialYearHelper::forDate($periodStart);
        $fyStart = FinancialYearHelper::startDate($financialYear);
        $remainingMonths = FinancialYearHelper::remainingMonthsInclusive($periodStart);

        $declaration = EmployeeTaxDeclaration::firstOrCreate(
            ['user_id' => $user->id, 'financial_year' => $financialYear],
            ['regime_choice' => 'new']
        );

        $structure->loadMissing('components.component');

        $currentMonthlyTaxable = $structure->components
            ->filter(fn ($c) => $c->component->type === SalaryComponent::TYPE_EARNING && $c->component->is_taxable)
            ->sum(fn ($c) => (float) $c->monthly_amount);

        ['taxable' => $ytdTaxable, 'tds' => $ytdTds] = $this->yearToDateActuals($user->id, $fyStart, $periodStart);

        $annualTaxableSalaryProjected = round($ytdTaxable + ($currentMonthlyTaxable * $remainingMonths), 2);

        $annualBasicPlusDa = $structure->componentAmount(SalaryComponent::CODE_BASIC) * 12;
        $annualHraReceived = $structure->componentAmount(SalaryComponent::CODE_HRA) * 12;

        $hraInput = $declaration->hraExemptionInput;
        $annualRentPaid = $hraInput ? (float) $hraInput->monthly_rent * 12 : 0.0;
        $isMetro = (bool) ($hraInput?->is_metro ?? false);

        $oldRegimeDeductions = $this->computeOldRegimeDeductions($declaration->investmentDeclarations);

        return new TaxCalculationInput(
            financialYear: $financialYear,
            regime: $declaration->regime_choice,
            annualTaxableSalaryProjected: $annualTaxableSalaryProjected,
            annualBasicPlusDa: $annualBasicPlusDa,
            annualHraReceived: $annualHraReceived,
            annualRentPaid: $annualRentPaid,
            isMetro: $isMetro,
            oldRegimeDeductions: $oldRegimeDeductions,
            incomeFromPreviousEmployer: (float) $declaration->income_from_previous_employer,
            tdsDeductedByPreviousEmployer: (float) $declaration->tds_deducted_by_previous_employer,
            tdsAlreadyDeductedThisYear: $ytdTds,
            remainingMonthsInYear: $remainingMonths,
        );
    }

    /**
     * Computes total old-regime deductions with correct sectional and aggregate caps.
     *
     * The Income Tax Act mandates a combined ₹1,50,000 cap across 80C + 80CCC + 80CCD(1)
     * (the employer's NPS contribution falls under 80CCD(1), not the additional ₹50K slot).
     * 80CCD(1B) is a separate ₹50,000 bucket that does NOT count toward this aggregate.
     * All other sections (80D, 80E, 80G, 80TTA, 24B) are capped individually and then
     * added on top.
     */
    private function computeOldRegimeDeductions(\Illuminate\Support\Collection $declarations): float
    {
        // Sections that share the ₹1,50,000 aggregate ceiling
        $aggregateCappedSections = ['80C', '80CCC', '80CCD1'];

        $aggregateBucket = 0.0;
        $otherDeductions = 0.0;

        foreach ($declarations as $d) {
            if (in_array($d->section_code, $aggregateCappedSections, true)) {
                $aggregateBucket += $d->effectiveAmount();
            } else {
                $otherDeductions += $d->effectiveAmount();
            }
        }

        // Apply the aggregate cap of ₹1,50,000 across 80C + 80CCC + 80CCD(1)
        $aggregateCap = (float) config('indianpayroll.investment_sections.80C.cap', 150000);
        $aggregateBucket = min($aggregateBucket, $aggregateCap);

        return round($aggregateBucket + $otherDeductions, 2);
    }

    /**
     * @return array{taxable: float, tds: float}
     */
    private function yearToDateActuals(int $userId, \DateTimeInterface $fyStart, \DateTimeInterface $beforeDate): array
    {
        $payslips = Payslip::where('user_id', $userId)
            ->whereHas('run', function ($q) use ($fyStart, $beforeDate) {
                $q->where('period_start', '>=', $fyStart)
                    ->where('period_start', '<', $beforeDate);
            })
            ->with('components.component')
            ->get();

        $taxable = 0.0;
        $tds = 0.0;

        foreach ($payslips as $payslip) {
            foreach ($payslip->components as $component) {
                $sc = $component->component;
                if ($sc && $component->type === SalaryComponent::TYPE_EARNING && $sc->is_taxable) {
                    $taxable += (float) $component->amount;
                } elseif ($component->is_manual && $component->type === SalaryComponent::TYPE_EARNING) {
                    // Manual one-off earnings (bonuses) are taxable income even when
                    // label-only (no catalog component to read is_taxable from).
                    $taxable += (float) $component->amount;
                }
                if (optional($sc)->code === SalaryComponent::CODE_TDS) {
                    $tds += (float) $component->amount;
                }
            }
        }

        return ['taxable' => round($taxable, 2), 'tds' => round($tds, 2)];
    }
}
