<?php

namespace Modules\IndianPayroll\Services\Attendance;

use Modules\IndianPayroll\Services\DTO\LossOfPayResult;

/**
 * Pure arithmetic — given resolved day counts (see AttendanceDataResolver), works out
 * the payable fraction of the period. Kept separate from the resolver so the math itself
 * is trivially unit-testable without touching Attendance/Leave tables.
 */
class LossOfPayCalculator
{
    public function calculate(int $daysInPeriod, float $paidDays): LossOfPayResult
    {
        if ($daysInPeriod <= 0) {
            return new LossOfPayResult(0, 0.0, 0.0, 0.0);
        }

        $paidDays = max(0.0, min($paidDays, $daysInPeriod));
        $lopDays = round($daysInPeriod - $paidDays, 2);

        return new LossOfPayResult(
            daysInPeriod: $daysInPeriod,
            paidDays: $paidDays,
            lossOfPayDays: $lopDays,
            payableFraction: round($paidDays / $daysInPeriod, 6),
        );
    }
}
