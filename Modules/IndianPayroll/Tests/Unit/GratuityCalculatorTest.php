<?php

namespace Modules\IndianPayroll\Tests\Unit;

use Carbon\Carbon;
use Modules\IndianPayroll\Entities\GratuitySetting;
use Modules\IndianPayroll\Services\Statutory\GratuityCalculator;
use Tests\TestCase;

class GratuityCalculatorTest extends TestCase
{
    private function settings(): GratuitySetting
    {
        return new GratuitySetting([
            'effective_from' => '2025-04-01',
            'exemption_ceiling' => 2000000.00,
            'days_per_year_first_slab' => 15,
            'divisor_days_per_month' => 26,
            'minimum_vesting_years' => 5,
            'is_active' => true,
        ]);
    }

    public function test_under_five_years_is_not_eligible(): void
    {
        $result = (new GratuityCalculator)->calculate(
            $this->settings(),
            lastDrawnBasicPlusDa: 50000.0,
            dateOfJoining: Carbon::parse('2022-01-01'),
            dateOfExit: Carbon::parse('2025-06-01'), // 3 years 5 months
        );

        $this->assertFalse($result->eligible);
        $this->assertEquals(0.0, $result->grossAmount);
    }

    public function test_death_waives_vesting_requirement(): void
    {
        $result = (new GratuityCalculator)->calculate(
            $this->settings(),
            lastDrawnBasicPlusDa: 50000.0,
            dateOfJoining: Carbon::parse('2024-01-01'),
            dateOfExit: Carbon::parse('2025-06-01'),
            isDeathOrDisablement: true,
        );

        $this->assertTrue($result->eligible);
    }

    public function test_exactly_five_years_is_eligible_and_computed_correctly(): void
    {
        $result = (new GratuityCalculator)->calculate(
            $this->settings(),
            lastDrawnBasicPlusDa: 60000.0,
            dateOfJoining: Carbon::parse('2020-01-01'),
            dateOfExit: Carbon::parse('2025-01-01'), // exactly 5 years
        );

        $this->assertTrue($result->eligible);
        $this->assertEquals(5, $result->completedYears);
        // (15 * 60000 * 5) / 26 = 173076.92
        $this->assertEquals(173076.92, $result->grossAmount);
        $this->assertEquals(173076.92, $result->exemptAmount); // well under the ceiling
        $this->assertEquals(0.0, $result->taxableAmount);
    }

    public function test_seven_months_past_a_full_year_rounds_up(): void
    {
        $result = (new GratuityCalculator)->calculate(
            $this->settings(),
            lastDrawnBasicPlusDa: 60000.0,
            dateOfJoining: Carbon::parse('2018-01-01'),
            dateOfExit: Carbon::parse('2025-08-01'), // 7 years 7 months -> rounds to 8
        );

        $this->assertEquals(8, $result->completedYears);
    }

    public function test_amount_above_exemption_ceiling_is_partly_taxable(): void
    {
        $settings = $this->settings();
        $settings->exemption_ceiling = 100000.00;

        $result = (new GratuityCalculator)->calculate(
            $settings,
            lastDrawnBasicPlusDa: 60000.0,
            dateOfJoining: Carbon::parse('2020-01-01'),
            dateOfExit: Carbon::parse('2025-01-01'),
        );

        $this->assertEquals(173076.92, $result->grossAmount);
        $this->assertEquals(100000.0, $result->exemptAmount);
        $this->assertEquals(73076.92, $result->taxableAmount);
    }
}
