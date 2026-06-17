<?php

namespace Modules\IndianPayroll\Services\Attendance;

use Carbon\CarbonPeriod;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;

/**
 * Resolves present/paid/loss-of-pay day counts for a payroll period by reading
 * Modules\Attendance and Modules\Leave directly — reused, not duplicated. Unlike the
 * legacy Payroll module's per-day N+1 query loop, this prefetches both data sets once
 * and iterates the period in memory.
 */
class AttendanceDataResolver
{
    public function resolve(int $userId, \DateTimeInterface $periodStart, \DateTimeInterface $periodEnd): array
    {
        $start = \Carbon\Carbon::parse($periodStart)->startOfDay();
        $end = \Carbon\Carbon::parse($periodEnd)->endOfDay();

        $attendanceByDate = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get(['date', 'status'])
            ->keyBy(fn ($a) => \Carbon\Carbon::parse($a->date)->toDateString());

        $approvedLeaves = Leave::where('user_id', $userId)
            ->where('status', LeaveStatus::Approved->value)
            ->with('type')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                    });
            })
            ->get();

        $paidLeaveDates = [];
        foreach ($approvedLeaves as $leave) {
            if (! $leave->type || ! $leave->type->is_paid) {
                continue;
            }
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) {
                $paidLeaveDates[$date->toDateString()] = $leave->is_half_day ? 0.5 : 1.0;
            }
        }

        $daysInPeriod = $start->diffInDays($end) + 1;
        $presentDays = 0.0;
        $paidNonWorkingDays = 0.0;
        $paidLeaveDays = 0.0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $key = $date->toDateString();
            $attendance = $attendanceByDate->get($key);
            $status = $attendance?->status instanceof AttendanceStatus ? $attendance->status : AttendanceStatus::tryFrom((string) $attendance?->status);

            if ($status === AttendanceStatus::Present || $status === AttendanceStatus::Late || $status === AttendanceStatus::EarlyOut) {
                $presentDays += 1.0;
            } elseif ($status === AttendanceStatus::HalfDay) {
                $presentDays += 0.5;
            } elseif ($status === AttendanceStatus::Weekend || $status === AttendanceStatus::Holiday) {
                $paidNonWorkingDays += 1.0;
            } elseif (isset($paidLeaveDates[$key])) {
                $paidLeaveDays += $paidLeaveDates[$key];
            }
            // Absent, unpaid leave, or no attendance record on a working day falls through
            // and is simply not counted as paid — reflected below via daysInPeriod - paidDays.
        }

        $paidDays = round($presentDays + $paidNonWorkingDays + $paidLeaveDays, 2);

        return [
            'days_in_period' => $daysInPeriod,
            'paid_days' => $paidDays,
            'loss_of_pay_days' => round($daysInPeriod - $paidDays, 2),
        ];
    }
}
