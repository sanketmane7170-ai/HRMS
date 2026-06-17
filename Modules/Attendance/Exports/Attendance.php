<?php
namespace Modules\Attendance\Exports;

use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Attendance\Entities\Visitin;
use Modules\Attendance\Enums\VisitinType;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Shift\Entities\UsersShift;

class Attendance implements FromQuery, WithHeadings, ShouldAutoSize, WithMapping
{
    public $request, $userId, $month, $year, $daysInMonth, $start_date, $end_date, $monthdata, $departname;

    public function __construct(Request $request, $userId = null)
    {
        $this->request     = $request;
        $this->start_date  = $request->start_date ? $request->start_date : '';
        $this->end_date    = $request->end_date ? $request->end_date : '';
        $this->year        = $request->year ? $request->year : $request->input('selected_year', date('Y'));
        $this->month       = $request->month ? $request->month : $request->input('selected_month', date('m'));
        $this->monthdata   = $request->start_date ? 'From: ' . $request->start_date . ' To Date : ' . $request->end_date : Carbon::create()->month($this->month)->format('F');
        $this->daysInMonth = now()->month($this->month)->daysInMonth;
        $departname        = Department::find($this->request->department);
        $this->departname  = $departname ? $departname->name : 'All';
        $this->userId      = $userId ? $userId : null;
    }

    public function query()
    {
        $employees = array_filter($this->request->employee ?? [], function ($value) {
            return ! is_null($value);
        });

        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->withWhereHas('attendances', function ($query) {
                if ($this->start_date && $this->end_date) {
                    $query->whereBetween('date', [$this->start_date, $this->end_date]);
                } else {
                    $query->whereMonth('date', $this->month)->whereYear('date', $this->year);
                }
            })
            ->when(! empty($employees), function ($query) {
                $query->whereIn('id', $this->request->employee);
            })
            ->when($this->userId, function ($query) {
                $query->where('id', $this->userId);
            })
            ->when(! empty($this->request->department), function ($query) {
                $query->where('department_id', $this->request->department);
            });

        return $users;
    }

