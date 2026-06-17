<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PHLeaveReport extends Model
{
    use HasFactory;

    protected $fillable = [ 'user_id','holiday_id','leave_type_id','date' ];
}
