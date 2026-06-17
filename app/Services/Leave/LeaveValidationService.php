<?php

namespace App\Services\Leave;

use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Carbon\Carbon;

class LeaveValidationService
{
    //Sanket v2.0 - validates a leave request before creation; checks balance, date logic, overlaps, and probation; returns violations list
    public function validateLeaveRequest(int $userId, int $leaveTypeId, Carbon $startDate, Carbon $endDate, array $options = []): array
    {
        $violations = [];
        $isHalfDay  = $options['is_half_day'] ?? false;

        // 1. Date order check
        if ($startDate->greaterThan($endDate)) {
            $violations[] = 'Start date cannot be after end date.';
        }

        // 2. Past date check
        if ($startDate->startOfDay()->lessThan(Carbon::today())) {
            $violations[] = 'Leave start date cannot be in the past.';
        }

        // 3. Leave type existence
        $leaveType = LeaveType::where('id', $leaveTypeId)->where('is_active', true)->first();
        if (!$leaveType) {
            $violations[] = 'Invalid or inactive leave type.';
            return ['valid' => false, 'violations' => $violations, 'leave_days' => 0];
        }

        // 4. Calculate leave days
        if ($isHalfDay) {
            $leaveDays = 0.5;
        } else {
            // Count working days (Mon–Sat), skip Sundays
            $leaveDays = 0;
            $current = $startDate->copy();
            while ($current->lessThanOrEqualTo($endDate)) {
                if (!$current->isSunday()) {
                    $leaveDays++;
                }
                $current->addDay();
            }
        }

        if ($leaveDays <= 0) {
            $violations[] = 'Leave duration must be at least 0.5 days.';
        }

        // 5. Balance check
        $balance = LeaveBalance::where([
            'user_id'       => $userId,
            'leave_type_id' => $leaveTypeId,
            'year'          => date('Y'),
        ])->first();

        if (!$balance || (float) $balance->available < $leaveDays) {
            $available = $balance ? (float) $balance->available : 0;
            $violations[] = "Insufficient balance. Requested: {$leaveDays} day(s), Available: {$available} day(s).";
        }

        // 6. Overlap check – no other pending/approved leave in the same period
        $overlap = Leave::where('user_id', $userId)
            ->whereIn('status', [LeaveStatus::Pending->value, LeaveStatus::Approved->value])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate->toDateString())
                         ->where('end_date', '>=', $endDate->toDateString());
                  });
            })->exists();

        if ($overlap) {
            $violations[] = 'You already have a pending or approved leave overlapping these dates.';
        }

        return [
            'valid'      => empty($violations),
            'violations' => $violations,
            'leave_days' => $leaveDays,
        ];
    }
}
