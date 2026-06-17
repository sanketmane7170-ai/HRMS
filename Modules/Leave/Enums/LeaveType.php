<?php


namespace Modules\Leave\Enums;

enum LeaveType: string
{
    case Calendar = 'calendar';
    case Working = 'working';

    public function getHtml()
    {
        $class = '';
        switch ($this->name) {
            case (self::Calendar->name):
                $class = 'success';
                break;
            case (self::Working->name):
                $class = 'warning';
                break;
        }
        return "<span class='badge badge-$class'>$this->name</span>";
    }
}
