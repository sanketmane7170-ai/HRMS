<?php

namespace Modules\IndianPayroll\Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\IndianPayroll\Entities\BankDetail;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructureComponent;
use Modules\IndianPayroll\Entities\EmployeeTaxDeclaration;
use Modules\IndianPayroll\Entities\IncomeTaxSlab;
use Modules\IndianPayroll\Entities\IncomeTaxSurchargeSlab;
use Modules\IndianPayroll\Entities\IpState;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Entities\SalaryStructureTemplate;
use Modules\IndianPayroll\Entities\SalaryStructureTemplateComponent;
use Modules\IndianPayroll\Services\SalaryStructureResolver;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;
use Modules\Leave\Entities\LeaveType;
use Spatie\Permission\Models\Role;

/**
 * Creates 6 named Indian Payroll employees with realistic attendance + leave data
 * from Jan 2026 to current date. Also purges generic seeded employees.
 *
 * Run:  php artisan module:seed IndianPayroll --class=NamedIndianEmployeesSeeder
 */
class NamedIndianEmployeesSeeder extends Seeder
{
    private Carbon $dateOfJoining;
    private Carbon $attendanceStart;
    private Carbon $attendanceEnd;

    /** @var array<string, string> date => leave_type_key */
    private array $leaveSchedule = [];

