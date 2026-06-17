<?php

namespace Modules\IndianPayroll\Services\DTO;

final class LossOfPayResult
{
    public function __construct(
        public readonly int $daysInPeriod,
        public readonly float $paidDays,
        public readonly float $lossOfPayDays,
        public readonly float $payableFraction, // paidDays / daysInPeriod
    ) {
    }
}
