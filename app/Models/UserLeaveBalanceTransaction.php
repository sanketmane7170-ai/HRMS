<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Leave\Entities\LeaveType;

class UserLeaveBalanceTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class)->where('status', User::STATUS_ACTIVE);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
