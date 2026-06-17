<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Carbon\Carbon;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;

class UpcomingLeaveList extends Component
{

    public $leavelist;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $today = Carbon::today();
        $this->leavelist = Leave::where('status', LeaveStatus::Approved)
                    ->where(function ($query) use ($today) {
                        $query->where(function ($q) use ($today) {
                            // Current leave
                            $q->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today);
                        })->orWhere(function ($q) use ($today) {
                            // Upcoming leave
                            $q->whereDate('start_date', '>', $today);
                        });
                    })->with('user.department')
                    ->orderBy('start_date', 'asc')
                    ->limit(5)
                    ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.leave-list');
    }
}
