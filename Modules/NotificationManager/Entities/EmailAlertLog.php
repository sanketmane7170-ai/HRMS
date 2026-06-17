<?php

namespace Modules\NotificationManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailAlertLog extends Model
{
    use HasFactory;

    protected $fillable = ['email','status','message','alert_type'];
    
    protected static function newFactory()
    {
        return \Modules\NotificationManager\Database\factories\EmailAlertLogFactory::new();
    }
}
