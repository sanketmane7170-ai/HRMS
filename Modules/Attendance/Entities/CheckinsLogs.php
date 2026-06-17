<?php

namespace Modules\Attendance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CheckinsLogs extends Model
{
    use HasFactory;

    protected $fillable = ['comment','user_id','date'];
    
    protected static function newFactory()
    {
        return \Modules\Attendance\Database\factories\CheckinsLogsFactory::new();
    }
}
