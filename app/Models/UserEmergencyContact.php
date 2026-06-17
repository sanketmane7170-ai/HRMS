<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'emergency_name',
        'emergency_relation',
        'emergency_phone',
        'emergency_isd_code',
        'emergency_email',
        'emergency_home_country',
        'emergency_home_address',
        'emergency_local_country',
        'local_person_name',
        'local_person_relation',
        'local_person_phone',
        'emergency_local_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
