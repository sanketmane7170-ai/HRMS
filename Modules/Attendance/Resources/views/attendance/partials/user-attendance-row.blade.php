<tr class="attendance-{{$user->id}}">
    <td class="fixed-column" style="position: sticky;left: 0;background: white;z-index: 2;">
        <div class="scrollable-content" style="max-width: 150px;overflow-x: auto;white-space: nowrap;">
            <h2 class="table-avatar">
                <a href="#" class="avatar avatar-sm me-2">
                    <img class="avatar-img rounded-circle" src="{{ asset($user->getProfileImage()) }}" alt="{{ $user->name }}">
                </a>
                <a href="#">{{ Str::limit($user->name, 25) }}</a>
            </h2>
        </div>
    </td>
    @for ($i = 1; $i <= $monthDays; $i++)
        @php
        $img="-";
            $total_working_hour = 0;
            $date = now()->parse("$year-$month-$i")->toDateString();
            $todayisleave = Modules\Leave\Entities\Leave::where([['user_id', $user->id],['status','approved']])
                        ->with('type')
                        ->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();
            if ($todayisleave) {
                $img = "<img src=" . \Module::asset('attendance:images/leave.svg') . " />";
                $keywords = ['Sick', 'sick', 'sick leave', 'Sick Leave' ];
                if (in_array($todayisleave->type->name, $keywords)) {
                    $img = "<img src=" . \Module::asset('attendance:images/sickleave.svg') . " />";
                }
                $PresentCheckOut = $user->attendances()
                                        ->where('date', $date)
                                        ->orderBy('id', 'desc')
                                        ->first();
                $PresentCheckOutstatus = $PresentCheckOut ? $PresentCheckOut->status : null;
                $Presentclockout = $PresentCheckOut ? $PresentCheckOut->clock_out : null;
                if($todayisleave->is_half_day==1 && $PresentCheckOutstatus == \Modules\Attendance\Enums\AttendanceStatus::Present && $Presentclockout!=null){
                    $img = "<img src=" . \Module::asset('attendance:images/halfday.svg') . " style='width:20px;' />";
                }
            }

            $todayisholiday = Modules\Attendance\Entities\Holiday::whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();
            if ($todayisholiday) {
                $img = "<img src=" . \Module::asset('attendance:images/holiday.svg') . " />";
            }
            $todayisweekend = in_array(\Carbon\Carbon::parse($date)->format('l'), explode(',', $user->workDetail->weekend ?? ''));
            if ($todayisweekend) {
                $img = "<img src=" . \Module::asset('attendance:images/weekend.svg') . " />";
            }
        @endphp
        <td>
            
            @if ($todayisleave)
                <a>
                    {!! $img !!}
                </a>
            
            @elseif ($todayisholiday && !auth()->user()->hasRole(['admin', 'Admin','Super Admin', 'superadmin']))
                @php
                    $status = $user->attendances->where('date', $date)->first();
                    $statusvalue = $status ? $status->status->value : null;
                    $PresentCheckOut = $user->attendances()
                                        ->where('date', $date)
                                        ->orderBy('id', 'desc')
                                        ->first();
                    $PresentCheckOutstatus = $PresentCheckOut ? $PresentCheckOut->status : null;
                    $Presentclockout = $PresentCheckOut ? $PresentCheckOut->clock_out : null;
                @endphp
                @if($statusvalue != null)
                    @if($PresentCheckOutstatus == \Modules\Attendance\Enums\AttendanceStatus::Present && $Presentclockout!=null)
                        @php
                            $isVisitin = Modules\Attendance\Entities\LocationVisits::query()
                                    ->when(!empty($user), fn ($q) => $q->where('user_id', $user->id))
                                    ->when($date, fn ($q) => $q->whereDate('date', $date))
                                    ->first();
                        @endphp
                        @if ($isVisitin)
                            <img src="{{ Module::asset('attendance:images/visit_in.jpeg') }}" class="" style="height: 30px;"/>
                        @else
                            {!! $user->attendances->where('date', $date)->first()->status->PresentCheckOutIcon() !!}
                        @endif
                    @else
                        <img src="{{ Module::asset('attendance:images/' . $statusvalue . '.svg') }}" class="" />
                    @endif
                @else
                    <a>
                        {!! $img !!}
                    </a>
                @endif
            @else
                <a href="{{route('backend.attendance.user-day-attendance.fetch',[$user,$date])}}" class="edit-button" style="color:#042356;" >
                    @if ($user->attendances->contains('date', $date))
                        @if($user->attendances->where('date', $date)->first()->status == \Modules\Attendance\Enums\AttendanceStatus::Present && $user->attendances->where('date', $date)->sortBy('id')->last()->clock_out!=null)
                            @php
                                $isVisitin = Modules\Attendance\Entities\LocationVisits::query()
                                            ->when(!empty($user), fn ($q) => $q->where('user_id', $user->id))
                                            ->when($date, fn ($q) => $q->whereDate('date', $date))
                                            ->first();
                            @endphp
                            @if ($isVisitin)
                                <img src="{{ Module::asset('attendance:images/visit_in.jpeg') }}" class="" style="height: 30px;"/>
                            @else
                                {!! $user->attendances->where('date', $date)->first()->status->PresentCheckOutIcon() !!}
                            @endif
                        @else
                            {{--  {!! $user->attendances->where('date', $date)->first()->status->getIcon() !!}  --}}
                            @php
                                $status = $user->attendances->where('date', $date)->first()->status;
                                $clock_in = $user->attendances->where('date', $date)->sortBy('id')->last()->clock_in;
                                $isVisitin = Modules\Attendance\Entities\LocationVisits::query()
                                            ->when(!empty($user), fn ($q) => $q->where('user_id', $user->id))
                                            ->when($date, fn ($q) => $q->whereDate('date', $date))
                                            ->first();
                            @endphp
                            {{--  @if($status->value != null && $status->value == \Modules\Attendance\Enums\AttendanceStatus::Weekend->value)  --}}
                            @if($status instanceof \Modules\Attendance\Enums\AttendanceStatus && $status === \Modules\Attendance\Enums\AttendanceStatus::Weekend)
                                @if($clock_in!=null && $clock_in != '00:00:00')
                                    <img src="{{ Module::asset('attendance:images/weekendWork.svg') }}" class="" style="height: 30px;"/>
                                @else
                                    @if ($isVisitin)
                                        <img src="{{ Module::asset('attendance:images/visit_in.jpeg') }}" class="" style="height: 30px;"/>
                                    @else
                                        <img src="{{ Module::asset('attendance:images/' . $status->value . '.svg') }}" @if($status->value == 'earlyout' || $status->value == 'halfday') style="height: 20px;" @endif class="" />
                                    @endif
                                @endif
                            @else
                                @if ($isVisitin)
                                    <img src="{{ Module::asset('attendance:images/visit_in.jpeg') }}" class="" style="height: 30px;"/>
                                @else
                                    <img src="{{ Module::asset('attendance:images/' . ($status instanceof \Modules\Attendance\Enums\AttendanceStatus ? $status->value : '') . '.svg') }}" @if($status instanceof \Modules\Attendance\Enums\AttendanceStatus && $status->value == 'earlyout' || $status->value == 'halfday') style="height: 20px;" @endif class="" />
                                @endif
                            @endif
                        @endif
                    @else
                       {!! $img !!}
                    @endif
                </a>
            @endif
        </td>
        @php
            if (getSetting('payroll_calculation') == 'hourly') {
                $working_hours = $user->attendances->where('date', $date)
                                    ->whereIn('status', [
                                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                                        \Modules\Attendance\Enums\AttendanceStatus::Late,
                                        \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                        \Modules\Attendance\Enums\AttendanceStatus::Weekend
                                    ])
                                    ->first();
                $working_hour = $working_hours !=null ? $working_hours->total_worked : 0;
                $total_working_hour = $total_working_hour + $working_hour;
            }
        @endphp
    @endfor
    
    <td class="text-end">
        <span class="text-success">
            @php
                $workingDays = userWorkingDays($user,$month,$year);
                $presentDates = $user->attendances->whereIn('status', [
                                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                                        \Modules\Attendance\Enums\AttendanceStatus::Late,
                                        \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                        \Modules\Attendance\Enums\AttendanceStatus::Weekend
                                    ])->unique('date')->count();
                $hours = intdiv($total_working_hour, 60);
                $minutes = $total_working_hour % 60;
                $total_worked_hours = $hours.':'.$minutes;
            @endphp
            @if(getSetting('payroll_calculation') == 'hourly')
                {{ $total_worked_hours }}
            @else
                {{--  {{ $workingDays['total_working_days'] }}/{{ $monthDays }}  --}}
                {{ $presentDates }}/{{ $monthDays }}
            @endif
        </span>
    </td>
    <td class="text-end">
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Get the input value from the first form
                var selectedYear = document.getElementById('selected_year_input').value;
                var selectedMonth = document.getElementById('selected_month_input').value;

                // Set the input value in the second form
                document.getElementById('second_form{{ $user->id }}').innerHTML += '<input type="hidden" name="selected_year" value="' + selectedYear + '">';
                document.getElementById('second_form{{ $user->id }}').innerHTML += '<input type="hidden" name="selected_month" value="' + selectedMonth + '">';
            });
        </script>
        <form id="second_form{{ $user->id }}" action="{{ route('backend.download.user.attendance.csv', [$user->id]) }}" method="GET">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-download"></i>
            </button>
            <a href="{{ route('backend.user.attendance.history', [$user->id]) }}" class="btn btn-success" title="Checkin History">
                <i class="fa fa-history"></i>
            </a>
            <a href="{{ route('backend.user.visit.history', [$user->id]) }}" class="btn btn-success" title="Visit History">
                <i class="fas fa-map-marked-alt"></i>
            </a>
        </form>
    </td>
</tr>
