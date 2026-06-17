<?php

namespace Modules\IndianPayroll\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\IndianPayroll\Entities\BankDetail;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructureComponent;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\HraExemptionInput;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Entities\InvestmentDeclaration;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Entities\SalaryStructureTemplate;
use Modules\IndianPayroll\Entities\SalaryStructureTemplateComponent;
use Modules\IndianPayroll\Services\SalaryStructureResolver;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;

/**
 * Demo data generator — NOT part of IndianPayrollDatabaseSeeder's default call list
 * (it must never run automatically against a real tenant). Run explicitly:
 *
 *   php artisan module:seed IndianPayroll --class=DemoIndianEmployeesSeeder
 *
 * Creates 100 fully-populated Indian Payroll employees: User + EmployeeProfile (PAN,
 * Aadhaar, UAN, PF/ESI/PT numbers, state, gender, DOJ) + BankDetail + an assigned CTC
 * salary structure (resolved into real Basic/HRA/Conveyance/Special components) +
 * a current-FY tax declaration (regime choice, and for Old Regime employees, HRA +
 * investment declarations in varying verification states). Requires
 * IndianPayrollDatabaseSeeder (states, salary components, statutory rates) to have
 * already run — every employee references that seeded data, not magic numbers.
 */
class DemoIndianEmployeesSeeder extends Seeder
{
    private const TOTAL_EMPLOYEES = 100;

    private array $maleFirstNames = [
        'Aarav', 'Vivaan', 'Aditya', 'Vihaan', 'Arjun', 'Sai', 'Reyansh', 'Krishna', 'Ishaan', 'Rohan',
        'Aryan', 'Kabir', 'Dhruv', 'Karan', 'Rahul', 'Amit', 'Vikram', 'Rajesh', 'Suresh', 'Manish',
        'Sandeep', 'Nikhil', 'Gaurav', 'Abhishek', 'Pranav', 'Siddharth', 'Akash', 'Varun', 'Harsh', 'Yash',
    ];

    private array $femaleFirstNames = [
        'Saanvi', 'Ananya', 'Diya', 'Aadhya', 'Kiara', 'Myra', 'Anika', 'Ishita', 'Riya', 'Priya',
        'Neha', 'Pooja', 'Sneha', 'Kavya', 'Aishwarya', 'Divya', 'Shreya', 'Swati', 'Meera', 'Nisha',
        'Anjali', 'Deepika', 'Kritika', 'Rashi', 'Tanvi', 'Vidya', 'Sunita', 'Rekha', 'Lakshmi', 'Geeta',
    ];

    private array $lastNames = [
        'Sharma', 'Verma', 'Gupta', 'Kumar', 'Singh', 'Patel', 'Shah', 'Mehta', 'Joshi', 'Desai',
        'Reddy', 'Rao', 'Nair', 'Menon', 'Iyer', 'Pillai', 'Chatterjee', 'Banerjee', 'Mukherjee', 'Das',
        'Agarwal', 'Bansal', 'Malhotra', 'Kapoor', 'Chopra', 'Khanna', 'Bhatt', 'Trivedi', 'Pandey', 'Mishra',
    ];

    private array $banks = [
        ['name' => 'State Bank of India', 'ifsc_prefix' => 'SBIN'],
        ['name' => 'HDFC Bank', 'ifsc_prefix' => 'HDFC'],
        ['name' => 'ICICI Bank', 'ifsc_prefix' => 'ICIC'],
        ['name' => 'Axis Bank', 'ifsc_prefix' => 'UTIB'],
        ['name' => 'Punjab National Bank', 'ifsc_prefix' => 'PUNB'],
        ['name' => 'Bank of Baroda', 'ifsc_prefix' => 'BARB'],
        ['name' => 'Kotak Mahindra Bank', 'ifsc_prefix' => 'KKBK'],
        ['name' => 'IndusInd Bank', 'ifsc_prefix' => 'INDB'],
    ];

    /** Weighted toward Maharashtra/Karnataka since those are the only states with
     *  Professional Tax + LWF slabs seeded by default — this makes the demo data
     *  actually exercise PT/LWF/ESI on a meaningful share of payslips out of the box. */
    private array $stateWeights = ['MH' => 35, 'KA' => 20, 'OTHER' => 45];

