<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Department;
use App\Models\Division;
use App\Models\Designation;
use App\Models\Country;
use App\Enums\Document;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Modules\CompanyDocument\Entities\CompanyDocument;
use Carbon\CarbonPeriod;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Entities\Attendance;

class UserAttendanceSampleExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $users;
    protected $statuses;

    public function __construct($startDate, $endDate, $users)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->users     = $users;

        // ✅ Get attendance statuses (names or values as needed)
        $this->statuses = collect(AttendanceStatus::cases())->pluck('name')->toArray();
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->users as $user) {
            $row = [$user->name];
            $row = $user->employee_id ? array_merge($row, [$user->employee_id]) : array_merge($row, ['N/A']);
            // Add empty cells for each date
            $period = CarbonPeriod::create($this->startDate, $this->endDate);
            foreach ($period as $date) {
               $attendance = Attendance::where([
                                            'user_id' => $user->id,
                                            'date' => $date
                                        ])->first();
                $row[] = $attendance ? $attendance->status->name : '';
            }

            $rows[] = $row;
        }

        return collect($rows);
    }

    public function headings(): array
    {
        $headings = ['User Name'];
        $headings[] = 'Employee ID';

        $period = CarbonPeriod::create($this->startDate, $this->endDate);
        foreach ($period as $date) {
            $headings[] = $date->format('Y-m-d');
        }

        return $headings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $statusList = implode(',', collect(AttendanceStatus::cases())->pluck('name')->toArray());

                $period = CarbonPeriod::create($this->startDate, $this->endDate);
                $rowStart = 2;
                $rowEnd = count($this->users) + 1;
                $colIndex = 3;

                foreach ($period as $date) {
                    
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);

                    for ($row = $rowStart; $row <= $rowEnd; $row++) {
                        $cell = $sheet->getCell("{$colLetter}{$row}");
                        $validation = $cell->getDataValidation();

                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1('"'.$statusList.'"');
                    }
                    $colIndex++;
                }
            },
        ];
    }

}
