<?php

namespace  App\Exports;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DesignationExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{

    public function query()
    {
        $query = Designation::with('department');
        return $query;
    }

    public function map($designation): array
    {
        $data = [
            $designation->id,
            $designation->name,
            $designation->code,
            optional($designation->department)->name ?? 'All'

        ];

        return $data;
    }

    public function headings(): array
    {
        $headers = [
            __trans('sr_no'),
            __trans('designation_name'),
            __trans('designation_code'),
            __trans('department'),
        ];

        return $headers;
    }
}
