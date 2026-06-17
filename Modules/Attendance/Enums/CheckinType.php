<?php

namespace Modules\Attendance\Enums;


enum CheckinType: string
{
    case IN = 'in';
    case OUT = 'out';
    case LATE = 'late';
}
