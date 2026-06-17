<?php
namespace App\Exports;

use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\CompanyDocument\Entities\CompanyDocument;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Permission\Models\Role;

class UserSampleExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $options = $this->getOptions();
        return [
            new UserSampleSheet($options),
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

        return [
            'roles'           => Role::whereNotIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->pluck('name')->toArray(),
            'designations'    => Designation::pluck('name')->toArray(),
            'departments'     => Department::pluck('name')->toArray(),
            'divisions'       => Division::pluck('name')->toArray(),
            'marital_status'  => ['Single', 'Married', 'Divorced', 'Widow'],
            'countries'       => Country::pluck('name')->toArray(),
            'gender'          => ['Male', 'Female'],
            'visa_categories' => $visa_categories,
            'status'          => ['active', 'in-active', 'resigned', 'terminated'],
            'company_docs'    => CompanyDocument::pluck('legal_trade_name')->toArray(),
        ];
    }
}

class UserSampleSheet extends DefaultValueBinder implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomValueBinder, WithStyles, WithTitle
{
    protected $options;
    protected $row_count = 1000;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function title(): string
    {
        return 'User Import Template';
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        $stringColumns = ['C', 'K', 'V', 'W', 'X', 'Z', 'AE', 'AF', 'AJ', 'AK', 'AN', 'AU', 'BA'];
        if (in_array($cell->getColumn(), $stringColumns) && $cell->getRow() > 1) {
            $cell->setValueExplicit((string)$value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $stringColumns = ['C', 'K', 'V', 'W', 'X', 'Z', 'AE', 'AF', 'AJ', 'AK', 'AN', 'AU', 'BA'];
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
        $rows = [];
        $rows[] = [
            'Sanket', 'User', '9632587410', 'sanket@example.com', 'Please Select', 'Please Select', 'Please Select', 'Please Select',
            '23/02/1990', 'personal@example.com', '74125896301', 'Single', 'India', 'Male', 'Dubai',
            '01/01/2024', '01/07/2024', 'WorkPilot', 'Dubai', 'MOM',
            'HDFC', '1234567890', 'IBAN123', 'SWIFT123', '5000',
            'P123456', 'India', 'India', '01/01/2015', '01/01/2025',
            'V123456', 'UID123', 'UAE', '01/01/2020', '01/01/2023',
            'L123456', '1234567890123', '01/01/2020', '01/01/2023',
            '784199012345678', '01/01/2020', '01/01/2023',
            'MOM', 'Software Engineer', 'Company Sponsored Visa', 'Employment',
            'MOL123456', '31/12/2025', 'Good Employee', 'INS123', '01/01/2024',
            'active', 'EMP001', 'Trade License'
        ];

        for ($i = 0; $i < ($this->row_count - 1); $i++) {
            $rows[] = array_fill(0, count($this->headings()), null);
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'First Name', 'Last Name', 'Phone', 'Email', 'Role', 'Designation', 'Branch', 'Department',
            'Date of Birth', 'Personal Email', 'Personal Phone', 'Marital Status', 'Country', 'Gender', 'Address',
            'Joining Date', 'Probation End Date', 'Company Name', 'Work Week', 'Location',
            'Bank Name', 'Account Number', 'IBAN', 'Swift Code', 'Basic Salary',
            'Passport No', 'Place of Issue', 'Country', 'Issue Date', 'Expiry Date',
            'Visa No', 'Visa UID Number', 'Country', 'Issue Date', 'Expiry Date',
            'Labor Card No', 'MOL Personal No', 'Issue Date', 'Expiry Date',
            'Emirates ID', 'Issue Date', 'Expiry Date',
            'Entity', 'Visa Designation', 'Visa Category', 'Visa Type',
            'MOL Number', 'Last Working Day', 'Remarks',
            'Insurance Number', 'Insurance Expiry',
            'Status', 'Employee ID', 'Company Document'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Define dropdown columns and their matching list in Options sheet
                // Mapping: Column in template => Index in getOptions()
                $dropdowns = [
                    'E' => ['range' => 'roles', 'col' => 'A'],
                    'F' => ['range' => 'designations', 'col' => 'B'],
                    'G' => ['range' => 'departments', 'col' => 'C'],
                    'H' => ['range' => 'divisions', 'col' => 'D'],
                    'L' => ['range' => 'marital_status', 'col' => 'E'],
                    'M' => ['range' => 'countries', 'col' => 'F'],
                    'N' => ['range' => 'gender', 'col' => 'G'],
                    'AB' => ['range' => 'countries', 'col' => 'F'],
                    'AG' => ['range' => 'countries', 'col' => 'F'],
                    'AS' => ['range' => 'visa_categories', 'col' => 'H'],
                    'AZ' => ['range' => 'status', 'col' => 'I'],
                    'BB' => ['range' => 'company_docs', 'col' => 'J'],
                ];

                foreach ($dropdowns as $col => $config) {
                    $count = count($this->options[$config['range']]);
                    if ($count == 0) continue;
                    
                    $optionsCol = $config['col'];
                    $formula = "Options!\${$optionsCol}\$2:\${$optionsCol}\$" . ($count + 1);
                    
                    $validation = $sheet->getCell("{$col}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1($formula);

                    for ($i = 3; $i <= $this->row_count; $i++) {
                        $sheet->getCell("{$col}{$i}")->setDataValidation(clone $validation);
                    }
                }
            },
        ];
    }
}

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
        $rows = [];
        $rows[] = ['Roles', 'Designations', 'Departments', 'Divisions', 'Marital Status', 'Countries', 'Gender', 'Visa Categories', 'Status', 'Company Docs'];
        
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
                $this->options['visa_categories'][$i] ?? null,
                $this->options['status'][$i] ?? null,
                $this->options['company_docs'][$i] ?? null,
            ];
        }

        return collect($rows);
    }
}
