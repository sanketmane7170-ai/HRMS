<?php

namespace App\Enums;

enum MartialStatus: string
{
    case Single  = 'single';
    case Married  = 'married';
    case Divorced  = 'divorced';
    case Widow  = 'widow';
}
