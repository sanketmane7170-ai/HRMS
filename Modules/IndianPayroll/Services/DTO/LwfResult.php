<?php

namespace Modules\IndianPayroll\Services\DTO;

final class LwfResult
{
    public function __construct(
        public readonly bool $applicable,
        public readonly float $employeeAmount,
        public readonly float $employerAmount,
        public readonly bool $isDueThisMonth,
    ) {
    }
}
