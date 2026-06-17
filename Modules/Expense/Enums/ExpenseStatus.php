<?php


namespace Modules\Expense\Enums;


enum ExpenseStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    // case Active = "1";
    // case active = "active";

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
        }
        return "<span class='badge badge-$class'>$this->name</span>";
    }
}
