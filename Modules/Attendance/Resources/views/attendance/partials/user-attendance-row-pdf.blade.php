<tr class="attendance-{{$user->id}}">
    <td class="fixed-column" style="position: sticky;left: 0;background: white;z-index: 2;">
        <div class="scrollable-content" style="max-width: 150px;overflow-x: auto;white-space: nowrap;">
            <h5 class="table-avatar">
                <span>{{ Str::limit($user->name, 25) }}</span>
            </h5>
        </div>
    </td>
    @for ($i = 1; $i <= $monthDays; $i++)
        @php
            $total_working_hour = 0;
            $date = now()->parse("$year-$month-$i")->toDateString();
            $todayisleave = Modules\Leave\Entities\Leave::where([['user_id', $user->id],['status','approved']])
                        ->with('type')
                        ->whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();
            if ($todayisleave) {
                $LimagePath = public_path('modules/attendance/images/leave.svg');
                $LimageData = base64_encode(file_get_contents($LimagePath));
                $Lsrc = 'data:image/png;base64,' . $LimageData;
                $img = "<img src=" .$Lsrc. " style='width:16px;height: 20px;position: relative !important; top: 10px !important;left:05px !important;' />";

                $keywords = ['Sick', 'sick', 'sick leave', 'Sick Leave' ];
                if (in_array($todayisleave->type->name, $keywords)) {
                    $LimagePath = public_path('modules/attendance/images/sickleave.svg');
                    $LimageData = base64_encode(file_get_contents($LimagePath));
                    $Lsrc = 'data:image/png;base64,' . $LimageData;
                    $img = "<img src=" .$Lsrc. " style='width:1.5px !important;height: 1.5px !important;position: relative !important; bottom: 15px !important; right:15px !important;' />";
                }
            }

            $todayisholiday = Modules\Attendance\Entities\Holiday::whereDate('start_date', '<=', $date)
                        ->whereDate('end_date', '>=', $date)
                        ->first();
            if ($todayisholiday) {
                $HimagePath = public_path('modules/attendance/images/holiday.svg');
                $HimageData = base64_encode(file_get_contents($HimagePath));
                $Hsrc = 'data:image/png;base64,' . $HimageData;
                $img = "<img src=" . $Hsrc . " style='width:16px;height: 20px;position: relative !important; top: 10px !important;left:05px !important;' />";
            }
        @endphp
        <td>
            @if ($todayisleave)
                <a>
                    {!! $img !!}
                </a>
            
            @elseif ($todayisholiday)
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
                            $PimagePath = public_path('modules/attendance/images/present_full.svg');
                            $PimageData = base64_encode(file_get_contents($PimagePath));
                            $Psrc = 'data:image/png;base64,' . $PimageData;

                            $VimagePath = public_path('modules/attendance/images/visit_in.jpeg');
                            $VimageData = base64_encode(file_get_contents($VimagePath));
                            $Vsrc = 'data:image/png;base64,' . $VimageData;
                            $isVisitin = Modules\Attendance\Entities\LocationVisits::query()
                                    ->when(!empty($user), fn ($q) => $q->where('user_id', $user->id))
                                    ->when($date, fn ($q) => $q->whereDate('date', $date))
                                    ->first();
                        @endphp
                        @if ($isVisitin)
                            <img src="{{ $Vsrc }}" class="" style="height: 30px;"/>
                        @else
                            <img src="{{ $Psrc }}" class="" style="width:16px;height: 20px;"/>
                        @endif
                    @else
                        @php
                            $SimagePath = public_path('modules/attendance/images/' . $statusvalue . '.svg');
                            $SimageData = base64_encode(file_get_contents($SimagePath));
                            $Ssrc = 'data:image/png;base64,' . $SimageData;
                        @endphp
                        @if($statusvalue =='sickleave')
                            <img src="{{ $Psrc }}" class="" style="width:1.5px !important;height: 1.5px !important;position: relative !important; bottom: 15px !important; right:15px !important;"/>
                        @else
                            <img src="{{ $Ssrc }}" class="" style="height: 20px; position: relative !important; top: 10px !important;left:05px !important;" />
                        @endif
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
                                $PimagePath = public_path('modules/attendance/images/present_full.svg');
                                $PimageData = base64_encode(file_get_contents($PimagePath));
                                $Psrc = 'data:image/png;base64,' . $PimageData;

                                $VimagePath = public_path('modules/attendance/images/visit_in.jpeg');
                                $VimageData = base64_encode(file_get_contents($VimagePath));
                                $Vsrc = 'data:image/png;base64,' . $VimageData;
                                $isVisitin = Modules\Attendance\Entities\LocationVisits::query()
                                    ->when(!empty($user), fn ($q) => $q->where('user_id', $user->id))
                                    ->when($date, fn ($q) => $q->whereDate('date', $date))
                                    ->first();
                            @endphp
                            @if ($isVisitin)
                                <img src="{{ $Vsrc }}" class="" style="height: 30px;"/>
                            @else
                                <img src="{{ $Psrc }}" class="" style='width:16px;height: 20px;position: relative !important; top: 10px !important;left:05px !important;'/>
                            @endif
                        @else
                            @php
                                $status = $user->attendances->where('date', $date)->first()->status;
                                $clock_in = $user->attendances->where('date', $date)->sortBy('id')->last()->clock_in;

                                $VimagePath = public_path('modules/attendance/images/visit_in.jpeg');
                                $VimageData = base64_encode(file_get_contents($VimagePath));
                                $Vsrc = 'data:image/png;base64,' . $VimageData;
                                $isVisitin = Modules\Attendance\Entities\LocationVisits::query()
                                    ->when(!empty($user), fn ($q) => $q->where('user_id', $user->id))
                                    ->when($date, fn ($q) => $q->whereDate('date', $date))
                                    ->first();
                            @endphp
                            @if($status->value == \Modules\Attendance\Enums\AttendanceStatus::Weekend->value)
                            
                                @if($clock_in!=null && $clock_in != '00:00:00')
                                    @php
                                        $WimagePath = public_path('modules/attendance/images/weekendWork.svg');
                                        $WimageData = base64_encode(file_get_contents($WimagePath));
                                        $Wsrc = 'data:image/png;base64,' . $WimageData;
                                    @endphp
                                    <img src="{{ $Wsrc }}" class="" style="height: 30px;position: relative !important; top: 10px !important;left:05px !important;"/>
                                @else
                                    @php
                                        $DimagePath = public_path('modules/attendance/images/' . $status->value . '.svg');
                                        $DimageData = base64_encode(file_get_contents($DimagePath));
                                        $Dsrc = 'data:image/png;base64,' . $DimageData;
                                    @endphp
                                    @if($status->value =='sickleave')
                                        <img src="{{ $Dsrc }}" class="" style="width:1.5px !important;height: 1.5px !important;position: relative !important; bottom: 15px !important; right:15px !important;"/>
                                    @else
                                        <img src="{{ $Dsrc }}" class="" style="height: 20px;position: relative !important; top: 10px !important;left:05px !important;"/>
                                    @endif
                                @endif
                            @else
                                @php
                                    $DimagePath = public_path('modules/attendance/images/' . $status->value . '.svg');
                                    $DimageData = base64_encode(file_get_contents($DimagePath));
                                    $Dsrc = 'data:image/png;base64,' . $DimageData;
                                @endphp
                                    @if($status->value =='sickleave')
                                        <img src="{{ $Dsrc }}" class="" style="width:1.5px !important;height: 1.5px !important;position: relative !important; bottom: 15px !important; right:15px !important;"/>
                                    @else
                                        @if ($isVisitin)
                                            <img src="{{ $Vsrc }}" class="" style="height: 30px;"/>
                                        @else
                                            <img src="{{ $Dsrc }}" class="" style="height: 20px;position: relative !important; top: 10px !important;left:05px !important;"/>
                                        @endif
                                    @endif
                            @endif
                        @endif
                    @else
                        {{ '-' }}
                    @endif
                </a>
            @endif
        </td>
        @php
            if (getSetting('payroll_calculation') == 'hourly') {
                $working_hours = $user->attendances->where('date', $date)
                                    ->whereIn('status', [
                                        \Modules\Attendance\Enums\AttendanceStatus::Present,
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
                $presentDates = $user->attendances->where('status', \Modules\Attendance\Enums\AttendanceStatus::Present)->unique('date')->count();
                $hours = intdiv($total_working_hour, 60);
                $minutes = $total_working_hour % 60;
                $total_worked_hours = $hours.':'.$minutes;
            @endphp
            @if(getSetting('payroll_calculation') == 'hourly')
                {{ $total_worked_hours }}
            @else
                {{ $presentDates }}/{{ $monthDays }}
            @endif
        </span>
    </td>
</tr>
