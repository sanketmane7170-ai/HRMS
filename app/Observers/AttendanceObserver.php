<?php
namespace App\Observers;

use Modules\Attendance\Entities\Attendance;
use App\Models\User;
use Modules\Attendance\Enums\AttendanceStatus;

class AttendanceObserver
{
    public function created(Attendance $attendance)
    {
        $this->updateTotalWorkingDays($attendance->user);
    }

    public function updated(Attendance $attendance)
    {
        $this->updateTotalWorkingDays($attendance->user);
    }

    private function updateTotalWorkingDays(User $user)
    {
        $year = date('Y');
        $month = date('m');
        $presentCount = $user->attendances()
        ->where('status', AttendanceStatus::Present)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->count();
        $user->salary()->update(['total_working_days' => $presentCount]);
        //$user->save();
    }
}
