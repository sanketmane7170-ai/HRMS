<?php

namespace Modules\IndianPayroll\Services\Statutory;

use Modules\IndianPayroll\Entities\GratuitySetting;
use Modules\IndianPayroll\Services\DTO\GratuityResult;

/**
 * Payment of Gratuity Act, 1972.
 *
 * Formula: (15 x last drawn Basic+DA x completed years of service) / 26.
 * Vesting: minimum 5 years of continuous service, EXCEPT on death or disablement where
 * the vesting requirement is waived entirely.
 * "Completed years" — per the Act, a period of service greater than 6 months past the
 * last completed year is rounded UP to the next full year (e.g. 5 years 7 months = 6 years).
 * Exemption: Section 10(10) caps the tax-free amount at the configured ceiling; any excess
 * is taxable salary income in the year of receipt.
 */
class GratuityCalculator
{
    public function calculate(
        GratuitySetting $settings,
        float $lastDrawnBasicPlusDa,
        \DateTimeInterface $dateOfJoining,
        \DateTimeInterface $dateOfExit,
        bool $isDeathOrDisablement = false,
    ): GratuityResult {
        $completedYears = $this->completedYears($dateOfJoining, $dateOfExit, $settings->divisor_days_per_month);

        $eligible = $isDeathOrDisablement || $completedYears >= $settings->minimum_vesting_years;

        if (! $eligible || $lastDrawnBasicPlusDa <= 0) {
            return new GratuityResult(false, $completedYears, 0.0, 0.0, 0.0);
        }

        $grossAmount = round(
            ($settings->days_per_year_first_slab * $lastDrawnBasicPlusDa * $completedYears) / $settings->divisor_days_per_month,
            2
        );

        $exemptAmount = min($grossAmount, (float) $settings->exemption_ceiling);
        $taxableAmount = max(0.0, round($grossAmount - $exemptAmount, 2));

        return new GratuityResult(true, $completedYears, $grossAmount, $exemptAmount, $taxableAmount);
    }

    /**
     * Years of service rounded per the Act: > 6 months past the last full year rounds up.
     */
    private function completedYears(\DateTimeInterface $from, \DateTimeInterface $to, int $divisorDays): int
    {
        $from = \Carbon\Carbon::parse($from);
        $to = \Carbon\Carbon::parse($to);

        $fullYears = $from->diffInYears($to);
        $remainderMonths = $from->copy()->addYears($fullYears)->diffInMonths($to);

        return $remainderMonths > 6 ? $fullYears + 1 : $fullYears;
    }
}
