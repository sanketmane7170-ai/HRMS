<?php

namespace Modules\IndianPayroll\Tests\Unit;

use Modules\IndianPayroll\Entities\PfSetting;
use Modules\IndianPayroll\Services\Statutory\EPFCalculator;
use Tests\TestCase;

class EPFCalculatorTest extends TestCase
{
    private function settings(): PfSetting
    {
        return new PfSetting([
            'effective_from' => '2025-04-01',
            'employee_rate' => 12.00,
            'employer_rate' => 12.00,
            'eps_rate' => 8.33,
            'wage_ceiling' => 15000.00,
            'eps_wage_ceiling' => 15000.00,
            'admin_charges_rate' => 0.50,
            'is_active' => true,
        ]);
    }

    /** Below the PF wage ceiling — both employee and employer contribute on actual wage. */
    public function test_below_ceiling_contributes_on_actual_wage(): void
    {
        $result = (new EPFCalculator)->calculate($this->settings(), 12000.0, voluntaryAboveCeiling: false);

        $this->assertTrue($result->applicable);
        $this->assertEquals(1440.0, $result->employeeAmount); // 12000 * 12%
        $this->assertEquals(1000.0, $result->employerEpsAmount); // 12000 * 8.33% = 999.6 -> rounds to nearest rupee
        $this->assertEquals(440.0, $result->employerEpfAmount); // employer total 1440 - EPS 1000
    }

    /** Above the ceiling, without voluntary opt-in — both sides cap at the statutory ceiling. */
    public function test_above_ceiling_without_voluntary_caps_at_ceiling(): void
    {
        $result = (new EPFCalculator)->calculate($this->settings(), 50000.0, voluntaryAboveCeiling: false);

        $this->assertEquals(15000.0, $result->pfWage);
        $this->assertEquals(1800.0, $result->employeeAmount); // 15000 * 12%
        $this->assertEquals(1250.0, $result->employerEpsAmount); // 15000 * 8.33% = 1249.5 -> rounds to 1250
        $this->assertEquals(550.0, $result->employerEpfAmount); // employer total 1800 - EPS 1250
    }

    /** Voluntary contribution above ceiling — employee side uncapped, EPS stays capped (statutory rule). */
    public function test_voluntary_above_ceiling_uncaps_employee_side_only(): void
    {
        $result = (new EPFCalculator)->calculate($this->settings(), 50000.0, voluntaryAboveCeiling: true);

        $this->assertEquals(50000.0, $result->pfWage);
        $this->assertEquals(6000.0, $result->employeeAmount); // 50000 * 12%
        $this->assertEquals(1250.0, $result->employerEpsAmount); // EPS still capped at 15000 ceiling
    }

    public function test_not_applicable_returns_zeroed_result(): void
    {
        $result = (new EPFCalculator)->calculate($this->settings(), 50000.0, voluntaryAboveCeiling: false, applicable: false);

        $this->assertFalse($result->applicable);
        $this->assertEquals(0.0, $result->employeeAmount);
    }
}
