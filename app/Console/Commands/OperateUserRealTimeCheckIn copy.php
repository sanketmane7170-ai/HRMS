<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\CheckinsLogs;
use Modules\Attendance\Enums\CheckinType;

class OperateUserRealTimeCheckIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:operation-checkin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $usersMissingCheckOut = User::whereDoesntHave('roles', function ($query) {
            return  $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
        })->where('status', User::STATUS_ACTIVE)->get();

        $this->info('[' . now() . '] operation-checkin started.');

        // \DB::enableQueryLog();
        // $usersMissingCheckOut = User::whereDoesntHave('roles', function ($query) {
        //     $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
        // })
        //     ->where('status', User::STATUS_ACTIVE)
        //     ->whereHas('checkins', function ($query) {
        //         $query->where('type', CheckinType::IN->value);
        //     })
        //     ->get();
        // Log::info('app:operation-checkin', ["getQueryLog" => \DB::getQueryLog()]);
        Log::info('app:operation-checkin', ["usersMissingCheckOut" => $usersMissingCheckOut]);

        $attendance_hour = Setting::where('key', 'new_attendance_hours')->value('value');
        Log::info('app:operation-checkin', ["attendance_hour" => $attendance_hour]);
        foreach ($usersMissingCheckOut as $user) {

            try {
                $this->info('[' . now() . '] user: ' . json_encode($user));

                /*$lastcheck = $user->checkins()
                ->orderByDesc('id')
                ->first();
                if ($lastcheck !== null && $lastcheck->type == CheckinType::IN->value) {
                    $totalMinutesWorked = 0;

                    $lastAttendance = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->first();
                    if(!empty($lastAttendance)){
                        // Fetch all checkins for the user on a specific date, sorted by time
                        $checkins = $user->checkins()
                        ->where('user_id', $user->id)
                        ->whereDate('date', $lastAttendance->date)
                        ->orderBy('time')
                        ->get();
                        \Log::info('name'.$user->name);
                        $lastCheckinTime = null;

                        foreach ($checkins as $checkin) {
                            $currentCheckinTime = Carbon::parse($checkin->date . ' ' . $checkin->time);
                            $current_exact_time = now()->toDateTimeString();
                            if ($checkin->type == 'in') {
                                // Store the current check-in time for pairing with the next check-out
                                $lastCheckinTime = $currentCheckinTime;
                            } elseif ($checkin->type == 'out' && $lastCheckinTime) {
                                // Calculate time difference with the last stored check-in time
                                if($lastCheckinTime != null){
                                    // This case if user checkin from 1 day ago and checkout in next day and again next day ingore this checkout entry and again checkin
                                    // for fresh day
                                    $minutesWorked = $lastCheckinTime->diffInMinutes($currentCheckinTime);
                                    $totalMinutesWorked += $minutesWorked;

                                    // Reset lastCheckinTime for the next pair
                                    $lastCheckinTime = null;
                                }
                            }
                        }

                        if ($lastCheckinTime != null) {
                            // The user is still checked in (no corresponding OUT), calculate till now
                            $current_exact_time = now();
                            $minutesWorked = $lastCheckinTime->diffInMinutes($current_exact_time);
                            \Log::info('Unmatched minutesWorked (no OUT): '.$minutesWorked);
                            $totalMinutesWorked += $minutesWorked;
                            \Log::info('Total minutesWorked with unmatched IN: '.$totalMinutesWorked);
                        }

                        // Convert total minutes to hours and minutes
                        //$totalHours = intdiv($totalMinutesWorked, 60);
                        $totalHours = $totalMinutesWorked/60;
                        \Log::info('totalHours'.$totalHours);
                        $totalMinutes = $totalMinutesWorked % 60;
                                if(floatval($attendance_hour) > 0){
                                    if(floatval($totalHours) > floatval($attendance_hour)) {
                                        $checkout = $user->checkins()->create([
                                            'date' => now()->toDateString(),
                                            'time' => now()->toTimeString(),
                                            'type' => CheckinType::OUT,
                                            'is_auto_update' => 1,
                                            'checkout_reason' => 'WORKING HOUR COMPLETE'
                                        ]);

                                        // $lastAttendance = Attendance::where('user_id', $checkout->user_id)
                                        //                 ->orderBy('id', 'desc')
                                        //                 ->first();
                                        //total worked update
                                        $last_latest_clock_in = Checkin::where(['user_id'=>$checkout->user_id,'type'=>'in'])->orderBy('id','DESC')->first();
                                        $lastCheckinTime = Carbon::parse(($last_latest_clock_in->date . " " . $last_latest_clock_in->time));
                                        $hoursWorked = $lastCheckinTime->diffInMinutes($current_exact_time);
                                        $final_total_worked = $lastAttendance->total_worked + $hoursWorked;
                                        $update_total_worked = $lastAttendance->total_worked + $totalMinutesWorked;
                                        \Log::info('OperateUserRealTimeCheckin.php File OUT SECTION => workedmins'. $update_total_worked);
                                        if ($checkout->type == CheckinType::OUT) {
                                            Attendance::updateOrCreate(
                                                [
                                                    'user_id' => $checkout->user_id,
                                                    'id' => $lastAttendance->id,
                                                ],
                                                [
                                                    'clock_out' => $checkout->time,
                                                    'clockout_date' => $checkout->date,
                                                    //'total_worked' => $update_total_worked
                                                ]
                                            );
                                        }

                                        CheckinsLogs::create([
                                            'user_id' => $user->id,
                                            'date' => now()->toDateString(),
                                            'comment' => "Cronjob: Auto Checkout by {$checkout->date} {$checkout->time}"
                                        ]);
                                    }
                                }
                            }
                }*/

                // $this->updatedScriptCode($user);
                $checkin = $user->checkins()
                    //->where('date', now()->toDateString())
                    ->orderByDesc('id')
                    ->first();
                Log::info('app:operation-checkin', ["checkin" => $checkin]);

                if ($checkin !== null && $checkin->type == CheckinType::IN->value) {
                    $current_exact_time = now()->toDateTimeString(); // it will be 12:17:06


                    //$checkintime = Carbon::parse(($checkin->date . ' ' . $checkin->time)); // it will be 12:08:43
                    $lastAttendance = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->first();

                    $checkintime    = Carbon::parse(($lastAttendance->date . ' ' . $lastAttendance->clock_in));

                    $exact_time     = Carbon::parse($current_exact_time);

                    $this->info("=========== User {$user->id} ===============");
                    $this->info("Time Now:  {$exact_time}");
                    $this->info("Last Attendance checkin id {$lastAttendance->id} , time {$checkintime}");


                    $clockInStart = $checkintime;
                    $clockInEnd = $exact_time;


                    $checkins = Checkin::where('user_id', $user->id)
                        ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$clockInStart->toDateTimeString()])
                        ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$clockInEnd->toDateTimeString()])
                        ->orderBy('id', 'asc')
                        ->get();
                    $totalWorkedMinutes = 0;
                    $lastInTime = null;
                    foreach ($checkins as $ci) {
                        if ($ci->type === CheckinType::IN->value) {
                            $lastInTime = Carbon::parse($ci->date . ' ' . $ci->time);
                        } elseif ($ci->type === CheckinType::OUT->value && $lastInTime) {
                            $outTime = Carbon::parse($ci->date . ' ' . $ci->time);
                            $totalWorkedMinutes += $lastInTime->diffInMinutes($outTime);
                            $lastInTime = null; // Reset after pairing
                        }
                    }

                    // If user is still IN and not checked out yet, count time till now
                    if ($lastInTime) {
                        $totalWorkedMinutes += $lastInTime->diffInMinutes($exact_time);
                    }


                    $breakMinutes=0;
                    $break_in = $lastAttendance ? $lastAttendance->break_in : 0;
                    $break_out = $lastAttendance ? $lastAttendance->break_out : 0;
                    if ($break_in && $break_out) {
                        $breakinTime = Carbon::createFromTimeString($break_in);
                        $breakoutTime = Carbon::createFromTimeString($break_out);
                        $breakMinutes = $breakinTime->diffInMinutes($breakoutTime);
                    }


                    $this->info("Total worked minutes before break: {$totalWorkedMinutes}");
                    $this->info("Break minutes: {$breakMinutes}");
                    $totalWorkedMinutes = $totalWorkedMinutes - $breakMinutes;
                    $this->info("Net worked minutes after break: {$totalWorkedMinutes}");

                    // $difference = $exact_time->diff($checkintime);
                    // $hoursAndMinutesDifference = $difference->format('%h.%i');
                    $hoursDifference = $exact_time->diffInMinutes($checkintime) / 60;
                    $this->info("hour/minute differnce {$hoursDifference}");
                    $valFloat = floatval($hoursDifference);

                    if ($totalWorkedMinutes > 0) {
                        $hoursDifference = $totalWorkedMinutes / 60;
                    }

                    $MinutesDifference = $exact_time->diffInMinutes($checkintime);

                    //$getTotalWorkingHour = $this->getTotalWorkingHourOfUser($checkin->user_id,$MinutesDifference);

                    $this->info("=========== User {$user->id} ===============");
                    $this->info("Time Now:  {$exact_time}");
                    $this->info("attendance_hour:  {$attendance_hour}");
                    $this->info("hoursDifference:  {$hoursDifference}");
                    if (floatval($attendance_hour) > 0) {
                        if (floatval($hoursDifference) > floatval($attendance_hour)) {
                            $this->info("WORKING HOUR COMPLETE");
                            //if(floatval($getTotalWorkingHour) > floatval($attendance_hour)) {
                            $checkout = $user->checkins()->create([
                                'date'            => now()->toDateString(),
                                'time'            => now()->toTimeString(),
                                'type'            => CheckinType::OUT,
                                'is_auto_update'  => 1,
                                'checkout_reason' => 'WORKING HOUR COMPLETE',
                            ]);

                            // $lastAttendance = Attendance::where('user_id', $checkout->user_id)
                            //                 ->orderBy('id', 'desc')
                            //                 ->first();
                            //total worked update
                            $last_latest_clock_in = Checkin::where(['user_id' => $checkout->user_id, 'type' => 'in'])->orderBy('id', 'DESC')->first();
                            $lastCheckinTime      = Carbon::parse(($last_latest_clock_in->date . " " . $last_latest_clock_in->time));
                            $hoursWorked          = $lastCheckinTime->diffInMinutes($current_exact_time);
                            $final_total_worked   = $lastAttendance->total_worked + $hoursWorked;

                            if ($checkout->type == CheckinType::OUT) {
                                Attendance::updateOrCreate(
                                    [
                                        'user_id' => $checkout->user_id,
                                        'id'      => $lastAttendance->id,
                                    ],
                                    [
                                        'clock_out'     => $checkout->time,
                                        'clockout_date' => $checkout->date,
                                        // Total worked automatically updated when we try to reterive information from checkins table(boot function update data)
                                        // Commented this line 26 June Trello Issue
                                        //'total_worked' => $final_total_worked
                                    ]
                                );

                                // update visit_out when visit_in is added incase checkout is done by user
                                // if ($lastAttendance->visit_in && !$lastAttendance->visit_out) {
                                //     $lastAttendance->update([
                                //         'visit_out' => $checkin->time
                                //     ]);
                                // }

                            }

                            $this->info("User {$user->name} manually checked out successfully. hour/minute differnce {$hoursDifference}");

                            CheckinsLogs::create([
                                'user_id' => $user->id,
                                'date'    => now()->toDateString(),
                                'comment' => "Cronjob: Auto Checkout by {$checkout->date} {$checkout->time}",
                            ]);
                        }
                    }
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        $this->info('[' . now() . '] operation-checkin ended.');
    }

    // public function updatedScriptCode($user){

    // }

    public function getTotalWorkingHourOfUser($user_id, $MinutesDifference)
    {
        $total_worked = Attendance::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->value('total_worked');
        $totalMinutes = $total_worked + $MinutesDifference;
        $totalHours   = $totalMinutes / 60;
        return $totalHours;
    }
}
