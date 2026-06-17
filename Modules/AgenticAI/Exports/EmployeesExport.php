<?php

namespace Modules\AgenticAI\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Department',
            'Designation',
            'Joining Date',
            'Status'
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->employee_id ?? 'N/A',
            $employee->name,
            $employee->email,
            $employee->phone ?? 'N/A',
            optional($employee->department)->name ?? 'N/A',
            optional($employee->designation)->name ?? 'N/A',
            $employee->workDetail && $employee->workDetail->joining_date 
                ? \Carbon\Carbon::parse($employee->workDetail->joining_date)->format('Y-m-d') 
                : 'N/A',
            $employee->status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
