<?php

namespace Modules\IndianPayroll\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\LwfRule;
use Modules\IndianPayroll\Services\Statutory\LWFCalculator;
use Tests\TestCase;

class LWFCalculatorTest extends TestCase
{
    use DatabaseTransactions;

    private IpState $maharashtra;

    protected function setUp(): void
    {
        parent::setUp();

        $this->maharashtra = IpState::firstOrCreate(
            ['code' => 'MH'],
            ['name' => 'Maharashtra', 'region_type' => 'state', 'pt_applicable' => true, 'lwf_applicable' => true, 'is_active' => true]
        );

        LwfRule::query()->where('state_id', $this->maharashtra->id)->delete();

        LwfRule::create([
            'state_id' => $this->maharashtra->id,
            'frequency' => 'half_yearly',
            'employee_contribution' => 25.00,
            'employer_contribution' => 75.00,
            'wage_ceiling' => null,
            'effective_from' => '2025-04-01',
            'is_active' => true,
        ]);
    }

    public function test_due_in_a_configured_month(): void
    {
        $result = (new LWFCalculator)->calculate($this->maharashtra->id, now(), month: 6, dueMonths: [6, 12], grossMonthlyWage: 20000.0);

        $this->assertTrue($result->isDueThisMonth);
        $this->assertEquals(25.0, $result->employeeAmount);
        $this->assertEquals(75.0, $result->employerAmount);
    }

    public function test_not_due_outside_configured_months(): void
    {
        $result = (new LWFCalculator)->calculate($this->maharashtra->id, now(), month: 7, dueMonths: [6, 12], grossMonthlyWage: 20000.0);

        $this->assertFalse($result->isDueThisMonth);
        $this->assertEquals(0.0, $result->employeeAmount);
    }

    public function test_above_wage_ceiling_is_exempt(): void
    {
        $rule = LwfRule::where('state_id', $this->maharashtra->id)->first();
        $rule->update(['wage_ceiling' => 15000.00]);

        $result = (new LWFCalculator)->calculate($this->maharashtra->id, now(), month: 6, dueMonths: [6, 12], grossMonthlyWage: 20000.0);

        $this->assertFalse($result->isDueThisMonth);
    }
}
