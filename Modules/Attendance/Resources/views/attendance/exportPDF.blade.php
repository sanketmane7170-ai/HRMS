<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            text-align: center;
            padding: 5px;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: left;
            font-weight: bold;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <!-- Report Header -->
    <h2 class="title">Monthly Attendance Report</h2>
    <p><strong>Branch:</strong> {{ $departname }}</p>
    <p><strong>{{ $monthdata }}</strong></p>
    <p><strong>Print Date:</strong> {{ date('d/m/Y') }}</p>

    <!-- Attendance Table -->
    @foreach ($data as $user)
    <table>
        <tr>
            @php
            $periodArray = iterator_to_array($dateHeaders);
            $count = count($periodArray);
            @endphp
            <td colspan="{{ $count + 1 }}" class="header">Name: {{ $user['name'] }} | E.Code: {{ $user['employee_id'] }}</td>
        </tr>
        <!-- Days Header -->
        <tr>
            <th>Day</th>
            @foreach ($dateHeaders as $date)
            @php
            $day = \Carbon\Carbon::parse($date)->format('M-d');
            @endphp
            <th>{{ $day }}</th>
            @endforeach
        </tr>
        <!-- Days Name Header -->
        <tr>
            <th>Day Name</th>
            @foreach ($dateHeaders as $date)
            @php
            $dayname = \Carbon\Carbon::parse($date)->format('D');
            @endphp
            <th>{{ $dayname }}</th>
            @endforeach
        </tr>
        <!-- Days Name Header -->
        <tr>
            <th>Shift</th>
            @foreach ($dateHeaders as $date)
            @php
            $show_shift = [];
            $user_shifts = Modules\Shift\Entities\UsersShift::where([['user_id',$user->id],['assigned_for_date', $date]])->with('shift_schedule_information')->get();
            foreach ($user_shifts as $user_shift){
            $show_shift[] = '('.\Carbon\Carbon::parse($user_shift->shift_schedule_information->shift_start)->format('H:i') . '-' . \Carbon\Carbon::parse($user_shift->shift_schedule_information->shift_end)->format('H:i').')';
            }
            $show_shift_string = implode('<br>', $show_shift);
            @endphp
            <th>
                @if (count($user_shifts) > 0)
                {!! $show_shift_string !!}
                @else
                -
                @endif
            </th>
            @endforeach
        </tr>
        <!-- Clock In Data -->
        <tr>
            <td>IN</td>
            @foreach ($dateHeaders as $date)
            @php
                $date = \Carbon\Carbon::parse($date)->toDateString();
                $attendance = $user->attendances->where('date', $date)->first();
                $clockIn = $attendance ? substr($attendance->clock_in, 0, 5) : '0:0';
                $clockoutdate = $attendance && $attendance->clockout_date ? $attendance->clockout_date : $date;

                $checkins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                                    ->whereDate('date', $date)
                                    ->orderBy('id', 'asc')
                                    ->get();
                $inTimes = [];
                if (count($checkins) > 0) {
                    foreach ($checkins as $row => $checkin) {
                        if ($checkin->type == "in") {
                            $inTimes[] = $checkin->time;
                        }
                    }
                }
                $visitin = Modules\Attendance\Entities\Visitin::where([['user_id',$user->id],['date', $date]])->orderBy('id', 'asc')->first();
                if($visitin && $visitin->type == Modules\Attendance\Enums\VisitinType::IN->value){
                    $clockIn = $visitin->time;
                }
                $clockRowIn = isset($inTimes) ?  implode('<br>', $inTimes) : '-';
                $clockIn = $clockRowIn;
            @endphp
            <td>{!! $clockIn !!}</td>
            @endforeach
        </tr>
        <!-- Clock In Branch -->
        <tr>
            <td>IN Branch</td>
            @foreach ($dateHeaders as $date)
            @php
            $date = \Carbon\Carbon::parse($date)->toDateString();
            $checkinIn = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
            ->where('date', $date)
            ->where('type', 'in')
            ->orderBy('id', 'asc')
            ->first();
            @endphp
            <td>{{ $checkinIn && $checkinIn->branch ? $checkinIn->branch->name : '-' }}</td>
            @endforeach
        </tr>
        <!-- Clock Out Data -->
        <tr>
            <td>OUT</td>
                @php
                    $totalmonthhous = 0;
                    $totalpaidday = 0;
                    $totalabsent = 0;
                    $totalleave = 0;
                    $extra_hours = 0;
                @endphp
            @foreach ($dateHeaders as $date)
            @php
                $date = \Carbon\Carbon::parse($date)->toDateString();
                $attendances = $user->attendances()->where('date', $date)->get();
                $totalMinutes = 0;
                $loop1 = 0;
                $attendancetime = 0;
                $todayisleave = Modules\Leave\Entities\Leave::where([['user_id', $user->id],['status',Modules\Leave\Enums\LeaveStatus::Approved]])
                ->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->first();
                    if($todayisleave){
                    $statusL = 'L';
                    $totalleave = $statusL=='L' ? $totalleave + 1 : 0;
                    }
                foreach ($attendances as $attendance){  
                    if($attendance->clock_out!= null){
                    
                        $loop1++;
                        $attendancetime = $attendance ? substr($attendance->clock_out, 0, 5) : '0:0';
                        $clockin = $attendance ? $attendance->clock_in : 0;
                        $clockout = $attendance ? $attendance->clock_out : 0;
                        $startDateTime = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->clock_in)->toDateTimeString();
                        $endDateTime = \Carbon\Carbon::parse($attendance->clockout_date . ' ' . $attendance->clock_out)->toDateTimeString();

                        $checkins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                        ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$startDateTime])
                        ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$endDateTime])
                            ->orderBy('id', 'asc')
                            ->get();
                        if (count($checkins) > 0) {
                            $checkinsclockin = "";
                            $checkinsclockout = "";
                            foreach ($checkins as $row => $checkin) {

                                if ($checkin->type == "in") {
                                    $checkinsclockin = $checkin ? $checkin->time : 0;
                                } elseif ( $row >0 && $checkin->type == "out") {
                                    $checkinsclockout = $checkin ? $checkin->time : 0;
                                }
                                if ($checkinsclockin && $checkinsclockout && $checkinsclockin != "" && $checkinsclockout !="" )  {

                                    $Minutes = 0;
                                    $clockin = $checkinsclockin;
                                    $clockout = $checkinsclockout;
                                    $checkinsclockin = "";
                                    $checkinsclockout = "";
                                    if ($clockin && $clockout) {
                                        $clockinTime = \Carbon\Carbon::createFromTimeString($clockin);
                                        $clockoutTime = \Carbon\Carbon::createFromTimeString($clockout);
                                        if ($clockoutTime->lessThan($clockinTime)) {
                                            $clockoutTime->addDay();
                                        }
                                        $Minutes = $clockinTime->diffInMinutes($clockoutTime);
                                        $clockin_date = \Carbon\Carbon::parse($attendance->date);
                                        $clockout_date = \Carbon\Carbon::parse($attendance->clockout_date);

                                        $totalMinutes = $totalMinutes + $Minutes;
                                        $totalmonthhous = $totalmonthhous + $Minutes;
                                    }
                                }
                            }
                        } else {
                            $Minutes = 0;
                            if ($clockin && $clockout) {
                            $clockinTime = \Carbon\Carbon::createFromTimeString($clockin);
                            $clockoutTime = \Carbon\Carbon::createFromTimeString($clockout);
                            if ($clockoutTime->lessThan($clockinTime)) {
                            $clockoutTime->addDay();
                            }
                            $Minutes = $clockinTime->diffInMinutes($clockoutTime);
                            $clockin_date = \Carbon\Carbon::parse($attendance->date);
                            $clockout_date = \Carbon\Carbon::parse($attendance->clockout_date);
                            if ($clockout_date->gt($clockin_date)) {
                            $clockinDate = Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                            $clockinTime = Carbon\Carbon::createFromTimeString($clockin)->format('H:i:s');
                            $clockIndatetime = Carbon\Carbon::parse("$clockinDate $clockinTime");

                            $clockoutDate = Carbon\Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                            $clockoutTime = Carbon\Carbon::createFromTimeString($clockout)->format('H:i:s');
                            $clockOutdatetime = Carbon\Carbon::parse("$clockoutDate $clockoutTime");

                            $Minutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                            }
                            $totalMinutes = $totalMinutes + $Minutes;
                            $totalmonthhous = $totalmonthhous + $Minutes;
                            }
                        }
                        if (isset($attendance->clock_out) && $attendance->clock_out != null) {
                            $breakins = Modules\Attendance\Entities\Breakin::where('user_id', $user->id)
                            ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$startDateTime])
                            ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$endDateTime])
                                ->orderBy('id', 'asc')
                                ->get();

                            if (count($breakins) > 0) {
                                $breakinsclockin = "";
                                $breakinsclockout = "";
                                foreach ($breakins as $row => $breakin) {

                                    if ($breakin->type == "in") {
                                        $breakinsclockin = $breakin ? $breakin->time : 0;
                                    } elseif ($row > 0 && $breakin->type == "out") {
                                        $breakinsclockout = $breakin ? $breakin->time : 0;
                                    }

                                    if ($breakinsclockin && $breakinsclockout) {

                                        $Minutes = 0;
                                        $clockin = $breakinsclockin;
                                        $clockout = $breakinsclockout;

                                        $breakinsclockin = "";
                                        $breakinsclockout = "";
                                        if ($clockin && $clockout) {
                                            $clockinTime = \Carbon\Carbon::createFromTimeString($clockin);

                                            $clockoutTime = \Carbon\Carbon::createFromTimeString($clockout);
                                            if ($clockoutTime->lessThan($clockinTime)) {
                                                $clockoutTime->addDay();
                                            }
                                            $Minutes = $clockinTime->diffInMinutes($clockoutTime);
                                            $totalMinutes = $totalMinutes - $Minutes;
                                            $totalmonthhous = $totalmonthhous - $Minutes;
                                        }
                                    }
                                }
                            }
                        }

                    }
                        $visitins = Modules\Attendance\Entities\Visitin::where([['user_id',$user->id],['date', $date]])->get();
                        $visitintime = null;
                        $visitouttime = null;
                        $totalVMinutes = 0;
                        foreach ($visitins as $visitin){
                            if($visitin->type == Modules\Attendance\Enums\VisitinType::IN->value){
                                $visitintime = Carbon\Carbon::createFromTimeString($visitin->time);
                            }
                            if($visitin->type == Modules\Attendance\Enums\VisitinType::OUT->value){
                                if ($visitintime instanceof Carbon\Carbon) {
                                    $visitouttime = Carbon\Carbon::createFromTimeString($visitin->time);
                                    $VMinutes = $visitintime->diffInMinutes($visitouttime);
                                    $totalVMinutes += $VMinutes;
                                    $visitintime = null;
                                    $totalmonthhous += $totalVMinutes;
                                    $attendancetime = $visitin->time;
                                }
                            }
                        }
                        $clockoutdate = $attendance && $attendance->clockout_date ? $attendance->clockout_date : $date;
                        $checkins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                                            ->whereDate('date', $date)
                                            ->orderBy('id', 'asc')
                                            ->get();
                        $outTimes = [];
                        if (count($checkins) > 0) {
                            $lastIndex = count($checkins) - 1;
                            foreach ($checkins as $row => $checkin) {
                                if ($row > 0 && $checkin->type == "out") {
                                    $outTimes[] = $checkin->time;
                                }
                                $isLastRow = ($row === $lastIndex);
                                if ($isLastRow) {
                                    if ($checkin->type == "in") {
                                        $nextDate = Carbon\Carbon::parse($date)->addDay();
                                        $lastcheckins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                                                    ->whereDate('date' ,$nextDate)
                                                    ->orderBy('id', 'asc')
                                                    ->first();
                                        $outTimes[] = $lastcheckins?->time;
                                    }
                                }
                            }
                        }
                        $clockRowOut = isset($outTimes) ?  implode('<br>', $outTimes) : '-';
                        $attendancetime = $clockRowOut;
                        // extra hours calculate
                        $shift_time = [];
                        $user_shifts = [];
                        $totalShiftMinuts = 0;
                        $total_worked_hours = '0';
                        if(count($attendances) > 0){
                            $hours = intdiv($totalMinutes, 60);
                            $minutes = $totalMinutes % 60;
                            $total_worked_hours = $hours.'.'.$minutes;

                            $company_hour = App\Models\Setting::where('key', 'minumum_working_hour')->value('value');
                            $user_shifts = App\Models\User::find($user->id)
                            ->assigned_shifts()
                            ->with('shift_schedule_information')
                            ->where('assigned_for_date', $date)
                            ->get();
                            foreach ($user_shifts as $index => $shiftData) {
                                $shift = $shiftData->shift_schedule_information;
                                // Convert shift start and end times to Carbon instances
                                $shiftStart = Carbon\Carbon::parse($shift->shift_start);
                                $shiftEnd = Carbon\Carbon::parse($shift->shift_end);

                                // Calculate the hours between shift start and end
                                if ($shiftEnd->lessThan($shiftStart)) {
                                $shiftEnd->addDay();
                                }
                                $hoursDifference = $shiftEnd->diffInMinutes($shiftStart);
                                $totalShiftMinuts += $hoursDifference;
                                $shift_time[] = $shift->shift_start.'-'.$shift->shift_end;
                            }

                            //if(count($user_shifts) != 0){
                            // $hours = intdiv($totalShiftMinuts, 60);
                            // $minutes = $totalShiftMinuts % 60;
                            // $totalShiftHours = $hours.'.'.$minutes;
                            // if($total_worked_hours > $totalShiftHours) {
                            // $extrahours = Carbon\Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon\Carbon::createFromTimeString($totalShiftHours));
                            // $extra_hours += $extrahours;
                            // }
                            //} else {
                            // $shift_time = [];
                            // $userShifts = [];
                            // if($company_hour > 0){
                            // if($total_worked_hours > $company_hour) {
                            // $extrahours = Carbon\Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon\Carbon::createFromTimeString($company_hour));
                            // $extra_hours += $extrahours;
                            // }
                            // }
                            //}
                            $workedHours = floor((float) $total_worked_hours);
                            $workedMinutes = (int) round(((float) $total_worked_hours - $workedHours) * 60);
                            // Prevent invalid minute value
                            if ($workedMinutes === 60) {
                            $workedMinutes = 0;
                            $workedHours += 1;
                            }
                            $totalWorkedTime = sprintf('%02d:%02d:00', $workedHours, $workedMinutes);
                            \Log::error('workedHours: ' . $workedHours);
                            \Log::error('workedMinutes: ' . $workedMinutes);
                            \Log::error('totalWorkedTime: ' . $totalWorkedTime);

                            if (count($user_shifts) != 0) {
                                    $hours = intdiv((int) $totalShiftMinuts, 60);
                                    $minutes = (int) $totalShiftMinuts % 60;

                                    // Prevent invalid minute value
                                    if ($minutes === 60) {
                                    $minutes = 0;
                                    $hours += 1;
                                    }

                                $totalShiftHours1 = sprintf('%02d:%02d:00', $hours, $minutes);
                                \Log::error('totalShiftHours1: ' . $totalShiftHours1);

                                try {
                                $workedTimeCarbon = Carbon\Carbon::createFromFormat('H:i:s', $totalWorkedTime);
                                $shiftTimeCarbon = Carbon\Carbon::createFromFormat('H:i:s', $totalShiftHours1);

                                if ($workedTimeCarbon->greaterThan($shiftTimeCarbon)) {
                                $extrahours = $workedTimeCarbon->diffInMinutes($shiftTimeCarbon);
                                $extra_hours += $extrahours;
                                }
                                } catch (\Exception $e) {
                                \Log::error('Carbon time format error', [
                                'worked' => $totalWorkedTime,
                                'shift' => $totalShiftHours1,
                                'error' => $e->getMessage(),
                                ]);
                                }
                            } else {
                                if ($company_hour > 0) {
                                $companyHours = floor((float) $company_hour);
                                $companyMinutes = round(((float) $company_hour - $companyHours) * 60);
                                $companyTime = sprintf('%02d:%02d:00', $companyHours, $companyMinutes);
                                try {
                                if (Carbon\Carbon::createFromFormat('H:i:s', $totalWorkedTime)->greaterThan(
                                Carbon\Carbon::createFromFormat('H:i:s', $companyTime)
                                )) {
                                $extrahours = Carbon\Carbon::createFromFormat('H:i:s', $totalWorkedTime)
                                ->diffInMinutes(Carbon\Carbon::createFromFormat('H:i:s', $companyTime));
                                $extra_hours += $extrahours;
                                }
                                } catch (\Exception $e) {
                                \Log::error('Time parsing or diff error: ' . $e->getMessage());
                                // Optionally handle the error gracefully, e.g. continue processing
                                }
                                }
                            }
                        } else {
                            if(!$todayisleave){
                                $totalabsent = $totalabsent + 1;
                                $visitin = Modules\Attendance\Entities\Visitin::where([['user_id',$user->id],['date', $date]])->first();
                                if($visitin){
                                    $totalabsent = $totalabsent - 1;
                                }
                            }
                        }
                }
                // end
                $extra_hourshours = intdiv($extra_hours, 60);
                $extra_hoursminutes = $extra_hours % 60;
            @endphp
            <td>{!! $attendancetime !!}</td>
            @endforeach
        </tr>
        <!-- Clock Out Branch -->
        <tr>
            <td>OUT Branch</td>
            @foreach ($dateHeaders as $date)
            @php
            $date = \Carbon\Carbon::parse($date)->toDateString();
            $checkinOut = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
            ->where('date', $date)
            ->where('type', 'out')
            ->orderBy('id', 'desc')
            ->first();
            @endphp
            <td>{{ $checkinOut && $checkinOut->branch ? $checkinOut->branch->name : '-' }}</td>
            @endforeach
        </tr>
        <!-- Total Working Hrs -->
        <tr>
            @php
            $monthHours = intdiv($totalmonthhous, 60);
            $monthminutes = $totalmonthhous % 60;
            @endphp
            <th>Total Working Hrs:- {{ $monthHours }} : {{ $monthminutes }}, Extra Working Hrs: ({{ $extra_hourshours.':'.$extra_hoursminutes }})</th>
            @foreach ($dateHeaders as $date)
            @php
            $date = \Carbon\Carbon::parse($date)->toDateString();
            $attendance = $user->attendances->where('date', $date)->first();
            $clockin = $attendance ? $attendance->clock_in : 0;
            $clockout = $attendance ? $attendance->clock_out : 0;

            if ($attendance && $attendance->clock_out != null)
            {
            $startDateTime = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->clock_in)->toDateTimeString();
            $endDateTime = \Carbon\Carbon::parse($attendance->clockout_date . ' ' . $attendance->clock_out)->toDateTimeString();


            $checkins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
            ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$startDateTime])
            ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$endDateTime])
                ->orderBy('id', 'asc')
                ->get();
                }
                else
                $checkins = collect(); // empty collection to avoid errors

                if (count($checkins) > 0) {
                $checkinsclockin = "";
                $checkinsclockout = "";
                $totalMinutes = 0;
                foreach ($checkins as $row => $checkin) {
                // if ($attendance->date == "2025-06-03") {
                if ($checkin->type == "in") {
                $checkinsclockin = $checkin ? $checkin->time : 0;
                } elseif ($row > 0 && $checkin->type == "out") {
                $checkinsclockout = $checkin ? $checkin->time : 0;
                }

                if ($checkinsclockin && $checkinsclockout && $checkinsclockin != "" && $checkinsclockout !="" ) {

                $Minutes = 0;
                $clockin = $checkinsclockin;
                $clockout = $checkinsclockout;
                $checkinsclockin = "";
                $checkinsclockout = "";

                $hours = 0;

                $minutes = 0;
                if ($clockin && $clockout) {
                $clockinTime = \Carbon\Carbon::createFromTimeString($clockin);
                $clockoutTime = \Carbon\Carbon::createFromTimeString($clockout);
                // Calculate the total difference
                if ($clockoutTime->lessThan($clockinTime)) {
                $clockoutTime->addDay();
                }
                $totalMinutes += $clockinTime->diffInMinutes($clockoutTime);
                $clockin_date = \Carbon\Carbon::parse($attendance->date);
                $clockout_date = \Carbon\Carbon::parse($attendance->clockout_date);

                $hours = intdiv($totalMinutes, 60);
                $minutes = $totalMinutes % 60;
                }
                }
                // }
                }

                } else {
                $hours = 0;
                $totalMinutes = 0;
                $minutes = 0;
                if ($clockin && $clockout) {
                $clockinTime = \Carbon\Carbon::createFromTimeString($clockin);
                $clockoutTime = \Carbon\Carbon::createFromTimeString($clockout);
                // Calculate the total difference
                if ($clockoutTime->lessThan($clockinTime)) {
                $clockoutTime->addDay();
                }
                $totalMinutes = $clockinTime->diffInMinutes($clockoutTime);
                $clockin_date = \Carbon\Carbon::parse($attendance->date);
                $clockout_date = \Carbon\Carbon::parse($attendance->clockout_date);
                if ($clockout_date->gt($clockin_date)) {
                $clockinDate = Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                $clockinTime = Carbon\Carbon::createFromTimeString($clockin)->format('H:i:s');
                $clockIndatetime = Carbon\Carbon::parse("$clockinDate $clockinTime");

                $clockoutDate = Carbon\Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                $clockoutTime = Carbon\Carbon::createFromTimeString($clockout)->format('H:i:s');
                $clockOutdatetime = Carbon\Carbon::parse("$clockoutDate $clockoutTime");

                $totalMinutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                }
                $hours = intdiv($totalMinutes, 60);
                $minutes = $totalMinutes % 60;
                }
                }
                if (isset($attendance->clock_out) && $attendance->clock_out != null) {
                $breakins = Modules\Attendance\Entities\Breakin::where('user_id', $user->id)
                ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$startDateTime])
                ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$endDateTime])
                    ->orderBy('id', 'asc')
                    ->get();



                    if (count($breakins) > 0) {
                    $breakinsclockin = "";
                    $breakinsclockout = "";
                    foreach ($breakins as $row => $breakin) {

                    if ($breakin->type == "in") {
                    $breakinsclockin = $breakin ? $breakin->time : 0;
                    } elseif ($row > 0 && $breakin->type == "out") {
                    $breakinsclockout = $breakin ? $breakin->time : 0;
                    }


                    if ($breakinsclockin && $breakinsclockout) {



                    $Minutes = 0;
                    $clockin = $breakinsclockin;
                    $clockout = $breakinsclockout;



                    $breakinsclockin = "";
                    $breakinsclockout = "";
                    if ($clockin && $clockout) {
                    $clockinTime = \Carbon\Carbon::createFromTimeString($clockin);

                    $clockoutTime = \Carbon\Carbon::createFromTimeString($clockout);
                    if ($clockoutTime->lessThan($clockinTime)) {
                    $clockoutTime->addDay();
                    }
                    $Minutes = $clockinTime->diffInMinutes($clockoutTime);




                    $totalMinutes = $totalMinutes - $Minutes;
                    $totalmonthhous = $totalmonthhous - $Minutes;
                    }
                    }
                    }
                    }
                    }

                    if($loop1 === 1){
                    $atstatus = $attendance && $Minutes > 0 ? Illuminate\Support\Str::substr($attendance->status->name, 0, 1) : '-';
                    $totalpaidday = $atstatus=='P' ? $totalpaidday + 1 : $totalpaidday + 0;
                    }
                    $visitins = Modules\Attendance\Entities\Visitin::where([['user_id',$user->id],['date', $date]])->get();
                    $visitintime = null;
                    $visitouttime = null;
                    $totalVMinutes = 0;
                    foreach ($visitins as $visitin){
                    if($visitin->type == Modules\Attendance\Enums\VisitinType::IN->value){
                    $visitintime = Carbon\Carbon::createFromTimeString($visitin->time);
                    }
                    if($visitin->type == Modules\Attendance\Enums\VisitinType::OUT->value){
                    if ($visitintime instanceof Carbon\Carbon) {
                    $visitouttime = Carbon\Carbon::createFromTimeString($visitin->time);
                    $VMinutes = $visitintime->diffInMinutes($visitouttime);
                    $totalVMinutes += $VMinutes;
                    $visitintime = null;
                    $totalMinutes += $totalVMinutes;
                    $attendancetime = $visitin->time;
                    }
                    }
                    }
                    $hours = intdiv($totalMinutes, 60);
                    $minutes = $totalMinutes % 60;
                    $time = $totalMinutes ? $hours.':'.$minutes : '-';
                    @endphp
                    <td>{{ $time }}</td>
                    @endforeach
        </tr>
         <!-- Remark  -->
        <tr>
            <th>Remark - Prsent ({{ $totalpaidday }}),Absent({{ $totalabsent }}),off.Leave({{ $totalleave }})</th>
            @foreach ($dateHeaders as $date)
            @php
            $date = \Carbon\Carbon::parse($date)->toDateString();
            $attendance = $user->attendances->where('date', $date)->first();
            $status = $attendance ? Str::substr($attendance->status->name, 0, 1) : 'A';
            $todayisleave = Modules\Leave\Entities\Leave::where([['user_id', $user->id],['status',Modules\Leave\Enums\LeaveStatus::Approved]])
            ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();
                if($todayisleave){
                $status = 'L';
                }
                $user_shifts = Modules\Shift\Entities\UsersShift::where([['user_id',$user->id],['assigned_for_date', $date]])->with('shift_schedule_information')->first();
                if (!empty($user_shifts) && !empty($attendance)) {
                $shift_start = Carbon\Carbon::parse($user_shifts->shift_schedule_information->shift_start)->format('H:i:00');
                $clock_in = Carbon\Carbon::parse($attendance->clock_in)->format('H:i:00');
                $locationvisits = Modules\Attendance\Entities\LocationVisits::where('user_id', $user->id)
                ->where('date', $date)
                ->orderBy('id', 'asc')->first();
                if ($locationvisits) {
                $visit_in = Carbon\Carbon::parse($locationvisits->visit_in)->format('H:i:00');
                if ($shift_start < $visit_in) {
                    $late=(new Carbon\Carbon($visit_in))->diff(new Carbon\Carbon($shift_start))->format('%h:%I');
                    $status = "L-P(" . $late . ")";
                    }
                    } else {
                    $checkins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                    ->where('date', $date)
                    ->where('type', 'in')
                    ->orderBy('id', 'asc')->first();

                    if (isset($checkins) && $shift_start < Carbon\Carbon::parse($checkins->time)->format('H:i:00')) {
                        $clock_in = Carbon\Carbon::parse($checkins->time)->format('H:i:00');
                        $early = (new Carbon\Carbon($clock_in))->diff(new Carbon\Carbon($shift_start))->format('%h:%I');
                        $status = "L-P(" . $early . ")";
                        } else if ($shift_start < $clock_in) {
                            $late=(new Carbon\Carbon($clock_in))->diff(new Carbon\Carbon($shift_start))->format('%h:%I');
                            $status = "L-P(" . $late . ")";
                            }
                            }
                            }
                            $visitin = Modules\Attendance\Entities\Visitin::where([['user_id',$user->id],['date', $date]])->orderBy('id', 'desc')->first();
                            if($visitin && $visitin->type == Modules\Attendance\Enums\VisitinType::OUT->value){
                            $status = $visitin ? 'V' : 'A';
                            }
                            @endphp
                            <td>{{ $status }}</td>
                            @endforeach
        </tr>
    </table>
    <br>
    @endforeach
</body>

</html>