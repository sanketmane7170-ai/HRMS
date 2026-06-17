<?php

namespace  App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Attendance\Entities\Attendance as EntitiesAttendance;
use Modules\Attendance\Enums\AttendanceStatus;

class UserBasicSalaryExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{

    public function query()
    {
        $query = User::with([
            'profile' => [
                'country'
            ],
            'department', 'designation',
            'workDetail','salary'
        ])->where('status', 'active')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });

        return $query;
    }

    public function map($user): array
    {
        $data = [
            $user->employee_id,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->department?->name ?? 'NA',
            $user->designation->name,
            $user->salary ? $user->salary->basic : 0
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
            __trans('basic_salary'),
        ];

        return $headers;
    }

}
