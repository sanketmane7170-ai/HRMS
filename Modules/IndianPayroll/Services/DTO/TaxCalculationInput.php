<?php

namespace Modules\IndianPayroll\Services\DTO;

final class TaxCalculationInput
{
    public function __construct(
        public readonly string $financialYear,
        public readonly string $regime, // 'old' | 'new'
        public readonly float $annualTaxableSalaryProjected, // Basic+HRA+Conveyance+Special etc., full-year run-rate projection, excludes employer contributions
        public readonly float $annualBasicPlusDa,
        public readonly float $annualHraReceived,
        public readonly float $annualRentPaid,
        public readonly bool $isMetro,
        public readonly float $oldRegimeDeductions, // pre-capped sum of 80C/80CCD1B/80D/80E/80G/80TTA/24B
        public readonly float $incomeFromPreviousEmployer,
        public readonly float $tdsDeductedByPreviousEmployer,
        public readonly float $tdsAlreadyDeductedThisYear,
        public readonly int $remainingMonthsInYear, // including the month being processed
    ) {
    }
}
