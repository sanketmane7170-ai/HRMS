<?php

namespace  Modules\Attendance\Exports;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Attendance\Entities\Attendance as EntitiesAttendance;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Entities\Attendance;
use Illuminate\Support\Facades\Log;
use App\Models\UserWorkDetail;
use App\Models\Setting;
use App\Models\UserShift;
use Carbon\Carbon;
use Modules\Attendance\Entities\LocationVisits;
use Exception;
use Modules\Attendance\Entities\Checkin;

class UPGRADE_SpecificUserAttendance implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    /**
     * Major Update in Export CSV That is config User Assigned Shifts using Shift Schedular
     * So Created Seperate Or Upgrade Logic
     * Replica File : Modules/Attendance/Exports/SpecificUserAttendance.php
     * Dt : 13-12-2023
     */ 
    public $user,$request, $month, $year;

    public function __construct($user, Request $request)
    {
        $this->user = $user;
        $this->year = $request->input('selected_year', date('Y'));
        $this->month = $request->input('selected_month', date('m'));

        $this->shifts = []; 
    }

    public function query()
    {
        $startDate = "{$this->year}-{$this->month}-01";
        $endDate = "{$this->year}-{$this->month}-" . date('t', strtotime($startDate));

        return Attendance::query()
            ->where('user_id', $this->user)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc');;
    }

    public function map($attendance): array
    {
        try{
            $workDetails = UserWorkDetail::where('user_id',$this->user)->first();

            $report_user = '';
            // if($workDetails->report_to_id != 0){
            //     $report_user = User::find($workDetails->report_to_id)->name;
            // }
            if (!empty($workDetails->report_to_ids)) {
                $reportToIds = $workDetails->report_to_ids;
                $reportUsers = User::whereIn('id', $reportToIds)->pluck('name');
                $report_user = $reportUsers->implode(', ');
            }
            $user = '';
            if($workDetails->user_id != 0){
                $user = User::find($workDetails->user_id)->name;
            }

            $extra_hours = '0'; $total_worked_hours = '0';
            $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
            $total_worked_hours = date('G.i', mktime(0, $attendance->total_worked));
            $visitData = LocationVisits::where('user_id', $this->user)
                ->where('date', $attendance->date)
                ->selectRaw('COUNT(*) as visit_count, SUM(total_worked) as total_worked_sum')
                ->first();

            $visit_count = $visitData->visit_count;
           // $total_worked_sum = $visitData->total_worked_sum;
            $total_worked_sum = 0;
            if($visit_count > 0){
                $total_worked_sum = date('G.i', mktime(0, $visitData->total_worked_sum));
            }
            
            $shift_time = [];
            $user_shifts = [];
            $this->totalShiftHours = 0;

            $user_shifts = User::find($this->user)
                ->assigned_shifts()
                ->with('shift_schedule_information')
                ->where('assigned_for_date', $attendance->date)
                ->get();
            $this->totalShiftHours = 0;
            foreach ($user_shifts as $index => $shiftData) {
                $shift = $shiftData->shift_schedule_information;
                // Convert shift start and end times to Carbon instances
                $shiftStart = Carbon::parse($shift->shift_start);
                $shiftEnd = Carbon::parse($shift->shift_end);

                // Calculate the hours between shift start and end
                if ($shiftEnd->lessThan($shiftStart)) {
                    $shiftEnd->addDay();
                }
                $hoursDifference = $shiftEnd->diffInHours($shiftStart);
                $this->totalShiftHours += $hoursDifference;
                //Log::info($this->totalShiftHours);

                $this->shifts[] = __trans('shift-' . $index+1);
                $shift_time[] = $shift->shift_start.'-'.$shift->shift_end;
            }

            if(count($user_shifts) != 0){
                // Shfit base calculations
                if($total_worked_hours > $this->totalShiftHours) {
                    //Log::info('entered this');
                    $extra_hours = $total_worked_hours -  $this->totalShiftHours;
                }
            } else {
                if($company_hour > 0){
                    if($total_worked_hours > $company_hour) {
                        //Log::info('entered this');
                        $extra_hours = $total_worked_hours -  $company_hour;
                    }
                }
            }
            
            $map = [
                $attendance->date,
                $user,
                $report_user,
                $attendance->status->value,
                $attendance->clock_in,
                $attendance->clockout_date,
                $attendance->clock_out,
                $total_worked_hours,
                $extra_hours,
                $visit_count ? $visit_count : 0,
                $total_worked_sum
            ];
           

            $result = array_merge($map,$shift_time);
            // Log::info(print_r($result, true));
            return $result;
        } catch(Exception $e){
            Log::error($e);
        }
    }

    public function headings(): array
    {
        $headers = [
            __trans('date'),
            __trans('employee_name'),
            __trans('report_to'),
            //__trans('schedule'),
            __trans('status'),
            __trans('clock_in'),
            __trans('clock_out_date'),
            __trans('clock_out'),
            __trans('hours_worked'),
            __trans('extra_hours'),
            __trans('number_of_visits'),
            __trans('total_visit_hours'),
            __trans('shift_1'),
            __trans('shift_2'),
            __trans('shift_3'),
            __trans('shift_4'),
            __trans('shift_5'),
        ];

        $result = array_merge($headers);
        // /Log::info(print_r($result, true));
        return $result;
    }

}