    public function run(): void
    {
        if (IpState::count() === 0 || SalaryComponent::count() === 0) {
            $this->command?->error('Run `php artisan module:seed IndianPayroll` first — states/components must be seeded.');
            return;
        }

        $this->dateOfJoining  = Carbon::create(2024, 2, 1);
        $this->attendanceStart = Carbon::create(2026, 1, 1);
        $this->attendanceEnd   = Carbon::now()->startOfDay();

        $this->command?->info('Cleaning up generic seeded employees…');
        $this->purgeGenericEmployees();

        $this->ensureCurrentFyTaxSlabs();

        $leaveTypes = $this->ensureLeaveTypes();
        $role       = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $maharashtra = IpState::where('code', 'MH')->firstOrFail();
        $template   = $this->ensureTemplate();
        $resolver   = new SalaryStructureResolver;
        $fy         = FinancialYearHelper::forDate(now());

        [$dept, $designations] = $this->ensureDeptAndDesignations();

        $employees = [
            [
                'name'       => 'Sanket Mane',
                'email'      => 'sanket.mane@company.com',
                'phone'      => '9876543210',
                'gender'     => 'male',
                'annual_ctc' => 1_040_000,
                'designation'=> 'senior',
                'pan_idx'    => 101,
                'bank'       => ['HDFC Bank', 'HDFC'],
                'regime'     => 'new',
            ],
            [
                'name'       => 'Jay Rathod',
                'email'      => 'jay.rathod@company.com',
                'phone'      => '9765432101',
                'gender'     => 'male',
                'annual_ctc' => 960_000,
                'designation'=> 'junior',
                'pan_idx'    => 102,
                'bank'       => ['ICICI Bank', 'ICIC'],
                'regime'     => 'new',
            ],
            [
                'name'       => 'Archik Naikre',
                'email'      => 'archik.naikre@company.com',
                'phone'      => '9654321012',
                'gender'     => 'male',
                'annual_ctc' => 1_120_000,
                'designation'=> 'senior',
                'pan_idx'    => 103,
                'bank'       => ['State Bank of India', 'SBIN'],
                'regime'     => 'old',
            ],
            [
                'name'       => 'Ananya Deshpande',
                'email'      => 'ananya.deshpande@company.com',
                'phone'      => '9543210123',
                'gender'     => 'female',
                'annual_ctc' => 880_000,
                'designation'=> 'junior',
                'pan_idx'    => 104,
                'bank'       => ['Axis Bank', 'UTIB'],
                'regime'     => 'new',
            ],
            [
                'name'       => 'Pratik Rahate',
                'email'      => 'pratik.rahate@company.com',
                'phone'      => '9432101234',
                'gender'     => 'male',
                'annual_ctc' => 1_200_000,
                'designation'=> 'senior',
                'pan_idx'    => 105,
                'bank'       => ['Kotak Mahindra Bank', 'KKBK'],
                'regime'     => 'new',
            ],
            [
                'name'       => 'Roshni Fernandis',
                'email'      => 'roshni.fernandis@company.com',
                'phone'      => '9321012345',
                'gender'     => 'female',
                'annual_ctc' => 800_000,
                'designation'=> 'junior',
                'pan_idx'    => 106,
                'bank'       => ['Punjab National Bank', 'PUNB'],
                'regime'     => 'old',
            ],
        ];

        foreach ($employees as $idx => $emp) {
            DB::transaction(function () use ($idx, $emp, $maharashtra, $template, $resolver, $dept, $designations, $fy, $leaveTypes, $role) {
                if (User::where('email', $emp['email'])->exists()) {
                    $this->command?->warn("Skipping {$emp['name']} — already exists.");
                    return;
                }

                $username = strtolower(str_replace(' ', '-', $emp['name']));

                $user = User::create([
                    'name'               => $emp['name'],
                    'email'              => $emp['email'],
                    'phone'              => $emp['phone'],
                    'password'           => Hash::make('Welcome2026'),
                    'status'             => 'active',
                    'department_id'      => $dept->id,
                    'designation_id'     => $designations[$emp['designation']]->id,
                    'username'           => $username,
                    'employee_id'        => 'IND' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                    'email_verified_at'  => now(),
                ]);

                $user->assignRole($role);

                $monthlyGross  = round($emp['annual_ctc'] / 12, 2);
                $esiApplicable = $monthlyGross <= 21_000;

                EmployeeProfile::create([
                    'user_id'               => $user->id,
                    'pan'                   => $this->makePan($emp['pan_idx']),
                    'aadhaar'               => $this->makeAadhaar($emp['pan_idx']),
                    'uan'                   => $this->makeUan($emp['pan_idx']),
                    'pf_number'             => 'MH/PUN/0056789/' . str_pad((string) $emp['pan_idx'], 3, '0', STR_PAD_LEFT),
                    'esi_number'            => $esiApplicable ? $this->makeEsi($emp['pan_idx']) : null,
                    'pt_enrollment_number'  => 'PTMH' . str_pad((string) $emp['pan_idx'], 6, '0', STR_PAD_LEFT),
                    'state_id'              => $maharashtra->id,
                    'pf_applicable'         => true,
                    'pf_voluntary_above_ceiling' => false,
                    'esi_applicable'        => $esiApplicable,
                    'pt_applicable'         => true,
                    'lwf_applicable'        => true,
                    'date_of_joining'       => $this->dateOfJoining,
                    'gender'                => $emp['gender'],
                ]);

                BankDetail::create([
                    'user_id'            => $user->id,
                    'bank_name'          => $emp['bank'][0],
                    'account_number'     => (string) random_int(100_000_000_000, 999_999_999_999),
                    'ifsc'               => $emp['bank'][1] . '0' . str_pad((string) random_int(0, 99999), 6, '0', STR_PAD_LEFT),
                    'account_type'       => 'savings',
                    'account_holder_name'=> $emp['name'],
                    'is_verified'        => true,
                ]);

                $resolved  = $resolver->resolve($template, $emp['annual_ctc']);
                $structure = EmployeeSalaryStructure::create([
                    'user_id'     => $user->id,
                    'template_id' => $template->id,
                    'annual_ctc'  => $emp['annual_ctc'],
                    'monthly_ctc' => round($emp['annual_ctc'] / 12, 2),
                    'effective_from' => $this->dateOfJoining,
                    'is_active'   => true,
                ]);

                $componentIds = SalaryComponent::pluck('id', 'code');
                foreach ($resolved as $code => $monthlyAmount) {
                    if (! isset($componentIds[$code])) {
                        continue;
                    }
                    EmployeeSalaryStructureComponent::create([
                        'structure_id'       => $structure->id,
                        'salary_component_id'=> $componentIds[$code],
                        'monthly_amount'     => $monthlyAmount,
                        'annual_amount'      => round($monthlyAmount * 12, 2),
                    ]);
                }

                EmployeeTaxDeclaration::create([
                    'user_id'        => $user->id,
                    'financial_year' => $fy,
                    'regime_choice'  => $emp['regime'],
                    'income_from_previous_employer'      => 0,
                    'tds_deducted_by_previous_employer'  => 0,
                ]);

                $this->seedAttendanceAndLeaves($user, $idx, $leaveTypes);

                $this->command?->info(sprintf(
                    'Created: %s | %.1f LPA | %s regime',
                    $emp['name'],
                    $emp['annual_ctc'] / 100_000,
                    $emp['regime']
                ));
            });
        }

        $this->command?->info('Done. 6 Indian employees seeded with attendance + leaves.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Attendance + Leave
    // ─────────────────────────────────────────────────────────────────────────

    private function seedAttendanceAndLeaves(User $user, int $empIdx, array $leaveTypes): void
    {
        $schedule = $this->leaveScheduleFor($empIdx);

        $attendanceRows = [];
        $leaveGroups    = [];   // keyed by "startDate|typeKey" → ['start','end','type','days']

        $current = $this->attendanceStart->copy();
        while ($current->lte($this->attendanceEnd)) {
            $dateStr   = $current->toDateString();
            $isWeekend = $current->isWeekend();

            if ($isWeekend) {
                $attendanceRows[] = $this->attRow($user->id, $dateStr, 'weekend', 0);
            } elseif (isset($schedule[$dateStr])) {
                $attendanceRows[] = $this->attRow($user->id, $dateStr, 'leave', 0);
                // Accumulate consecutive leave days into groups
                $typeKey = $schedule[$dateStr];
                $prev    = $current->copy()->subDay();
                while ($prev->isWeekend()) {
                    $prev->subDay();
                }
                $prevStr = $prev->toDateString();
                $grouped = false;
                foreach ($leaveGroups as &$g) {
                    if ($g['type'] === $typeKey && $g['end'] === $prevStr) {
                        $g['end']  = $dateStr;
                        $g['days'] += 1;
                        $grouped   = true;
                        break;
                    }
                }
                unset($g);
                if (! $grouped) {
                    $leaveGroups[] = ['start' => $dateStr, 'end' => $dateStr, 'type' => $typeKey, 'days' => 1];
                }
            } else {
                [$status, $clockIn, $clockOut, $worked] = $this->randomDayStatus($current, $empIdx);
                $attendanceRows[] = $this->attRow($user->id, $dateStr, $status, $worked, $clockIn, $clockOut);
            }

            $current->addDay();
        }

        // Batch-insert attendance
        foreach (array_chunk($attendanceRows, 200) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }

        // Insert leave records directly (bypass boot to avoid probation check & helper deps)
        $typeMap = [];
        foreach ($leaveTypes as $lt) {
            $key = match (strtolower($lt->name)) {
                'sick'         => 'sick',
                'casual'       => 'casual',
                'earned leave' => 'earned',
                default        => 'casual',
            };
            $typeMap[$key] = $lt->id;
        }

        $now = now()->toDateTimeString();
        foreach ($leaveGroups as $g) {
            $ltId = $typeMap[$g['type']] ?? reset($typeMap);
            DB::table('leaves')->insert([
                'user_id'         => $user->id,
                'leave_type_id'   => $ltId,
                'start_date'      => $g['start'],
                'end_date'        => $g['end'],
                'total_leave_days'=> $g['days'],
                'is_half_day'     => 0,
                'reason'          => $this->randomReason($g['type']),
                'remark'          => null,
                'file_path'       => null,
                'status'          => 'approved',
                'year'            => (int) substr($g['end'], 0, 4),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    /**
     * Returns the pre-planned leave schedule for one employee.
     * Keys = date strings (Y-m-d), values = leave type key ('sick'|'casual'|'earned').
     * Every date here MUST be a weekday between Jan 1 and Jun 17 2026.
     */
    private function leaveScheduleFor(int $empIdx): array
    {
        // 6 employees × 6 months = realistic variety: 2-4 leaves per month
        $plans = [
            // 0 — Sanket Mane
            [
                '2026-01-08' => 'sick',   '2026-01-09' => 'sick',
                '2026-02-16' => 'casual', '2026-02-17' => 'casual',
                '2026-03-25' => 'sick',
                '2026-04-14' => 'earned', '2026-04-15' => 'earned', '2026-04-16' => 'earned',
                '2026-05-06' => 'sick',
                '2026-06-03' => 'casual', '2026-06-04' => 'casual',
            ],
            // 1 — Jay Rathod
            [
                '2026-01-19' => 'sick',   '2026-01-20' => 'sick',
                '2026-02-09' => 'casual',
                '2026-03-02' => 'sick',   '2026-03-03' => 'sick',
                '2026-04-21' => 'earned', '2026-04-22' => 'earned', '2026-04-23' => 'earned',
                '2026-05-20' => 'casual', '2026-05-21' => 'casual',
                '2026-06-10' => 'sick',
            ],
            // 2 — Archik Naikre
            [
                '2026-01-05' => 'sick',   '2026-01-06' => 'sick',
                '2026-02-02' => 'earned', '2026-02-03' => 'earned', '2026-02-04' => 'earned',
                '2026-03-16' => 'casual', '2026-03-17' => 'casual',
                '2026-04-06' => 'sick',   '2026-04-07' => 'sick',
                '2026-05-11' => 'earned', '2026-05-12' => 'earned', '2026-05-13' => 'earned',
                '2026-06-08' => 'casual', '2026-06-09' => 'casual',
            ],
            // 3 — Ananya Deshpande
            [
                '2026-01-27' => 'casual',
                '2026-02-23' => 'sick',   '2026-02-24' => 'sick',   '2026-02-25' => 'sick',
                '2026-03-19' => 'casual', '2026-03-20' => 'casual',
                '2026-04-28' => 'sick',   '2026-04-29' => 'sick',
                '2026-05-04' => 'earned', '2026-05-05' => 'earned', '2026-05-06' => 'earned', '2026-05-07' => 'earned',
                '2026-06-02' => 'casual',
            ],
            // 4 — Pratik Rahate
            [
                '2026-01-12' => 'sick',   '2026-01-13' => 'sick',   '2026-01-14' => 'sick',
                '2026-02-05' => 'casual', '2026-02-06' => 'casual',
                '2026-03-09' => 'earned', '2026-03-10' => 'earned', '2026-03-11' => 'earned', '2026-03-12' => 'earned',
                '2026-04-01' => 'casual', '2026-04-02' => 'casual',
                '2026-05-25' => 'sick',   '2026-05-26' => 'sick',
                '2026-06-11' => 'casual', '2026-06-12' => 'casual',
            ],
            // 5 — Roshni Fernandis
            [
                '2026-01-15' => 'casual', '2026-01-16' => 'casual',
                '2026-02-19' => 'sick',   '2026-02-20' => 'sick',
                '2026-03-05' => 'casual', '2026-03-06' => 'casual',
                '2026-04-08' => 'sick',   '2026-04-09' => 'sick',   '2026-04-10' => 'sick',
                '2026-05-14' => 'earned', '2026-05-15' => 'earned',
                '2026-06-16' => 'sick',   '2026-06-17' => 'sick',
            ],
        ];

        $raw = $plans[$empIdx] ?? [];

        // Drop dates outside the attendance window (safety guard)
        return array_filter($raw, fn ($d) => Carbon::parse($d)->between($this->attendanceStart, $this->attendanceEnd), ARRAY_FILTER_USE_KEY);
    }

    /**
     * Returns [status, clockIn, clockOut, totalWorkedMinutes] for a random weekday.
     * Biased: 82% present, 7% late, 5% halfday, 4% earlyout, 2% absent.
     * Makes each employee's pattern slightly different using empIdx as seed offset.
     */
    private function randomDayStatus(Carbon $date, int $empIdx): array
    {
        // Deterministic-ish seed so the same date always gives the same status per employee
        mt_srand($date->timestamp + $empIdx * 997);
        $rand = mt_rand(1, 100);
        mt_srand(); // restore randomness

        if ($rand <= 82) {
            $minIn  = mt_rand(0, 15);
            $minOut = mt_rand(0, 30);
            return ['present', "09:{$this->pad($minIn)}:00", "18:{$this->pad($minOut)}:00", 540];
        }
        if ($rand <= 89) {
            $minIn  = mt_rand(15, 55);
            $minOut = mt_rand(0, 30);
            return ['late', "10:{$this->pad($minIn)}:00", "19:{$this->pad($minOut)}:00", 480];
        }
        if ($rand <= 93) {
            $minIn = mt_rand(0, 15);
            return ['halfday', "09:{$this->pad($minIn)}:00", '13:30:00', 270];
        }
        if ($rand <= 97) {
            $minIn = mt_rand(0, 10);
            return ['earlyout', "09:{$this->pad($minIn)}:00", '15:00:00', 360];
        }
        return ['absent', null, null, 0];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Infrastructure helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function purgeGenericEmployees(): void
    {
        $patterns = [
            'employee%@workpilot.com',
            'admin@workpilot.com',
            'hr@workpilot.com',
            'manager@workpilot.com',
        ];

        $ids = collect();
        foreach ($patterns as $p) {
            $ids = $ids->merge(User::where('email', 'like', $p)->pluck('id'));
        }
        $ids = $ids->unique()->values()->all();

        if (empty($ids)) {
            $this->command?->info('No generic employees found to purge.');
            return;
        }

        // Disable FK checks so we can delete without enumerating every child table
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $deleted = User::whereIn('id', $ids)->delete();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command?->info("Purged {$deleted} generic employee(s).");
    }

    private function ensureLeaveTypes(): array
    {
        return [
            LeaveType::firstOrCreate(['name' => 'Sick'],         ['days' => 15, 'is_paid' => true, 'type' => 'working']),
            LeaveType::firstOrCreate(['name' => 'Casual'],       ['days' => 12, 'is_paid' => true, 'type' => 'working']),
            LeaveType::firstOrCreate(['name' => 'Earned Leave'], ['days' => 21, 'is_paid' => true, 'type' => 'calendar']),
        ];
    }

    private function ensureDeptAndDesignations(): array
    {
        $dept = Department::firstOrCreate(
            ['name' => 'Engineering'],
            ['code' => 'ENG', 'slug' => 'engineering']
        );

        $junior = Designation::firstOrCreate(
            ['name' => 'Software Engineer', 'department_id' => $dept->id],
            ['code' => 'SWE']
        );

        $senior = Designation::firstOrCreate(
            ['name' => 'Senior Software Engineer', 'department_id' => $dept->id],
            ['code' => 'SSWE']
        );

        return [$dept, ['junior' => $junior, 'senior' => $senior]];
    }

    private function ensureTemplate(): SalaryStructureTemplate
    {
        $template = SalaryStructureTemplate::firstOrCreate(
            ['name' => 'Standard 40% Basic (Demo)'],
            ['description' => 'Basic 40% of CTC, HRA 50% of Basic, ₹1600 Conveyance, balance as Special Allowance.', 'is_active' => true]
        );

        if ($template->components()->count() > 0) {
            return $template;
        }

        $componentIds = SalaryComponent::pluck('id', 'code');
        $rules = [
            [SalaryComponent::CODE_BASIC,             SalaryStructureTemplateComponent::CALC_PERCENTAGE_OF_CTC,   40],
            [SalaryComponent::CODE_HRA,               SalaryStructureTemplateComponent::CALC_PERCENTAGE_OF_BASIC, 50],
            [SalaryComponent::CODE_CONVEYANCE,         SalaryStructureTemplateComponent::CALC_FLAT,                1600],
            [SalaryComponent::CODE_SPECIAL_ALLOWANCE, SalaryStructureTemplateComponent::CALC_REMAINDER_OF_CTC,    0],
        ];

        foreach ($rules as [$code, $calcType, $value]) {
            if (! isset($componentIds[$code])) {
                continue;
            }
            SalaryStructureTemplateComponent::create([
                'template_id'        => $template->id,
                'salary_component_id'=> $componentIds[$code],
                'calculation_type'   => $calcType,
                'value'              => $value,
            ]);
        }

        return $template->refresh();
    }

    private function ensureCurrentFyTaxSlabs(): void
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
                    'regime'         => $regime,
                    'slab_from'      => $slab->slab_from,
                    'slab_to'        => $slab->slab_to,
                    'rate'           => $slab->rate,
                ]);
            }
            foreach (IncomeTaxSurchargeSlab::forRegime($sourceFy, $regime) as $slab) {
                IncomeTaxSurchargeSlab::create([
                    'financial_year'  => $currentFy,
                    'regime'          => $regime,
                    'income_from'     => $slab->income_from,
                    'income_to'       => $slab->income_to,
                    'surcharge_rate'  => $slab->surcharge_rate,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Row builders
    // ─────────────────────────────────────────────────────────────────────────

    private function attRow(int $userId, string $date, string $status, int $worked, ?string $in = null, ?string $out = null): array
    {
        return [
            'user_id'        => $userId,
            'date'           => $date,
            'status'         => $status,
            'clock_in'       => $in,
            'clock_out'      => $out,
            'total_worked'   => $worked,
            'created_by_id'  => $userId,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    private function randomReason(string $type): string
    {
        $pool = match ($type) {
            'sick'   => ['Not feeling well', 'Fever and cold', 'Doctor appointment', 'Medical checkup', 'Migraine'],
            'casual' => ['Personal work', 'Family function', 'Urgent personal matter', 'Bank work', 'Home repair'],
            default  => ['Planned vacation', 'Family trip', 'Rest and relaxation', 'Festival celebration', 'Travel'],
        };
        return $pool[array_rand($pool)];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAN / Aadhaar / UAN / ESI generators
    // ─────────────────────────────────────────────────────────────────────────

    private function makePan(int $i): string
    {
        $letters = '';
        for ($j = 0; $j < 5; $j++) {
            $letters .= chr(65 + ($i * 3 + $j) % 26);
        }
        return $letters . sprintf('%04d', $i) . chr(65 + ($i % 26));
    }

    private function makeAadhaar(int $i): string
    {
        return sprintf('4%03d%04d%04d', $i, random_int(1000, 9999), random_int(1000, 9999));
    }

    private function makeUan(int $i): string
    {
        return sprintf('10%010d', 3_000_000 + $i);
    }

    private function makeEsi(int $i): string
    {
        return sprintf('31%015d', 400_000_000 + $i);
    }

    private function pad(int $n): string
    {
        return str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    }
}
