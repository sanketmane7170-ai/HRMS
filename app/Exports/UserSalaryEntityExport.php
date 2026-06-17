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
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserSalaryAllowance;

class UserSalaryEntityExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{

    public function query()
    {
        $query = User::with([
            'profile' => [
                'country'
            ],
            'department', 'designation',
            'workDetail','salary'
        ])->where("status","active")
        ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->where('settlement_status', 0);

        return $query;
    }

    public function map($user): array
    {
        $fixed_allowance = isset($user->salary) ? json_decode($user->salary->fixed_allowances, true) :" "; 
        $fixed_deduction = isset($user->salary) ? json_decode($user->salary->fixed_deductions, true) : " "; 
        $allowances = SetAllowanceDeducation::where('type', 1)->select('name','amount')->get();
        $deductions = SetAllowanceDeducation::where('type', 2)->select('name','amount')->get();

        $data = [
            isset($user->employee_id) ? $user->employee_id : null,
            isset($user->first_name) ? $user->first_name : null,
            isset($user->last_name) ? $user->last_name : null,

            isset($user->email) ? $user->email : null,
            $user->department?->name ?? 'NA',
            isset($user->designation->name) ? $user->designation->name : null,
            $user->salary ? $user->salary->basic : 0,
            isset($fixed_allowance['housing_allowance']) ? $fixed_allowance['housing_allowance'] : 0,
            isset($fixed_allowance['transportation_allowance']) ? $fixed_allowance['transportation_allowance'] : 0,
            isset($fixed_allowance['functional_allowance']) ? $fixed_allowance['functional_allowance'] : 0,
            isset($fixed_allowance['other_allowance']) ? $fixed_allowance['other_allowance'] : 0,
            isset($fixed_allowance['tips']) ? $fixed_allowance['tips'] : 0,
        ];
        // // Get dynamic allowances and deductions
        // foreach ($allowances as $allowanceName) {
        //     $allowancesAmount = UserSalaryAllowance::where('title', $allowanceName->name)->where('user_id', $user->id)->select('title', 'amount')->first();
        //     $data[] = isset($allowancesAmount) ? $allowancesAmount->amount : 0;
        // }
        
        $data = array_merge($data,[
            isset($fixed_deduction['advance_salary']) ? $fixed_deduction['advance_salary'] : 0,
            isset($fixed_deduction['loan_deduction']) ? $fixed_deduction['loan_deduction'] : 0,
            isset($fixed_deduction['other_deduction']) ? $fixed_deduction['other_deduction'] : 0,
        ]);
        // // Append deduction data to $data
        // foreach ($deductions as $deductionName) {
        //     $deductionsAmount = UserDeduction::where('title', $deductionName->name)->where('user_id', $user->id)->select('title', 'amount')->first();
        //     $data[] = isset($deductionsAmount) ? $deductionsAmount->amount : 0;
        // }
        return $data;
    }

    public function headings(): array
    {
        $allowances = SetAllowanceDeducation::where('type', 1)->pluck('name')->toArray();  // Type 1 for allowances
        $deductions = SetAllowanceDeducation::where('type', 2)->pluck('name')->toArray();  // Type 2 for deductions

        $headers = [
            __trans('employee_id'),
            __trans('first_name'),
            __trans('last_name'),
            __trans('work_email'),
            __trans('department'),
            __trans('designation'),
            __trans('basic_salary'),
            __trans('housing_allowance'),
            __trans('transportation_allowance'),
            __trans('functional_allowance'),
            __trans('other_allowance'),
            __trans('tips'),
           
        ];

        // Append allowance headers dynamically
        foreach ($allowances as $allowance) {
            $headers[] = __trans($allowance);
        }
        $headers = array_merge($headers, [
            __trans('advance_salary'),
            __trans('loan_deduction'),
            __trans('other_deduction'),
        ]);
        // Append deduction headers dynamically
        foreach ($deductions as $deduction) {
            $headers[] = __trans($deduction);
        }
        return $headers;
    }

}
