<?php
namespace App\Imports;

use App\Enums\Gender;
use App\Enums\MartialStatus;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class UserImport implements ToModel, WithStartRow, WithMapping,WithMultipleSheets
{
    use Importable;

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    protected $failedRows = [];

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Pre-parse dates to avoid Carbon errors later
     */
    public function map($row): array
    {
        $row[8] = $this->parseDate($row[8] ?? null);  // DOB
        $row[15] = $this->parseDate($row[15] ?? null); // Joining Date
        $row[16] = $this->parseDate($row[16] ?? null); // Probation End Date
        $row[28] = $this->parseDate($row[28] ?? null); // Passport Issue
        $row[29] = $this->parseDate($row[29] ?? null); // Passport Expiry
        $row[33] = $this->parseDate($row[33] ?? null); // Visa Issue
        $row[34] = $this->parseDate($row[34] ?? null); // Visa Expiry
        $row[37] = $this->parseDate($row[37] ?? null); // Labor Issue
        $row[38] = $this->parseDate($row[38] ?? null); // Labor Expiry
        $row[40] = $this->parseDate($row[40] ?? null); // Emirates Issue
        $row[41] = $this->parseDate($row[41] ?? null); // Emirates Expiry
        $row[47] = $this->parseDate($row[47] ?? null); // Last Working Day
        $row[50] = $this->parseDate($row[50] ?? null); // Insurance Expiry
        return $row;
    }

    public function model(array $row)
    {
        if (!array_filter($row)) {
            return null;
        }

        try {
            Log::info("Processing new user row:", ['row_data' => $row]);
            
            if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) {
                throw new \Exception('Name, phone, or email cannot be empty');
            }

            // Check if phone and email are unique
            if (User::where('phone', trim($row[2]))->exists()) {
                throw new \Exception('Phone number must be unique');
            }

            $validator = Validator::make(['email' => trim($row[3])], [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                throw new \Exception('Invalid email format');
            }

            if (User::where('email', trim($row[3]))->exists()) {
                throw new \Exception('Email must be unique');
            }

            // Validate and Normalize Martial Status
            $row[11] = strtolower(trim($row[11] ?? ''));
            if (!MartialStatus::tryFrom($row[11])) {
                $expectedMartialStatuses = implode(', ', array_column(MartialStatus::cases(), 'value'));
                throw new \Exception("Invalid marital status: '$row[11]'. Expected: $expectedMartialStatuses");
            }

            // Validate and Normalize Gender
            $row[13] = ucfirst(strtolower(trim($row[13] ?? '')));
            if (!Gender::tryFrom($row[13])) {
                $expectedGenders = implode(', ', array_column(Gender::cases(), 'value'));
                throw new \Exception("Invalid gender: '$row[13]'. Expected: $expectedGenders");
            }

            $get_id = $this->retreiveIdsBasedOnName(
                $row[4] ?? null, 
                $row[5] ?? null, 
                $row[6] ?? null, 
                $row[7] ?? null, 
                $row[12] ?? null, 
                $row[53] ?? null
            );
            
            $user = new User();
            $user->name = trim(($row[0] ?? '') . ' ' . ($row[1] ?? ''));
            $user->phone = trim($row[2] ?? '');
            $user->email = trim($row[3] ?? '');
            $user->password = bcrypt("Welcome" . date('Y'));
            $user->designation_id = $get_id['designation_id'];
            $user->department_id = $get_id['department_id'];
            $user->division_id = $get_id['division_id'];
            $user->status = !empty($row[51]) ? strtolower(trim($row[51])) : 'active';
            $user->employee_id = !empty($row[52]) ? trim($row[52]) : $user->employee_id;
            $user->company_document_id = $get_id['company_document_id'] ?? null;
            $user->save();

            // Profile creation
            $user->profile()->create([
                'date_of_birth' => $row[8] ?? null,
                'personal_email' => $row[9] ?? null,
                'personal_phone' => $row[10] ?? null,
                'martial_status' => $row[11] ?? null,
                'country_id' => $get_id['country_id'],
                'gender' => $row[13] ?? null,
                'address' => $row[14] ?? null,
                'visa_designation' => $row[43] ?? null,
                'visa_category' => $this->mapVisaCategory($row[44] ?? null),
                'visa_type' => $row[45] ?? null,
            ]);

            // Work Detail creation
            $user->workDetail()->create([
                'joining_date' => $row[15] ?? null,
                'probation_end_date' => $row[16] ?? null,
                'company_name' => $row[17] ?? null,
                'work_week' => $row[18] ?? null,
                'location' => $row[19] ?? null,
                'entity' => $row[42] ?? null,
                'mol_number' => $row[46] ?? null,
                'last_working_day' => $row[47] ?? null,
                'remarks' => $row[48] ?? null,
                'insurance_number' => $row[49] ?? null,
                'insurance_expiry' => $row[50] ?? null,
            ]);

            // Bank Detail creation
            $user->bankDetail()->create([
                'bank_name' => $row[20] ?? null,
                'account_number' => $row[21] ?? null,
                'iba_number' => $row[22] ?? null,
                'swift_code' => $row[23] ?? null,
            ]);

            // Salary creation
            $user->salary()->create([
                'basic' => isset($row[24]) ? $row[24] : '0',
            ]);

            // Documents
            // Passport
            if (!empty($row[25])) {
                $user->documents()->create([
                    'original_name' => 'Passport',
                    'path' => '/uploads/default-document.jpeg',
                    'serial_number' => $row[25],
                    'issue_date' => $row[28] ?? null,
                    'expiry_date' => $row[29] ?? null,
                    'type' => 'passport',
                    'place_of_issue' => $row[26] ?? null,
                    'country_name' => $row[27] ?? null,
                ]);
            }

            // Visa
            if (!empty($row[30])) {
                $user->documents()->create([
                    'original_name' => 'Visa',
                    'path' => '/uploads/default-document.jpeg',
                    'serial_number' => $row[30],
                    'issue_date' => $row[33] ?? null,
                    'expiry_date' => $row[34] ?? null,
                    'type' => 'visa',
                    'place_of_issue' => $row[31] ?? null,
                    'country_name' => $row[32] ?? null,
                ]);
            }

            // Labor Card
            if (!empty($row[35])) {
                $user->documents()->create([
                    'original_name' => 'Labor Card',
                    'path' => '/uploads/default-document.jpeg',
                    'serial_number' => $row[35],
                    'ministry_of_labor_personal_no' => $row[36] ?? null,
                    'issue_date' => $row[37] ?? null,
                    'expiry_date' => $row[38] ?? null,
                    'type' => 'labor_card_no',
                ]);
            }

            // Emirates ID
            if (!empty($row[39])) {
                $user->documents()->create([
                    'original_name' => 'Emirates ID',
                    'path' => '/uploads/default-document.jpeg',
                    'serial_number' => $row[39],
                    'issue_date' => $row[40] ?? null,
                    'expiry_date' => $row[41] ?? null,
                    'type' => 'emirates_id',
                ]);
            }

            if (!empty($get_id['role_name'])) {
                $user->assignRole($get_id['role_name']);
            }

            return $user;

        } catch (\Exception $e) {
            $row['error'] = $e->getMessage();
            $this->failedRows[] = $row;
            return null;
        }
    }

    private function retreiveIdsBasedOnName($role_name, $designation_name, $department_name, $division_name, $country_name, $company_doc = null)
    {
        $role_name = trim($role_name);
        $designation_name = trim($designation_name) ?: "Employee";
        $department_name = trim($department_name);
        $division_name = trim($division_name);
        $country_name = trim($country_name);

        $role_record = Role::where('name', $role_name)->first();
        $designation_id = Designation::where('name', $designation_name)->pluck('id')->first();
        $department_id = Department::where('name', $department_name)->pluck('id')->first();
        $division_id = Division::where('name', $division_name)->pluck('id')->first();
        $country_id = Country::where('name', $country_name)->pluck('id')->first();
        $company_document_id = null;
        if (!empty($company_doc)) {
            $company_document_id = \Modules\CompanyDocument\Entities\CompanyDocument::where('legal_trade_name', trim($company_doc))->pluck('id')->first();
        }

        return [
            'role_name' => $role_record->name ?? $role_name,
            'role_id' => $role_record->id ?? null,
            'designation_id' => $designation_id ?? '1',
            'department_id' => $department_id,
            'division_id' => $division_id,
            'country_id' => $country_id ?? 103,
            'company_document_id' => $company_document_id,
        ];
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) return null;

        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        $value = trim($value);
        $formats = [
            'd/m/Y',
            'Y-m-d',
            'd-m-Y',
            'm/d/Y',
            'm-d-Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function mapVisaCategory(?string $value): ?string
    {
        if (empty($value)) return null;
        
        $map = [
            'Golden Visa'             => 'golden_visa',
            'Company Sponsored Visa'  => 'company_sponsored',
            'Family Sponsored Visa'   => 'family_sponsored',
            'Partner / Investor Visa' => 'partner_investor',
            'Freelance Visa'          => 'freelance',
            'Student Visa'            => 'student',
            'Visit Visa'              => 'visit',
            'Work Permit'             => 'work_permit',
        ];

        return $map[$value] ?? strtolower(str_replace(' ', '_', $value));
    }

    public function getFailedRows()
    {
        return $this->failedRows;
    }
}
