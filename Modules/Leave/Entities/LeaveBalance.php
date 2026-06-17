<?php
namespace Modules\Leave\Entities;

use App\Models\PreviousLeaveBalance;
use App\Models\User;
use App\Traits\Query;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;

class LeaveBalance extends Model
{
    use HasFactory, Query;

    protected $fillable = [
        'year',
        'user_id',
        'leave_type_id',
        'available',
        'thisYearAvailableLeave',
        'isAddThisMonthLeave',
        'is_add_ph_leave',
        'monthwiseDay',
        'initial_balance',
        'initial_balance_date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'available' => 'float',
    ];

    public function getAvailableAttribute($value)
    {
        return round($value, 2);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
    /* UPDATED FUNCTION */
    public static function calculate(int $user_id, int $leave_type_id, $leave = null, $is_remove = null, $is_approved = null)
    {
        $oneYearBack = Carbon::now()->subYear()->year;
        $model = static::where([
            'user_id' => $user_id,
            'leave_type_id' => $leave_type_id,
        ])->first();
        $leaveType = LeaveType::whereId($leave_type_id)->first();
        $approvedLeaves = Leave::where(
            [
                'user_id' => $user_id,
                'leave_type_id' => $leave_type_id,
                'status' => LeaveStatus::Approved,
                'year' => date('Y'),
            ]
        )->sum('total_leave_days');
        // Condition Check if is_recurring enabled or not 02-05-2024
        $total_leaves = $leaveType->days;

        $new_days = 0;
        $extra_days = 0;

        $current_date = now();
        $created_at = User::where('id', $user_id)->value('created_at');

        // Check if the created_at date is within one year from the current date
        if ($created_at->diffInYears($current_date) < 1) {
            //echo "Implement previous leave balance concept";
            $extra_days = PreviousLeaveBalance::where(['user_id' => $user_id, 'leave_type_id' => $leaveType->id])->value('days');
        } else {
            // if ($leaveType->is_recurring == '1') {
                $totalapprovedLeaves = Leave::where(
                    [
                        'user_id' => $user_id,
                        'leave_type_id' => $leaveType->id,
                        'status' => LeaveStatus::Approved,
                        'year' => $oneYearBack,
                    ]
                )->sum('total_leave_days');
                $total_given_in_year = $leaveType->days;
                $total_is_recurring_leaves = $leaveType->no_of_leaves;

                $total_carry_forword_leaves = ($total_given_in_year + $total_is_recurring_leaves) - $totalapprovedLeaves;

                if ($total_carry_forword_leaves >= $total_is_recurring_leaves) {
                    $extra_days = $total_is_recurring_leaves;
                } else {
                    $extra_days = $total_carry_forword_leaves;
                }
            // }
        }
        // if($leaveType->is_recurring == 1){
        //     $last_year_balance = LeaveBalance::where(['leave_type_id'=>$leave_type_id,'user_id'=>$user_id,'year'=>$oneYearBack])->value('available');
        //     $carry_forword_set_balance = $leaveType->no_of_leaves;
        //     /*
        //       ex last year leaves = 10 & carry forword leave set by admin 5
        //       so in this case we can only addon carry forword leaves
        //     */
        //     if($last_year_balance >= $carry_forword_set_balance){
        //         $new_days = $carry_forword_set_balance;
        //     }
        //     /*
        //       ex last year leaves = 4 & carry forword leave set by admin 5
        //       so in this case we can only addon last year leaves
        //     */
        //     if($last_year_balance < $carry_forword_set_balance){
        //         $new_days = $last_year_balance;
        //     }
        // }

        // $total_leaves = $total_leaves + $new_days;
        $total_leaves = $total_leaves + $extra_days;
        $user = User::find($user_id);
        $yearMonth = 12;
        $joining_date = $user->workDetail?->joining_date->toDateString();
        if (Carbon::parse($joining_date)->isCurrentYear()) {
            $month = Carbon::parse($joining_date)->format('m');
            $leaveTotal = $total_leaves / $yearMonth;
            $totalmonth = 12 - $month;
            $total_leaves = floor($leaveTotal * $totalmonth);
            $yearMonth = $totalmonth;
        }
        // dd($total_leaves);die;
        $isLeaveBalance = LeaveBalance::where([
            'user_id' => $user_id,
            'leave_type_id' => $leave_type_id,
            'year' => date('Y'),
        ])->first();
        if ($isLeaveBalance) {
            $leaveBalance = $isLeaveBalance->available;
            $availableleave = $leaveBalance - 0;
            $thisYearAvailableLeave = $leaveBalance - 0;
            if (is_null($leave)) {
                $availableleave = $leaveBalance - 0;
                $thisYearAvailableLeave = $leaveBalance - 0;
            } else {
                if ($is_remove == 1) {
                    $availableleave = $leaveBalance + $leave->total_leave_days;
                    $thisYearAvailableLeave = $leaveBalance + $leave->total_leave_days;
                } else {
                    if ($is_approved == 1) {
                        $availableleave = $leaveBalance - $leave->total_leave_days;
                        $thisYearAvailableLeave = $leaveBalance - $leave->total_leave_days;
                    }
                }
            }
            $isLeaveBalance->available = round($availableleave, 1);
            $isLeaveBalance->thisYearAvailableLeave = $thisYearAvailableLeave;
            $isLeaveBalance->save();
        } else {
            $total_leaves = round($total_leaves / $yearMonth, 1);
            LeaveBalance::updateOrCreate([
                'user_id' => $user_id,
                'leave_type_id' => $leave_type_id,
                'year' => date('Y'),
            ], [
                'available' => round($total_leaves - $leave->total_leave_days, 1),
                'isAddThisMonthLeave' => date('m'),
                'thisYearAvailableLeave' => $total_leaves - $leave->total_leave_days,
            ]);
        }
    }

    /* OLD FUNCTION - COMMENTED OUT
    public static function calculate(int $user_id, int $leave_type_id)
    {
        $model = static::where([
            'user_id' => $user_id,
            'leave_type_id' => $leave_type_id
        ])->first();

        $leaveType = LeaveType::whereId($leave_type_id)->first();

        $approvedLeaves = Leave::where(
            [
                'user_id' => $user_id,
                'leave_type_id' => $leave_type_id,
                'status' => LeaveStatus::Approved
            ]
        )->sum('total_leave_days');

        // $approvedLeaves = Leave::where('user_id',$user_id)
        //                 ->where('leave_type_id',$leave_type_id)
        //                 ->where('status',LeaveStatus::Approved)
        //                 ->first();
        // $startDate = new DateTime($approvedLeaves->start_date);
        // $endDate = new DateTime($approvedLeaves->end_date);

        // $interval = $startDate->diff($endDate);
        // $totalDays = $interval->days + 1;

        // if($leaveType->days < $totalDays){
        //     $available = 0;
        // }else{
        //     $available = $leaveType->days - ($totalDays == 0 ? 1 : $totalDays);
        // }

        LeaveBalance::updateOrCreate([
            'user_id' => $user_id,
            'leave_type_id' => $leave_type_id,
            'year' => date('Y')
        ], [
            'available' => $leaveType->days - $approvedLeaves
        ]);
    }
    */
}
