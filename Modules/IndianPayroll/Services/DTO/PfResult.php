<?php

namespace Modules\IndianPayroll\Services\DTO;

final class PfResult
{
    public function __construct(
        public readonly float $pfWage,
        public readonly float $employeeAmount,
        public readonly float $employerEpfAmount,
        public readonly float $employerEpsAmount,
        public readonly bool $applicable,
        public readonly float $adminChargesAmount = 0.0,
        public readonly float $edliChargesAmount = 0.0,
    ) {
    }

    public function employerTotal(): float
    {
        return round(
            $this->employerEpfAmount + $this->employerEpsAmount
            + $this->adminChargesAmount + $this->edliChargesAmount,
            2
        );
    }
}
