<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    public function query()
    {
        return User::with([
            'profile.country',
            'department',
            'designation',
            'workDetail',
            'documents'
        ])
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
    }

    public function map($user): array
    {
        return [
            // $user->employee_id,
            is_numeric($user->employee_id) ? (int) $user->employee_id : $user->employee_id,
            $user->first_name,
            $user->last_name,
            isset($user->profile->date_of_birth) ? $user->profile->date_of_birth->format(config('project.birth_date_format')) : "",
            isset($user->profile->country) ? $user->profile->country->name : "",
            isset($user->profile->gender) ? $user->profile->gender : "",
            isset($user->profile->martial_status) ? $user->profile->martial_status->name : "",
            $user->email,
            $user->phone,
            isset($user->profile->personal_email) ? $user->profile->personal_email : "",
            isset($user->profile->personal_phone) ? $user->profile->personal_phone : "",
            $user->department?->name ?? 'NA',
            @$user->designation->name,
            isset($user->workDetail?->joining_date) ? $user->workDetail?->joining_date->format(config('project.date_format')) : "",
            isset($user->workDetail->probation_end_date) ? $user->workDetail->probation_end_date->format(config('project.date_format')) : "",
            isset($user->workDetail->company_name) ? $user->workDetail->company_name : "",
            isset($user->workDetail->work_week) ? $user->workDetail->work_week : "",
            isset($user->workDetail->location) ? $user->workDetail->location : "",
            isset($user->profile->visa_designation) ? $user->profile->visa_designation : "",
            isset($user->profile->visa_category) ? __trans($user->profile->visa_category) : "",
            isset($user->profile->visa_type) ? $user->profile->visa_type : "",
            isset($user->workDetail->mol_number) ? $user->workDetail->mol_number : "",
            isset($user->workDetail->last_working_day) ? $user->workDetail->last_working_day->format(config('project.date_format')) : "",
            isset($user->workDetail->remarks) ? $user->workDetail->remarks : "",
            isset($user->workDetail->insurance_number) ? $user->workDetail->insurance_number : "",
            isset($user->workDetail->insurance_expiry) ? $user->workDetail->insurance_expiry->format(config('project.date_format')) : "",
            
            // Passport Details
            $user->documents->where('type', \App\Enums\Document::Passport)->first()?->serial_number ?? "",
            $user->documents->where('type', \App\Enums\Document::Passport)->first()?->place_of_issue ?? "",
            $user->documents->where('type', \App\Enums\Document::Passport)->first()?->country_name ?? "",
            $user->documents->where('type', \App\Enums\Document::Passport)->first()?->issue_date ? \Carbon\Carbon::parse($user->documents->where('type', \App\Enums\Document::Passport)->first()->issue_date)->format('d/m/Y') : "",
            $user->documents->where('type', \App\Enums\Document::Passport)->first()?->expiry_date ? \Carbon\Carbon::parse($user->documents->where('type', \App\Enums\Document::Passport)->first()->expiry_date)->format('d/m/Y') : "",
            $user->status,
        ];
    }

    public function headings(): array
    {
        return [
            __trans('employee_id'),
            __trans('first_name'),
            __trans('last_name'),
            __trans('dob'),
            __trans('nationality'),
            __trans('gender'),
            __trans('martial_status'),
            __trans('work_email'),
            __trans('work_phone'),
            __trans('personal_email'),
            __trans('personal_phone'),
            __trans('department'),
            __trans('designation'),
            __trans('date_of_joining'),
            __trans('probation_end_date'),
            __trans('company_name'),
            __trans('work_week'),
            __trans('location'),
            __trans('visa_designation'),
            __trans('visa_category'),
            __trans('visa_type'),
            __trans('mol_number'),
            __trans('last_working_day'),
            __trans('remarks'),
            __trans('insurance_number'),
            __trans('insurance_expiry'),
            __trans('passport_number'),
            __trans('passport_place_of_issue'),
            __trans('passport_country'),
            __trans('passport_issue_date'),
            __trans('passport_expiry_date'),
            __trans('status'), // Added by Sanket
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Right align the first column (A)
            'A' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }
}
