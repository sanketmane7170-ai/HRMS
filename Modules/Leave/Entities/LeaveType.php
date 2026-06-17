<?php

namespace Modules\Leave\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Leave\Enums\LeaveType as EnumsLeaveType;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'days', 'is_paid', 'type'
    ];

    protected $casts = [
        'type' => EnumsLeaveType::class
    ];
}
