<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Otp extends Model
{
    protected $fillable = ['user_id', 'code_hash', 'expires_at', 'attempts'];

    // Preferred in modern Laravel: cast expires_at to a Carbon instance automatically
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // helper
    public function isExpired(): bool
    {
        // now() and $this->expires_at are Carbon instances thanks to $casts
        return now()->greaterThan($this->expires_at);
        // or return $this->expires_at->isPast();
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
