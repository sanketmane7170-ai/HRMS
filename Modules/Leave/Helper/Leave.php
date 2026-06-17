<?php

use App\Models\User;
use Carbon\Carbon;
use Modules\Leave\Enums\LeaveType;

if (!function_exists('userCanApplyLeave')) {
    function userCanApplyLeave(User $user)
    {
        if (config('leave.probation_user_can_apply')) {
            return true;
        }

        if ($user->isInProbation()) {
            return false;
        }

        return true;
    };
}

if (!function_exists('leaveDaysBewteenDate')) {

    function leaveDaysBewteenDate($start_date, $end_date, $leaveType)
    {
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date)->addDay();
        $type = $leaveType->type;
        
        $days = $startDate->diffInDays($endDate);
        
        return $days;
    }

    /* WITH WEEKEND DAYS FUNCTIONALITY 
    function leaveDaysBewteenDate($start_date, $end_date, $leaveType)
    {
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date)->addDay('1');
        $type = $leaveType->type;
        $days = $startDate->diffInDaysFiltered(function (Carbon $date) use ($type) {
            if (LeaveType::Working == $type) {
                //// excluded if a weekend day and type of leave is working
                if (in_array($date->dayName, config('leave.weekend_days'))) {
                    return false;
                }
            }
            return true;
        }, $endDate);

        return $days;
    }
    */
}
