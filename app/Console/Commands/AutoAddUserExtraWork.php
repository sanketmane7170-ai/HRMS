<?php
namespace App\Console\Commands;

use App\Models\extraWorkRequest;
use App\Models\lateCome;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Payroll\Traits\SalaryCalculation;

class AutoAddUserExtraWork extends Command
{

    protected $signature = 'app:auto-add-user-extra-work';

    protected $description = 'add user extra work.';
    use SalaryCalculation;

    public function handle()
    {

        $this->info('[' . now() . '] Processing extra work hours...');

        $autoaddextraWork    = 0;
        $autoaddextrasetting = Setting::where('key', 'auto_add_extra_work')->first();
        if ($autoaddextrasetting && $autoaddextrasetting->value == 1) {
            $autoaddextraWork = 1;
        }
        if ($autoaddextraWork == 1) {
            $yesterday = Carbon::yesterday()->toDateString();
            DB::enableQueryLog();
            $users = User::query()
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                })
                ->where('status', User::STATUS_ACTIVE)
                ->whereHas('attendances', function ($query) use ($yesterday) {
                    $query->whereDate('date', $yesterday);
                })
                ->get();
            Log::info('app:auto-add-user-extra-work', ["getQueryLog" => DB::getQueryLog()]);
            $month = Carbon::parse($yesterday)->format('m');
            $year  = Carbon::parse($yesterday)->format('Y');
            foreach ($users as $user) {
                $this->info('[' . now() . '] User ' . json_encode($user));
                //total hoursh
                $attendances  = $user->attendances()->where('date', $yesterday)->get();
                $totalMinutes = 0;
                $extra_hours  = 0;
                foreach ($attendances as $attendance) {

                    $this->info('[' . now() . '] attendance: ' . json_encode($attendance));

                    $clockintime  = $attendance ? $attendance->clock_in : 0;
                    $clockouttime = $attendance ? $attendance->clock_out : 0;

                    if ($clockintime && $clockouttime) {
                        $attendDate   = Carbon::parse($yesterday);
                        $clockinTime  = Carbon::parse($attendDate->format('Y-m-d') . ' ' . $clockintime);
                        $clockoutTime = Carbon::parse($attendDate->format('Y-m-d') . ' ' . $clockouttime);
                        if ($clockoutTime->lessThan($clockinTime)) {
                            $clockoutTime->addDay();
                        }
                        $Minutes       = $clockinTime->diffInMinutes($clockoutTime);
                        $totalMinutes += $Minutes;
                    }
                    $clockoutTime = isset($clockoutTime) ? $clockoutTime : Carbon::parse('00:00');
                }
                // extra hours calculate
                $shift_time         = [];
                $user_shifts        = [];
                $totalShiftMinuts   = 0;
                $total_worked_hours = 0;
                if (count($attendances) > 0) {
                    $hours              = intdiv($totalMinutes, 60);
                    $minutes            = $totalMinutes % 60;
                    $total_worked_hours = $hours . '.' . $minutes;

                    $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
                    $user_shifts  = User::find($user->id)
                        ->assigned_shifts()
                        ->with('shift_schedule_information')
                        ->where('assigned_for_date', $yesterday)
                        ->get();

                    foreach ($user_shifts as $index => $shiftData) {
                        $shift = $shiftData->shift_schedule_information;

                        $shiftDate  = Carbon::parse($shiftData->assigned_for_date);
                        $shiftStart = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $shift->shift_start);
                        $shiftEnd   = Carbon::parse($shiftDate->format('Y-m-d') . ' ' . $shift->shift_end);
                        // Calculate the hours between shift start and end
                        if ($shiftEnd->lessThan($shiftStart)) {
                            $shiftEnd->addDay();
                        }
                        $hoursDifference   = $shiftStart->diffInMinutes($shiftEnd);
                        $totalShiftMinuts += $hoursDifference;
                    }
                    if (count($user_shifts) != 0) {
                        $sshours         = intdiv($totalShiftMinuts, 60);
                        $ssminutes       = $totalShiftMinuts % 60;
                        $totalShiftHours = $sshours . '.' . $ssminutes;

                        if ($clockoutTime->greaterThan($shiftEnd)) {
                            if ($total_worked_hours > $totalShiftHours) {
                                $extrahours   = $shiftEnd->diffInMinutes($clockoutTime);
                                $extra_hours += $extrahours;
                            }
                        }
                    } else {
                        if ($company_hour > 0) {
                            if ($total_worked_hours > $company_hour) {
                                $extrahours   = Carbon::createFromTimeString($total_worked_hours)->diffInMinutes(Carbon::createFromTimeString($company_hour));
                                $extra_hours += $extrahours;
                            }
                        }
                    }
                }
                // end
                $overtime_hours       = Setting::where("key", "overtime_hours")->first();
                $overtime_hours_limit = 00;
                if ($overtime_hours) {
                    $overtime_hours_limit = $overtime_hours->value;
                }