    public function run(): void
    {
        if (IpState::count() === 0 || SalaryComponent::count() === 0) {
            $this->command?->error('Run `php artisan module:seed IndianPayroll` first — states/components are not seeded.');

            return;
        }

        $this->ensureCurrentFinancialYearTaxSlabs();
        $template = $this->ensureDemoTemplate();
        $resolver = new SalaryStructureResolver;
        $states = IpState::where('is_active', true)->get();
        $maharashtra = $states->firstWhere('code', 'MH');
        $karnataka = $states->firstWhere('code', 'KA');
        $otherStates = $states->whereNotIn('code', ['MH', 'KA'])->values();
        $financialYear = FinancialYearHelper::forDate(now());

        $department = \App\Models\Department::first();
        $designation = \App\Models\Designation::first();

        for ($i = 1; $i <= self::TOTAL_EMPLOYEES; $i++) {
            DB::transaction(function () use ($i, $template, $resolver, $maharashtra, $karnataka, $otherStates, $financialYear, $department, $designation) {
                $isMale = $i % 2 === 0;
                $firstName = $isMale ? $this->maleFirstNames[$i % count($this->maleFirstNames)] : $this->femaleFirstNames[$i % count($this->femaleFirstNames)];
                $lastName = $this->lastNames[($i * 7) % count($this->lastNames)];
                $name = "{$firstName} {$lastName}";

                $user = User::factory()->create([
                    'name' => $name,
                    'email' => Str::slug($firstName.'.'.$lastName).$i.'@indiandemo.example.com',
                    'phone' => '9'.str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                    'status' => User::STATUS_ACTIVE,
                    'department_id' => $department?->id ?? 1,
                    'designation_id' => $designation?->id ?? 1,
                ]);

                $state = $this->pickState($i, $maharashtra, $karnataka, $otherStates);
                $dateOfJoining = now()->subDays(random_int(60, 6 * 365));
                // ~30% land below the Rs. 21,000 ESI threshold (Rs. 1.8L-2.4L/yr), the
                // rest span junior to senior bands — so the demo data actually exercises
                // ESI applicability instead of every employee landing above it.
                $annualCtc = $i % 10 < 3
                    ? random_int(18, 24) * 10000
                    : random_int(30, 240) * 10000;
                $monthlyGrossEstimate = round($annualCtc / 12, 2);
                $esiApplicable = $monthlyGrossEstimate <= 21000;

                $profile = EmployeeProfile::create([
                    'user_id' => $user->id,
                    'pan' => $this->generatePan($i),
                    'aadhaar' => $this->generateAadhaar($i),
                    'uan' => $this->generateUan($i),
                    'pf_number' => 'MH/BAN/0012345/'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'esi_number' => $esiApplicable ? $this->generateEsiNumber($i) : null,
                    'pt_enrollment_number' => $state->pt_applicable ? 'PT'.$state->code.str_pad((string) $i, 6, '0', STR_PAD_LEFT) : null,
                    'state_id' => $state->id,
                    'pf_applicable' => true,
                    'pf_voluntary_above_ceiling' => false,
                    'esi_applicable' => $esiApplicable,
                    'pt_applicable' => $state->pt_applicable,
                    'lwf_applicable' => $state->lwf_applicable,
                    'date_of_joining' => $dateOfJoining,
                    'gender' => $isMale ? 'male' : 'female',
                ]);

                BankDetail::create([
                    'user_id' => $user->id,
                    'bank_name' => $this->banks[$i % count($this->banks)]['name'],
                    'account_number' => (string) random_int(100000000000, 999999999999),
                    'ifsc' => $this->banks[$i % count($this->banks)]['ifsc_prefix'].'0'.str_pad((string) random_int(0, 99999), 6, '0', STR_PAD_LEFT),
                    'account_type' => $i % 5 === 0 ? 'current' : 'savings',
                    'account_holder_name' => $name,
                    'is_verified' => true,
                ]);

                $resolvedComponents = $resolver->resolve($template, $annualCtc);
                $structure = EmployeeSalaryStructure::create([
                    'user_id' => $user->id,
                    'template_id' => $template->id,
                    'annual_ctc' => $annualCtc,
                    'monthly_ctc' => round($annualCtc / 12, 2),
                    'effective_from' => $dateOfJoining,
                    'is_active' => true,
                ]);

                $componentIds = SalaryComponent::pluck('id', 'code');
                foreach ($resolvedComponents as $code => $monthlyAmount) {
                    if (! isset($componentIds[$code])) {
                        continue;
                    }
                    EmployeeSalaryStructureComponent::create([
                        'structure_id' => $structure->id,
                        'salary_component_id' => $componentIds[$code],
                        'monthly_amount' => $monthlyAmount,
                        'annual_amount' => round($monthlyAmount * 12, 2),
                    ]);
                }

                $this->createTaxDeclaration($user, $profile, $financialYear, $i, $resolvedComponents);
                $this->seedAttendance($user, $dateOfJoining);
            });

            $this->command?->info("Seeded employee {$i}/".self::TOTAL_EMPLOYEES);
        }
    }

