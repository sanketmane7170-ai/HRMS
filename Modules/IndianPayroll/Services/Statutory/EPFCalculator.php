<?php

namespace Modules\IndianPayroll\Services\Statutory;

use Modules\IndianPayroll\Entities\PfSetting;
use Modules\IndianPayroll\Services\DTO\PfResult;

/**
 * Employees' Provident Fund — EPF & MP Act, 1952.
 *
 * Employee contributes `employee_rate`% of PF wage (Basic + DA, statutorily capped at
 * `wage_ceiling` unless the employee has opted for voluntary contribution above the ceiling).
 * Employer's `employer_rate`% splits into EPS (capped at `eps_wage_ceiling`) and the
 * remainder into EPF — this split does NOT change the employee's own deduction.
 */
class EPFCalculator
{
    public function calculate(PfSetting $settings, float $actualPfWage, bool $voluntaryAboveCeiling, bool $applicable = true): PfResult
    {
        if (! $applicable || $actualPfWage <= 0) {
            return new PfResult(0.0, 0.0, 0.0, 0.0, false);
        }

        $employeePfWage = $voluntaryAboveCeiling
            ? $actualPfWage
            : min($actualPfWage, (float) $settings->wage_ceiling);

        $epsWage = min($actualPfWage, (float) $settings->eps_wage_ceiling);

        $employeeAmount = $this->roundRupee($employeePfWage * ((float) $settings->employee_rate / 100));

        $employerEpsAmount = $this->roundRupee($epsWage * ((float) $settings->eps_rate / 100));

        // Employer's total statutory outlay is employer_rate% of the (capped, unless voluntary) wage;
        // EPS is carved out of that total, the remainder goes to the employee's EPF account.
        $employerWage = $voluntaryAboveCeiling ? $actualPfWage : min($actualPfWage, (float) $settings->wage_ceiling);
        $employerTotal = $this->roundRupee($employerWage * ((float) $settings->employer_rate / 100));
        $employerEpfAmount = round($employerTotal - $employerEpsAmount, 2);

        return new PfResult(
            pfWage: $employeePfWage,
            employeeAmount: $employeeAmount,
            employerEpfAmount: max(0.0, $employerEpfAmount),
            employerEpsAmount: $employerEpsAmount,
            applicable: true,
        );
    }

    /**
     * EPFO rounds contributions to the nearest rupee per member, per contribution head.
     */
    private function roundRupee(float $amount): float
    {
        return (float) round($amount);
    }
}
