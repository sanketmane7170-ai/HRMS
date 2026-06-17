<?php

namespace Modules\IndianPayroll\Tests\Unit;

use Illuminate\Support\Collection;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Services\DTO\TaxCalculationInput;
use Modules\IndianPayroll\Services\Tax\IncomeTaxCalculator;
use Tests\TestCase;

class IncomeTaxCalculatorTest extends TestCase
{
    private function newRegimeSlabs(): Collection
    {
        return collect([
            [0, 400000, 0],
            [400000, 800000, 5],
            [800000, 1200000, 10],
            [1200000, 1600000, 15],
            [1600000, 2000000, 20],
            [2000000, 2400000, 25],
            [2400000, null, 30],
        ])->map(fn ($s) => new IncomeTaxSlab(['slab_from' => $s[0], 'slab_to' => $s[1], 'rate' => $s[2]]));
    }

    private function oldRegimeSlabs(): Collection
    {
        return collect([
            [0, 250000, 0],
            [250000, 500000, 5],
            [500000, 1000000, 20],
            [1000000, null, 30],
        ])->map(fn ($s) => new IncomeTaxSlab(['slab_from' => $s[0], 'slab_to' => $s[1], 'rate' => $s[2]]));
    }

    private function surchargeSlabs(string $regime): Collection
    {
        return collect([
            [5000000, 10000000, 10],
            [10000000, 20000000, 15],
            [20000000, null, 25],
        ])->map(fn ($s) => new IncomeTaxSurchargeSlab(['income_from' => $s[0], 'income_to' => $s[1], 'surcharge_rate' => $s[2]]));
    }

    private function baseInput(array $overrides = []): TaxCalculationInput
    {
        return new TaxCalculationInput(
            financialYear: $overrides['financialYear'] ?? '2025-2026',
            regime: $overrides['regime'] ?? 'new',
            annualTaxableSalaryProjected: $overrides['annualTaxableSalaryProjected'] ?? 0,
            annualBasicPlusDa: $overrides['annualBasicPlusDa'] ?? 0,
            annualHraReceived: $overrides['annualHraReceived'] ?? 0,
            annualRentPaid: $overrides['annualRentPaid'] ?? 0,
            isMetro: $overrides['isMetro'] ?? false,
            oldRegimeDeductions: $overrides['oldRegimeDeductions'] ?? 0,
            incomeFromPreviousEmployer: $overrides['incomeFromPreviousEmployer'] ?? 0,
            tdsDeductedByPreviousEmployer: $overrides['tdsDeductedByPreviousEmployer'] ?? 0,
            tdsAlreadyDeductedThisYear: $overrides['tdsAlreadyDeductedThisYear'] ?? 0,
            remainingMonthsInYear: $overrides['remainingMonthsInYear'] ?? 12,
        );
    }

    public function test_new_regime_mid_income_no_rebate(): void
    {
        $input = $this->baseInput(['annualTaxableSalaryProjected' => 1075000]); // net = 1,000,000 after 75k std deduction

        $result = (new IncomeTaxCalculator)->calculate($input, $this->newRegimeSlabs(), $this->surchargeSlabs('new'));

        $this->assertEquals(1000000.0, $result->netTaxableIncome);
        $this->assertEquals(40000.0, $result->taxBeforeRebate);
        $this->assertEquals(0.0, $result->rebate87A);
        $this->assertEquals(0.0, $result->surcharge);
        $this->assertEquals(1600.0, $result->cess);
        $this->assertEquals(41600.0, $result->annualTaxLiability);
        $this->assertEquals(3466.67, $result->monthlyTds); // 41600 / 12
    }

    public function test_new_regime_below_rebate_threshold_is_fully_rebated(): void
    {
        $input = $this->baseInput(['annualTaxableSalaryProjected' => 725000]); // net = 650,000

        $result = (new IncomeTaxCalculator)->calculate($input, $this->newRegimeSlabs(), $this->surchargeSlabs('new'));

        $this->assertEquals(650000.0, $result->netTaxableIncome);
        $this->assertEquals(12500.0, $result->taxBeforeRebate);
        $this->assertEquals(12500.0, $result->rebate87A);
        $this->assertEquals(0.0, $result->annualTaxLiability);
        $this->assertEquals(0.0, $result->monthlyTds);
    }

    public function test_old_regime_with_hra_and_80c_can_zero_out_tax(): void
    {
        $input = $this->baseInput([
            'regime' => 'old',
            'annualTaxableSalaryProjected' => 800000,
            'annualBasicPlusDa' => 400000,
            'annualHraReceived' => 160000,
            'annualRentPaid' => 180000,
            'isMetro' => true,
            'oldRegimeDeductions' => 150000,
        ]);

        $result = (new IncomeTaxCalculator)->calculate($input, $this->oldRegimeSlabs(), $this->surchargeSlabs('old'));

        // exemptions = 50k std + 140k HRA (min of 160k actual / 140k rent-10%basic / 200k 50%basic) + 150k 80C = 340k
        $this->assertEquals(460000.0, $result->netTaxableIncome);
        $this->assertEquals(10500.0, $result->taxBeforeRebate);
        $this->assertEquals(10500.0, $result->rebate87A); // net income <= 5L old-regime rebate threshold
        $this->assertEquals(0.0, $result->annualTaxLiability);
    }

    public function test_surcharge_applies_marginal_relief_at_threshold_crossing(): void
    {
        // net taxable income = 50,10,000 — just 10,000 over the 50-lakh surcharge threshold.
        $input = $this->baseInput(['annualTaxableSalaryProjected' => 5085000]);

        $result = (new IncomeTaxCalculator)->calculate($input, $this->newRegimeSlabs(), $this->surchargeSlabs('new'));

        $this->assertEquals(5010000.0, $result->netTaxableIncome);
        $this->assertEquals(1083000.0, $result->taxBeforeRebate);
        // Uncapped surcharge would be 108,300 (10% of tax) — marginal relief caps the
        // *increase* over the threshold to the 10,000 of income that crossed it.
        $this->assertEquals(7000.0, $result->surcharge);
    }

    public function test_remaining_months_spreads_liability_and_credits_tds_already_paid(): void
    {
        $input = $this->baseInput([
            'annualTaxableSalaryProjected' => 1075000,
            'tdsAlreadyDeductedThisYear' => 20000,
            'remainingMonthsInYear' => 6,
        ]);

        $result = (new IncomeTaxCalculator)->calculate($input, $this->newRegimeSlabs(), $this->surchargeSlabs('new'));

        $this->assertEquals(41600.0, $result->annualTaxLiability);
        $this->assertEquals(20000.0, $result->tdsAlreadyDeducted);
        $this->assertEquals(3600.0, $result->monthlyTds); // (41600 - 20000) / 6
    }
}