    /**
     * Without slabs for the CURRENT financial year, IncomeTaxCalculator silently skips
     * TDS (a deliberate "don't guess" choice in the engine) — so a freshly-installed demo
     * would show zero TDS on every payslip. If the current FY has no slabs yet, clone the
     * seeded FY 2025-2026 rates forward as a starting point purely so the demo is fully
     * functional; this is NOT an assertion about actual future-year tax rates — replace
     * it via Statutory Settings → Income Tax Slabs once the real budget rates are known.
     */
    private function ensureCurrentFinancialYearTaxSlabs(): void
    {
        $currentFy = FinancialYearHelper::forDate(now());

        if (IncomeTaxSlab::where('financial_year', $currentFy)->exists()) {
            return;
        }

        $sourceFy = '2025-2026';

        foreach (['old', 'new'] as $regime) {
            foreach (IncomeTaxSlab::forRegime($sourceFy, $regime) as $slab) {
                IncomeTaxSlab::create([
                    'financial_year' => $currentFy,
                    'regime' => $regime,
                    'slab_from' => $slab->slab_from,
                    'slab_to' => $slab->slab_to,
                    'rate' => $slab->rate,
                ]);
            }

            foreach (IncomeTaxSurchargeSlab::forRegime($sourceFy, $regime) as $slab) {
                IncomeTaxSurchargeSlab::create([
                    'financial_year' => $currentFy,
                    'regime' => $regime,
                    'income_from' => $slab->income_from,
                    'income_to' => $slab->income_to,
                    'surcharge_rate' => $slab->surcharge_rate,
                ]);
            }
        }
    }

    private function ensureDemoTemplate(): SalaryStructureTemplate
    {
        $template = SalaryStructureTemplate::firstOrCreate(
            ['name' => 'Standard 40% Basic (Demo)'],
            ['description' => 'Basic 40% of CTC, HRA 50% of Basic, fixed Conveyance, balance as Special Allowance.', 'is_active' => true]
        );

        if ($template->components()->count() > 0) {
            return $template;
        }

        $componentIds = SalaryComponent::pluck('id', 'code');

        $rules = [
            [SalaryComponent::CODE_BASIC, SalaryStructureTemplateComponent::CALC_PERCENTAGE_OF_CTC, 40],
            [SalaryComponent::CODE_HRA, SalaryStructureTemplateComponent::CALC_PERCENTAGE_OF_BASIC, 50],
            [SalaryComponent::CODE_CONVEYANCE, SalaryStructureTemplateComponent::CALC_FLAT, 1600],
            [SalaryComponent::CODE_SPECIAL_ALLOWANCE, SalaryStructureTemplateComponent::CALC_REMAINDER_OF_CTC, 0],
        ];

        foreach ($rules as [$code, $calcType, $value]) {
            if (! isset($componentIds[$code])) {
                continue;
            }
            SalaryStructureTemplateComponent::create([
                'template_id' => $template->id,
                'salary_component_id' => $componentIds[$code],
                'calculation_type' => $calcType,
                'value' => $value,
            ]);
        }

        return $template->refresh();
    }

