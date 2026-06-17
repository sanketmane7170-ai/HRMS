<?php

namespace Modules\Shift\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ShiftSchedule;

class UsersShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_for_date','schedule_id','user_id','assigned_by_id'
    ];
    
    protected static function newFactory()
    {
        return \Modules\Shift\Database\factories\ShiftAssignFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function created_shifts(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class);
    }

    public function shift_schedule_information()
    {
        return $this->belongsTo(ShiftSchedule::class, 'schedule_id');
    }
}
