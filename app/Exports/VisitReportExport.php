<?php

namespace App\Exports;

use Modules\Attendance\Entities\LocationVisits;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;

class VisitReportExport implements FromQuery ,WithMapping , WithHeadings , ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $user_id;
    protected $employee;
    protected $department;
    protected $month;
    protected $year;

    public function __construct($user_id, $startDate = null, $endDate = null, $department = null, $month = null,$year = null)
    {
        $this->user_id = $user_id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->department = $department;
        $this->month = $month ? $month : date('m');
        $this->year = $year ? $year : date('Y');
    }

    public function query()
    {
        $userIds = array_filter((array) $this->user_id);
        $query =  LocationVisits::query()
            ->select(
                'user_id',
                'location',
                'date',
                'visit_purpose',
                'visit_in',
                'visit_out',
                'total_worked'
            )
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('date', [
                    $this->startDate,
                    $this->endDate
                ]);
            })
            ->when(empty($this->startDate) && empty($this->endDate), function ($q) {
                $q->whereMonth('date', (int) $this->month)
                ->whereYear('date', (int) $this->year);
            })
            ->when($this->department, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('department_id', $this->department);
                });
            })
            ->when(!in_array(0, $userIds) && !empty($userIds), function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            })
            ->with(['user.department'])
            ->orderBy('date', 'asc');

        return $query;
    }

    public function map($row): array
    {
        $employeeId = $row->user?->employee_id ?? 'N/A';
        $name       = $row->user?->name ?? 'N/A';

        return [
            "({$employeeId}) {$name}",
            $row->location,
            $row->date,
            $row->visit_purpose,
            $row->visit_in,
            $row->visit_out,
            $row->total_worked,
        ];
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Location',
            'Date',
            'Visit Purpose',
            'Visit Start Time',
            'Visit End Time',
            'Total Work Time (Hours:Minutes)',
        ];
    }
}
