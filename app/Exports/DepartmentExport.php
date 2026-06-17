<?php

namespace  App\Exports;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DepartmentExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{

    public function query()
    {
        $query = Department::with('manager');
        return $query;
    }

    public function map($department): array
    {
        $data = [
            $department->id,
            $department->name,
            $department->code,
            $department->manager->name,
        ];

        return $data;
    }

    public function headings(): array
    {
        $headers = [
            __trans('sr_no'),
            //__trans('department_name'),
            //__trans('department_code'),
            __trans('branch_name'),
            __trans('branch_code'),
            __trans('assigned_manager'),
        ];

        return $headers;
    }
}
