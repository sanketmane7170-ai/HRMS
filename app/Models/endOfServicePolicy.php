<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Leave\Entities\LeaveType;

class endOfServicePolicy extends Model
{
    protected $guarded = ['id'];

    public function type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }
}
