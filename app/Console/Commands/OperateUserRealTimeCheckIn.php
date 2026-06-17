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
    protected $signature = 'app:operation-checkin';
    protected $description = 'Automatically check out users who have completed their working hours.';

    public function handle()
    {
        $this->info('[' . now() . '] operation-checkin started.');
        Log::info('app:operation-checkin => Started at: ' . now());

        // Fetch active non-admin users
        Log::info('app:operation-checkin => Fetching users...');
        $usersMissingCheckOut = User::whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
        })
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        Log::info('app:operation-checkin => Total users found', ['count' => $usersMissingCheckOut->count()]);

        // Fetch attendance hour setting
        $attendance_hour = Setting::where('key', 'new_attendance_hours')->value('value');
        Log::info('app:operation-checkin => attendance_hour setting value', ['attendance_hour' => $attendance_hour]);

        foreach ($usersMissingCheckOut as $user) {
            Log::info('------------------------------------------');
            Log::info("Processing user: {$user->id} - {$user->name}");
            $this->info("Processing user: {$user->id} - {$user->name}");

            try {
                $checkin = $user->checkins()->orderByDesc('id')->first();
                Log::info("Last checkin fetched", ['checkin' => $checkin]);

                if (!$checkin) {
                    Log::info("No checkin found for user: {$user->id}");
                    continue;
                }

                if ($checkin->type != CheckinType::IN->value) {
                    Log::info("User {$user->id} last checkin type is not 'IN'. Skipping...");
                    continue;
                }

                Log::info("User {$user->id} last checkin is IN. Checking working hours...");

                $current_exact_time = now();
                Log::info("Current exact time", ['current_exact_time' => $current_exact_time->toDateTimeString()]);

                $lastAttendance = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->first();
                Log::info("Last attendance fetched", ['attendance' => $lastAttendance]);

                if (!$lastAttendance) {
                    Log::warning("No attendance record found for user: {$user->id}");
                    continue;
                }

                $checkintime = Carbon::parse($lastAttendance->date . ' ' . $lastAttendance->clock_in);
                $exact_time  = Carbon::parse($current_exact_time);
                Log::info("Calculated checkin start and current time", [
                    'checkin_time' => $checkintime->toDateTimeString(),
                    'now' => $exact_time->toDateTimeString(),
                ]);

                // Fetch checkins between these times
                $checkins = Checkin::where('user_id', $user->id)
                    ->whereRaw("CONCAT(`date`, ' ', `time`) >= ?", [$checkintime->toDateTimeString()])
                    ->whereRaw("CONCAT(`date`, ' ', `time`) <= ?", [$exact_time->toDateTimeString()])
                    ->orderBy('id', 'asc')
                    ->get();

                Log::info("Fetched checkins within timeframe", ['count' => $checkins->count()]);

                $totalWorkedMinutes = 0;
                $lastInTime = null;

                foreach ($checkins as $ci) {
                    Log::info("Processing checkin ID {$ci->id}", ['type' => $ci->type, 'datetime' => "{$ci->date} {$ci->time}"]);

                    if ($ci->type === CheckinType::IN->value) {
                        $lastInTime = Carbon::parse($ci->date . ' ' . $ci->time);
                    } elseif ($ci->type === CheckinType::OUT->value && $lastInTime) {
                        $outTime = Carbon::parse($ci->date . ' ' . $ci->time);
                        $workedMinutes = $lastInTime->diffInMinutes($outTime);
                        $totalWorkedMinutes += $workedMinutes;
                        Log::info("Paired IN-OUT duration added", ['worked_minutes' => $workedMinutes]);
                        $lastInTime = null;
                    }
                }

                // If user still IN and not checked out
                if ($lastInTime) {
                    $stillWorked = $lastInTime->diffInMinutes($exact_time);
                    $totalWorkedMinutes += $stillWorked;
                    Log::info("User still checked IN. Added till now.", ['worked_minutes' => $stillWorked]);
                }

                // Calculate break time if exists
                $breakMinutes = 0;
                if ($lastAttendance->break_in && $lastAttendance->break_out) {
                    $breakinTime = Carbon::createFromTimeString($lastAttendance->break_in);
                    $breakoutTime = Carbon::createFromTimeString($lastAttendance->break_out);
                    $breakMinutes = $breakinTime->diffInMinutes($breakoutTime);
                    Log::info("Break detected", ['break_minutes' => $breakMinutes]);
                }

                $totalWorkedMinutes -= $breakMinutes;
                Log::info("Net worked minutes after deducting break", ['net_worked_minutes' => $totalWorkedMinutes]);

                $hoursDifference = $totalWorkedMinutes / 60;
                Log::info("Converted total minutes to hours", ['hours' => $hoursDifference]);

                // Compare with attendance hour limit
                if (floatval($attendance_hour) > 0 && floatval($hoursDifference) > floatval($attendance_hour)) {
                    Log::info("Working hours exceeded. Auto-checkout initiated", [
                        'user_id' => $user->id,
                        'hoursDifference' => $hoursDifference,
                        'limit' => $attendance_hour,
                    ]);

                    $checkout = $user->checkins()->create([
                        'date'            => now()->toDateString(),
                        'time'            => now()->toTimeString(),
                        'type'            => CheckinType::OUT,
                        'is_auto_update'  => 1,
                        'checkout_reason' => 'WORKING HOUR COMPLETE',
                    ]);

                    Log::info("Auto checkout created", ['checkout' => $checkout]);

                    $last_latest_clock_in = Checkin::where(['user_id' => $checkout->user_id, 'type' => 'in'])
                        ->orderBy('id', 'DESC')->first();

                    $lastCheckinTime = Carbon::parse($last_latest_clock_in->date . ' ' . $last_latest_clock_in->time);
                    $hoursWorked = $lastCheckinTime->diffInMinutes($current_exact_time);
                    $final_total_worked = $lastAttendance->total_worked + $hoursWorked;

                    Log::info("Updating attendance total worked", [
                        'last_checkin_time' => $lastCheckinTime,
                        'hours_worked' => $hoursWorked,
                        'final_total_worked' => $final_total_worked,
                    ]);

                    Attendance::updateOrCreate(
                        [
                            'user_id' => $checkout->user_id,
                            'id'      => $lastAttendance->id,
                        ],
                        [
                            'clock_out'     => $checkout->time,
                            'clockout_date' => $checkout->date,
                        ]
                    );

                    CheckinsLogs::create([
                        'user_id' => $user->id,
                        'date'    => now()->toDateString(),
                        'comment' => "Cronjob: Auto Checkout by {$checkout->date} {$checkout->time}",
                    ]);

                    Log::info("CheckinsLogs entry created for user", ['user_id' => $user->id]);
                } else {
                    Log::info("User {$user->id} has not yet completed working hours", [
                        'worked_hours' => $hoursDifference,
                        'limit' => $attendance_hour,
                    ]);
                }
            } catch (Exception $e) {
                Log::error("Error processing user {$user->id}", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }

        $this->info('[' . now() . '] operation-checkin ended.');
        Log::info('app:operation-checkin => Ended at: ' . now());
    }

    public function getTotalWorkingHourOfUser($user_id, $MinutesDifference)
    {
        Log::info("Calculating total working hour for user", ['user_id' => $user_id]);
        $total_worked = Attendance::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->value('total_worked');
        $totalMinutes = $total_worked + $MinutesDifference;
        $totalHours = $totalMinutes / 60;
        Log::info("Total working hours calculated", [
            'user_id' => $user_id,
            'total_hours' => $totalHours,
        ]);
        return $totalHours;
    }
}
