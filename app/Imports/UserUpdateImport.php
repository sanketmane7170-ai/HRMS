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
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Modules\CompanyDocument\Entities\CompanyDocument;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Spatie\Permission\Models\Role;

class UserUpdateImport implements ToModel, WithStartRow, WithMapping, WithMultipleSheets
{
    use Importable;

    public function sheets(): array
    {
        return [
            0 => $this, 
        ];
    }

    protected $failedRows = [];

    protected $dateFormats = [
        'Y-m-d', // Format used in the CSV file
    ];

    protected array $visaCategoryMap = [
        'Golden Visa'             => 'golden_visa',
        'Company Sponsored Visa'  => 'company_sponsored',
        'Family Sponsored Visa'   => 'family_sponsored',
        'Partner / Investor Visa' => 'partner_investor',
        'Freelance Visa'          => 'freelance',
        'Student Visa'            => 'student',
        'Visit Visa'              => 'visit',
        'Work Permit'             => 'work_permit',
    ];

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    public function map($row): array
    {
        // Parse all date columns
        $dateColumns = [8, 15, 16, 30, 31, 35, 36, 40, 41, 43, 44, 49, 52];
        foreach ($dateColumns as $col) {
            if (isset($row[$col])) {
                $row[$col] = $this->parseDate($row[$col]);
            }
        }
        return $row;
    }

