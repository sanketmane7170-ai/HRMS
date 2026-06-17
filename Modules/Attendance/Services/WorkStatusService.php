<?php

namespace Modules\Attendance\Services;

use App\Models\User;
use Carbon\Carbon;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\WorkStatusLog;
use Modules\Attendance\Enums\WorkStatus;
use Illuminate\Support\Facades\DB;

class WorkStatusService
{
    /**
     * Update the work status of a user and handle associated logic.
     */
    public function transitionTo(User $user, WorkStatus $newStatus, ?string $remarks = null): void
    {
        $oldStatus = $user->work_status;

        if ($oldStatus === $newStatus) {
            return;
        }

        DB::transaction(function () use ($user, $newStatus, $oldStatus, $remarks) {
            $now = Carbon::now();

            // 1. Close previous status log
            $currentLog = WorkStatusLog::where('user_id', $user->id)
                ->whereNull('ended_at')
                ->latest()
                ->first();

            if ($currentLog) {
                $currentLog->update([
                    'ended_at' => $now,
                    'duration_minutes' => $currentLog->started_at->diffInMinutes($now),
                ]);
            }

            // 2. Start new status log
            WorkStatusLog::create([
                'user_id' => $user->id,
                'status' => $newStatus->value,
                'started_at' => $now,
                'remarks' => $remarks,
            ]);

            // 3. Update User model
            $user->update([
                'work_status' => $newStatus,
                'status_updated_at' => $now,
            ]);

            // 4. Handle Triggers
            $this->handleTriggers($user, $newStatus, $oldStatus);
        });
    }

    /**
     * Handle side effects of status transitions (Attendance, Check-in, etc.)
     */
    protected function handleTriggers(User $user, WorkStatus $newStatus, WorkStatus $oldStatus): void
    {
        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();

        // Ensure we find the branch_id gracefully without throwing errors
        $branchId = $user->assigned_branch_id ?? $user->department_id ?? null;

        // Trigger A: Transition to ANY active working state (Available, Engaged) -> Check-In if not clocked in
        if (in_array($newStatus, [WorkStatus::ENGAGED, WorkStatus::AVAILABLE])) {
            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if (!$attendance || !$attendance->clock_in) {
                Checkin::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'time' => $nowTime,
                    'type' => 'in',
                    'branch_id' => $branchId,
                ]);
            }
        }

        // Trigger B: Meal Break or Short Break -> Record break_in
        if (in_array($newStatus, [WorkStatus::MEAL_BREAK, WorkStatus::SHORT_BREAK])) {
            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if ($attendance) {
                if (!$attendance->break_in) {
                    $attendance->update(['break_in' => $nowTime]);
                }
            }
        }

        // Trigger C: Returning from break to Available/Engaged -> Record break_out
        if (in_array($newStatus, [WorkStatus::AVAILABLE, WorkStatus::ENGAGED]) && in_array($oldStatus, [WorkStatus::MEAL_BREAK, WorkStatus::SHORT_BREAK])) {
            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if ($attendance && $attendance->break_in && !$attendance->break_out) {
                $attendance->update(['break_out' => $nowTime]);
            }
        }

        // Trigger D: Going Offline -> Automatic Clock Out
        if ($newStatus === WorkStatus::OFFLINE) {
            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
            if ($attendance && $attendance->clock_in && !$attendance->clock_out) {
                Checkin::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'time' => $nowTime,
                    'type' => 'out',
                    'branch_id' => $branchId,
                ]);
            }
        }
    }
}
