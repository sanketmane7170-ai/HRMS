<?php

namespace App\Enums;

enum Relation: string
{
    case Father  = 'father';
    case Mother  = 'mother';
    case Husband  = 'husband';
    case Wife  = 'wife';
    case Spouse = 'spouse';
    case Son  = 'son';
    case Daughter  = 'daughter';
    case Other  = 'other';
}