    public function model(array $row)
    {
        if (! array_filter($row)) {
            return null;
        }

        try {
            Log::info("Processing update user row:", ['row_data' => $row]);

            $userId = trim($row[58] ?? '');
            if (empty($userId)) {
                throw new Exception('User ID is missing at column BF');
            }

            $user = User::find($userId);
            if (! $user) {
                throw new Exception("User not found with ID: $userId");
            }

            // Normalization
            $row[11] = strtolower(trim($row[11] ?? ''));
            if (! empty($row[11]) && ! MartialStatus::tryFrom($row[11])) {
                throw new Exception("Invalid marital status: $row[11]");
            }

            $row[13] = ucfirst(strtolower(trim($row[13] ?? '')));
            if (! empty($row[13]) && ! Gender::tryFrom($row[13])) {
                throw new Exception("Invalid gender: $row[13]");
            }

            $role_name        = $row[4];
            $designation_name = $row[5];
            $department_name  = $row[6];
            $division_name    = $row[7];
            $country_name     = $row[12];
            $company_document = $row[55] ?? null;

            $get_id = $this->retreiveIdsBasedOnName($role_name, $designation_name, $department_name, $division_name, $country_name, $company_document);
            Log::info($row);
            $user = User::where('id', $row[58])->first();
            $id   = $user->id ?? null;
            if (! $id) {
                Log::error('User not found for id: ' . $row[48], ['row' => $row]);
            }
            if (! $user) {
                Log::error('User not found for id: ' . $row[48], ['row' => $row]);
            } else {
                if ($user) {
                    $user->update([
                        'name'                => "$row[0] $row[1]",
                        'phone'               => $row[2],
                        'email'               => $row[3],
                        'designation_id'      => $get_id['designation_id'],
                        'department_id'       => $get_id['department_id'],
                        'division_id'         => $get_id['division_id'],
                        'company_document_id' => $get_id['company_document_id'],
                    ]);
                }
                try {
                    if (! empty($row[8])) {
                        if (is_numeric($row[8])) {
                            $dateofbirth = Carbon::createFromFormat('Y-m-d', Date::excelToDateTimeObject($row[8])->format('Y-m-d'));
                        } else {
                            $dateofbirth = Carbon::parse($row[8]);
                        }
                    }
                    $visaCategory = null;

                    if (isset($row[37]) && trim($row[37]) !== '') {
                        $visaCategory = $this->visaCategoryMap[trim($row[37])] ?? null;
                    }

                    $user->profile()->update([
                        'date_of_birth'  => ! empty($row[8]) ? $dateofbirth->toDateString() : null,
                        'personal_email' => $row[9],
                        'personal_phone' => $row[10],
                        'martial_status' => $row[11],
                        'country_id'     => $get_id['country_id'],
                        'gender'         => $row[13],
                        'address'        => $row[14],
                        'visa_category'  => $visaCategory,
                    ]);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
                if (isset($row[45]) && trim($row[45]) !== '') {
                    $employeeId = trim($row[45]);

                    $conflictempid = User::where('employee_id', $employeeId)->where('id', '!=', $id)->first();
                    if ($conflictempid) {
                        throw new Exception("Employee ID  must be unique. Already used by: {$conflictempid->name} ({$conflictempid->employee_id})");
                    }

                    $user->update([
                        'employee_id' => $employeeId,
                    ]);
                }

                if (isset($row[15]) && trim($row[15]) !== '') {
                    if (! empty($row[15])) {
                        if (is_numeric($row[15])) {
                            $joindate = Carbon::createFromFormat('Y-m-d', Date::excelToDateTimeObject($row[15])->format('Y-m-d'));
                        } else {
                            $joindate = Carbon::parse($row[15]);
                        }

                        $joiningDate     = Carbon::parse($joindate);
                        $probationMonths = getSetting('probation_period_month');

                        if ($probationMonths == '1_month') {
                            $probationMonths = 1;
                        } elseif ($probationMonths == '3_month') {
                            $probationMonths = 3;
                        } else {
                            $probationMonths = 6;
                        }

                        $probationdate = $joiningDate->copy()->addMonths($probationMonths)->format('Y-m-d');
                    }
                }

                $excelValue = $row[18];
                if (! empty($excelValue)) {
                    $reportToNamesRaw = array_map('trim', explode(',', $excelValue));
                    $empid            = [];
                    foreach ($reportToNamesRaw as $value) {
                        if (preg_match('/\((.*?)\)/', $value, $matches)) {
                            $empid[] = trim($matches[1]);
                        }
                    }
                    $reportToIds = User::whereIn('employee_id', $empid)
                        ->get()
                        ->pluck('id')
                        ->map(fn($id) => (string) $id)
                        ->toArray();
                } else {
                    $reportToIds = [null];
                }

                $user->workDetail()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'joining_date'       => ($row[15] ?? null) ?: $user->workDetail?->joining_date,
                        'probation_end_date' => ($row[16] ?? null) ?: $user->workDetail?->probation_end_date,
                        'company_name'       => $row[17] ?? $user->workDetail?->company_name,
                        'report_to_ids'      => $reportToIds,
                        'work_week'          => $row[19] ?? $user->workDetail?->work_week,
                        'location'           => $row[20] ?? $user->workDetail?->location,
                        'mol_number'         => $row[48] ?? $user->workDetail?->mol_number,
                        'last_working_day'   => ($row[49] ?? null) ?: $user->workDetail?->last_working_day,
                        'remarks'            => $row[50] ?? $user->workDetail?->remarks,
                        'insurance_number'   => $row[51] ?? $user->workDetail?->insurance_number,
                        'insurance_expiry'   => ($row[52] ?? null) ?: $user->workDetail?->insurance_expiry,
                        'entity'             => $row[56] ?? $user->workDetail?->entity,
                    ]
                );

                // Update Bank Detail
                $user->bankDetail()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'bank_name'      => $row[21] ?? $user->bankDetail?->bank_name,
                        'routing_number' => $row[22] ?? $user->bankDetail?->routing_number,
                        'account_number' => $row[23] ?? $user->bankDetail?->account_number,
                        'iba_number'     => $row[24] ?? $user->bankDetail?->iba_number,
                        'swift_code'     => $row[25] ?? $user->bankDetail?->swift_code,
                    ]
                );

                // Update Salary
                if (isset($row[26])) {
                    $user->salary()->updateOrCreate(['user_id' => $user->id], ['basic' => $row[26]]);
                }

