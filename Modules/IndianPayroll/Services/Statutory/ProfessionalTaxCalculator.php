<?php

namespace Modules\IndianPayroll\Services\Statutory;

use Modules\IndianPayroll\Entities\ProfessionalTaxSlab;
use Modules\IndianPayroll\Services\DTO\PtResult;

/**
 * Professional Tax — a state subject (each state's PT Act), so the slab itself is always
 * looked up by (state, gross, gender, date) rather than passed as a single config object
 * like PF/ESI. Not every state levies PT — IpState::pt_applicable / EmployeeProfile::pt_applicable
 * gate whether this runs at all.
 */
class ProfessionalTaxCalculator
{
    public function calculate(
        ?int $stateId,
        float $grossMonthlyWage,
        string $gender,
        \DateTimeInterface $asOf,
        bool $applicable = true,
    ): PtResult {
        if (! $applicable || ! $stateId) {
            return new PtResult(false, 0.0);
        }

        $slab = ProfessionalTaxSlab::findFor($stateId, $grossMonthlyWage, $gender, $asOf);

        if (! $slab) {
            return new PtResult(false, 0.0);
        }

        return new PtResult(true, (float) $slab->monthly_tax);
    }
}
