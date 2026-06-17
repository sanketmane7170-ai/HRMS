<?php

namespace Modules\Announcement\View\Components;

use Illuminate\View\Component;

class ShowAnnouncement extends Component
{

    public $announcements;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->announcements = getActiveAccouncements();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('announcement::components.showannouncement');
    }
}