    public function map($user): array
    {
        $rows   = [];
        $rows[] = [''];

        // Row 2: Headers for Dates
        if ($this->start_date != '') {
            $period = new \DatePeriod(
                new \DateTime($this->start_date),
                new \DateInterval('P1D'),
                (new \DateTime($this->end_date))->modify('+1 day')
            );
        } else {
            $yeardata  = $this->year;
            $monthdata = $this->month;

            $startDate = Carbon::create($yeardata, $monthdata, 1)->startOfMonth()->toDateString();
            $endDate   = Carbon::create($yeardata, $monthdata, 1)->endOfMonth()->toDateString();

            $period = new \DatePeriod(
                new \DateTime($startDate),
                new \DateInterval('P1D'),
                (new \DateTime($endDate))->modify('+1 day')
            );
        }
        $dateHeaders = ['Name: ' . $user->name . ' (' . $user->employee_id . ')'];
        foreach ($period as $date) {
            $day           = Carbon::parse($date)->format('M-d');
            $dateHeaders[] = $day;
        }
        $rows[] = $dateHeaders;

        // Row 2: Day name
        $dayNameRow = ['Day Name'];
        foreach ($period as $date) {
            $day          = Carbon::parse($date)->format('D');
            $dayNameRow[] = $day;
        }
        $rows[] = $dayNameRow;

        // Row 2: Shift
        $ShiftRow = ['Shift'];
        foreach ($period as $date) {
            $attendance  = $user->attendances()->where('date', $date)->first();
            $user_shifts = UsersShift::where([['user_id', $user->id], ['assigned_for_date', $date]])->with('shift_schedule_information')->get();
            $show_shift  = [];
            foreach ($user_shifts as $user_shift) {
                $show_shift[] = '(' . Carbon::parse($user_shift->shift_schedule_information->shift_start)->format('H:i') . '-' . Carbon::parse($user_shift->shift_schedule_information->shift_end)->format('H:i') . ')';
            }
            $show_shift_string = implode(',', $show_shift);
            if (count($user_shifts) > 0) {
                $show_shift_string = $show_shift_string;
            } else {
                $show_shift_string = '-';
            }
            $ShiftRow[] = $show_shift_string;
        }
        $rows[] = $ShiftRow;

        // Row 3: Clock In Times
        $clockInRow = ['Clock In'];
        foreach ($period as $date) {
            $date       = Carbon::parse($date)->toDateString();
            $attendance = $user->attendances->where('date', $date)->first();
            $status     = $attendance ? Str::substr($attendance->status->name, 0, 1) : '00:00';
            // $clockInRow[] = $attendance ? $attendance->clock_in : $status;
            $clockoutdate = $attendance?->clockout_date != null ? $attendance->clockout_date : $date;

            $checkins = Checkin::where('user_id', $user->id)
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
            $visitin = Visitin::where([['user_id', $user->id], ['date', $date]])->orderBy('id', 'asc')->first();
            if ($visitin && $visitin->type == VisitinType::IN->value) {
                $clockInRow[count($clockInRow) - 1] = $visitin->time;
            }
            $clockRowIn   = isset($inTimes) ? implode(',', $inTimes) : '-';
            $clockInRow[] = $clockRowIn;
        }
        $rows[] = $clockInRow;

        $clockInBranchRow = ['Clock In Branch'];
        foreach ($period as $date) {
            $checkinIn = $user->checkins()
                ->whereDate('date', $date)
                ->where('type', 'in')
                ->orderBy('id', 'asc')
                ->first();
            $clockInBranchRow[] = $checkinIn?->branch?->name ?? 'N/A';
        }
        $rows[] = $clockInBranchRow;

        // Row 4: Clock Out Times
        $clockOutRow = ['Clock Out'];
        foreach ($period as $date) {
            $date       = Carbon::parse($date)->toDateString();
            $attendance = $user->attendances->where('date', $date)->first();
            $status     = $attendance ? Str::substr($attendance->status->name, 0, 1) : '00:00';
            // $clockOutRow[] = $attendance ? $attendance->clock_out : $status;
            $clockoutdate = $attendance?->clockout_date != null ? $attendance->clockout_date : $date;

            $checkins = Checkin::where('user_id', $user->id)
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
                            $nextDate     = Carbon::parse($date)->addDay();
                            $lastcheckins = Checkin::where('user_id', $user->id)
                                ->whereDate('date', $nextDate)
                                ->orderBy('id', 'asc')
                                ->first();
                            $outTimes[] = $lastcheckins?->time;
                        }
                    }
                }
            }

            $visitin = Visitin::where([['user_id', $user->id], ['date', $date]])->orderBy('id', 'desc')->first();
            if ($visitin && $visitin->type == VisitinType::OUT->value) {
                $clockOutRow[count($clockOutRow) - 1] = $visitin->time;
            }
            $clockRowOut   = isset($outTimes) ? implode(',', $outTimes) : '-';
            $clockOutRow[] = $clockRowOut;
        }
        $rows[]            = $clockOutRow;
        $clockOutBranchRow = ['Clock Out Branch'];
        foreach ($period as $date) {
            $checkinOut = $user->checkins()
                ->whereDate('date', $date)
                ->where('type', 'out')
                ->orderBy('id', 'desc')
                ->first();
            $clockOutBranchRow[] = $checkinOut?->branch?->name ?? 'N/A';
        }
        $rows[] = $clockOutBranchRow;
        // Row 5: Total Working Hrs
        $totalmonthhous = 0;
        $totalpaidday   = 0;
        $totalabsent    = 0;
        $totalleave     = 0;
        $extra_hours    = 0;
        foreach ($period as $date) {
            $date         = Carbon::parse($date)->toDateString();
            $attendances  = $user->attendances()->where('date', $date)->get();
            $totalMinutes = 0;
            $loop1        = 0;
            foreach ($attendances as $attendance) {
                Log::error('attendance: ' . $attendance->date);
                $loop1++;
                $clockin  = $attendance ? $attendance->clock_in : 0;
                $clockout = $attendance ? $attendance->clock_out : 0;
                $Minutes  = 0;
                if ($clockin && $clockout != null) {
                    $clockinTime  = Carbon::createFromTimeString($clockin);
                    $clockoutTime = Carbon::createFromTimeString($clockout);
                    // $checkins = Checkin::where('user_id', $user->id)
                    //     ->where('date', $date)
                    //     ->orderBy('id', 'asc')->get();
                    $clockInCreate = Carbon::parse($attendance->created_at);
                    $clockInStart  = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                    $clockInEnd    = Carbon::parse($attendance->clockout_date . ' ' . $attendance->clock_out);

                    $checkins = Checkin::where('user_id', $user->id)
                        ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$clockInStart->toDateTimeString()])
                        ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$clockInEnd->toDateTimeString()])
                        ->orderBy('id', 'asc')
                        ->get();

                    if (count($checkins) > 0) {
                        $checkinsclockin  = "";
                        $checkinsclockout = "";
                        foreach ($checkins as $row => $checkin) {
                            // if ($attendance->date == "2025-06-03") {

                            if ($checkin->type == "in") {
                                $checkinsclockin = $checkin ? $checkin->time : 0;
                            } elseif ($row > 0 && $checkin->type == "out") {
                                $checkinsclockout = $checkin ? $checkin->time : 0;
                            }

                            if ($checkinsclockin && $checkinsclockout) {

                                $Minutes  = 0;
                                $clockin  = $checkinsclockin;
                                $clockout = $checkinsclockout;

                                $checkinsclockin  = "";
                                $checkinsclockout = "";
                                if ($clockin && $clockout) {
                                    $clockinTime = Carbon::createFromTimeString($clockin);

                                    $clockoutTime = Carbon::createFromTimeString($clockout);
                                    if ($clockoutTime->lessThan($clockinTime)) {
                                        $clockoutTime->addDay();
                                    }
                                    $Minutes = $clockinTime->diffInMinutes($clockoutTime);

                                    // $clockin_date = Carbon::parse($attendance->date);
                                    // $clockout_date = Carbon::parse($attendance->clockout_date);
                                    // if ($clockout_date->gt($clockin_date)) {
                                    //     $clockinDate = Carbon::parse($attendance->date)->format('Y-m-d');
                                    //     $clockinTime = Carbon::createFromTimeString($clockin)->format('H:i:s');
                                    //     $clockIndatetime = Carbon::parse("$clockinDate $clockinTime");

                                    //     $clockoutDate = Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                                    //     $clockoutTime = Carbon::createFromTimeString($clockout)->format('H:i:s');
                                    //     $clockOutdatetime = Carbon::parse("$clockoutDate $clockoutTime");

                                    //     $Minutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                                    // }

                                    $totalMinutes   = $totalMinutes + $Minutes;
                                    $totalmonthhous = $totalmonthhous + $Minutes;
                                }
                            }
                            // }
                        }
                    } else {
                        $Minutes = 0;
                        if ($clockin && $clockin) {
                            $clockinTime = Carbon::createFromTimeString($clockin);

                            $clockoutTime = Carbon::createFromTimeString($clockout);
                            if ($clockoutTime->lessThan($clockinTime)) {
                                $clockoutTime->addDay();
                            }
                            $Minutes = $clockinTime->diffInMinutes($clockoutTime);

                            $clockin_date  = Carbon::parse($attendance->date);
                            $clockout_date = Carbon::parse($attendance->clockout_date);
                            if ($clockout_date->gt($clockin_date)) {
                                $clockinDate     = Carbon::parse($attendance->date)->format('Y-m-d');
                                $clockinTime     = Carbon::createFromTimeString($clockin)->format('H:i:s');
                                $clockIndatetime = Carbon::parse("$clockinDate $clockinTime");

                                $clockoutDate     = Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                                $clockoutTime     = Carbon::createFromTimeString($clockout)->format('H:i:s');
                                $clockOutdatetime = Carbon::parse("$clockoutDate $clockoutTime");

                                $Minutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                            }

                            $totalMinutes   = $totalMinutes + $Minutes;
                            $totalmonthhous = $totalmonthhous + $totalMinutes;
                        }
                    }
                }
                if ($attendance->clock_out != null) {
                    $clockInStart = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                    $clockInEnd   = Carbon::parse($attendance->clockout_date . ' ' . $attendance->clock_out);

                    $breakins = Breakin::where('user_id', $user->id)
                        ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$clockInStart->toDateTimeString()])
                        ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$clockInEnd->toDateTimeString()])
                        ->orderBy('id', 'asc')
                        ->get();

                    if (count($breakins) > 0) {
                        $breakinsclockin  = "";
                        $breakinsclockout = "";
                        foreach ($breakins as $row => $breakin) {

                            if ($breakin->type == "in") {
                                $breakinsclockin = $breakin ? $breakin->time : 0;
                            } elseif ($row > 0 && $breakin->type == "out") {
                                $breakinsclockout = $breakin ? $breakin->time : 0;
                            }

                            if ($breakinsclockin && $breakinsclockout) {

                                $Minutes  = 0;
                                $clockin  = $breakinsclockin;
                                $clockout = $breakinsclockout;

                                $breakinsclockin  = "";
                                $breakinsclockout = "";
                                if ($clockin && $clockout) {
                                    $clockinTime = Carbon::createFromTimeString($clockin);

                                    $clockoutTime = Carbon::createFromTimeString($clockout);
                                    if ($clockoutTime->lessThan($clockinTime)) {
                                        $clockoutTime->addDay();
                                    }
                                    $Minutes = $clockinTime->diffInMinutes($clockoutTime);

                                    $totalMinutes   = $totalMinutes - $Minutes;
                                    $totalmonthhous = $totalmonthhous - $Minutes;
                                }
                            }
                        }
                    }
                }

                if ($loop1 === 1) {
                    $atstatus     = $attendance && $Minutes > 0 ? Str::substr($attendance->status->name, 0, 1) : '-';
                    $totalpaidday = $atstatus == 'P' ? $totalpaidday + 1 : $totalpaidday + 0;
                }
            }
            $todayisleave = Leave::where([['user_id', $user->id], ['status', LeaveStatus::Approved]])
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();
            if ($todayisleave) {
                $statusL    = 'L';
                $totalleave = $statusL == 'L' ? $totalleave + 1 : 0;
            }
            // extra hours calculate
            $shift_time         = [];
            $user_shifts        = [];
            $totalShiftMinuts   = 0;
            $total_worked_hours = '0';
            if (count($attendances) > 0) {
                $hours              = intdiv($totalMinutes, 60);
                $minutes            = $totalMinutes % 60;
                $total_worked_hours = $hours . '.' . $minutes;

                $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
                $user_shifts  = User::find($user->id)
                    ->assigned_shifts()
                    ->with('shift_schedule_information')
                    ->where('assigned_for_date', $attendance->date)
                    ->get();
                foreach ($user_shifts as $index => $shiftData) {
                    $shift = $shiftData->shift_schedule_information;
                    // Convert shift start and end times to Carbon instances
                    $shiftStart = Carbon::parse($shift->shift_start);
                    $shiftEnd   = Carbon::parse($shift->shift_end);

                    // Calculate the hours between shift start and end
                    if ($shiftEnd->lessThan($shiftStart)) {
                        $shiftEnd->addDay();
                    }
                    $hoursDifference = $shiftEnd->diffInMinutes($shiftStart);

                    $totalShiftMinuts += $hoursDifference;
                    $shift_time[]      = $shift->shift_start . '-' . $shift->shift_end;
                }

                //if(count($user_shifts) != 0){
                //    $hours = intdiv($totalShiftMinuts, 60);
                //    $minutes = $totalShiftMinuts % 60;
                //    $totalShiftHours = $hours.'.'.$minutes;
                //    if($total_worked_hours > $totalShiftHours) {
                //        $extrahours = Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon::createFromTimeString($totalShiftHours));
                //        $extra_hours += $extrahours;
                //    }
                //} else {
                //    $shift_time = [];
                //    $userShifts = [];
                //    if($company_hour > 0){
                //        if($total_worked_hours > $company_hour) {
                //            $extrahours = Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon::createFromTimeString($company_hour));
                //            $extra_hours += $extrahours;
                //        }
                //    }
                //}
                $workedHours   = floor((float) $total_worked_hours);
                $workedMinutes = (int) round(((float) $total_worked_hours - $workedHours) * 60);
                // Prevent invalid minute value
                if ($workedMinutes === 60) {
                    $workedMinutes  = 0;
                    $workedHours   += 1;
                }
                $totalWorkedTime  = sprintf('%02d:%02d:00', $workedHours, $workedMinutes);
                Log::error('workedHours: ' . $workedHours);
                Log::error('workedMinutes: ' . $workedMinutes);
                Log::error('totalWorkedTime: ' . $totalWorkedTime);

                if (count($user_shifts) != 0) {
                    $hours   = intdiv((int) $totalShiftMinuts, 60);
                    $minutes = (int) $totalShiftMinuts % 60;

                    // Prevent invalid minute value
                    if ($minutes === 60) {
                        $minutes  = 0;
                        $hours   += 1;
                    }

                    $totalShiftHours1 = sprintf('%02d:%02d:00', $hours, $minutes);
                    Log::error('totalShiftHours1: ' . $totalShiftHours1);

                    try {
                        $workedTimeCarbon = Carbon::createFromFormat('H:i:s', $totalWorkedTime);
                        $shiftTimeCarbon  = Carbon::createFromFormat('H:i:s', $totalShiftHours1);

                        if ($workedTimeCarbon->greaterThan($shiftTimeCarbon)) {
                            $extrahours   = $workedTimeCarbon->diffInMinutes($shiftTimeCarbon);
                            $extra_hours += $extrahours;
                        }
                    } catch (\Exception $e) {
                        Log::error('Carbon time format error', [
                            'worked' => $totalWorkedTime,
                            'shift'  => $totalShiftHours1,
                            'error'  => $e->getMessage(),
                        ]);
                    }
                } else {
                    if ($company_hour > 0) {
                        $companyHours   = floor((float) $company_hour);
                        $companyMinutes = round(((float) $company_hour - $companyHours) * 60);
                        $companyTime    = sprintf('%02d:%02d:00', $companyHours, $companyMinutes);

                        try {
                            $workedTime     = Carbon::createFromFormat('H:i:s', $totalWorkedTime);
                            $companyTimeObj = Carbon::createFromFormat('H:i:s', $companyTime);
                            Log::error('workedTime: ' . $workedTime);
                            Log::error('companyTimeObj: ' . $companyTimeObj);
                            if ($workedTime->greaterThan($companyTimeObj)) {
                                $extrahours   = $workedTime->diffInMinutes($companyTimeObj);
                                $extra_hours += $extrahours;

                                Log::error('extrahours: ' . $extrahours);
                            }
                        } catch (\Exception $e) {
                            Log::error('Time parsing or diff error: ' . $e->getMessage());
                            // Optionally handle the error gracefully, e.g. continue processing
                        }
                    }
                }
            } else {
                if (! $todayisleave) {
                    $totalabsent = $totalabsent + 1;
                    $visitin     = Visitin::where([['user_id', $user->id], ['date', $date]])->first();
                    if ($visitin) {
                        $totalabsent = $totalabsent - 1;
                    }
                }
            }

            $visitins      = Visitin::where([['user_id', $user->id], ['date', $date]])->get();
            $visitintime   = null;
            $visitouttime  = null;
            $totalVMinutes = 0;
            foreach ($visitins as $visitin) {
                if ($visitin->type == VisitinType::IN->value) {
                    $visitintime = Carbon::createFromTimeString($visitin->time);
                }
                if ($visitin->type == VisitinType::OUT->value) {
                    if ($visitintime instanceof Carbon\Carbon) {
                        $visitouttime    = Carbon::createFromTimeString($visitin->time);
                        $VMinutes        = $visitintime->diffInMinutes($visitouttime);
                        $totalVMinutes  += $VMinutes;
                        $visitintime     = null;
                        $totalmonthhous += $totalVMinutes;
                        $attendancetime  = $visitin->time;
                    }
                }
            }
            // end
            $extra_hourshours   = intdiv($extra_hours, 60);
            $extra_hoursminutes = $extra_hours % 60;
        }

        $monthHours   = intdiv($totalmonthhous, 60);
        $monthminutes = $totalmonthhous % 60;
        $clockInRow   = ['Total Working Hrs: ' . $monthHours . ':' . $monthminutes . ', Extra Working Hrs: ' . $extra_hourshours . ':' . $extra_hoursminutes];
        // $extraHoursRow = ['Total Extra Working Hrs: ' . $extra_hours];
        foreach ($period as $date) {
            $date         = Carbon::parse($date)->toDateString();
            $attendances  = $user->attendances()->where('date', $date)->get();
            $hours        = 0;
            $totalMinutes = 0;
            $minutes      = 0;
            foreach ($attendances as $attendance) {
                $clockin  = $attendance ? $attendance->clock_in : 0;
                $clockout = $attendance ? $attendance->clock_out : 0;
                if ($clockin && $clockout != null) {
                    // $checkins = Checkin::where('user_id', $user->id)
                    //     ->where('date', $date)
                    //     ->orderBy('id', 'asc')->get();
                    $clockInStart = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
                    $clockInEnd   = Carbon::parse($attendance->clockout_date . ' ' . $attendance->clock_out);

                    $checkins = Checkin::where('user_id', $user->id)
                        ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$clockInStart->toDateTimeString()])
                        ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$clockInEnd->toDateTimeString()])
                        ->orderBy('id', 'asc')
                        ->get();

                    if (count($checkins) > 0) {
                        $checkinsclockin  = "";
                        $checkinsclockout = "";
                        foreach ($checkins as $row => $checkin) {
                            // if ($attendance->date == "2025-06-03") {
                            if ($checkin->type == "in") {
                                $checkinsclockin = $checkin ? $checkin->time : 0;
                            } elseif ($row > 0 && $checkin->type == "out") {
                                $checkinsclockout = $checkin ? $checkin->time : 0;
                            }

                            if ($checkinsclockin && $checkinsclockout) {

                                $Minutes          = 0;
                                $clockin          = $checkinsclockin;
                                $clockout         = $checkinsclockout;
                                $checkinsclockin  = "";
                                $checkinsclockout = "";

                                if ($clockin && $clockout) {
                                    $clockinTime  = Carbon::createFromTimeString($clockin);
                                    $clockoutTime = Carbon::createFromTimeString($clockout);
                                    // Calculate the total difference
                                    if ($clockoutTime->lessThan($clockinTime)) {
                                        $clockoutTime->addDay();
                                    }
                                    $Minutes       = $clockinTime->diffInMinutes($clockoutTime);
                                    $clockin_date  = Carbon::parse($attendance->date);
                                    $clockout_date = Carbon::parse($attendance->clockout_date);
                                    // if ($clockout_date->gt($clockin_date)) {
                                    //     $clockinDate = Carbon::parse($attendance->date)->format('Y-m-d');
                                    //     $clockinTime = Carbon::createFromTimeString($clockin)->format('H:i:s');
                                    //     $clockIndatetime = Carbon::parse("$clockinDate $clockinTime");

                                    //     $clockoutDate = Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                                    //     $clockoutTime = Carbon::createFromTimeString($clockout)->format('H:i:s');
                                    //     $clockOutdatetime = Carbon::parse("$clockoutDate $clockoutTime");

                                    //     $Minutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                                    // }
                                    $totalMinutes = $totalMinutes + $Minutes;
                                }
                            }
                            // }
                        }
                    } else {
                        if ($clockin && $clockout) {
                            $clockinTime  = Carbon::createFromTimeString($clockin);
                            $clockoutTime = Carbon::createFromTimeString($clockout);
                            // Calculate the total difference
                            if ($clockoutTime->lessThan($clockinTime)) {
                                $clockoutTime->addDay();
                            }
                            $Minutes       = $clockinTime->diffInMinutes($clockoutTime);
                            $clockin_date  = Carbon::parse($attendance->date);
                            $clockout_date = Carbon::parse($attendance->clockout_date);
                            if ($clockout_date->gt($clockin_date)) {
                                $clockinDate     = Carbon::parse($attendance->date)->format('Y-m-d');
                                $clockinTime     = Carbon::createFromTimeString($clockin)->format('H:i:s');
                                $clockIndatetime = Carbon::parse("$clockinDate $clockinTime");

                                $clockoutDate     = Carbon::parse($attendance->clockout_date)->format('Y-m-d');
                                $clockoutTime     = Carbon::createFromTimeString($clockout)->format('H:i:s');
                                $clockOutdatetime = Carbon::parse("$clockoutDate $clockoutTime");

                                $Minutes = $clockIndatetime->diffInMinutes($clockOutdatetime);
                            }
                            $totalMinutes = $totalMinutes + $Minutes;
                        }
                    }
                }
                if ($attendance->clock_out != null) {
                    $breakins = Breakin::where('user_id', $user->id)
                        ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$clockInStart->toDateTimeString()])
                        ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$clockInEnd->toDateTimeString()])
                        ->orderBy('id', 'asc')
                        ->get();

                    if (count($breakins) > 0) {
                        $breakinsclockin  = "";
                        $breakinsclockout = "";
                        foreach ($breakins as $row => $breakin) {

                            if ($breakin->type == "in") {
                                $breakinsclockin = $breakin ? $breakin->time : 0;
                            } elseif ($row > 0 && $breakin->type == "out") {
                                $breakinsclockout = $breakin ? $breakin->time : 0;
                            }

                            if ($breakinsclockin && $breakinsclockout) {

                                $Minutes  = 0;
                                $clockin  = $breakinsclockin;
                                $clockout = $breakinsclockout;

                                $breakinsclockin  = "";
                                $breakinsclockout = "";
                                if ($clockin && $clockout) {
                                    $clockinTime = Carbon::createFromTimeString($clockin);

                                    $clockoutTime = Carbon::createFromTimeString($clockout);
                                    if ($clockoutTime->lessThan($clockinTime)) {
                                        $clockoutTime->addDay();
                                    }
                                    $Minutes = $clockinTime->diffInMinutes($clockoutTime);

                                    $totalMinutes   = $totalMinutes - $Minutes;
                                    $totalmonthhous = $totalmonthhous - $Minutes;
                                }
                            }
                        }
                    }
                }
            }
            $visitins      = Visitin::where([['user_id', $user->id], ['date', $date]])->get();
            $visitintime   = null;
            $visitouttime  = null;
            $totalVMinutes = 0;
            foreach ($visitins as $visitin) {
                if ($visitin->type == VisitinType::IN->value) {
                    $visitintime = Carbon::createFromTimeString($visitin->time);
                }
                if ($visitin->type == VisitinType::OUT->value) {
                    if ($visitintime instanceof Carbon\Carbon) {
                        $visitouttime    = Carbon::createFromTimeString($visitin->time);
                        $VMinutes        = $visitintime->diffInMinutes($visitouttime);
                        $totalVMinutes  += $VMinutes;
                        $visitintime     = null;
                        $totalMinutes   += $totalVMinutes;
                        $attendancetime  = $visitin->time;
                    }
                }
            }
            $hours        = intdiv($totalMinutes, 60);
            $minutes      = $totalMinutes % 60;
            $clockInRow[] = $totalMinutes ? $hours . ':' . $minutes : 0;

            // // extra hours calculate
            // $shift_time = [];
            // $user_shifts = [];
            // $totalShiftHours = 0;
            // $total_worked_hours = '0';
            // $extra_hours = 0;
            // if($attendance){
            //     $total_worked_hours = $attendance ? date('G.i', mktime(0, $attendance->total_worked)) : 0;

            //     $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
            //     $user_shifts = User::find($user->id)
            //     ->assigned_shifts()
            //     ->with('shift_schedule_information')
            //     ->where('assigned_for_date', $attendance->date)
            //     ->get();
            //     foreach ($user_shifts as $index => $shiftData) {
            //         $shift = $shiftData->shift_schedule_information;
            //         // Convert shift start and end times to Carbon instances
            //         $shiftStart = Carbon::parse($shift->shift_start);
            //         $shiftEnd = Carbon::parse($shift->shift_end);

            //         // Calculate the hours between shift start and end
            //         $hoursDifference = $shiftEnd->diffInHours($shiftStart);
            //         $totalShiftHours += $hoursDifference;
            //         //Log::info($this->totalShiftHours);

            //         $shift_time[] = $shift->shift_start.'-'.$shift->shift_end;
            //     }
            //     if(count($user_shifts) != 0){
            //         if($total_worked_hours > $totalShiftHours) {
            //             $extra_hours = $total_worked_hours -  $totalShiftHours;
            //         }
            //     } else {
            //         $shift_time = [];
            //         $userShifts = [];
            //         if($company_hour > 0){
            //             if($total_worked_hours > $company_hour) {
            //                 $extra_hours = $total_worked_hours -  $company_hour;
            //             }
            //         }
            //     }
            // }
            // $extraHoursRow[] = $extra_hours ? $extra_hours : 0;
            // end
        }
        $rows[] = $clockInRow;
        // $rows[] = $extraHoursRow;

        // Row 6: Paid Day
        $clockOutRow = ['Remark - Prsent (' . $totalpaidday . '), Absent (' . $totalabsent . '), off.Leave (' . $totalleave . ')'];
        foreach ($period as $date) {
            $date         = Carbon::parse($date)->toDateString();
            $attendance   = $user->attendances->where('date', $date)->first();
            $user_shifts  = UsersShift::where([['user_id', $user->id], ['assigned_for_date', $date]])->with('shift_schedule_information')->first();
            $status       = $attendance ? Str::substr($attendance->status->name, 0, 1) : 'A';
            $todayisleave = Leave::where([['user_id', $user->id], ['status', LeaveStatus::Approved]])
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();
            if ($todayisleave) {
                $status = $attendance ? Str::substr($attendance->status->name, 0, 1) : 'L';
            }
            if (! empty($user_shifts) && ! empty($attendance)) {
                $shift_start    = Carbon::parse($user_shifts->shift_schedule_information->shift_start)->format('H:i:00');
                $clock_in       = Carbon::parse($attendance->clock_in)->format('H:i:00');
                $locationvisits = LocationVisits::where('user_id', $user->id)
                    ->where('date', $date)
                    ->orderBy('id', 'asc')->first();
                if ($locationvisits) {
                    $visit_in = Carbon::parse($locationvisits->visit_in)->format('H:i:00');
                    if ($shift_start < $visit_in) {
                        $late   = (new Carbon($visit_in))->diff(new Carbon($shift_start))->format('%h:%I');
                        $status = "L-P(" . $late . ")";
                    }
                } else {
                    $checkins = Checkin::where('user_id', $user->id)
                        ->where('date', $date)
                        ->where('type', 'in')
                        ->orderBy('id', 'asc')->first();

                    if (isset($checkins) && $shift_start < Carbon::parse($checkins->time)->format('H:i:00')) {
                        $clock_in = Carbon::parse($checkins->time)->format('H:i:00');
                        $early    = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                        $status   = "L-P(" . $early . ")";
                    } else if ($shift_start < $clock_in) {
                        $late   = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                        $status = "L-P(" . $late . ")";
                    }
                }
            }
            $visitin = Visitin::where([['user_id', $user->id], ['date', $date]])->orderBy('id', 'desc')->first();
            if ($visitin && $visitin->type == VisitinType::OUT->value) {
                $status = $visitin ? 'V' : 'A';
            }
            $clockOutRow[] = $status;
        }
        $rows[] = $clockOutRow;

        return $rows;
    }

    public function headings(): array
    {
        $headings = [
            [$this->monthdata . ' Attendance Report'],
            [env('APP_NAME')],
            $this->userId ? [] : ['Branch: ' . $this->departname],
            [], // Blank row
            [], // Blank row
        ];

        return $headings;
    }
}
