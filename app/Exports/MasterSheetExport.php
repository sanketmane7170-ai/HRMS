<?php

namespace App\Exports;

use App\Models\User;
use App\Enums\Document;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterSheetExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomValueBinder
{
    protected $count = 0;

    public function bindValue(Cell $cell, $value)
    {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        // Columns that should be treated as strings to avoid scientific notation
        // B: Emp Id, G: Personal Phone, V: MOL Number,
        // AA: Bank Account No, AB: Iban No, AC: Routing Code,
        // AJ: Passport No, AO: Visa No, AP: Visa UID Number,
        // AT: Labor Card No, AU: Personal No, AX: Emirates Id, BD: Insurance Number.
        $stringColumns = ['B', 'G', 'V', 'AA', 'AB', 'AC', 'AJ', 'AO', 'AP', 'AT', 'AU', 'AX', 'BD'];
        
        if (in_array($cell->getColumn(), $stringColumns) && $cell->getRow() > 1) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function collection()
    {
        return User::with([
            'profile.country',
            'workDetail.designation',
            'division',
            'department',
            'roles',
            'bankDetail',
            'salary',
            'documents',
            'companyDocument',
            'emergencyContacts',
            'creator'
                ])
            ->where('id', '!=', 1)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->get(); // Updated by Sanket to exclude admin/superadmin
    }

    public function headings(): array
    {
        return [
            'S. No',
            'Emp Id',
            'First Name',
            'Last Name',
            'Gender',
            'Email',
            'Personal Phone',
            'Role',
            'Designation',
            'Department',
            'Branch',
            'Date Of Birth',
            'Marital Status',
            'Nationality',
            'Local Address',
            'Joining Date',
            'Probation End Date',
            'Company Name',
            'Work Week',
            'Work Location',
            'MOL Location',
            'MOL Number',
            'VISA Category',
            'Visa Designation',
            'Visa Type',
            'Bank Name',
            'Bank Account No',
            'Iban No',
            'Routing Code',
            'Total Salary',
            'Basic Salary',
            'Functional Allowance',
            'Transportation Allowance',
            'Housing Allowance',
            'Other Allowances',
            'Passport No',
            'Place Of Issue',
            'Country',
            'Issue Date',
            'Expiry Date',
            'Visa No',
            'Visa UID Number',
            'Country.1',
            'Issue Date.1',
            'Expiry Date.1',
            'Labor Card No',
            'Personal No',
            'Issue Date.2',
            'Expiry Date.2',
            'Emirates Id',
            'Issue Date.3',
            'Expiry Date.3',
            'Emergency Contact',
            'Company Accomodation',
            'Home Country Address',
            'Insurance Number',
            'Insurance Expiry',
            'Profile Created By',
            'Profile Approval Status',
            'Remarks',
            'Employment Status',
            'Last working Day'
        ];
    }

    public function map($user): array
    {
        $this->count++;
        
        // Documents
        $passport = $user->documents->where('type', Document::Passport)->first();
        $visa = $user->documents->where('type', Document::Visa)->first();
        $laborCard = $user->documents->where('type', Document::LaborCard)->first();
        $emiratesId = $user->documents->where('type', Document::EmiratesID)->first();

        // Salary
        $fixed_allowances = isset($user->salary->fixed_allowances) && !empty($user->salary->fixed_allowances) ? json_decode($user->salary->fixed_allowances, true) : [];
        $basic = (float) (optional($user->salary)->basic ?? 0);
        $hra = (float) ($fixed_allowances['housing_allowance'] ?? (optional($user->salary)->hra ?? 0));
        $transport = (float) ($fixed_allowances['transportation_allowance'] ?? (optional($user->salary)->travel_allowance ?? 0));
        $functional = (float) ($fixed_allowances['functional_allowance'] ?? 0);
        $food = (float) ($fixed_allowances['food_allowance'] ?? (optional($user->salary)->food_allowance ?? 0));
        $other_allowance = (float) ($fixed_allowances['other_allowance'] ?? (optional($user->salary)->other_allowance ?? 0));
        $tips = (float) ($fixed_allowances['tips'] ?? 0);
        $totalSalary = $basic + $hra + $transport + $functional + $food + $other_allowance + $tips;

        // Emergency Contact
        $emergencyContact = optional($user->emergencyContacts)->emergency_name;
        if (optional($user->emergencyContacts)->emergency_phone) {
            $emergencyContact .= ' (' . optional($user->emergencyContacts)->emergency_phone . ')';
        }

        return [
            $this->count,
            $user->employee_id,
            $user->first_name,
            $user->last_name,
            optional($user->profile)->gender,
            $user->email,
            optional($user->profile)->personal_phone ?? $user->phone,
            $user->roles->first()->name ?? '',
            optional($user->designation)->name,
            optional($user->division)->name, // Swapped to show Branch under Department column in UI terms
            optional($user->department)->name, // Swapped to show Department under Branch column in UI terms
            optional($user->profile)->date_of_birth ? $user->profile->date_of_birth->format('d/m/Y') : '',
            optional($user->profile)->martial_status?->value ?? optional($user->profile)->martial_status,
            optional(optional($user->profile)->country)->name,
            optional($user->profile)->address,
            optional($user->workDetail)->joining_date ? $user->workDetail->joining_date->format('d/m/Y') : '',
            optional($user->workDetail)->probation_end_date ? $user->workDetail->probation_end_date->format('d/m/Y') : '',
            optional($user->workDetail)->company_name,
            optional($user->workDetail)->work_week,
            optional($user->workDetail)->location,
            optional($user->companyDocument)->legal_trade_name,
            optional($user->workDetail)->mol_number,
            optional($user->profile)->visa_category,
            optional($user->profile)->visa_designation,
            optional($user->profile)->visa_type,
            optional($user->bankDetail)->bank_name,
            optional($user->bankDetail)->account_number,
            optional($user->bankDetail)->iba_number,
            optional($user->bankDetail)->routing_number,
            $totalSalary,
            $basic,
            $functional, // Functional Allowance
            $transport, // Transportation Allowance
            $hra, // Housing Allowance
            $other_allowance, // Other Allowances
            // Passport
            optional($passport)->serial_number,
            optional($passport)->place_of_issue,
            optional($passport)->country_name,
            optional($passport)->issue_date ? \Carbon\Carbon::parse($passport->issue_date)->format('d/m/Y') : '',
            optional($passport)->expiry_date ? \Carbon\Carbon::parse($passport->expiry_date)->format('d/m/Y') : '',
            // Visa
            optional($visa)->serial_number,
            optional($visa)->place_of_issue,
            optional($visa)->country_name,
            optional($visa)->issue_date ? \Carbon\Carbon::parse($visa->issue_date)->format('d/m/Y') : '',
            optional($visa)->expiry_date ? \Carbon\Carbon::parse($visa->expiry_date)->format('d/m/Y') : '',
            // Labor Card
            optional($laborCard)->serial_number,
            (string)optional($laborCard)->ministry_of_labor_personal_no, // Personal No
            optional($laborCard)->issue_date ? \Carbon\Carbon::parse($laborCard->issue_date)->format('d/m/Y') : '',
            optional($laborCard)->expiry_date ? \Carbon\Carbon::parse($laborCard->expiry_date)->format('d/m/Y') : '',
            // Emirates ID
            optional($emiratesId)->serial_number,
            optional($emiratesId)->issue_date ? \Carbon\Carbon::parse($emiratesId->issue_date)->format('d/m/Y') : '',
            optional($emiratesId)->expiry_date ? \Carbon\Carbon::parse($emiratesId->expiry_date)->format('d/m/Y') : '',
            
            $emergencyContact,
            optional($user->workDetail)->company_accommodation ? 'Yes' : 'No',
            optional($user->emergencyContacts)->emergency_home_address,
            optional($user->workDetail)->insurance_number,
            optional($user->workDetail)->insurance_expiry ? $user->workDetail->insurance_expiry->format('d/m/Y') : '',
            optional($user->creator)->name, // Profile Created By
            optional($user->workDetail)->approved_first_level ? 'Approved' : 'Pending',
            optional($user->workDetail)->remarks,
            $user->status,
            optional($user->workDetail)->last_working_day ? $user->workDetail->last_working_day->format('d/m/Y') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        // Columns: Emp Id (B), Personal Phone (G), MOL Number (V), Bank Account No (AA), Iban No (AB), Routing Code (AC),
        // Passport No (AJ), Visa No (AO), Visa UID Number (AP), Labor Card No (AT), Personal No (AU), Emirates Id (AX), Insurance Number (BD).
        $stringColumns = ['B', 'G', 'V', 'AA', 'AB', 'AC', 'AJ', 'AO', 'AP', 'AT', 'AU', 'AX', 'BD'];
        foreach ($stringColumns as $col) {
            $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
