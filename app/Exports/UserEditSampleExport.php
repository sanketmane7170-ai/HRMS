<?php
namespace App\Exports;

use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\CompanyDocument\Entities\CompanyDocument;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Permission\Models\Role;

class UserEditSampleExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $options = $this->getOptions();
        return [
            new UserEditSampleSheet($options),
            new OptionsSheet($options),
        ];
    }

    protected function getOptions()
    {
        $visa_categories = [
            'Golden Visa',
            'Company Sponsored Visa',
            'Family Sponsored Visa',
            'Partner / Investor Visa',
            'Freelance Visa',
            'Student Visa',
            'Visit Visa',
            'Work Permit',
        ];

        $reportTo = User::query()
            ->select('name', 'employee_id')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->get()
            ->map(function ($user) {
                return "{$user->name} ({$user->employee_id})";
            })
            ->toArray();

        return [
            'roles'           => Role::whereNotIn('id', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->pluck('name')->toArray(),
            'designations'    => Designation::pluck('name')->toArray(),
            'departments'     => Department::pluck('name')->toArray(),
            'divisions'       => Division::pluck('name')->toArray(),
            'marital_status'  => ['Single', 'Married', 'Divorced', 'Widow'],
            'countries'       => Country::pluck('name')->toArray(),
            'gender'          => ['Male', 'Female'],
            'managers'        => $reportTo,
            'visa_categories' => $visa_categories,
            'status'          => ['active', 'in-active', 'resigned', 'terminated'],
            'company_docs'    => CompanyDocument::pluck('legal_trade_name')->toArray(),
        ];
    }
}

