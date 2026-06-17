<?php

namespace  Modules\Attendance\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Log;
use App\Models\UserWorkDetail;
use App\Models\Setting;
use App\Models\UserShift;
use Carbon\Carbon;
use Modules\Attendance\Entities\LocationVisits;

class BulkAttendance implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    public $request, $month, $year;

    public function __construct(Request $request)
    {
        $this->year = $request->input('year', date('Y'));
        $this->month = $request->input('month', date('m'));

        $this->shifts = [];
        $this->shiftTime = [];
        $this->userShifts = [];
        $this->totalShiftHours = 0;
        $this->shift_time = [];

        $this->startDate = "{$this->year}-{$this->month}-01";
        $this->endDate = "{$this->year}-{$this->month}-" . date('t', strtotime($this->startDate));
    }

    public function query()
    {
        $startDate = "{$this->year}-{$this->month}-01";
        $endDate = "{$this->year}-{$this->month}-" . date('t', strtotime($startDate));

        $query = User::query()
        ->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        })
        ->withWhereHas('attendances', function ($query) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        });
        return $query;
    }

    public function map($user): array
    {
        $workDetails = UserWorkDetail::where('user_id',$user->id)->first();
        $report_user = '';
        // if($workDetails->report_to_id != 0){
        //     $report_user = User::find($workDetails->report_to_id)->name;
        // }
        if (!empty($workDetails->report_to_ids)) {
            $reportToIds = $workDetails->report_to_ids;
            $reportUsers = User::whereIn('id', $reportToIds)->pluck('name');
            $report_user = $reportUsers->implode(', ');
        }
        
        $this->user_shifts = UserShift::where('user_id', $user->id)->get();
        foreach ($this->user_shifts as $index => $shift) {
            $shiftStart = Carbon::parse($shift->shift_start);
            $shiftEnd = Carbon::parse($shift->shift_end);

            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
            $hoursDifference = $shiftEnd->diffInHours($shiftStart);
            $this->totalShiftHours += $hoursDifference;

            // Log::info($this->totalShiftHours);

            $this->shifts[] = __trans('shift-' . $index+1);
            $this->shift_time[] = $shift->shift_start.'-'.$shift->shift_end;
        }
        $result = [];
        $lastUserId = null;
        foreach($user->attendances as $attendance){
            $extra_hours = '0'; $total_worked_hours = '0';
            $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
            $visit_count = LocationVisits::where('user_id',$user->id)->where('date',$attendance->date)->count();
            // foreach ($user->attendances as $attendance) {
            //     $attendance->total_worked = $attendance->total_worked ?? 0;
            // }
            $total_worked_hours = date('G.i', mktime(0, $attendance->total_worked));
    
            if(count($this->user_shifts) != 0){
                if($total_worked_hours > $this->totalShiftHours) {
                    $extra_hours = $total_worked_hours -  $this->totalShiftHours;
                }
            } else {
                $this->shift_time = [];
                $this->userShifts = [];
                if($company_hour > 0){
                    if($total_worked_hours > $company_hour) {
                        $extra_hours = $total_worked_hours -  $company_hour;
                    }
                }
            }
                $map = [
                    $attendance->date,
                    $user->name,
                    $user->employee_id,
                    $report_user,
                    $attendance->status->value,
                    $attendance->clock_in,
                    $attendance->clock_out,
                    $total_worked_hours,
                    $extra_hours,
                    $visit_count ? $visit_count : 0,
                ];
            $result[] = array_merge($map,$this->shift_time);

                $lastUserId = $user->id;
            }
             // Check if the user ID changes
             if ($lastUserId == $user->id) {
                // Log::error($lastUserId);
                $this->shift_time= [];
                $this->userShifts = [];
                $result[] = []; // Add an empty row
            }
        // Log::info(print_r($result, true));
        return $result;
    }

    public function headings(): array
    {
        $headers = [
            __trans('date'),
            __trans('employee_name'),
            __trans('employee_id'),
            __trans('report_to'),
            //__trans('schedule'),
            __trans('status'),
            __trans('clock_in'),
            __trans('clock_out'),
            __trans('hours_worked'),
            __trans('extra_hours'),
            __trans('number_of_visits'),
        ];

        $result = array_merge($headers,$this->shifts);
        // /Log::info(print_r($result, true));
        return $result;
    }
}