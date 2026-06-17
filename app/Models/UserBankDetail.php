<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'bank_name', 'account_number', 'iba_number', 'swift_code','routing_number'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
