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
use Carbon\Carbon;
use Modules\Attendance\Entities\LocationVisits;

class Visitin extends Model
{
    use HasFactory, Query;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'date',
        'time',
        'type',
        'location_id',
        'location',
        'longitude',
        'latitude',
    ];

    public static function boot()
    {
        static::created(function ($visitin) {
            if ($visitin->type == VisitinType::IN) {
                $attendance = Attendance::firstOrNew(
                    [
                        'user_id' => $visitin->user_id,
                        'date' => $visitin->date,
                    ],
                );
                if ($attendance) {
                    // if (!$attendance->clock_in) {
                    //     $attendance->clock_in = $visitin->time;
                    //     $attendance->save();

                    //     // create checkin entry if not created
                    //     $checkinExist = Checkin::where([
                    //         'date' => $visitin->date,
                    //         'user_id' => $visitin->user_id
                    //     ])->orderByDesc('id')->first();
                    //     if (!isset($checkinExist) || empty($checkinExist)
                    //         || $checkinExist->type == 'out' ){
                    //         Checkin::create([
                    //             'user_id' => $visitin->user_id,
                    //             'date' => $visitin->date,
                    //             'time' => $visitin->time,
                    //             'type' => 'in'
                    //         ]);
                    //     }
                    // }
                    if (!$attendance->visit_in) {
                        //$attendance->status = AttendanceStatus::Present;
                        $attendance->visit_in = $visitin->time;
                        $attendance->save();
                    }
                }
            }

            if ($visitin->type == VisitinType::OUT) {
                $locationvisit = LocationVisits::find($visitin->location_id);

                $to = Carbon::parse(($locationvisit->date . " " . $locationvisit->visit_in));
                $from = Carbon::parse(($locationvisit->date . " " . $locationvisit->visit_out));
                $locationvisit->total_worked = $to->diffInMinutes($from);
                $locationvisit->save();
                Attendance::updateOrCreate(
                    [
                        'user_id' => $visitin->user_id,
                        'date' => $visitin->date,
                    ],
                    [
                        'visit_out' => $visitin->time,
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
