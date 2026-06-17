<?php

namespace Modules\IndianPayroll\Tests\Unit;

use Modules\IndianPayroll\Services\SalaryStructureResolver;
use Tests\TestCase;

class SalaryStructureResolverReconciliationTest extends TestCase
{
    public function test_balanced_components_have_no_reconciliation_error(): void
    {
        $resolver = new SalaryStructureResolver;

        $error = $resolver->reconciliationError(
            ['BASIC' => 20000, 'HRA' => 10000, 'CONVEYANCE' => 1600, 'SPECIAL_ALLOWANCE' => 18400],
            50000.0
        );

        $this->assertNull($error);
    }

    public function test_over_allocated_components_are_flagged(): void
    {
        $resolver = new SalaryStructureResolver;

        $error = $resolver->reconciliationError(['BASIC' => 35000, 'HRA' => 28000], 50000.0);

        $this->assertNotNull($error);
        $this->assertStringContainsString('MORE than the stated CTC', $error);
    }

    public function test_under_allocated_components_are_flagged(): void
    {
        $resolver = new SalaryStructureResolver;

        $error = $resolver->reconciliationError(['BASIC' => 10000], 50000.0);

        $this->assertNotNull($error);
        $this->assertStringContainsString('LESS than the stated CTC', $error);
    }

    public function test_rounding_within_tolerance_is_not_flagged(): void
    {
        $resolver = new SalaryStructureResolver;

        $error = $resolver->reconciliationError(['BASIC' => 49999.50], 50000.0);

        $this->assertNull($error);
    }
}
