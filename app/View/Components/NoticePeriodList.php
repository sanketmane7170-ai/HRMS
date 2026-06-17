<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class NoticePeriodList extends Component
{
    public Collection $noticePeriodList;

    /**
     * Create a new component instance.
     * 
     * Author: Sanket - Fetches employees in notice period using helper
     */
    public function __construct()
    {
        $this->noticePeriodList = getNoticePeriodQuery()->take(5)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.notice-period-list');
    }
}
