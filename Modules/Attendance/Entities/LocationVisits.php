<?php

namespace Modules\Attendance\Entities;

use App\Models\User;
use App\Traits\Query;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Enums\VisitinType;
use Modules\Attendance\Entities\Attendance;

class LocationVisits extends Model
{
    use HasFactory, Query;

    //public $timestamps = false;

    protected $fillable = ['user_id', 'location', 'visit_purpose', 'visit_in', 'visit_out','date','total_worked','latitude','longitude'];

    // public static function boot()
    // {

        
    //     static::created(function ($visitin) {
    //         if ($visitin->type == VisitinType::IN) {
    //             $attendance = Attendance::firstOrNew(
    //                 [
    //                     'user_id' => $visitin->user_id,
    //                     'date' => $visitin->date,
    //                 ],
    //             );
    //             if ($attendance) {
    //                 if (!$attendance->visit_in) {
    //                     //$attendance->status = AttendanceStatus::Present;
    //                     $attendance->visit_in = $visitin->time;
    //                     $attendance->save();
    //                 }
    //             }
    //         }

    //         if ($visitin->type == VisitinType::OUT) {
    //             Attendance::updateOrCreate(
    //                 [
    //                     'user_id' => $visitin->user_id,
    //                     'date' => $visitin->date,
    //                 ],
    //                 [
    //                     'visit_out' => $visitin->time,
    //                 ]
    //             );
    //         }
    //     });

    //     parent::boot();
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalWorkedAttribute($value)
    {
        if (request()->is('api/*') || request()->expectsJson()) {
            return (int) $value;
        }
        $minutes = (int) $value;

        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }
}
