<?php


namespace Modules\Leave\Enums;


enum LeaveStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function getHtml()
    {
        $class = '';
        switch ($this->name) {
            case (self::Pending->name):
                $class = 'warning';
                break;
            case (self::Approved->name):
                $class = 'success';
                break;
            case (self::Rejected->name):
                $class = 'danger';
                break;
            case (self::Cancelled->name):
                $class = 'secondary';
                break;
        }
        return "<span class='badge badge-$class'>$this->name</span>";
    }
}
