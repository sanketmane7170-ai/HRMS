<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BirthdayList extends Component
{
    public $birthdays;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->birthdays = getUpcomingBirthdayQuery()->take(5)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.birthday-list');
    }
}
