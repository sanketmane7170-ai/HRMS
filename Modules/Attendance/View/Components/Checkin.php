<?php

namespace Modules\Attendance\View\Components;

use Illuminate\View\Component;

class Checkin extends Component
{
    public $selector;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($selector = null)
    {
        $this->selector = $selector;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('attendance::components.checkin');
    }
}
