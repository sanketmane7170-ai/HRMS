<?php

namespace Modules\IndianPayroll\Services\DTO;

final class GratuityResult
{
    public function __construct(
        public readonly bool $eligible,
        public readonly int $completedYears,
        public readonly float $grossAmount,
        public readonly float $exemptAmount,
        public readonly float $taxableAmount,
    ) {
    }
}
