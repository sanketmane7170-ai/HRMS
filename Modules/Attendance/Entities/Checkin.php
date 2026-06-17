<?php
namespace Modules\Attendance\Entities;

use App\Models\Setting;
use App\Models\User;
use App\Traits\Query;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Enums\CheckinType;

class Checkin extends Model
{
    use HasFactory, Query;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'date',
        'time',
        'type',
        'latecomment',
        'is_auto_update',
        'face_attendance',
        'checkout_reason',
        'location',
        'longitude',
        'latitude',
        'branch_id',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($checkin) {
            $newCheckinTime = Carbon::parse(($checkin->date . " " . $checkin->time));

            // $lastAttendance = Attendance::where('user_id', $checkin->user_id)
            //     ->orderBy('date', 'desc')
            //     ->first();
            // $lastAttendance = Attendance::where('user_id', $checkin->user_id)
            //     ->where('status', 'present')
            //     ->orderBy('date', 'desc')
            //     ->first();
            // $lastAttendance = Attendance::where('user_id', $checkin->user_id)
            //     ->whereNull('clock_out') // THIS IS THE FIX
            //     ->orderBy('id', 'desc')
            //     ->first();
            // $lastAttendance = Attendance::where('user_id', $checkin->user_id)
            //     ->where(function ($q) use ($checkin) {
            //         $q->where('date', $checkin->date)
            //             ->orWhere('date', Carbon::parse($checkin->date)->subDay()->toDateString());
            //     })
            //     ->orderBy('date', 'desc')
            //     ->first();

            $lastAttendance = Attendance::where('user_id', $checkin->user_id)
                ->where(function ($q) use ($checkin) {

                    // Today's attendance (any status)
                    $q->whereDate('date', $checkin->date)

                    // Previous day only if valid status
                        ->orWhere(function ($sub) use ($checkin) {
                            $sub->whereDate('date', Carbon::parse($checkin->date)->subDay()->toDateString())
                                ->whereIn('status', [
                                    'present',
                                    'late',
                                    'earlyout',
                                    'halfday',
                                ]);
                        });

                })
                ->orderBy('date', 'desc')
                ->first();

            if (isset($lastAttendance)) {
                Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Last Attendance Found', [
                    'attendance_id'       => $lastAttendance->id,
                    'date'                => $lastAttendance->date,
                    'clock_in'            => $lastAttendance->clock_in,
                    'clock_out'           => $lastAttendance->clock_out,
                    'total_worked'        => $lastAttendance->total_worked,
                    'check_in_branch_id'  => $lastAttendance->check_in_branch_id,
                    'check_out_branch_id' => $lastAttendance->check_out_branch_id,
                ]);
            } else {

                Log::info('Checkin::created-user_id-' . $checkin->user_id . ' No Last Attendance Found', [
                    'checkin_date' => $checkin->date,
                    'checkin_time' => $checkin->time,
                ]);
            }

            $attendance_hour = Setting::where('key', 'new_attendance_hours')->value('value');
            Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Attendance Hour Setting', [
                'attendance_hour' => $attendance_hour,
            ]);

            if ($checkin->type == CheckinType::IN || $checkin->type == 'in') {
                Log::info('Checkin::created-user_id-' . $checkin->user_id . ' checkin', [
                    'checkin'         => $checkin,
                    'attendance_hour' => $attendance_hour,
                ]);

                if (isset($lastAttendance)) {
                    if (isset($lastAttendance->clock_in)) {
                        $lastCheckinTime = Carbon::parse(($lastAttendance->date . " " . $lastAttendance->clock_in));
                        $hoursDifference = $lastCheckinTime->diffInMinutes($newCheckinTime) / 60;
                        if ($lastAttendance->status == AttendanceStatus::Weekend or $lastAttendance->status == AttendanceStatus::Leave or $lastAttendance->status == AttendanceStatus::Holiday and $lastCheckinTime = "00:00:00") {
                            $hoursDifference = 0;
                        }

                        Log::info('Checkin::created-user_id-' . $checkin->user_id . ' checkin', [
                            'checkin'         => $checkin,
                            'attendance_hour' => $attendance_hour,
                            'lastCheckinTime' => $lastCheckinTime,
                            'newCheckinTime'  => $newCheckinTime,
                            'hoursDifference' => $hoursDifference,
                        ]);
                        if ($hoursDifference <= $attendance_hour) {

                            $lastAttendance->update([
                                'clock_out'           => null,
                                'clockout_date'       => null,
                                'check_out_branch_id' => null,
                            ]);

                            if ($checkin->time != $lastAttendance->clock_in) {
                                Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Updating existing attendance record for IN - hoursDifference <= attendance_hour', [
                                    'checkin_date'       => $checkin->date,
                                    'checkin_time'       => $checkin->time,
                                    'check_in_branch_id' => $checkin->branch_id,
                                ]);
                                $lastAttendance->update([
                                    'clock_in'           => $checkin->time,
                                    'check_in_branch_id' => $checkin->branch_id,
                                ]);
                            }
                        } else {

                            Log::info('Checkin::created-user_id-' . $checkin->user_id . ' checkin - hoursDifference > attendance_hour', [
                                'checkin'         => $checkin,
                                'attendance_hour' => $attendance_hour,
                                'lastCheckinTime' => $lastCheckinTime,
                                'newCheckinTime'  => $newCheckinTime,
                                'hoursDifference' => $hoursDifference,
                            ]);
                            if (($attendance_hour > 0) || (($lastCheckinTime->toDateString() != $newCheckinTime->toDateString()) && $lastAttendance->clock_out != null)) {
                                Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Creating new attendance record for IN - hoursDifference > attendance_hour', [
                                    'checkin_date'       => $checkin->date,
                                    'checkin_time'       => $checkin->time,
                                    'check_in_branch_id' => $checkin->branch_id,
                                ]);
                                Attendance::create([
                                    'date'               => $checkin->date,
                                    'clock_in'           => $checkin->time,
                                    'check_in_branch_id' => $checkin->branch_id,
                                    'user_id'            => $checkin->user_id,
                                    'created_by_id'      => $checkin->user_id,
                                ]);
                            } else if ($lastCheckinTime->toDateString() == $newCheckinTime->toDateString() && $lastAttendance->clock_out != null) {

                                $lastAttendance->update([
                                    'clock_out'           => null,
                                    'clockout_date'       => null,
                                    'check_out_branch_id' => null,
                                ]);
                            }
                        }
                    } else {
                        Log::info('Checkin::created-user_id-' . $checkin->user_id . ' No clock_in in last attendance, checking date', [
                            'lastAttendance_date' => $lastAttendance->date,
                            'checkin_date'        => $checkin->date,
                        ]);
                        if ($lastAttendance->date == $checkin->date) {
                            Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Updating existing attendance record for IN - same date', [
                                'checkin_date'       => $checkin->date,
                                'checkin_time'       => $checkin->time,
                                'check_in_branch_id' => $checkin->branch_id,
                            ]);

                            $lastAttendance->update([
                                'clock_in'           => $checkin->time,
                                'check_in_branch_id' => $checkin->branch_id,
                                'clock_out'          => null,
                                'clockout_date'      => null,
                            ]);
                        } else {
                            Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Creating new attendance record for new date', [
                                'checkin_date'       => $checkin->date,
                                'checkin_time'       => $checkin->time,
                                'check_in_branch_id' => $checkin->branch_id,
                            ]);
                            Attendance::create([
                                'date'               => $checkin->date,
                                'clock_in'           => $checkin->time,
                                'user_id'            => $checkin->user_id,
                                'created_by_id'      => $checkin->user_id,
                                'check_in_branch_id' => $checkin->branch_id,
                            ]);
                        }
                    }
                } else if ($lastAttendance && $lastAttendance->visit_in) {
                    Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Updating existing attendance record for IN - visit_in exists but no clock_in', [
                        'checkin_date'       => $checkin->date,
                        'checkin_time'       => $checkin->time,
                        'check_in_branch_id' => $checkin->branch_id,
                    ]);

                    $lastAttendance->update([
                        'clock_in'           => $checkin->time,
                        'check_in_branch_id' => $checkin->branch_id,
                    ]);
                } else {
                    Log::info('Checkin::created-user_id-' . $checkin->user_id . ' Creating new attendance record - no lastAttendance found', [
                        'checkin_date'       => $checkin->date,
                        'checkin_time'       => $checkin->time,
                        'check_in_branch_id' => $checkin->branch_id,
                    ]);
                    Attendance::create([
                        'date'               => $checkin->date,
                        'clock_in'           => $checkin->time,
                        'user_id'            => $checkin->user_id,
                        'created_by_id'      => $checkin->user_id,
                        'check_in_branch_id' => $checkin->branch_id,
                    ]);
                }
            }

            if ($checkin->type == CheckinType::LATE || $checkin->type == 'late') {

                $attendance = Attendance::firstOrNew([
                    'user_id' => $checkin->user_id,
                    'date'    => $checkin->date,
                ]);

                if ($attendance) {

                    if (! $attendance->clock_in) {

                        $attendance->status             = AttendanceStatus::Late;
                        $attendance->clock_in           = $checkin->time;
                        $attendance->check_in_branch_id = $checkin->branch_id;
                        $attendance->latecomment        = $checkin->latecomment;
                        $attendance->save();
                    }
                }
            }

            if ($checkin->type == CheckinType::OUT || $checkin->type == 'out') {

                if (isset($lastAttendance)) {
                    $last_latest_clock_in = Checkin::where(['user_id' => $checkin->user_id, 'type' => 'in'])
                        ->orderBy('id', 'DESC')->first();

                    $lastCheckinTime    = Carbon::parse(($last_latest_clock_in->date . " " . $last_latest_clock_in->time));
                    $calculate_mins     = $lastCheckinTime->diffInMinutes($newCheckinTime);
                    $final_total_worked = $lastAttendance->total_worked + $calculate_mins;

                    $lastAttendance->update([
                        'clockout_date'       => $checkin->date,
                        'clock_out'           => $checkin->time,
                        'total_worked'        => $final_total_worked,
                        'check_out_branch_id' => $checkin->branch_id,
                    ]);

                } else {
                    Log::warning('No lastAttendance found for OUT checkin', [
                        'user_id'      => $checkin->user_id,
                        'checkin_time' => $checkin->time,
                        'checkin_date' => $checkin->date,
                    ]);
                }
            }

            if ($checkin->type == CheckinType::IN) {

                $currentAttendance = Attendance::where('user_id', $checkin->user_id)
                    ->where('date', $checkin->date)
                    ->first();
                if ($currentAttendance) {

                    if (! $currentAttendance->clock_in) {
                        $currentAttendance->update([
                            'clock_in' => $checkin->time,
                            'status'   => AttendanceStatus::Present,
                        ]);
                    }

                    return;
                }

                Attendance::create([
                    'date'          => $checkin->date,
                    'clock_in'      => $checkin->time,
                    'user_id'       => $checkin->user_id,
                    'created_by_id' => $checkin->user_id,
                ]);

                return;
            }
            if ($checkin->type == CheckinType::OUT) {

                $openAttendance = Attendance::where('user_id', $checkin->user_id)
                    ->whereNull('clock_out')
                    ->wherenotNull('clock_in') 
                    ->orderBy('date', 'desc')
                    ->first();
                if ($openAttendance) {

                    $lastCheckinTime = Carbon::parse(
                        $openAttendance->date . " " . $openAttendance->clock_in
                    );

                    $newCheckoutTime = Carbon::parse(
                        $checkin->date . " " . $checkin->time
                    );

                    $minutes = $lastCheckinTime->diffInMinutes($newCheckoutTime);

                    $openAttendance->update([
                        'clock_out'     => $checkin->time,
                        'clockout_date' => $checkin->date,
                        'total_worked'  => ($openAttendance->total_worked ?? 0) + $minutes,
                    ]);

                    return;
                }

                // 2. Fallback (rare case)
                Log::warning('OUT without open attendance', [
                    'user_id' => $checkin->user_id,
                    'time'    => $checkin->time,
                ]);
            }
        });

    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }
}
