<?php
namespace App\Exports;

use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Enums\AttendanceStatus;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AttendanceMonthSampleExport implements FromCollection, WithHeadings, WithEvents
{
    protected int $month;
    protected int $year;
    protected $user;

    public function __construct($user, int $month, int $year)
    {
        $this->user  = $user;
        $this->month = $month;
        $this->year  = $year;
    }

    public function headings(): array
    {
        return [
            'employee_id',
            'employee_name',
            'date',
            'status',
            'in_time',
            'out_time',
            'clockout_date',
            'mode',
            'checkout_reason',
            'location',
            'branch_name',
        ];
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $daysInMonth = \Carbon\Carbon::create($this->year, $this->month)->daysInMonth;
        $rows        = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::create($this->year, $this->month, $day)->format('Y-m-d');

            // Fetch existing attendance
            $attendance = Attendance::where('user_id', $this->user->id)
                ->where('date', $date)
                ->first();

            $rows[] = [
                'employee_id'     => $this->user->employee_id ?? $this->user->id,
                'employee_name'   => $this->user->name,
                'date'            => $date,
                'status'          => $attendance?->status?->value ?? 'present',
                'in_time'         => $attendance->clock_in ?? '',
                'out_time'        => $attendance->clock_out ?? '',
                'clockout_date'   => $attendance->clockout_date ?? '',
                'mode'            => '', // optional, can fill based on attendance type
                'checkout_reason' => $attendance->checkout_reason ?? '',
                'location'        => '', // optional, if you track location
                'branch_name'     => $attendance?->branch_name ?? '',

            ];
        }

        return collect($rows);
    }

    // public function registerEvents(): array
    // {
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
    //             $sheet = $event->sheet->getDelegate();

    //             // 🔹 Get branch names
    //             $branches   = Department::pluck('name')->toArray();
    //             $branchList = '"' . implode(',', $branches) . '"';

    //             // 🔹 Apply dropdown to column J (branch_name)
    //             for ($row = 2; $row <= 500; $row++) {
    //                 $validation = new DataValidation();
    //                 $validation->setType(DataValidation::TYPE_LIST);
    //                 $validation->setErrorStyle(DataValidation::STYLE_STOP);
    //                 $validation->setAllowBlank(true);
    //                 $validation->setShowDropDown(true);
    //                 $validation->setFormula1($branchList);
    //                 $validation->setShowErrorMessage(true);
    //                 $validation->setErrorTitle('Invalid Branch');
    //                 $validation->setError('Please select branch from dropdown');

    //                 $sheet->getCell("K{$row}")->setDataValidation($validation);
    //             }

    //             // 🔹 Unlock editable columns
    //             foreach (['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
    //                 $sheet->getStyle("{$col}2:{$col}500")
    //                     ->getProtection()
    //                     ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
    //             }
    //         },
    //     ];
    // }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 🔹 Get branch names
                $branches   = Department::pluck('name')->toArray();
                $branchList = '"' . implode(',', $branches) . '"';

                // 🔹 Get status values from enum
                $statuses   = array_map(fn($status) => $status->value, AttendanceStatus::cases());
                $statusList = '"' . implode(',', $statuses) . '"';

                // 🔹 Apply dropdown to column D (status)
                for ($row = 2; $row <= 500; $row++) {
                    $statusValidation = new DataValidation();
                    $statusValidation->setType(DataValidation::TYPE_LIST);
                    $statusValidation->setErrorStyle(DataValidation::STYLE_STOP);
                    $statusValidation->setAllowBlank(true);
                    $statusValidation->setShowDropDown(true);
                    $statusValidation->setFormula1($statusList);
                    $statusValidation->setShowErrorMessage(true);
                    $statusValidation->setErrorTitle('Invalid Status');
                    $statusValidation->setError('Please select status from dropdown');

                    $sheet->getCell("D{$row}")->setDataValidation($statusValidation);

                    // 🔹 Branch dropdown
                    $branchValidation = new DataValidation();
                    $branchValidation->setType(DataValidation::TYPE_LIST);
                    $branchValidation->setErrorStyle(DataValidation::STYLE_STOP);
                    $branchValidation->setAllowBlank(true);
                    $branchValidation->setShowDropDown(true);
                    $branchValidation->setFormula1($branchList);
                    $branchValidation->setShowErrorMessage(true);
                    $branchValidation->setErrorTitle('Invalid Branch');
                    $branchValidation->setError('Please select branch from dropdown');

                    $sheet->getCell("K{$row}")->setDataValidation($branchValidation);
                }

                // 🔹 Unlock editable columns (include D for status + K for branch)
                foreach (['C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}500")
                        ->getProtection()
                        ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
                }
            },
        ];
    }
}