                if (getSetting('multi_branch_wise_payroll') == 'true') {
                    $department = $user->department;
                    if ($department && $department->over_time == 0) {
                        $extra_hours = 0;
                    }

                } else {
                    if ($extra_hours != 0 && $extra_hours >= $overtime_hours_limit) {
                        $extra_hourshours   = intdiv($extra_hours, 60);
                        $extra_hoursminutes = $extra_hours % 60;
                        $extra_hours        = $extra_hourshours . '.' . $extra_hoursminutes;

                        $extraWorkPending = extraWorkRequest::where([['user_id', $user->id], ['status', 0]])->whereDate('date', $yesterday)->first();

                        if ($extraWorkPending) {
                            $extraWorkPending->update([
                                'user_id'     => $user->id,
                                'added_by'    => 0,
                                'extra_hours' => $extra_hours,
                                'hours'       => $extra_hourshours,
                                'minit'       => $extra_hoursminutes,
                                'month'       => $month,
                                'year'        => $year,
                                'status'      => 0,
                                'date'        => $yesterday, //Carbon::now()->toDateString(),
                            ]);
                        } else {
                            $thismonthadd = extraWorkRequest::create([
                                'user_id'     => $user->id,
                                'added_by'    => 0,
                                'extra_hours' => $extra_hours,
                                'hours'       => $extra_hourshours,
                                'minit'       => $extra_hoursminutes,
                                'month'       => $month,
                                'year'        => $year,
                                'status'      => 0,
                                'date'        => $yesterday, //Carbon::now()->toDateString(),
                            ]);
                        }
                    }
                }

                // late come report
                $date = $yesterday;
                if (! empty($attendances[0]->status->name)) {
                    if ($attendances[0]->status->name == 'Present' || $attendances[0]->status->name == 'Late') {
                        $lateMinutes  = 0;
                        $users_shifts = DB::table('users_shifts')
                            ->join('shift_schedules', 'users_shifts.schedule_id', '=', 'shift_schedules.id')
                            ->where('users_shifts.user_id', $user->id)
                            ->where('users_shifts.assigned_for_date', $date)
                            ->get();
                        if (! empty($users_shifts[0]->shift_start)) {
                            $shift_start    = Carbon::parse($users_shifts[0]->shift_start)->format('H:i:00');
                            $clock_in       = Carbon::parse($attendances[0]->clock_in)->format('H:i:00');
                            $locationvisits = LocationVisits::where('user_id', $user->id)
                                ->where('date', $date)
                                ->orderBy('id', 'asc')->first();

                            if ($locationvisits) {
                                $visit_in = Carbon::parse($locationvisits->visit_in)->format('H:i:00');
                                if ($shift_start < $visit_in) {
                                    $late        = (new Carbon($visit_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                    $lateMinutes = (new Carbon($visit_in))->diffInMinutes(new Carbon($shift_start), true);
                                }
                            } else {
                                $checkins = Checkin::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->where('type', 'in')
                                    ->orderBy('id', 'asc')->first();
                                if (isset($checkins) && $shift_start > Carbon::parse($checkins->time)->format('H:i:00')) {
                                    $clock_in = Carbon::parse($checkins->time)->format('H:i:00');
                                    $early    = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                } else if ($shift_start < $clock_in) {
                                    $late        = (new Carbon($clock_in))->diff(new Carbon($shift_start))->format('%h:%I');
                                    $lateMinutes = (new Carbon($clock_in))->diffInMinutes(new Carbon($shift_start), true);
                                }
                            }
                            // add late come report
                            $maxLateMinute = Setting::where('key', 'maximum_late_come_minute')->value('value');
                            if ($lateMinutes > $maxLateMinute) {
                                $company_hour = Setting::where('key', 'minumum_working_hour')->value('value');
                                $month        = Carbon::parse($date)->format('m');
                                $year         = Carbon::parse($date)->format('Y');
                                $start_date   = date('Y-m-01', strtotime("$year-$month-01"));
                                $end_date     = date('Y-m-t', strtotime("$year-$month-01"));
                                $gross_salary = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                                $dailyRate    = $gross_salary * 12 / 365;
                                $hoursRate    = $dailyRate / $company_hour;
                                $minitRate    = $hoursRate / 60;
                                $lateAmount   = $minitRate * $lateMinutes;
                                $lateAmount   = round($lateAmount, 2);
                                $lateCome     = lateCome::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->first();
                                if ($lateCome) {
                                    $lateCome->late_minute   = $lateMinutes;
                                    $lateCome->charge_amount = $lateAmount;
                                    $lateCome->save();
                                } else {
                                    $addlate = lateCome::create([
                                        'user_id'       => $user->id,
                                        'date'          => $date,
                                        'late_minute'   => $lateMinutes,
                                        'charge_amount' => $lateAmount,
                                    ]);
                                }
                            }
                            //end
                        }
                    }
                }
                //end
            }
            //end
            $this->info('[' . now() . '] Extra work hours updated successfully.');
        } else {
            $this->info('[' . now() . '] Auto add extra work is disabled.');
        }
    }
}
