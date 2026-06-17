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

class SpecificUserAttendance implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    public $user,$request, $month, $year;

    public function __construct($user, Request $request)
    {
        $this->user = $user;
        $this->year = $request->input('selected_year', date('Y'));
        $this->month = $request->input('selected_month', date('m'));

        $this->shifts = []; 
        $this->shift_time = [];
        $this->user_shifts = UserShift::where('user_id', $this->user)->get();

        $this->totalShiftHours = 0;
        foreach ($this->user_shifts as $index => $shift) {
            // Convert shift start and end times to Carbon instances
            $shiftStart = Carbon::parse($shift->shift_start);
            $shiftEnd = Carbon::parse($shift->shift_end);

            // Calculate the hours between shift start and end
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
            $hoursDifference = $shiftEnd->diffInHours($shiftStart);
            $this->totalShiftHours += $hoursDifference;
            Log::info($this->totalShiftHours);

            $this->shifts[] = __trans('shift-' . $index+1);
            $this->shift_time[] = $shift->shift_start.'-'.$shift->shift_end;
        }
    }

    public function query()
    {
        $startDate = "{$this->year}-{$this->month}-01";
        $endDate = "{$this->year}-{$this->month}-" . date('t', strtotime($startDate));

        return Attendance::query()
            ->where('user_id', $this->user)
            ->whereBetween('date', [$startDate, $endDate]);
    }

    public function map($attendance): array
    {
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

        $extra_hours = '0'; $total_worked_hours = '0';
        $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
        $total_worked_hours = date('G.i', mktime(0, $attendance->total_worked));
        $visit_count = LocationVisits::where('user_id',$this->user)->where('date',$attendance->date)->count();

        if(count($this->user_shifts) != 0){
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

        // $user_shift = $this->user_shifts;
        // if($user_shift)
        //Log::info($company_hour);
        $map = [
            $attendance->date,
            $report_user,
            //$workDetails->shift_start.'-'.$workDetails->shift_end,
            $attendance->status->value,
            $attendance->clock_in,
            $attendance->clock_out,
            $total_worked_hours,
            $extra_hours,
            $visit_count ? $visit_count : 0,
        ];
        $result = array_merge($map,$this->shift_time);
        // Log::info(print_r($result, true));
        return $result;
    }

    public function headings(): array
    {
        $headers = [
            __trans('date'),
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

    // private function parseAttendanceStatus(EntitiesAttendance $attendance)
    // {
    //     return Str::substr($attendance->status->name, 0, 1);
    // }
}
