<?php

namespace Modules\NotificationManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\NotificationManager\Database\factories\AlertRecipientFactory;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AlertRecipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role_id',
        'user_id',
        'alert_status'
    ];
    
    protected static function newFactory(): AlertRecipientFactory
    {
        //return AlertRecipientFactory::new();
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(){
        return $this->belongsTo(Role::class, 'role_id');
    }
}
