@extends('layouts.backend')
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('attendance_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('attendance_report')}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <form action="{{ route('backend.showAttendanceReport') }}" method="POST">
            @csrf
            <div class="att-filter-outer">
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('employee_name') }}:</strong></label>
                            <select name="employee[]" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.users') }}" multiple>
                                <option value="">{{ __trans('search_employee ...') }}</option>
                                @foreach ($filterEmployees as $employee)
                                <option value="{{ $employee->id }}" selected>{{ $employee->employee_id }}
                                    {{ $employee->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('department') }}:</strong></label>
                            {{--  <select name="department" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.departments') }}">  --}}
                            <select name="department" class="form-control">
                                <option value="" >All</option>
                                @foreach($filterDepartment as $value)
                                    <option value="{{ $value->id }}" @if($value->id==$department) selected @endif>
                                        {{ $value->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_month') }}:</strong></label>
                            <select name="month" class="form-control select-search" id="selected_month_input">
                                @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" @if ($month==$i) selected @endif>
                                    {{ date('F', strtotime(date('Y') . '-' . $i)) }}</option>
                                    @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_year') }}:</strong></label>
                            <input type="text" name="year" value="{{ $year }}" class="form-control" id="selected_year_input">
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_start_date') }}:</strong></label>
                            <input type="text" name="start_date" value="{{ $startdate }}" class="form-control datepicker" placeholder="{{__trans('select_start_date')}}">
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_end_date') }}:</strong></label>
                            <input type="text" name="end_date" value="{{ $enddate }}" class="form-control datepicker" placeholder="{{__trans('select_end_date')}}">
                        </div>
                    </div>
                </div>
                <div class="att-filter-box ">
                    <div class="form-group">
                        <label>&nbsp; </label>
                        <button type="submit" class="btn btn-primary w-100">
                            {{ __trans('apply') }}
                        </button>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="export" value="1" class="btn btn-success">
                            <i class="fa fa-download"></i> {{ __trans('export') }}
                        </button>
                        <button type="submit" name="export" value="2" class="btn btn-success">
                            <i class="fa fa-file-pdf"></i> {{ __trans('export-pdf') }}
                        </button>
                        {{--  <button formaction="{{ route('backend.download.attendance.csv') }}" class="btn btn-success">
                            <i class="fa fa-download"></i> {{ __trans('export') }}
                        </button>  --}}
                    </div>
                </div>
            </div>
        </form>
        
        <!-- /Page Header -->
        @if (count($users) > 0)
            @php
            set_time_limit(300);
                if($startdate != ''){
                    $period = new DatePeriod(
                        new DateTime($startdate),
                        new DateInterval('P1D'),
                        (new DateTime($enddate))->modify('+1 day')
                    );
                } else {
                    $yeardata = $year;
                    $monthdata = $month;

                    $startDate =  Carbon\Carbon::create($yeardata, $monthdata, 1)->startOfMonth()->toDateString();
                    $endDate = Carbon\Carbon::create($yeardata, $monthdata, 1)->endOfMonth()->toDateString();

                    $period = new DatePeriod(
                        new DateTime($startDate),
                        new DateInterval('P1D'),
                        (new DateTime($endDate))->modify('+1 day')
                    );
                }
                
            @endphp
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <div class="table-responsive" style="overflow-y: auto;overflow-x: auto;">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <td>{{ __trans('employee') }}</td>
                                            @foreach($period as $date)
                                                <td>{{ $date->format('M-d') }}</td>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                        <tr class="attendance-{{$user->id}}">
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#"> Day name</a>
                                                </h2><br><br>
                                                <h2 class="table-avatar">
                                                    <a href="#"> Shift</a>
                                                </h2><br><br>
                                                <h2 class="table-avatar">
                                                    <a href="#">{{ Str::limit($user->name, 25). ' ('.$user->employee_id.')'.' Clock In'}}</a>
                                                </h2><br><br>
                                                 <h2 class="table-avatar">
                                                    <a href="#"> Clock In Branch</a>
                                                </h2><br><br>
                                                <h2 class="table-avatar">
                                                    <a href="#"> Clock out</a>
                                                </h2><br><br>
                                                  <h2 class="table-avatar">
                                                    <a href="#"> Clock Out Branch</a>
                                                </h2><br><br>
                                                <h2 class="table-avatar">
                                                    @php
                                                        $totalmonthhous = 0;
                                                        $totalpaidday = 0;
                                                        $totalabsent = 0;
                                                        $totalleave = 0;
                                                        $extra_hours = 0;
                                                    @endphp
                                                    @foreach($period as $date)
                                                        @php
                                                            //total hoursh
                                                            $attendances = $user->attendances()->where('date', $date)->get();
                                                            $totalMinutes  = 0;
                                                            $loop1 = 0;
                                                            $Minutes = 0;
                                                            $todayisleave = Modules\Leave\Entities\Leave::where([['user_id', $user->id],['status',Modules\Leave\Enums\LeaveStatus::Approved]])
                                                                                ->whereDate('start_date', '<=', $date)
                                                                                ->whereDate('end_date', '>=', $date)
                                                                                ->first();
                                                            if($todayisleave){
                                                                $statusL = 'L';
                                                                $totalleave = $statusL=='L' ? $totalleave + 1 : 0;
                                                            }
                                                            if(count($attendances) > 0){
                                                                foreach ($attendances as $attendance){
                                                                    $loop1++;
                                                                    $clockintime = $attendance ? $attendance->clock_in : 0;
                                                                    $clockouttime = $attendance ? $attendance->clock_out : 0;
                                                                    if ($clockintime && $clockouttime != null) {
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
                                                                        $totalMinutes  = 0;
                                                                        foreach ($checkins as $row => $checkin) {
                                                                        
                                                                            if ($checkin->type == "in") {
                                                                                $checkinsclockin = $checkin ? $checkin->time : 0;
                                                                            } elseif ($row > 0 && $checkin->type == "out") {
                                                                                $checkinsclockout = $checkin ? $checkin->time : 0;
                                                                            }

                                                                            if ($checkinsclockin && $checkinsclockout) {

                                                                                $Minutes = 0;
                                                                                $clockintime = $checkinsclockin;
                                                                                $clockouttime = $checkinsclockout;
                                                                                $checkinsclockin = "";
                                                                                $checkinsclockout = "";

                                                                                if ($clockintime && $clockouttime) {
                                                                                    $clockinTime = Carbon\Carbon::createFromTimeString($clockintime);
                                                                                    $clockoutTime = Carbon\Carbon::createFromTimeString($clockouttime);
                                                                                    // Check if clock-out time is on the next day
                                                                                    if ($clockoutTime->lessThan($clockinTime)) {
                                                                                        $clockoutTime->addDay();
                                                                                    }
                                                                                    $Minutes = $clockinTime->diffInMinutes($clockoutTime);
                                                                                    $clockin_date = Carbon\Carbon::parse($attendance->date);
                                                                                    $clockout_date = Carbon\Carbon::parse($attendance->clockout_date);
                                                                                    
                                                                                    $totalMinutes += $Minutes;
                                                                                    $totalmonthhous += $Minutes;
                                                                                }
                                                                                }}}
                                                                    else{
                                                                        $Minutes = 0;
                                                                        if ($clockintime && $clockouttime) {
                                                                            $clockinTime = Carbon\Carbon::createFromTimeString($clockintime);
                                                                            $clockoutTime = Carbon\Carbon::createFromTimeString($clockouttime);
                                                                            // Check if clock-out time is on the next day
                                                                            if ($clockoutTime->lessThan($clockinTime)) {
                                                                                $clockoutTime->addDay();
                                                                            }
                                                                            $Minutes = $clockinTime->diffInMinutes($clockoutTime);
                                                                            $clockin_date = Carbon\Carbon::parse($attendance->date);
                                                                            $clockout_date = Carbon\Carbon::parse($attendance->clockout_date);
                                                                            if ($clockout_date->gt($clockin_date)) {
                                                                                $clockinDate = Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                                                                                $clockinTime = Carbon\Carbon::createFromTimeString($clockintime)->format('H:i:s');
                                                                                $clockIndatetime = Carbon\Carbon::parse("$clockinDate $clockinTime");

                                                                                $clockoutDate = Carbon\Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                                                                                $clockoutTime = Carbon\Carbon::createFromTimeString($clockouttime)->format('H:i:s');
                                                                                $clockOutdatetime = Carbon\Carbon::parse("$clockoutDate $clockoutTime");

                                                                                $Minutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                                                                            }
                                                                            $totalMinutes += $Minutes;
                                                                            $totalmonthhous += $totalMinutes;
                                                                        }
                                                                    }
                                                                }
                                                                     if ($attendance->clock_out != null) {
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
                                                                    }
                                                                }
                                                            }
                                                            
                                                            // extra hours calculate
                                                            $shift_time = [];
                                                            $user_shifts = [];
                                                            $totalShiftMinuts = 0;
                                                            $total_worked_hours = 0;
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
                                                                    //$hours = intdiv($totalShiftMinuts, 60);
                                                                    //$minutes = $totalShiftMinuts % 60;
                                                                    //$totalShiftHours = $hours.'.'.$minutes;
                                                                   // 
                                                                    //if($total_worked_hours > $totalShiftHours) {
                                                                     //   $extrahours = Carbon\Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon\Carbon::createFromTimeString($totalShiftHours));
                                                                    //    $extra_hours += $extrahours;
                                                                    //}
                                                                //} else {
                                                                    //$shift_time = [];
                                                                    //$userShifts = [];
                                                                    //if($company_hour > 0){
                                                                    //    if($total_worked_hours > $company_hour) {
                                                                    //        $extrahours = Carbon\Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon\Carbon::createFromTimeString($company_hour));
                                                                    //        $extra_hours += $extrahours;
                                                                    //    }
                                                                    //}
                                                                //}
                                                                $workedHours = floor((float) $total_worked_hours); // Convert to float
                                                                $workedMinutes = round(((float) $total_worked_hours - $workedHours) * 60);
                                                                $totalWorkedTime = sprintf('%02d:%02d:00', $workedHours, $workedMinutes);

                                                                if (count($user_shifts) != 0) {
                                                                    $hours = intdiv((int) $totalShiftMinuts, 60);
                                                                    $minutes = (int) $totalShiftMinuts % 60;
                                                                    $totalShiftHours1 = sprintf('%02d:%02d:00', $hours, $minutes);
                                                                    try {
                                                                            $worked = Carbon\Carbon::createFromFormat('H:i:s', $totalWorkedTime);
                                                                            $shift = Carbon\Carbon::createFromFormat('H:i:s', $totalShiftHours1);

                                                                            if ($worked->greaterThan($shift)) {
                                                                                $extrahours = $worked->diffInMinutes($shift);
                                                                                $extra_hours += $extrahours;
                                                                            }
                                                                        } catch (\Exception $e) {     
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
                                                            }
                                                            // end
                                                            $extra_hourshours = intdiv($extra_hours, 60);
                                                            $extra_hoursminutes = $extra_hours % 60;
                                                        @endphp
                                                    @endforeach
                                                    @php
                                                        $monthHours = intdiv($totalmonthhous, 60);
                                                        $monthminutes = $totalmonthhous % 60;
                                                    @endphp
                                                    <a href="#"> Total Working Hrs  ({{ $monthHours .':'.$monthminutes }}), Extra Working Hrs: ({{ $extra_hourshours.':'.$extra_hoursminutes }})</a>
                                                </h2><br><br>
                                                <h2 class="table-avatar">
                                                    <a href="#"> Remark - Prsent ({{ $totalpaidday }}),Absent({{ $totalabsent }}),off.Leave({{ $totalleave }}) </a>
                                                </h2>
                                            </td>
                                                @foreach($period as $date)
                                                    @php
                                                        //total hoursh
                                                        $totalmonthhous = 0;
                                                        $attendances = $user->attendances()->where('date', $date)->get();
                                                        $loop = 0;
                                                        $clockIn = '00:00';
                                                        $clockOut = '00:00';
                                                        $inTimes = [];
                                                        $outTimes = [];
                                                        $status = 'A';
                                                        $todayisleave = Modules\Leave\Entities\Leave::where([['user_id', $user->id],['status',Modules\Leave\Enums\LeaveStatus::Approved]])
                                                                                ->whereDate('start_date', '<=', $date)
                                                                                ->whereDate('end_date', '>=', $date)
                                                                                ->first();
                                                        if($todayisleave){
                                                            $status = 'L';
                                                        }
                                                        foreach ($attendances as $attendance){
                                                            $loop++;
                                                            $clockIn = $attendance ? $attendance->clock_in : '00:00';
                                                            $clockOut = $attendance ? $attendance->clock_out : '00:00';
                                                            if ($clockIn && $clockOut != null) {
                                                                $clockintime = $attendance ? $attendance->clock_in : 0;
                                                                $clockouttime = $attendance ? $attendance->clock_out : 0;
                                                                $clockoutdate = $attendance->clockout_date != null ? $attendance->clockout_date : $attendance->date;

                                                                $startDateTime = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->clock_in)->toDateTimeString();
                                                                $endDateTime = \Carbon\Carbon::parse($clockoutdate . ' ' . $attendance->clock_out)->toDateTimeString();
                                                                $checkins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                                                                                ->whereDate('date' ,$attendance->date)
                                                                                ->orderBy('id', 'asc')
                                                                                ->get();
                                                                if (count($checkins) > 0) {
                                                                    $checkinsclockin = "";
                                                                    $checkinsclockout = "";
                                                                    $totalMinutes  = 0;
                                                                    $lastIndex = count($checkins) - 1;
                                                                    foreach ($checkins as $row => $checkin) {
                                                                        if ($checkin->type == "in") {
                                                                            $checkinsclockin = $checkin ? $checkin->time : 0;
                                                                            $inTimes[] = $checkin->time;
                                                                        } elseif ($row > 0 && $checkin->type == "out") {
                                                                            $checkinsclockout = $checkin ? $checkin->time : 0;
                                                                            $outTimes[] = $checkin->time;
                                                                        }
                                                                        $isLastRow = ($row === $lastIndex);
                                                                        if ($isLastRow) {
                                                                            if ($checkin->type == "in") {
                                                                                $nextDate = Carbon\Carbon::parse($attendance->date)->addDay();
                                                                                $lastcheckins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                                                                                            ->whereDate('date' ,$nextDate)
                                                                                            ->orderBy('id', 'asc')
                                                                                            ->first();
                                                                                $outTimes[] = $lastcheckins?->time;
                                                                            }
                                                                        }
                                                                        if ($checkinsclockin && $checkinsclockout) {

                                                                            $Minutes = 0;
                                                                            $clockintime = $checkinsclockin;
                                                                            $clockouttime = $checkinsclockout;
                                                                            $checkinsclockin = "";
                                                                            $checkinsclockout = "";

                                                                            if ($clockintime && $clockouttime) {
                                                                                $clockinTime = Carbon\Carbon::createFromTimeString($clockintime);
                                                                                $clockoutTime = Carbon\Carbon::createFromTimeString($clockouttime);
                                                                                // Check if clock-out time is on the next day
                                                                                if ($clockoutTime->lessThan($clockinTime)) {
                                                                                    $clockoutTime->addDay();
                                                                                }
                                                                                $clockin_date = Carbon\Carbon::parse($attendance->date);
                                                                                $clockout_date = Carbon\Carbon::parse($attendance->clockout_date);
                                                                                $totalMinutes = $clockinTime->diffInMinutes($clockoutTime);
                                                                                
                                                                                $totalmonthhous = $totalmonthhous + $totalMinutes;
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $totalMinutes  = 0;

                                                                    if ($clockintime && $clockouttime) {
                                                                        $clockinTime = Carbon\Carbon::createFromTimeString($clockintime);
                                                                        $clockoutTime = Carbon\Carbon::createFromTimeString($clockouttime);
                                                                        // Check if clock-out time is on the next day
                                                                        if ($clockoutTime->lessThan($clockinTime)) {
                                                                            $clockoutTime->addDay();
                                                                        }
                                                                        $clockin_date = Carbon\Carbon::parse($attendance->date);
                                                                        $clockout_date = Carbon\Carbon::parse($attendance->clockout_date);
                                                                        $totalMinutes = $clockinTime->diffInMinutes($clockoutTime);
                                                                        if ($clockout_date->gt($clockin_date)) {
                                                                            $clockinDate = Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                                                                            $clockinTime = Carbon\Carbon::createFromTimeString($clockintime)->format('H:i:s');
                                                                            $clockIndatetime = Carbon\Carbon::parse("$clockinDate $clockinTime");

                                                                            $clockoutDate = Carbon\Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                                                                            $clockoutTime = Carbon\Carbon::createFromTimeString($clockouttime)->format('H:i:s');
                                                                            $clockOutdatetime = Carbon\Carbon::parse("$clockoutDate $clockoutTime");

                                                                            $totalMinutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                                                                        }
                                                                        $totalmonthhous = $totalmonthhous + $totalMinutes;
                                                                    }
                                                                }
                                                            }
                                                            if ($attendance->clock_out != null) {
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
                                                            $status = $attendance ? Illuminate\Support\Str::substr($attendance->status->name, 0, 1) : 'A';
                                                            
                                                        }
                                                        //
                                                        $visitins = Modules\Attendance\Entities\Visitin::where([['user_id',$user->id],['date', $date]])->get();
                                                        $visitintime = null;
                                                        $visitouttime = null;
                                                        $totalVMinutes = 0;
                                                        foreach ($visitins as $visitin){
                                                            if($visitin->type == Modules\Attendance\Enums\VisitinType::IN->value){
                                                                $clockIn = $visitin->time;
                                                                $visitintime = Carbon\Carbon::createFromTimeString($visitin->time);
                                                            }
                                                            if($visitin->type == Modules\Attendance\Enums\VisitinType::OUT->value){
                                                                if ($visitintime instanceof Carbon\Carbon) {
                                                                    $status = $visitin ? 'V' : 'A';
                                                                    $clockOut = $visitin->time;
                                                                    $visitouttime = Carbon\Carbon::createFromTimeString($visitin->time);
                                                                    $VMinutes = $visitintime->diffInMinutes($visitouttime);
                                                                    $totalVMinutes += $VMinutes;
                                                                    $visitintime = null;
                                                                    $totalmonthhous += $totalVMinutes;
                                                                }
                                                            }
                                                        }
                                                        $monthHours = intdiv($totalmonthhous, 60);
                                                        $monthminutes = $totalmonthhous % 60;
                                                    @endphp
                                                    @php
                                                        $clockInBranch ="NA";
                                                        $clockOutBranch ="NA";
                                                        $attendance = $user->attendances()->where('date', $date)->orderBy('id','asc')->first();
                                                        if($attendance){
                                                        $clockInbranch = $user->checkins()
                                                            ->where('date', $date)
                                                            ->where('type', 'in')
                                                            ->first();

                                                        $clockOutbranch = $user->checkins()
                                                            ->where('date', $date)
                                                            ->where('type', 'out')
                                                            ->orderByDesc('id')
                                                            ->first();
                                                             $clockInBranch  = $clockInbranch?->branch?->name ?? "NA";
                                                            $clockOutBranch = $clockOutbranch?->branch?->name ?? "NA";

                                                            $user_shift = Modules\Shift\Entities\UsersShift::where([['user_id',$user->id],['assigned_for_date', $date]])->with('shift_schedule_information')->first();
                                                        
                                                            if ($user_shift) {
                                                                
                                                                $shift_start =  Carbon\Carbon::parse($user_shift->shift_schedule_information->shift_start)->format('H:i:00');
                                                                $clock_in =  Carbon\Carbon::parse($attendance->clock_in)->format('H:i:00');
                                                                // $visit_in =  Carbon::parse($attendance[0]->visit_in)->format('H:i:00') ;
                                                                $locationvisits = Modules\Attendance\Entities\LocationVisits::where('user_id', $user->id)
                                                                    ->where('date', $date)
                                                                    ->orderBy('id', 'asc')->first();
                                                                if ($locationvisits) {
                                                                    $visit_in =  Carbon\Carbon::parse($locationvisits->visit_in)->format('H:i:00');
                                                                    // dd($visit_in);  
                                                                    if ($shift_start <  $visit_in) {
                                                                        // dd($locationvisits->visit_in);
                                                                        $late =  (new Carbon\Carbon($visit_in))->diff(new Carbon\Carbon($shift_start))->format('%h:%I');
                                                                        $status = "L-P(" . $late . ")";
                                                                    }
                                                                } else {
                                                                    $checkins = Modules\Attendance\Entities\Checkin::where('user_id', $user->id)
                                                                        ->where('date', $date)
                                                                        ->where('type', 'in')
                                                                        ->orderBy('id', 'asc')->first();
                                                                    
                                                                    if (isset($checkins) && $shift_start <  Carbon\Carbon::parse($checkins->time)->format('H:i:00')) {
                                                                        $clock_in =  Carbon\Carbon::parse($checkins->time)->format('H:i:00');
                                                                        $early =  (new Carbon\Carbon($clock_in))->diff(new Carbon\Carbon($shift_start))->format('%h:%I');
                                                                        $status = "L-P(" . $early . ")";
                                                                    } else if ($shift_start <  $clock_in) {
                                                                        $late =  (new Carbon\Carbon($clock_in))->diff(new Carbon\Carbon($shift_start))->format('%h:%I');
                                                                        $status = "L-P(" . $late . ")";
                                                                    }
                                                                }
                                                                
                                                            }
                                                        }
                                                    $show_shift = [];
                                                    $user_shifts = Modules\Shift\Entities\UsersShift::where([['user_id',$user->id],['assigned_for_date', $date]])->with('shift_schedule_information')->get();
                                                    foreach ($user_shifts as $user_shift){
                                                        $show_shift[] = '('.\Carbon\Carbon::parse($user_shift->shift_schedule_information->shift_start)->format('H:i') . '-' . \Carbon\Carbon::parse($user_shift->shift_schedule_information->shift_end)->format('H:i').')';
                                                    }
                                                    $show_shift_string = implode('<br>', $show_shift);
                                                    @endphp
                                                    <td>
                                                        {{ $date->format('D') }}<br><br>
                                                        {!! count($user_shifts) > 0 ? $show_shift_string : '-' !!}<br><br>
                                                        @if($inTimes == [])
                                                            NA<br><br>
                                                        @else
                                                            @php
                                                            $inTimes = array_unique($inTimes);
                                                                sort($inTimes);
                                                            @endphp
                                                            @foreach($inTimes as $timein)
                                                                {{ $timein }} <br>
                                                            @endforeach
                                                            <br><br>
                                                        @endif
                                                        {{--  {{ $clockIn }}<br><br>  --}}
                                                        {{ $clockInBranch }}<br><br>
                                                         @if($outTimes == [])
                                                            NA<br><br>
                                                        @else
                                                            @php
                                                              $outTimes = array_unique($outTimes);  
                                                                sort($outTimes);
                                                            @endphp
                                                            @foreach($outTimes as $timeout)
                                                                {{ $timeout }} <br>
                                                            @endforeach
                                                            <br><br>
                                                        @endif
                                                        {{--  {{ $clockOut }}<br><br>  --}}
                                                        {{ $clockOutBranch }}<br><br>
                                                        {{ $monthHours.':'.$monthminutes }}<br><br>
                                                        {{ $status }}
                                                    </td>
                                                @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {{--  @else
            @if($search==false)
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            <p class="text-danger">{{__trans('no_employee_found')}}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif  --}}
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
    loadAjaxSelect2();
</script>
<script>
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
    $('select[name="month"]').on('change', function () {
        $('input[name="start_date"], input[name="end_date"]').val('');
    });
    
</script>

@endpush
