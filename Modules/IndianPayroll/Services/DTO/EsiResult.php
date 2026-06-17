<?php

namespace Modules\IndianPayroll\Services\DTO;

final class EsiResult
{
    public function __construct(
        public readonly bool $applicable,
        public readonly float $employeeAmount,
        public readonly float $employerAmount,
    ) {
    }
}
