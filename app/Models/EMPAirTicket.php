<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMPAirTicket extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';
    protected $guarded = ['id'];
    protected $attributes = [
        'status' => self::STATUS_PENDING,

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
