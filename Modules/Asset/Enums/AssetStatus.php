<?php

namespace Modules\Asset\Enums;

enum AssetStatus: string
{
    case Available  = 'available';
    case Assigned  = 'assigned';
    case Damaged = 'damaged';
    case Defective = 'defective';


    public function getHtml()
    {
        $class = '';
        switch ($this->name) {
            case (self::Available->name):
                $class = 'success';
                break;
            case (self::Assigned->name):
                $class = 'danger';
                break;
        }
        return "<span class='badge badge-$class'>$this->name</span>";
    }
}