    private function pickState(int $i, ?IpState $maharashtra, ?IpState $karnataka, $otherStates): IpState
    {
        $roll = $i % 100;
        if ($roll < $this->stateWeights['MH'] && $maharashtra) {
            return $maharashtra;
        }
        if ($roll < $this->stateWeights['MH'] + $this->stateWeights['KA'] && $karnataka) {
            return $karnataka;
        }

        return $otherStates->isNotEmpty() ? $otherStates[$i % $otherStates->count()] : $maharashtra;
    }

    /**
     * Without attendance records, PayrollRunService's Loss-of-Pay calculation correctly
     * treats every day as unpaid (no record = absent) — so a payroll run against these
     * employees would zero out every payslip. Seed full attendance (present on weekdays,
     * weekend status on Sat/Sun) across a window wide enough to cover whichever month an
     * admin first tries to run payroll for, without backdating before date_of_joining.
     */
    private function seedAttendance(User $user, \Carbon\Carbon $dateOfJoining): void
    {
        $start = $dateOfJoining->copy()->max(now()->copy()->subDays(90));
        $end = now()->copy()->addDays(45);

        $rows = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $isWeekend = $date->isWeekend();

            $rows[] = [
                'date' => $date->toDateString(),
                'status' => $isWeekend ? 'weekend' : 'present',
                'total_worked' => $isWeekend ? 0 : 480, // 8 hours, in minutes
                'user_id' => $user->id,
                'created_by_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }

    /**
     * @param  array<string, float>  $resolvedComponents
     */
    private function createTaxDeclaration(User $user, EmployeeProfile $profile, string $financialYear, int $i, array $resolvedComponents): void
    {
        $regime = $i % 3 === 0 ? 'old' : 'new'; // ~1/3 still on Old Regime, reflecting real adoption split

        $declaration = EmployeeTaxDeclaration::create([
            'user_id' => $user->id,
            'financial_year' => $financialYear,
            'regime_choice' => $regime,
            'income_from_previous_employer' => 0,
            'tds_deducted_by_previous_employer' => 0,
        ]);

        if ($regime !== 'old') {
            return;
        }

        $basicMonthly = $resolvedComponents[SalaryComponent::CODE_BASIC] ?? 0;

        HraExemptionInput::create([
            'declaration_id' => $declaration->id,
            'monthly_rent' => round($basicMonthly * 0.35, 0),
            'is_metro' => $i % 2 === 0,
            'landlord_pan' => $i % 4 === 0 ? $this->generatePan($i + 1000) : null,
            'landlord_name' => $i % 4 === 0 ? 'Landlord '.$i : null,
        ]);

        $sections = ['80C' => 150000, '80D' => 25000, '80CCD1B' => 50000];
        $statuses = ['verified', 'pending', 'rejected'];

        foreach ($sections as $section => $cap) {
            if ($i % 2 === 0 && $section === '80CCD1B') {
                continue; // not everyone invests in NPS — keep the demo data realistic, not maxed-out
            }

            $declared = random_int((int) ($cap * 0.2), $cap);
            $status = $statuses[$i % count($statuses)];

            InvestmentDeclaration::create([
                'declaration_id' => $declaration->id,
                'section_code' => $section,
                'declared_amount' => $declared,
                'proof_path' => $status !== 'pending' ? "demo/proofs/{$user->id}_{$section}.pdf" : null,
                'verified_amount' => $status === 'verified' ? $declared : ($status === 'rejected' ? 0 : null),
                'verified_by' => $status !== 'pending' ? 1 : null,
                'verified_at' => $status !== 'pending' ? now() : null,
                'status' => $status,
            ]);
        }
    }

    private function generatePan(int $i): string
    {
        // Str::random() mixes letters and digits — PAN's first 5 / last 1 positions
        // must be pure A-Z, so pick letters explicitly rather than alnum random.
        $letters = '';
        for ($j = 0; $j < 5; $j++) {
            $letters .= chr(65 + random_int(0, 25));
        }

        return $letters.sprintf('%04d', 1000 + $i).chr(65 + ($i % 26));
    }

    private function generateAadhaar(int $i): string
    {
        return sprintf('2%03d%04d%04d', $i, random_int(0, 9999), random_int(0, 9999));
    }

    private function generateUan(int $i): string
    {
        return sprintf('10%010d', 1000000 + $i);
    }

    private function generateEsiNumber(int $i): string
    {
        return sprintf('31%015d', 100000000 + $i);
    }
}
