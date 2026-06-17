<?php

namespace Modules\IndianPayroll\Services;

use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Entities\SalaryStructureTemplate;
use Modules\IndianPayroll\Entities\SalaryStructureTemplateComponent;

/**
 * Resolves a CTC template's percentage/flat/remainder rules into actual monthly rupee
 * amounts for one employee's annual CTC. Resolution happens in three passes because
 * percentage_of_basic depends on Basic already being resolved, and remainder_of_ctc
 * must run last (it's whatever's left over).
 */
class SalaryStructureResolver
{
    /**
     * @return array<string, float> component code => monthly amount
     */
    public function resolve(SalaryStructureTemplate $template, float $annualCtc): array
    {
        $monthlyCtc = round($annualCtc / 12, 2);
        $components = $template->components->load('component');

        $resolved = [];

        // Pass 1: flat amounts and percentage-of-CTC (Basic is almost always defined this way).
        foreach ($components as $tc) {
            if (in_array($tc->calculation_type, [SalaryStructureTemplateComponent::CALC_FLAT, SalaryStructureTemplateComponent::CALC_PERCENTAGE_OF_CTC])) {
                $resolved[$tc->component->code] = $tc->calculation_type === SalaryStructureTemplateComponent::CALC_FLAT
                    ? (float) $tc->value
                    : round($monthlyCtc * ((float) $tc->value / 100), 2);
            }
        }

        // Pass 2: percentage-of-Basic (HRA is almost always defined this way).
        $basic = $resolved[SalaryComponent::CODE_BASIC] ?? 0.0;
        foreach ($components as $tc) {
            if ($tc->calculation_type === SalaryStructureTemplateComponent::CALC_PERCENTAGE_OF_BASIC) {
                $resolved[$tc->component->code] = round($basic * ((float) $tc->value / 100), 2);
            }
        }

        // Pass 3: remainder-of-CTC — whatever's left, split evenly if more than one such component.
        $remainderComponents = $components->filter(fn ($tc) => $tc->calculation_type === SalaryStructureTemplateComponent::CALC_REMAINDER_OF_CTC);
        if ($remainderComponents->isNotEmpty()) {
            $allocated = array_sum($resolved);
            $remainder = max(0.0, round($monthlyCtc - $allocated, 2));
            $share = round($remainder / $remainderComponents->count(), 2);
            foreach ($remainderComponents as $tc) {
                $resolved[$tc->component->code] = $share;
            }
        }

        return $resolved;
    }

    /**
     * resolve() clamps an over-allocated remainder to zero rather than going negative —
     * that's the right behavior for the calculation itself, but it means a template
     * whose flat/percentage rules already exceed the CTC fails *silently*: the employee
     * ends up paid more per month than their stated annual CTC implies, with nothing
     * surfacing that mismatch. Call this after resolve() and reject the assignment if
     * it returns a message, rather than saving a structure that doesn't reconcile.
     *
     * @param  array<string, float>  $resolvedComponents
     */
    public function reconciliationError(array $resolvedComponents, float $monthlyCtc, float $toleranceRupees = 1.0): ?string
    {
        $total = array_sum($resolvedComponents);
        $delta = round($total - $monthlyCtc, 2);

        if (abs($delta) <= $toleranceRupees) {
            return null;
        }

        return $delta > 0
            ? sprintf(
                'This template resolves to %s/month, which is %s MORE than the stated CTC of %s/month. '
                .'Its flat amounts and percentages overshoot 100%% of CTC — fix the template before assigning it.',
                number_format($total, 2), number_format($delta, 2), number_format($monthlyCtc, 2)
            )
            : sprintf(
                'This template only resolves to %s/month, which is %s LESS than the stated CTC of %s/month. '
                .'Add a "Remainder of CTC" component to the template (or adjust its percentages) so the full CTC is allocated.',
                number_format($total, 2), number_format(abs($delta), 2), number_format($monthlyCtc, 2)
            );
    }
}
