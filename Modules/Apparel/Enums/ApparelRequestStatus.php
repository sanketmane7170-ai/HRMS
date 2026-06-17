<?php


namespace Modules\Apparel\Enums;


enum ApparelRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
// 0=>pending,1=>approved,2=>rejected,3=>cancelled	
    public function getHtml()
    {
        $class = '';
        switch ($this->name) {
            case (0):
                $class = 'warning';
                break;
            case (1):
                $class = 'success';
                break;
            case (2):
                $class = 'danger';
                break;
        }
        return "<span class='badge badge-$class'>$this->name</span>";
    }
}
