<?php
namespace Modules\Attendance\Entities;

use App\Models\User;
use App\Traits\Query;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attendance\Enums\AttendanceStatus;

class Attendance extends Model
{
    use HasFactory, Query;

    public static function boot()
    {

        static::creating(function ($attendance) {
            if ($attendance->clock_out) {
                $to   = Carbon::parse(($attendance->date . " " . $attendance->clock_in));
                $from = Carbon::parse(($attendance->date . " " . $attendance->clock_out));
                //$attendance->total_worked = $to->diffInMinutes($from);
            }
            if (php_sapi_name() != 'cli') {
                $attendance->created_by_id = auth()->id() ?? $attendance->user_id;
                $attendance->latecomment   = 'facerecognition';
            } else {
                $attendance->created_by_id = $attendance->user_id;
            }
        });

        static::updating(function ($attendance) {
            if ($attendance->isDirty('clock_out')) {
                $to   = Carbon::parse(($attendance->date . " " . $attendance->clock_in));
                $from = Carbon::parse(($attendance->date . " " . $attendance->clock_out));
                //$attendance->total_worked = $to->diffInMinutes($from);
            }
        });
        parent::boot();
    }

    protected $fillable = [
        'user_id', 'date', 'clock_in', 'clock_out', 'total_worked', 'status', 'created_by_id', 'break_in', 'break_out', 'visit_in', 'visit_out', 'clockout_date', 'remarks',
         'latecomment', 'check_in_branch_id', 'check_out_branch_id',
    ];

    protected $casts = [
        'status' => AttendanceStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function getUserDayAttendance($date)
    {
        $query = Attendance::where([
            'user_id' => $this->user_id,
            'date'    => $date,
        ])->first();

        return $query;
    }

    public function getTotalWorkedInHours()
    {
        if ($this->total_worked > 0) {
            return round(($this->total_worked / 60), 2);
        }

        return 0;
    }

    public function scopeMy($query)
    {
        return $query->where('user_id', auth()->id());
    }
    public function CalculateTotalWorkingDays($userId, $monthCode, $yearCode)
    {
        $startDate = "{$yearCode}-{$monthCode}-01";
        $endDate   = "{$yearCode}-{$monthCode}-" . date('t', strtotime($startDate));

        $count = 0;
        $count = Attendance::where('user_id', $userId)->whereBetween('date', [$startDate, $endDate])->count();
        return $count;
    }
    public function getBranchNameAttribute()
    {
        $checkin = Checkin::where('user_id', $this->user_id)
            ->where('date', $this->date)
            ->latest('id')
            ->first();

        return $checkin->branch->name ?? '';
    }

}
