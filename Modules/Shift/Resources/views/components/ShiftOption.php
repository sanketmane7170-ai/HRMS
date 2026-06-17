<?php

namespace Modules\Shift\Resources\View\Components;

use Illuminate\View\Component;

class ShiftOption extends Component
{
    public $shift;

    public function __construct($shift)
    {
        $this->shift = $shift;
    }

    public function render()
    {
        return view('shift::components.ShiftOption');
    }
}
