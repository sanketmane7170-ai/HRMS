<?php

namespace Modules\IndianPayroll\Services\Statutory;

use Modules\IndianPayroll\Entities\EsiSetting;
use Modules\IndianPayroll\Services\DTO\EsiResult;

/**
 * Employees' State Insurance — ESI Act, 1948.
 *
 * Applicable only while gross monthly wage is at/below the wage threshold at the START
 * of the contribution period (April-Sept / Oct-March). Per ESIC rules, once a member is
 * covered, contribution continues for the rest of that 6-month contribution period even
 * if a mid-period raise pushes wages above the threshold — that "sticky" rule is a payroll
 * policy decision the caller (PayrollRunService) must apply by passing $alreadyCoveredThisPeriod;
 * this calculator only decides fresh applicability.
 */
class ESICalculator
{
    public function calculate(
        EsiSetting $settings,
        float $grossMonthlyWage,
        bool $hasDisability = false,
        bool $alreadyCoveredThisPeriod = false,
    ): EsiResult {
        $threshold = $hasDisability ? (float) $settings->wage_threshold_disabled : (float) $settings->wage_threshold;

        $applicable = $alreadyCoveredThisPeriod || $grossMonthlyWage <= $threshold;

        if (! $applicable || $grossMonthlyWage <= 0) {
            return new EsiResult(false, 0.0, 0.0);
        }

        $employeeAmount = round($grossMonthlyWage * ((float) $settings->employee_rate / 100), 2);
        $employerAmount = round($grossMonthlyWage * ((float) $settings->employer_rate / 100), 2);

        return new EsiResult(true, $employeeAmount, $employerAmount);
    }
}
