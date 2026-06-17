<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\EmployeeWorkingDay;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeWorkingDaySheet implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    protected  $month;
    protected  $year;                                              

    public function __construct($month, $year)
    {
        $this->month = $month; 
        $this->year =  $year; 
    }

    public function query()
    {
        $month = $this->month;
        $year = $this->year;
        $query = User::with([
            'department', 'designation',
            'workDetail'
        ])->where('status', 'active')
        ->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        })->with('without_attendance_days', function($query) use ($month,$year){
            $query->where(['month_code' => $month,'year'=>$year]);
        });

        return $query;
    }

    public function map($user): array
    {
        $data = [
            isset($user->employee_id) ? $user->employee_id : null,
            isset($user->first_name) ? $user->first_name : null,
            isset($user->last_name) ? $user->last_name : null,

            isset($user->email) ? $user->email : null,
            $user->department?->name ?? 'NA',
            isset($user->designation->name) ? $user->designation->name : null,
            isset($user->without_attendance_days->total_working_days) ? $user->without_attendance_days->total_working_days : 0,
        ];

        return $data;
    }

    public function headings(): array
    {
        $headers = [
            __trans('employee_id'),
            __trans('first_name'),
            __trans('last_name'),
            __trans('work_email'),
            __trans('department'),
            __trans('designation'),
            __trans('total_working_days'),
        ];

        return $headers;
    }
}
