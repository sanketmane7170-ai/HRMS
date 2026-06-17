<?php

namespace Modules\Attendance\Enums;


enum VisitinType: string
{
    case IN = 'start';
    case OUT = 'end';
}
