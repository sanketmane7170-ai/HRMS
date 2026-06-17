<?php

namespace Modules\IndianPayroll\Services\DTO;

final class TaxResult
{
    public function __construct(
        public readonly string $regime,
        public readonly float $grossTaxableIncome,
        public readonly float $totalExemptionsAndDeductions,
        public readonly float $netTaxableIncome,
        public readonly float $taxBeforeRebate,
        public readonly float $rebate87A,
        public readonly float $taxAfterRebate,
        public readonly float $surcharge,
        public readonly float $cess,
        public readonly float $annualTaxLiability,
        public readonly float $tdsAlreadyDeducted,
        public readonly int $remainingMonthsInYear,
        public readonly float $monthlyTds,
    ) {
    }
}
