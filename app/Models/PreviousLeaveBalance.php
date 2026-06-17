<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreviousLeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id', 'leave_type_id', 'days', 'comment'
    ];

    public function leave_name(){
        return $this->hasOne('Modules\Leave\Entities\LeaveType','id','leave_type_id');
    }
}
