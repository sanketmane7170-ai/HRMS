<?php

namespace Modules\IndianPayroll\Tests\Unit;

use Modules\IndianPayroll\Entities\EsiSetting;
use Modules\IndianPayroll\Services\Statutory\ESICalculator;
use Tests\TestCase;

class ESICalculatorTest extends TestCase
{
    private function settings(): EsiSetting
    {
        return new EsiSetting([
            'effective_from' => '2025-04-01',
            'employee_rate' => 0.75,
            'employer_rate' => 3.25,
            'wage_threshold' => 21000.00,
            'wage_threshold_disabled' => 25000.00,
            'is_active' => true,
        ]);
    }

    public function test_below_threshold_is_applicable(): void
    {
        $result = (new ESICalculator)->calculate($this->settings(), 18000.0);

        $this->assertTrue($result->applicable);
        $this->assertEquals(135.0, $result->employeeAmount); // 18000 * 0.75%
        $this->assertEquals(585.0, $result->employerAmount); // 18000 * 3.25%
    }

    public function test_above_threshold_is_not_applicable(): void
    {
        $result = (new ESICalculator)->calculate($this->settings(), 25000.0);

        $this->assertFalse($result->applicable);
        $this->assertEquals(0.0, $result->employeeAmount);
    }

    /** The "sticky" contribution-period rule: once covered, stays covered even if a raise crosses the threshold mid-period. */
    public function test_already_covered_this_period_stays_applicable_above_threshold(): void
    {
        $result = (new ESICalculator)->calculate($this->settings(), 23000.0, alreadyCoveredThisPeriod: true);

        $this->assertTrue($result->applicable);
        $this->assertEquals(172.5, $result->employeeAmount); // 23000 * 0.75%
    }

    public function test_disabled_employee_uses_higher_threshold(): void
    {
        $result = (new ESICalculator)->calculate($this->settings(), 23000.0, hasDisability: true);

        $this->assertTrue($result->applicable);
    }
}
