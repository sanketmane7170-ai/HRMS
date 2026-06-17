<?php

namespace Modules\Document\Enums;

enum DocumentRequestStatus: string
{
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function getHtml()
    {
        $class = 'danger';
        switch ($this->name) {
            case (self::Pending->name):
                $class = 'warning';
                break;
            case (self::Completed->name):
                $class = 'success';
                break;
        }

        return "<span class='badge badge-$class'>$this->name</span>";
    }
}
