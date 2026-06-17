<?php

namespace Modules\IndianPayroll\Services\Statutory;

use Modules\IndianPayroll\Entities\LwfRule;
use Modules\IndianPayroll\Services\DTO\LwfResult;

/**
 * Labour Welfare Fund — state-specific, and almost always periodic (half-yearly/annual)
 * rather than monthly. `$month` (1-12) decides whether the contribution is actually due
 * in the period being run, per the state's due-month convention passed in $dueMonths.
 */
class LWFCalculator
{
    /**
     * @param  int[]  $dueMonths  Calendar months (1-12) in which this state's LWF falls due.
     *                            e.g. Maharashtra half-yearly: [6, 12] (June & December).
     */
    public function calculate(
        ?int $stateId,
        \DateTimeInterface $asOf,
        int $month,
        array $dueMonths,
        float $grossMonthlyWage,
        bool $applicable = true,
    ): LwfResult {
        if (! $applicable || ! $stateId) {
            return new LwfResult(false, 0.0, 0.0, false);
        }

        $rule = LwfRule::findFor($stateId, $asOf);

        if (! $rule) {
            return new LwfResult(false, 0.0, 0.0, false);
        }

        if ($rule->wage_ceiling !== null && $grossMonthlyWage > (float) $rule->wage_ceiling) {
            return new LwfResult(true, 0.0, 0.0, false);
        }

        $isDueThisMonth = $rule->frequency === 'monthly' || in_array($month, $dueMonths, true);

        if (! $isDueThisMonth) {
            return new LwfResult(true, 0.0, 0.0, false);
        }

        return new LwfResult(true, (float) $rule->employee_contribution, (float) $rule->employer_contribution, true);
    }
}
