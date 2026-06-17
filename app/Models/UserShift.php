<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShift extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id','shift_start','shift_end'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
