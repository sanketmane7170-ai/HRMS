<?php

namespace App\View\Components;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Checkin;

class CheckinList extends Component
{
    public int $userCount;
    public int $checkInCount;
    public int $percentage;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        // $this->userCount = User::where('status', User::STATUS_ACTIVE)
        //     ->notAdmin()->count();
        // //$this->checkInCount = Checkin::where('date', now()->toDateString())->distinct('user_id')->count();
        // $this->checkInCount = Attendance::whereDate('date', today())
        //             ->where('status', 'present')
        //             ->distinct('user_id')
        //             ->count('user_id');
        // $this->percentage =  ($this->checkInCount /  $this->userCount) * 100;
        $this->userCount = User::where('status', User::STATUS_ACTIVE)
            ->notAdmin()
            ->count();

        $this->checkInCount = Attendance::whereDate('date', today())
            ->where('status', 'present')
            ->distinct('user_id')
            ->count('user_id');

        // Prevent division by zero
        if ($this->userCount > 0) {
            $this->percentage = ($this->checkInCount / $this->userCount) * 100;
        } else {
            $this->percentage = 0;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.checkin-list');
    }
}