                // Documents
                $this->updateDoc($user, 'passport', $row[27] ?? null, $row[30] ?? null, $row[31] ?? null, $row[28] ?? null, $row[29] ?? null);
                $this->updateDoc($user, 'visa', $row[32] ?? null, $row[35] ?? null, $row[36] ?? null, $row[33] ?? null, $row[34] ?? null);
                $this->updateDoc($user, 'labor_card_no', $row[38] ?? null, $row[40] ?? null, $row[41] ?? null, null, null, $row[39] ?? null);
                $this->updateDoc($user, 'emirates_id', $row[42] ?? null, $row[43] ?? null, $row[44] ?? null);

                if (isset($row[57]) && trim($row[57]) !== '') {
                    $biometric_user_id = trim($row[57]);

                    $conflictempid = User::where('biometric_user_id', $biometric_user_id)->where('id', '!=', $id)->first();
                    if ($conflictempid) {
                        throw new Exception("Biometric User ID must be unique. Already used by: {$conflictempid->name} ({$conflictempid->employee_id})");
                    }

                    $user->update([
                        'biometric_user_id' => $biometric_user_id,
                    ]);
                }

                if (! empty($get_id['role_name'])) {
                    $user->syncRoles($get_id['role_name']);
                }
            }

            return $user;

        } catch (Exception $e) {
            $row['error']       = $e->getMessage();
            $this->failedRows[] = $row;
            return null;
        }
    }

    public function updateDoc($user, $type, $no, $issue, $expiry, $place = null, $country = null, $molNo = null)
    {
        // if (empty($no)) {
        //     return;
        // }

        // $data = [
        //     'serial_number' => $no,
        //     'issue_date'    => $issue,
        //     'expiry_date'   => $expiry,
        //     'original_name' => ucwords(str_replace('_', ' ', $type)),
        //     'path'          => '/uploads/default-document.jpeg',
        // ];
        if ($no) {
            $data['serial_number'] = $no;
        }
        if ($issue) {
            $data['issue_date'] = $issue;
        }
        if ($expiry) {
            $data['expiry_date'] = $expiry;
        }
        if ($type) {
            $data['original_name'] = ucwords(str_replace('_', ' ', $type));
        }
        if ($place) {
            $data['place_of_issue'] = $place;
        }

        if ($country) {
            $data['country_name'] = $country;
        }

        if ($molNo) {
            $data['ministry_of_labor_personal_no'] = $molNo;
        }

        $user->documents()->updateOrCreate(['type' => $type], $data);
    }

    public function retreiveIdsBasedOnName($role_name, $designation_name, $department_name, $division_name, $country_name, $company_doc)
    {
        $res = [
            'designation_id'      => Designation::where('name', trim($designation_name))->pluck('id')->first(),
            'department_id'       => Department::where('name', trim($department_name))->pluck('id')->first(),
            'division_id'         => Division::where('name', trim($division_name))->pluck('id')->first(),
            'country_id'          => Country::where('name', trim($country_name))->pluck('id')->first(),
            'company_document_id' => CompanyDocument::where('legal_trade_name', trim($company_doc))->pluck('id')->first(),
        ];
        $role = Role::where('name', trim($role_name))->first();
        if ($role) {
            $res['role_name'] = $role->name;
            $res['role_id']   = $role->id;
        }
        return $res;
    }

    public function parseDate($value): ?string
    {
        if (empty($value) || $value === 'Please Select') {
            return null;
        }

        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        $value   = trim($value);
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y', 'm-d-Y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }

            } catch (Exception $e) {continue;}
        }
        try {return Carbon::parse($value)->format('Y-m-d');} catch (Exception $e) {return null;}
    }

    public function mapVisaCategory(?string $value): ?string
    {
        if (empty($value) || $value === 'Please Select') {
            return null;
        }

        $map = [
            'Golden Visa'           => 'golden_visa', 'Company Sponsored Visa'       => 'company_sponsored',
            'Family Sponsored Visa' => 'family_sponsored', 'Partner / Investor Visa' => 'partner_investor',
            'Freelance Visa'        => 'freelance', 'Student Visa'                   => 'student', 'Visit Visa' => 'visit', 'Work Permit' => 'work_permit',
        ];
        return $map[$value] ?? strtolower(str_replace(' ', '_', $value));
    }

    public function getFailedRows()
    {return $this->failedRows;}
}
