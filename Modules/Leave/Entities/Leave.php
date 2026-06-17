<?php

namespace Modules\Leave\Entities;

use App\Models\User;
use App\Traits\Query;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Leave\Enums\LeaveStatus;

class Leave extends Model
{
    use HasFactory, Query;

    public static function boot()
    {
        static::creating(function ($leave) {
            $user = $leave->user;
            if (!userCanApplyLeave($user)) {
                throw new Exception(__trans('leaves_are_not_allowed_in_probation_period'));
            }
            if($leave->is_half_day==1){
                $leave->total_leave_days = 0.5;
            } else {
                $leave->total_leave_days = $leave->calculateTotalDays();
            }
            $leave->year = date('Y', strtotime($leave->end_date));
        });

        static::updating(function ($leave) {
            $user = $leave->user;
            if (!userCanApplyLeave($user)) {
                throw new Exception(__trans('leaves_are_not_allowed_in_probation_period'));
            }
            if($leave->is_half_day==1){
                $leave->total_leave_days = 0.5;
            } else {
                $leave->total_leave_days = $leave->calculateTotalDays();
            }
        });

        static::updated(function ($leave) {

            if ($leave->status->value === LeaveStatus::Rejected->value) {
                
            } else {
                if ($leave->status->value === LeaveStatus::Approved->value) {
                    //LeaveBalance::calculate($leave->user_id, $leave->leave_type_id, $leave, $is_remove=0, $is_approved=1);
                }
                return $leave;
                // else {
                //     LeaveBalance::calculate($leave->user_id, $leave->leave_type_id, $leave);
                // }
            }

        });

        parent::boot();
    }

    protected $fillable = [
        'user_id', 'start_date', 'leave_type_id', 'is_half_day',
        'end_date', 'status', 'reason', 'remark', 'total_leave_days', 'file_path','year'
    ];

    protected $casts = [
        'status' => LeaveStatus::class
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Interact with the leave's total leave days.
     */

    public function calculateTotalDays(): int
    {
        $days = leaveDaysBewteenDate($this->start_date, $this->end_date, $this->type);

        return $days;
    }
}
