<?php

namespace Modules\IndianPayroll\Services\Tax;

use Illuminate\Support\Collection;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Services\DTO\TaxCalculationInput;
use Modules\IndianPayroll\Services\DTO\TaxResult;

/**
 * Annual-projection TDS engine (Sections 192 / 115BAC), regime-aware.
 *
 * Each payroll run re-projects the FULL financial year's tax liability from the employee's
 * actual income so far plus the current run-rate for remaining months, then spreads the
 * REMAINING liability evenly over the remaining months. This is the standard employer-TDS
 * method and self-corrects for mid-year salary revisions, bonuses, or late declarations —
 * it does not require a separate "true-up" pass in March.
 */
class IncomeTaxCalculator
{
    public function calculate(TaxCalculationInput $input, Collection $slabs, Collection $surchargeSlabs): TaxResult
    {
        $grossTaxableIncome = round(
            $input->annualTaxableSalaryProjected + $input->incomeFromPreviousEmployer,
            2
        );

        $exemptionsAndDeductions = $this->standardDeduction($input->regime);

        if ($input->regime === 'old') {
            $exemptionsAndDeductions += $this->hraExemption($input);
            $exemptionsAndDeductions += $input->oldRegimeDeductions;
        }

        $netTaxableIncome = max(0.0, round($grossTaxableIncome - $exemptionsAndDeductions, 2));

        $taxBeforeRebate = $this->applySlabs($netTaxableIncome, $slabs);

        $rebate = $this->rebate87A($input->regime, $netTaxableIncome, $taxBeforeRebate);

        $taxAfterRebate = max(0.0, round($taxBeforeRebate - $rebate, 2));

        $surcharge = $this->applySurchargeWithMarginalRelief($netTaxableIncome, $taxAfterRebate, $surchargeSlabs, $slabs);

        $cess = round(($taxAfterRebate + $surcharge) * (float) config('indianpayroll.tds.cess_rate'), 2);

        $annualTaxLiability = round($taxAfterRebate + $surcharge + $cess, 2);

        $tdsDeductedSoFar = round($input->tdsAlreadyDeductedThisYear + $input->tdsDeductedByPreviousEmployer, 2);

        $remainingLiability = max(0.0, round($annualTaxLiability - $tdsDeductedSoFar, 2));

        $months = max(1, $input->remainingMonthsInYear);

        $monthlyTds = round($remainingLiability / $months, 2);

        return new TaxResult(
            regime: $input->regime,
            grossTaxableIncome: $grossTaxableIncome,
            totalExemptionsAndDeductions: round($exemptionsAndDeductions, 2),
            netTaxableIncome: $netTaxableIncome,
            taxBeforeRebate: round($taxBeforeRebate, 2),
            rebate87A: $rebate,
            taxAfterRebate: $taxAfterRebate,
            surcharge: $surcharge,
            cess: $cess,
            annualTaxLiability: $annualTaxLiability,
            tdsAlreadyDeducted: $tdsDeductedSoFar,
            remainingMonthsInYear: $months,
            monthlyTds: $monthlyTds,
        );
    }

    private function standardDeduction(string $regime): float
    {
        return (float) config('indianpayroll.tds.standard_deduction.'.($regime === 'old' ? 'old_regime' : 'new_regime'));
    }

    /**
     * Section 10(13A): least of —
     *  a) actual HRA received,
     *  b) rent paid minus 10% of Basic+DA,
     *  c) 50% (metro) / 40% (non-metro) of Basic+DA.
     */
    private function hraExemption(TaxCalculationInput $input): float
    {
        if ($input->annualRentPaid <= 0 || $input->annualHraReceived <= 0) {
            return 0.0;
        }

        $a = $input->annualHraReceived;
        $b = max(0.0, $input->annualRentPaid - (0.10 * $input->annualBasicPlusDa));
        $c = ($input->isMetro ? 0.50 : 0.40) * $input->annualBasicPlusDa;

        return round(min($a, $b, $c), 2);
    }

    private function applySlabs(float $netTaxableIncome, Collection $slabs): float
    {
        $tax = 0.0;

        foreach ($slabs as $slab) {
            $from = (float) $slab->slab_from;
            $to = $slab->slab_to !== null ? (float) $slab->slab_to : null;

            if ($netTaxableIncome <= $from) {
                continue;
            }

            $taxableInSlab = $to !== null
                ? max(0.0, min($netTaxableIncome, $to) - $from)
                : max(0.0, $netTaxableIncome - $from);

            $tax += $taxableInSlab * ((float) $slab->rate / 100);
        }

        return round($tax, 2);
    }

    /**
     * Section 87A rebate — full rebate (capped) if net taxable income is within the
     * regime's rebate threshold, effectively making tax nil up to that income level.
     */
    private function rebate87A(string $regime, float $netTaxableIncome, float $taxBeforeRebate): float
    {
        $config = config('indianpayroll.tds.rebate_87a.'.($regime === 'old' ? 'old_regime' : 'new_regime'));

        if ($netTaxableIncome > (float) $config['income_limit']) {
            if ($regime === 'new' && $netTaxableIncome <= 727770) {
                // New Regime Section 87A Marginal Relief:
                // Tax payable cannot exceed (net taxable income - 7,00,000).
                // Thus: rebate = taxBeforeRebate - (netTaxableIncome - 700000).
                $maxTaxPayable = $netTaxableIncome - 700000;
                return max(0.0, round($taxBeforeRebate - $maxTaxPayable, 2));
            }
            return 0.0;
        }

        return round(min($taxBeforeRebate, (float) $config['max_rebate']), 2);
    }

    /**
     * Surcharge applies once net taxable income crosses a threshold, but without relief
     * it could make total tax+surcharge rise by MORE than the income that pushed it over
     * the threshold. Marginal relief caps the increase to exactly that excess income.
     */
    private function applySurchargeWithMarginalRelief(
        float $netTaxableIncome,
        float $taxAfterRebate,
        Collection $surchargeSlabs,
        Collection $slabs,
    ): float {
        if ($taxAfterRebate <= 0) {
            return 0.0;
        }

        $rate = 0.0;
        $threshold = null;
        foreach ($surchargeSlabs as $slab) {
            $from = (float) $slab->income_from;
            $to = $slab->income_to !== null ? (float) $slab->income_to : null;

            if ($netTaxableIncome > $from && ($to === null || $netTaxableIncome <= $to)) {
                $rate = (float) $slab->surcharge_rate;
                $threshold = $from;
                break;
            }
        }

        if ($rate <= 0.0 || $threshold === null) {
            return 0.0;
        }

        $surcharge = round($taxAfterRebate * ($rate / 100), 2);

        $taxAtThreshold = $this->applySlabs($threshold, $slabs);
        $maxAllowedTotal = $taxAtThreshold + ($netTaxableIncome - $threshold);
        $uncappedTotal = $taxAfterRebate + $surcharge;

        if ($uncappedTotal > $maxAllowedTotal) {
            $surcharge = max(0.0, round($maxAllowedTotal - $taxAfterRebate, 2));
        }

        return $surcharge;
    }
}
