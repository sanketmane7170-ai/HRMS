<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AnniversaryList extends Component
{

    public $anniversaries;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->anniversaries = getUpcomingWorkAnniversariesQuery()->take(5)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.anniversary-list');
    }
}
