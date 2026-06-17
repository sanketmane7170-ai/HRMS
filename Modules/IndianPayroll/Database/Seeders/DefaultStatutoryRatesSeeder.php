<?php

namespace Modules\IndianPayroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\IndianPayroll\Entities\EsiSetting;
use Modules\IndianPayroll\Entities\GratuitySetting;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\LwfRule;
use Modules\IndianPayroll\Entities\PfSetting;
use Modules\IndianPayroll\Entities\ProfessionalTaxSlab;

/**
 * Seeds the rates/slabs in force at module launch (FY 2025-26) as a starting point.
 * All of these are admin-editable afterwards — see Modules/IndianPayroll/Http/Controllers/
 * StatutorySettingController, IncomeTaxSlabController, ProfessionalTaxSlabController, LwfRuleController.
 * Government revisions must be entered as a NEW effective-dated row, never by editing history.
 */
class DefaultStatutoryRatesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPf();
        $this->seedEsi();
        $this->seedGratuity();
        $this->seedIncomeTaxSlabs();
        $this->seedProfessionalTax();
        $this->seedLwf();
    }

    private function seedPf(): void
    {
        PfSetting::firstOrCreate(
            ['effective_from' => '2025-04-01'],
            [
                'employee_rate' => 12.00,
                'employer_rate' => 12.00,
                'eps_rate' => 8.33,
                'wage_ceiling' => 15000.00,
                'eps_wage_ceiling' => 15000.00,
                'admin_charges_rate' => 0.50,
                'is_active' => true,
            ]
        );
    }

    private function seedEsi(): void
    {
        EsiSetting::firstOrCreate(
            ['effective_from' => '2025-04-01'],
            [
                'employee_rate' => 0.75,
                'employer_rate' => 3.25,
                'wage_threshold' => 21000.00,
                'wage_threshold_disabled' => 25000.00,
                'is_active' => true,
            ]
        );
    }

    private function seedGratuity(): void
    {
        GratuitySetting::firstOrCreate(
            ['effective_from' => '2025-04-01'],
            [
                'exemption_ceiling' => 2000000.00,
                'days_per_year_first_slab' => 15,
                'divisor_days_per_month' => 26,
                'minimum_vesting_years' => 5,
                'is_active' => true,
            ]
        );
    }

    private function seedIncomeTaxSlabs(): void
    {
        $fy = '2025-2026';

        // New Regime (default since FY 2023-24)
        $newRegimeSlabs = [
            [0, 400000, 0],
            [400000, 800000, 5],
            [800000, 1200000, 10],
            [1200000, 1600000, 15],
            [1600000, 2000000, 20],
            [2000000, 2400000, 25],
            [2400000, null, 30],
        ];

        foreach ($newRegimeSlabs as [$from, $to, $rate]) {
            IncomeTaxSlab::firstOrCreate([
                'financial_year' => $fy,
                'regime' => 'new',
                'slab_from' => $from,
            ], ['slab_to' => $to, 'rate' => $rate]);
        }

        // Old Regime (unchanged historically)
        $oldRegimeSlabs = [
            [0, 250000, 0],
            [250000, 500000, 5],
            [500000, 1000000, 20],
            [1000000, null, 30],
        ];

        foreach ($oldRegimeSlabs as [$from, $to, $rate]) {
            IncomeTaxSlab::firstOrCreate([
                'financial_year' => $fy,
                'regime' => 'old',
                'slab_from' => $from,
            ], ['slab_to' => $to, 'rate' => $rate]);
        }

        // Surcharge — same structure for both regimes
        $surchargeSlabs = [
            [5000000, 10000000, 10],
            [10000000, 20000000, 15],
            [20000000, 50000000, 25],
            [50000000, null, 25], // New regime caps surcharge at 25% (old regime allows up to 37%, configurable separately if needed)
        ];

        foreach (['old', 'new'] as $regime) {
            foreach ($surchargeSlabs as [$from, $to, $rate]) {
                IncomeTaxSurchargeSlab::firstOrCreate([
                    'financial_year' => $fy,
                    'regime' => $regime,
                    'income_from' => $from,
                ], ['income_to' => $to, 'surcharge_rate' => $rate]);
            }
        }
    }

    private function seedProfessionalTax(): void
    {
        $maharashtra = IpState::where('code', 'MH')->first();
        $karnataka = IpState::where('code', 'KA')->first();

        if ($maharashtra) {
            // [salary_from, salary_to, monthly_tax, february_tax]
            // MH PT Schedule: nil below ₹7.5K, ₹175/mo for ₹7.5K-₹10K, ₹200/mo + ₹300 in Feb for >₹10K.
            // The ₹300 February amount (vs standard ₹200) brings the annual total to ₹2,500 as mandated.
            $slabs = [
                [0,     7500,  0,   null],
                [7500,  10000, 175, null],
                [10000, null,  200, 300],
            ];
            foreach ($slabs as [$from, $to, $tax, $febTax]) {
                ProfessionalTaxSlab::firstOrCreate([
                    'state_id' => $maharashtra->id,
                    'salary_from' => $from,
                    'effective_from' => '2025-04-01',
                ], [
                    'salary_to' => $to,
                    'monthly_tax' => $tax,
                    'february_tax' => $febTax,
                    'gender' => 'all',
                    'frequency' => 'monthly',
                    'is_active' => true,
                ]);
            }
        }

        if ($karnataka) {
            $slabs = [
                [0, 25000, 0],
                [25000, null, 200],
            ];
            foreach ($slabs as [$from, $to, $tax]) {
                ProfessionalTaxSlab::firstOrCreate([
                    'state_id' => $karnataka->id,
                    'salary_from' => $from,
                    'effective_from' => '2025-04-01',
                ], ['salary_to' => $to, 'monthly_tax' => $tax, 'gender' => 'all', 'frequency' => 'monthly', 'is_active' => true]);
            }
        }
    }

    private function seedLwf(): void
    {
        // due_months: calendar months (1-12) when the LWF contribution falls due.
        // monthly frequency → due every month (due_months left null, dueMonths() returns 1-12).
        // half_yearly     → June (6) and December (12) is the most common convention.
        // annual          → December (12) only.
        $rules = [
            // Half-yearly Jun/Dec states
            'MH' => ['frequency' => 'half_yearly', 'due_months' => [6, 12], 'employee' => 25, 'employer' => 75],
            'KA' => ['frequency' => 'half_yearly', 'due_months' => [6, 12], 'employee' => 20, 'employer' => 40],
            'GJ' => ['frequency' => 'half_yearly', 'due_months' => [6, 12], 'employee' => 6,  'employer' => 12],
            'HR' => ['frequency' => 'half_yearly', 'due_months' => [6, 12], 'employee' => 16, 'employer' => 29],
            // Monthly states
            'AP' => ['frequency' => 'monthly',     'due_months' => null,     'employee' => 30, 'employer' => 70],
            'TS' => ['frequency' => 'monthly',     'due_months' => null,     'employee' => 30, 'employer' => 70],
            'TN' => ['frequency' => 'monthly',     'due_months' => null,     'employee' => 10, 'employer' => 20],
            'KL' => ['frequency' => 'monthly',     'due_months' => null,     'employee' => 20, 'employer' => 40],
            // Annual December states
            'WB' => ['frequency' => 'annual',      'due_months' => [12],     'employee' => 3,  'employer' => 0],
            'OD' => ['frequency' => 'annual',      'due_months' => [12],     'employee' => 6,  'employer' => 12],
        ];

        foreach ($rules as $stateCode => $rule) {
            $state = IpState::where('code', $stateCode)->first();
            if (! $state) {
                continue;
            }

            LwfRule::firstOrCreate(
                ['state_id' => $state->id, 'effective_from' => '2025-04-01'],
                [
                    'frequency' => $rule['frequency'],
                    'due_months' => $rule['due_months'],
                    'employee_contribution' => $rule['employee'],
                    'employer_contribution' => $rule['employer'],
                    'wage_ceiling' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
