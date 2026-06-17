<?php

namespace Modules\Attendance\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'started_at',
        'ended_at',
        'duration_minutes',
        'remarks',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
