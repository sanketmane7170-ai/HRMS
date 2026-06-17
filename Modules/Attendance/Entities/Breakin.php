<?php

namespace Modules\Attendance\Entities;

use App\Models\User;
use App\Traits\Query;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Enums\BreakinType;
use Modules\Attendance\Entities\Attendance;

class Breakin extends Model
{
    use HasFactory, Query;

    public $timestamps = false;

    protected $fillable = ['user_id', 'date', 'time', 'type'];

    public static function boot()
    {

        
        static::created(function ($breakin) {
            if ($breakin->type == BreakinType::IN) {
                $attendance = Attendance::firstOrNew(
                    [
                        'user_id' => $breakin->user_id,
                        'date' => $breakin->date,
                    ],
                );
                if ($attendance) {
                    if (!$attendance->break_in) {
                        //$attendance->status = AttendanceStatus::Present;
                        $attendance->break_in = $breakin->time;
                        $attendance->save();
                    }
                }
            }

            if ($breakin->type == BreakinType::OUT) {
                Attendance::updateOrCreate(
                    [
                        'user_id' => $breakin->user_id,
                        'date' => $breakin->date,
                    ],
                    [
                        'break_out' => $breakin->time,
                    ]
                );
            }
        });

        parent::boot();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
