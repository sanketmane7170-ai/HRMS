<?php

namespace Modules\IndianPayroll\Tests\Unit;

use Modules\IndianPayroll\Services\Attendance\LossOfPayCalculator;
use Tests\TestCase;

class LossOfPayCalculatorTest extends TestCase
{
    public function test_full_attendance_has_no_loss_of_pay(): void
    {
        $result = (new LossOfPayCalculator)->calculate(30, 30.0);

        $this->assertEquals(0.0, $result->lossOfPayDays);
        $this->assertEquals(1.0, $result->payableFraction);
    }

    public function test_partial_attendance_computes_lop_days(): void
    {
        $result = (new LossOfPayCalculator)->calculate(30, 27.5);

        $this->assertEquals(2.5, $result->lossOfPayDays);
        $this->assertEquals(round(27.5 / 30, 6), $result->payableFraction);
    }

    public function test_paid_days_cannot_exceed_period_length(): void
    {
        // Defensive clamp — a data anomaly upstream should never produce >100% pay.
        $result = (new LossOfPayCalculator)->calculate(30, 35.0);

        $this->assertEquals(30.0, $result->paidDays);
        $this->assertEquals(0.0, $result->lossOfPayDays);
    }
}
