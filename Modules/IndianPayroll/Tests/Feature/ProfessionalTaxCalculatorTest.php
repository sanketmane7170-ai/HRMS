<?php

namespace Modules\IndianPayroll\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\ProfessionalTaxSlab;
use Modules\IndianPayroll\Services\Statutory\ProfessionalTaxCalculator;
use Tests\TestCase;

class ProfessionalTaxCalculatorTest extends TestCase
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

        ProfessionalTaxSlab::query()->where('state_id', $this->maharashtra->id)->delete();

        $slabs = [[0, 7500, 0], [7500, 10000, 175], [10000, null, 200]];
        foreach ($slabs as [$from, $to, $tax]) {
            ProfessionalTaxSlab::create([
                'state_id' => $this->maharashtra->id,
                'gender' => 'all',
                'salary_from' => $from,
                'salary_to' => $to,
                'monthly_tax' => $tax,
                'frequency' => 'monthly',
                'effective_from' => '2025-04-01',
                'is_active' => true,
            ]);
        }
    }

    public function test_salary_in_lowest_slab_is_exempt(): void
    {
        $result = (new ProfessionalTaxCalculator)->calculate($this->maharashtra->id, 6000.0, 'male', now());

        $this->assertTrue($result->applicable);
        $this->assertEquals(0.0, $result->amount);
    }

    public function test_salary_in_middle_slab(): void
    {
        $result = (new ProfessionalTaxCalculator)->calculate($this->maharashtra->id, 9000.0, 'female', now());

        $this->assertEquals(175.0, $result->amount);
    }

    public function test_salary_in_top_slab(): void
    {
        $result = (new ProfessionalTaxCalculator)->calculate($this->maharashtra->id, 50000.0, 'male', now());

        $this->assertEquals(200.0, $result->amount);
    }

    public function test_state_with_no_slabs_is_not_applicable(): void
    {
        $other = IpState::firstOrCreate(
            ['code' => 'AR'],
            ['name' => 'Arunachal Pradesh', 'region_type' => 'state', 'pt_applicable' => false, 'lwf_applicable' => false, 'is_active' => true]
        );

        $result = (new ProfessionalTaxCalculator)->calculate($other->id, 50000.0, 'male', now());

        $this->assertFalse($result->applicable);
    }
}
