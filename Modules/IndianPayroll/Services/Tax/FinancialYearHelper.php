<?php

namespace Modules\IndianPayroll\Services\Tax;

use Carbon\Carbon;

/**
 * Indian financial year runs April 1 - March 31.
 */
class FinancialYearHelper
{
    public static function forDate(\DateTimeInterface $date): string
    {
        $date = Carbon::parse($date);
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;

        return $startYear.'-'.($startYear + 1);
    }

    public static function startDate(string $financialYear): Carbon
    {
        [$startYear] = explode('-', $financialYear);

        return Carbon::create((int) $startYear, 4, 1)->startOfDay();
    }

    public static function endDate(string $financialYear): Carbon
    {
        [, $endYear] = explode('-', $financialYear);

        return Carbon::create((int) $endYear, 3, 31)->endOfDay();
    }

    /**
     * Number of months from the given date's month through March of its financial year,
     * INCLUSIVE of the given month — used to spread remaining TDS liability.
     */
    public static function remainingMonthsInclusive(\DateTimeInterface $date): int
    {
        $date = Carbon::parse($date);
        $fyEnd = self::endDate(self::forDate($date));

        return $date->diffInMonths($fyEnd) + 1;
    }

    /**
     * How many whole months of the financial year have elapsed BEFORE the given date's month.
     */
    public static function monthsElapsedBefore(\DateTimeInterface $date): int
    {
        $date = Carbon::parse($date);
        $fyStart = self::startDate(self::forDate($date));

        return $fyStart->diffInMonths($date);
    }
}
