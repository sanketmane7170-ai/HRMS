<?php

namespace Modules\Attendance\Traits;

use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Enums\CheckinType;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Leave\Enums\LeaveType;

trait AttendanceHelper
{
    /**
     * Check if user has check in for the day or not
     *
     * @return Checkin
     */

    public static function hasUserCheckIn($user_id, $date): Checkin|null
    {
        $checkin = Checkin::where([
            'user_id' => $user_id,
            'date' => $date,
            'type' => CheckinType::IN
        ])->first();

        return $checkin;
    }

    /**
     * Check if Today is marked as holiday
     *
     * @return bool
     */
    public static function isTodayHoliday($date): bool
    {
        /// if particular Date is holiday
        $query = Holiday::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
        $isHoliday = $query->exists();
        if (!$isHoliday) {
        }
        if (!$isHoliday) {
            $isHoliday = self::checkIsRecurringHoliday($date);
        }
        return $isHoliday;
    }

    public static function checkIsRecurringHoliday($date)
    {
        $month = now()->parse($date)->format('m');
        $day = now()->parse($date)->format('d');
        $query = Holiday::where(function ($query) use ($month, $day) {
            $query->whereMonth('start_date', '<=', $month)->whereDay('start_date', '<=', $day);
        })->where(function ($query) use ($month, $day) {
            $query->whereMonth('end_date', '>=', $month)->whereDay('end_date', '>=', $day);
        })->where('is_recurring', Holiday::RECURRING);

        return $query->exists();
    }

    /**
     * Check if Current Date is weekend or not
     *
     * @return Leave
     */
    public static function isWeekend(string $date): bool
    {
        return now()->parse($date)->isWeekend();
    }

    /**
     * Check if User is on Leave or not
     *
     * @return Leave
     */
    public static function isUserOnLeave($user_id, $date): Leave|null
    {
        $leave = Leave::with('type')->where([
            'user_id' => $user_id,
            'status' => LeaveStatus::Approved
        ])->where(function ($query) use ($date) {
            return $query->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date);
        })->first();

        if ($leave) {
            if ($leave->type->type == LeaveType::Working) {
                if (in_array(now()->parse($date)->dayName, config('leave.weekend_days'))) {
                    $leave = null;
                }
            }
        }
        return $leave;
    }

    /**
     *@param \App\Models\User $employee
     *@param date $date
     *@param bool $isHoliday Check is supplied date is holiday
     *@param bool $isWeekend Check is supplied date is weekend
     */
    public function logEmployeeAttendance($employee, $date, $isholiday, $isWeekend): void
    {
        $employee_id = $employee->id;
        $clockIn = null;
        $status = AttendanceStatus::Absent;

        if ($isWeekend) {
            $status = AttendanceStatus::Weekend;
        }

        $onleave = self::isUserOnLeave($employee_id, $date);
        if ($onleave) {
            $status = AttendanceStatus::Leave;
            $keywords = ['Sick', 'sick', 'sick leave', 'Sick Leave' ];
            if (in_array($onleave->type->name, $keywords)) {
                $status = AttendanceStatus::SickLeave;
            }
        }
        if (!$onleave) {
            if ($isholiday) {
                $status = AttendanceStatus::Holiday;
            }
        }

        $checkin = self::hasUserCheckIn($employee_id, $date);

        if ($checkin) {
            $status = AttendanceStatus::Present;
            $clockIn = $checkin->time;
        }

        Attendance::updateOrCreate(
            [
                'user_id' => $employee_id,
                'date' => $date,
            ],
            [
                'clock_in' => $clockIn,
                'status' => $status,
            ]
        );
    }
}