class UserEditSampleSheet extends DefaultValueBinder implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomValueBinder, WithStyles, WithTitle
{
    protected $options;
    protected $row_count = 1000;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function title(): string
    {
        return 'User Update Template';
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        $stringColumns = ['C', 'K', 'X', 'Y', 'Z', 'AB', 'AG', 'AH', 'AM', 'AN', 'AQ', 'AW', 'BC'];
        if (in_array($cell->getColumn(), $stringColumns) && $cell->getRow() > 1) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow       = $sheet->getHighestRow();
        $stringColumns = ['C', 'K', 'X', 'Y', 'Z', 'AB', 'AG', 'AH', 'AM', 'AN', 'AQ', 'AW', 'BC'];
        foreach ($stringColumns as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->with(['profile', 'workDetail', 'bankDetail', 'salary', 'documents'])
            ->get();

        $query = [];

        foreach ($users as $user) {
            $nameParts = explode(' ', trim($user->name));
            if (count($nameParts) >= 2) {
                $lastName  = array_pop($nameParts);
                $firstName = implode(' ', $nameParts);
            } else {
                $firstName = $user->name;
                $lastName  = '';
            }

            $currentRole = $user->getCurrentRole();
            $get_id      = $this->retreiveIdsBasedOnName(
                $currentRole?->id,
                $user->designation_id,
                $user->department_id,
                $user->division_id,
                optional($user->profile)->country_id,
                $user->company_document_id
            );

            $reportToNames = 'Please Select';
            if (! empty($user->workDetail->report_to_ids)) {
                $reportToIds = is_array($user->workDetail->report_to_ids)
                    ? $user->workDetail->report_to_ids
                    : explode(',', $user->workDetail->report_to_ids);

                $reportToNames = User::whereIn('id', $reportToIds)
                    ->get()
                    ->map(function ($u) {
                        return "{$u->name} ({$u->employee_id})";
                    })
                    ->implode(', ');
            }

            $documents = $user->documents()
                ->whereIn('type', ['passport', 'visa', 'emirates_id', 'labor_card_no', 'labor_card'])
                ->get()
                ->groupBy('type');

            $passport = $documents->get('passport')?->first();
            $visa     = $documents->get('visa')?->first();
            $emirates = $documents->get('emirates_id')?->first();
            $labor    = $documents->get('labor_card_no')?->first() ?? $documents->get('labor_card')?->first();

            $query[] = [
                "first_name"        => $firstName,
                "last_name"         => $lastName,
                "phone"             => $user->phone,
                "email"             => $user->email,
                "role"              => $get_id['role_id'] ?? 'Please Select',
                "designation"       => $get_id['designation_id'] ?? 'Please Select',
                "branch"            => $get_id['department_id'] ?? 'Please Select',
                "department"        => $get_id['division_id'] ?? 'Please Select',
                "dob"               => optional($user->profile)->date_of_birth ? $user->profile->date_of_birth->format('d/m/Y') : '',
                "personal_email"    => $user->profile->personal_email ?? '',
                "personal_phone"    => $user->profile->personal_phone ?? '',
                "marry"             => ($user->profile->martial_status?->name ?? $user->profile?->martial_status) ?: 'Please Select',
                "country"           => $get_id['country_id'] ?? 'Please Select',
                "gender"            => $user->profile->gender ?? 'Please Select',
                "address"           => $user->profile->address ?? '',
                "joining"           => optional($user->workDetail)->joining_date ? $user->workDetail->joining_date->format('d/m/Y') : '',
                "probation"         => optional($user->workDetail)->probation_end_date ? $user->workDetail->probation_end_date->format('d/m/Y') : '',
                "company"           => $user->workDetail->company_name ?? '',
                "manager"           => $reportToNames,
                "work_week"         => $user->workDetail->work_week ?? '',
                "location"          => $user->workDetail->location ?? '',
                "bank"              => optional($user->bankDetail)->bank_name ?? '',
                "routing"           => optional($user->bankDetail)->routing_number ?? '',
                "acc_no"            => optional($user->bankDetail)->account_number ?? '',
                "iban"              => optional($user->bankDetail)->iba_number ?? '',
                "swift"             => optional($user->bankDetail)->swift_code ?? '',
                "salary"            => optional($user->salary)->basic ?? '',
                "pass_no"           => $passport->serial_number ?? '',
                "pass_place"        => $passport->place_of_issue ?? '',
                "pass_country"      => $passport->country_name ?? '',
                "pass_issue"        => optional($passport)->issue_date ? Carbon::parse($passport->issue_date)->format('d/m/Y') : '',
                "pass_expiry"       => optional($passport)->expiry_date ? Carbon::parse($passport->expiry_date)->format('d/m/Y') : '',
                "visa_no"           => $visa->serial_number ?? '',
                "visa_uid"          => $visa->place_of_issue ?? '',
                "visa_country"      => $visa->country_name ?? '',
                "visa_issue"        => optional($visa)->issue_date ? Carbon::parse($visa->issue_date)->format('d/m/Y') : '',
                "visa_expiry"       => optional($visa)->expiry_date ? Carbon::parse($visa->expiry_date)->format('d/m/Y') : '',
                "visa_cat"          => $user->profile->visa_category ?? '',
                "labor_no"          => $labor->serial_number ?? '',
                "labor_mol_no"      => (string) ($labor->ministry_of_labor_personal_no ?? ''),
                "labor_issue"       => optional($labor)->issue_date ? Carbon::parse($labor->issue_date)->format('d/m/Y') : '',
                "labor_expiry"      => optional($labor)->expiry_date ? Carbon::parse($labor->expiry_date)->format('d/m/Y') : '',
                "eid_no"            => $emirates->serial_number ?? '',
                "eid_issue"         => optional($emirates)->issue_date ? Carbon::parse($emirates->issue_date)->format('d/m/Y') : '',
                "eid_expiry"        => optional($emirates)->expiry_date ? Carbon::parse($emirates->expiry_date)->format('d/m/Y') : '',
                "v_desig"           => $user->profile->visa_designation ?? '',
                "v_cat2"            => $user->profile->visa_category ?? '',
                "v_type"            => $user->profile->visa_type ?? '',
                "mol_id"            => $user->workDetail->mol_number ?? '',
                "lwd"               => optional($user->workDetail)->last_working_day ? $user->workDetail->last_working_day->format('d/m/Y') : '',
                "remarks"           => $user->workDetail->remarks ?? '',
                "ins_no"            => $user->workDetail->insurance_number ?? '',
                "ins_expiry"        => optional($user->workDetail)->insurance_expiry ? $user->workDetail->insurance_expiry->format('d/m/Y') : '',
                "status"            => $user->status ?? '',
                "emp_id"            => $user->employee_id,
                "comp_doc"          => $get_id['company_document_id'] ?? 'Please Select',
                "entity"            => optional($user->workDetail)->entity ?? '',
                "biometric_user_id" => $user->biometric_user_id ?? '',
                "id"                => $user->id,
            ];
        }

        $this->row_count = max(1000, count($query) + 1);

        return collect($query);
    }

    public function retreiveIdsBasedOnName($role_id, $designation_id, $department_id, $division_id, $country_id, $company_document_id)
    {
        return [
            'role_id'             => $role_id ? Role::where('id', $role_id)->pluck('name')->first() : null,
            'designation_id'      => $designation_id ? Designation::where('id', $designation_id)->pluck('name')->first() : null,
            'department_id'       => $department_id ? Department::where('id', $department_id)->pluck('name')->first() : null,
            'division_id'         => $division_id ? Division::where('id', $division_id)->pluck('name')->first() : null,
            'country_id'          => $country_id ? Country::where('id', $country_id)->pluck('name')->first() : null,
            'company_document_id' => $company_document_id ? CompanyDocument::where('id', $company_document_id)->pluck('legal_trade_name')->first() : null,
        ];
    }

    public function headings(): array
    {
        return [
            'First Name', 'Last Name', 'Phone', 'Email', 'Role', 'Designation', 'Branch', 'Department',
            'Date of Birth', 'Personal Email', 'Personal Phone', 'Marital Status', 'Country', 'Gender', 'Address',
            'Joining Date', 'Probation End Date', 'Company Name', 'Reporting Manager', 'Work Week', 'Location',
            'Bank Name', 'Routing Number', 'Account Number', 'IBAN', 'Swift Code', 'Basic Salary',
            'Passport No', 'Place of Issue', 'Country', 'Issue Date', 'Expiry Date',
            'Visa No', 'Visa UID Number', 'Country', 'Issue Date', 'Expiry Date', 'Visa Category',
            'Labor Card No', 'MOL Personal No', 'Issue Date', 'Expiry Date',
            'Emirates ID', 'Issue Date', 'Expiry Date',
            'Visa Designation', 'Visa Category', 'Visa Type',
            'MOL Number', 'Last Working Day', 'Remarks',
            'Insurance Number', 'Insurance Expiry', 'Status',
            'Employee ID', 'Company Document', 'Entity', 'Biometric User ID', 'ID',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle($sheet->calculateWorksheetDimension())
                    ->getProtection()
                    ->setLocked(Protection::PROTECTION_UNPROTECTED);

                $dropdowns = [
                    'E'  => ['range' => 'roles', 'col' => 'A'],
                    'F'  => ['range' => 'designations', 'col' => 'B'],
                    'G'  => ['range' => 'departments', 'col' => 'C'],
                    'H'  => ['range' => 'divisions', 'col' => 'D'],
                    'L'  => ['range' => 'marital_status', 'col' => 'E'],
                    'M'  => ['range' => 'countries', 'col' => 'F'],
                    'N'  => ['range' => 'gender', 'col' => 'G'],
                    'S'  => ['range' => 'managers', 'col' => 'H'],
                    'AD' => ['range' => 'countries', 'col' => 'F'],
                    'AI' => ['range' => 'countries', 'col' => 'F'],
                    'AL' => ['range' => 'visa_categories', 'col' => 'I'],
                    'AU' => ['range' => 'visa_categories', 'col' => 'I'],
                    'BB' => ['range' => 'status', 'col' => 'J'],
                    'BD' => ['range' => 'company_docs', 'col' => 'K'],
                ];

                foreach ($dropdowns as $col => $config) {
                    $count = count($this->options[$config['range']]);
                    if ($count == 0) {
                        continue;
                    }

                    $optionsCol = $config['col'];
                    $formula    = "Options!\${$optionsCol}\$2:\${$optionsCol}\$" . ($count + 1);

                    $validation = $event->sheet->getCell("{$col}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1($formula);

                    for ($i = 3; $i <= $this->row_count; $i++) {
                        $event->sheet->getCell("{$col}{$i}")->setDataValidation(clone $validation);
                    }
                }
            },
        ];
    }
}

// OptionsSheet class is identical, but I can share it or redefine it.
// I'll redefine it to include Managers column (H).

class OptionsSheet implements FromCollection, WithTitle
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function title(): string
    {
        return 'Options';
    }

    public function collection()
    {
        $rows   = [];
        $rows[] = ['Roles', 'Designations', 'Departments', 'Divisions', 'Marital Status', 'Countries', 'Gender', 'Managers', 'Visa Categories', 'Status', 'Company Docs'];

        $max = 0;
        foreach ($this->options as $list) {
            $max = max($max, count($list));
        }

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $this->options['roles'][$i] ?? null,
                $this->options['designations'][$i] ?? null,
                $this->options['departments'][$i] ?? null,
                $this->options['divisions'][$i] ?? null,
                $this->options['marital_status'][$i] ?? null,
                $this->options['countries'][$i] ?? null,
                $this->options['gender'][$i] ?? null,
                $this->options['managers'][$i] ?? null,
                $this->options['visa_categories'][$i] ?? null,
                $this->options['status'][$i] ?? null,
                $this->options['company_docs'][$i] ?? null,
            ];
        }

        return collect($rows);
    }
}
