<?php

namespace  Modules\Leave\Exports;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Leave\Entities\Leave;
use Carbon\Carbon;

class LeaveReportExport implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    public $request;

    public function __construct(Request $request)
    {
        $this->start_date = $request->input('start_date');
        $this->end_date = $request->input('end_date');
        $this->leave_type = $request->input('leave_type');
        $this->employee = $request->input('employee');
    }

    public function query()
    {
        $query = Leave::with('type', 'user');

        if($this->leave_type){
            $type = $this->leave_type;
            $query->where(function ($query) use ($type){
                $query->where('leave_type_id',$type);
            });
        }

        if ($this->start_date && $this->end_date) {
            $startDate = $this->start_date;
            $endDate = $this->end_date;

            $query->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '>=', $startDate)
                    ->where('end_date', '<=', $endDate);
            });
        }

        if($this->employee) {
            $employee = $this->employee; 
            $query->where(function ($query)  use ($employee) {
                $query->whereIn('user_id',$employee);
            });
        }

        return $query;
    }

    public function map($leave): array
    {
        $report_to_manager_name = 'N/A';
        // if(isset($leave->user->workDetail->report_to_id)){
        //     $user_id = $leave->user->workDetail->report_to_id;
        //     $report_to_manager_name = \App\Models\User::where('id',$user_id)->value('name');
        // }
        if (isset($leave->user->workDetail->report_to_id) && !empty($leave->user->workDetail->report_to_ids)) {
            $reportToIds = $workDetails->report_to_ids;
            $reportUsers = \App\Models\User::whereIn('id', $reportToIds)->pluck('name');
            $report_to_manager_name = $reportUsers->implode(', ');
        }
        $data = [
            $leave->user->employee_id,
            $leave->user->name,
            $leave->user->department->name,
            $leave->type->name,
            $leave->status->name,
            Carbon::parse($leave->start_date)->format('j M y'),
            Carbon::parse($leave->end_date)->format('j M y'),
            $leave->total_leave_days,
            $leave->reason,
            $report_to_manager_name,
            Carbon::parse($leave->created_at)->format('j M y'),
            Carbon::parse($leave->updated_at)->format('j M y')
        ];

        return $data;
    }

    public function headings(): array
    {
        $headers = [
            __trans('employee_id'),
            __trans('full_name'),
            __trans('department_name'),
            __trans('leave_type'),
            __trans('state'),
            __trans('leave_start_date'),
            __trans('leave_end_date'),
            __trans('number_of_days'),
            __trans('message'),
            __trans('report_manager_full_name'),
            __trans('leave_create_date'),
            __trans('approved_rejected_date')
        ];

        return $headers;
    }
}
