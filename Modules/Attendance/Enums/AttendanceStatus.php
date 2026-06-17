<?php

namespace Modules\Attendance\Enums;


enum AttendanceStatus: string
{
    case Holiday = 'holiday';
    case Weekend = 'weekend';
    case Present = 'present';
    case Absent = 'absent';
    case Leave = 'leave';
    case Late = 'late';
    case SickLeave = 'sickleave';
    case EarlyOut = 'earlyout';
    case HalfDay = 'halfday';



    public function getHtml()
    {
        $class = 'success';
        switch ($this->name) {
            case (self::Holiday->name):
                $class = 'warning';
                break;
            case (self::Weekend->name):
                $class = 'warning';
                break;
            case (self::Absent->name):
                $class = 'danger';
                break;
            case (self::Leave->name):
                $class = 'warning';
                break;
            case (self::Late->name):
                $class = 'warning';
                break;
            case (self::SickLeave->name):
                $class = 'warning';
                break;
            case (self::EarlyOut->name):
                $class = 'warning';
                break;
            case (self::HalfDay->name):
                $class = 'warning';
                break;
        }

        return "<span class='badge badge-$class'>$this->name</span>";
    }

    public function getIcon()
    {

        return "<img src=" .  \Module::asset('attendance:images/' . $this->value . '.svg') . "  />";
    }

    public function PresentCheckOutIcon()
    {

        return "<img src=" .  \Module::asset('attendance:images/present_full.svg') . " style='width:16px' />";
    }
}
