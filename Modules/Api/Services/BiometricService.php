<?php

namespace Modules\Api\Services;

use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Enums\AttendanceStatus;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BiometricService
{
    /**
     * Sync the Checkin data to the Attendance table (Daily Summary).
     * Logic adapted from Checkin Observer.
     */
    public function syncAttendance(Checkin $checkin)
    {
        Log::info('BiometricService::syncAttendance - Start for Checkin ID: ' . $checkin->id);

        $newCheckinTime = Carbon::parse($checkin->date . " " . $checkin->time);
        
        // Find last 'present' attendance
        $lastAttendance = Attendance::where('user_id', $checkin->user_id)
            ->where('status', 'present')
            ->orderBy('date', 'desc')
            ->first();

        $attendance_hour = Setting::where('key', 'new_attendance_hours')->value('value');
        if (is_null($attendance_hour)) {
            $attendance_hour = 12; // Default fallback if setting missing
        }

        // =========================================================
        // Handle IN Type
        // =========================================================
        if ($checkin->type == CheckinType::IN->value || $checkin->type == 'in') { // Handle string or enum
            
            if ($lastAttendance) {
                // If last attendance exists
                if ($lastAttendance->clock_in) {
                    $lastCheckinTime = Carbon::parse($lastAttendance->date . " " . $lastAttendance->clock_in);
                    $hoursDifference = $lastCheckinTime->diffInMinutes($newCheckinTime) / 60;

                    if ($hoursDifference <= $attendance_hour) {
                        // Re-checkin within allowed hours: Clear previous clock-out to treat as continuous session
                        $lastAttendance->update([
                            'clock_out' => null,
                            'clockout_date' => null,
                        ]);
                    } else {
                        // Check if it's a new day or gap is too large
                        if (($attendance_hour > 0) || ($lastCheckinTime->toDateString() != $newCheckinTime->toDateString() && $lastAttendance->clock_out != null)) {
                            // Create NEW attendance
                            Attendance::create([
                                'date' => $checkin->date,
                                'clock_in' => $checkin->time,
                                'user_id' => $checkin->user_id,
                                'created_by_id' => $checkin->user_id, // Biometric machine -> User self
                                'status' => AttendanceStatus::Present,
                            ]);
                        } else if ($lastCheckinTime->toDateString() == $newCheckinTime->toDateString() && $lastAttendance->clock_out != null) {
                            // Same day, clock_out was present -> Clear it (resume work)
                            $lastAttendance->update([
                                'clock_out' => null,
                                'clockout_date' => null,
                            ]);
                        }
                    }
                } else {
                    // Last attendance exists but has no clock_in? (Rare/Edge case)
                    if ($lastAttendance->date == $checkin->date) {
                         $lastAttendance->update([
                            'clock_in' => $checkin->time,
                            'clock_out' => null,
                            'clockout_date' => null,
                        ]);
                    } else {
                        Attendance::create([
                            'date' => $checkin->date,
                            'clock_in' => $checkin->time,
                            'user_id' => $checkin->user_id,
                            'created_by_id' => $checkin->user_id,
                            'status' => AttendanceStatus::Present,
                        ]);
                    }
                }
            } else {
                // No last attendance found -> Create NEW
                 Attendance::create([
                    'date' => $checkin->date,
                    'clock_in' => $checkin->time,
                    'user_id' => $checkin->user_id,
                    'created_by_id' => $checkin->user_id,
                    'status' => AttendanceStatus::Present,
                ]);
            }
        }

        // =========================================================
        // Handle LATE Type
        // =========================================================
        if ($checkin->type == CheckinType::LATE->value || $checkin->type == 'late') {
            $attendance = Attendance::firstOrNew([
                'user_id' => $checkin->user_id,
                'date' => $checkin->date,
            ]);

            if (!$attendance->exists || !$attendance->clock_in) {
                 $attendance->status = AttendanceStatus::Late;
                 $attendance->clock_in = $checkin->time;
                 $attendance->latecomment = $checkin->latecomment;
                 $attendance->created_by_id = $checkin->user_id;
                 $attendance->save();
            }
        }

        // =========================================================
        // Handle OUT Type
        // =========================================================
        if ($checkin->type == CheckinType::OUT->value || $checkin->type == 'out') {
             if ($lastAttendance) {
                 // Calculate total worked
                 // Find the latest IN for this user to calculate diff correctly? 
                 // Actually logic uses $lastAttendance->total_worked + diff(last_latest_clock_in, current_out)
                 
                 $last_latest_clock_in = Checkin::where('user_id', $checkin->user_id)
                    ->where('type', 'in')
                    ->orderBy('id', 'DESC')
                    ->first();

                 if ($last_latest_clock_in) {
                     $lastInTime = Carbon::parse($last_latest_clock_in->date . " " . $last_latest_clock_in->time);
                     $calculate_mins = $lastInTime->diffInMinutes($newCheckinTime);
                     
                     // Aggregate previous total_worked
                     $final_total_worked = ($lastAttendance->total_worked ?? 0) + $calculate_mins;

                     $lastAttendance->update([
                        'clockout_date' => $checkin->date,
                        'clock_out' => $checkin->time,
                        'total_worked' => $final_total_worked
                    ]);
                 }
             }
        }

        Log::info('BiometricService::syncAttendance - End for Checkin ID: ' . $checkin->id);
    }
}
