<?php

namespace Modules\Warning\Enums;


enum WarningType: string
{
    case VERBAL_WARNING = 'verbal';
    case FIRST_WARNING = 'first';
    case SECOND_WARNING = 'second';
    case THIRD_WARNING = 'third';
    /* Above Two Warnings For Boon Portal Only */
    case PERFORMANCE = 'performance';
    case NOTICE_OF_TERMINATION = 'notice_of_termination';
    case ATTENDANCE_ISSUE = 'attendance_issue';
    case TERMINATION = 'termination';
    

    public function getHtml()
    {
        $class = 'success';
        switch ($this->name) {
            case (self::VERBAL_WARNING->name):
                $class = 'warning';
                break;
            case (self::FIRST_WARNING->name):
                $class = 'secondary';
                break;
            case (self::SECOND_WARNING->name):
                $class = 'info';
                break;
            case (self::THIRD_WARNING->name):
                $class = 'danger';
                break;
            case (self::PERFORMANCE->name);
                $class = 'danger';
                break;
            case (self::NOTICE_OF_TERMINATION->name);
                $class = 'danger';
                break; 
            case (self::ATTENDANCE_ISSUE->name);
                $class = 'danger';
                break;        
            case (self::TERMINATION->name);
                $class = 'danger';
                break;
        }

        return "<span class='badge badge-$class'>" . __trans($this->name) . "</span>";
    }

    public function getName()
    {
        return __trans($this->name);
    }
}
