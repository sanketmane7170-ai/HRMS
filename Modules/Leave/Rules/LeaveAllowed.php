<?php

namespace Modules\Leave\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Carbon\Carbon;
use Modules\Leave\Entities\LeaveBalance;
use App\Models\Setting;

class LeaveAllowed implements ValidationRule
{

    public $except;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id = null)
    {
        $this->except = $id;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    /* UPDATED FUNCTION */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $oneYearBack = Carbon::now()->subYear()->year;
        $type = LeaveType::find($value);
        $count = Leave::my()->whereYear('start_date', date('Y'))->whereYear('end_date', date('Y'))
            ->where('status', LeaveStatus::Approved->value)
            ->where('leave_type_id',$type->id)
            ->when($this->except, function ($query) {
                return $query->whereNotIn('id', [$this->except]);
            })->sum('total_leave_days');
        $total_days = $type->days;
        $new_days = 0;
        $extra_days = 0;
        if($type->is_recurring == '1'){
            $totalapprovedLeaves = Leave::my()->where(
                [
                    'leave_type_id' => $type->id,
                    'status' => LeaveStatus::Approved,
                    'year' => $oneYearBack
                ]
            )->sum('total_leave_days');
            $total_given_in_year = $type->days;
            $total_is_recurring_leaves = $type->no_of_leaves;

            $total_carry_forword_leaves = ($total_given_in_year + $total_is_recurring_leaves) - $totalapprovedLeaves;
            
            if($total_carry_forword_leaves >= $total_is_recurring_leaves){
                $extra_days = $total_is_recurring_leaves;
            } else { 
                $extra_days = $total_carry_forword_leaves;
            }
        }
        // if($type->is_recurring == 1){
        //     $last_year_balance = LeaveBalance::my()->where(['leave_type_id'=>$type->id,'year'=>$oneYearBack])->value('available');
        //     $carry_forword_set_balance = $type->no_of_leaves;
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
        // $total_days = $total_days + $new_days;
        $checkLeaveBalance = LeaveBalance::where([
            'user_id' => auth()->id(),
            'leave_type_id' => $type->id,
            'year' => date('Y')
        ])->first();
        if($checkLeaveBalance){
            $checkmonthwise = Setting::where('key','is_month_wise_show_leave')->value('value');
            if($checkmonthwise == 1){
                $total_days = $checkLeaveBalance->monthwiseDay;
            } else {
                $total_days = $checkLeaveBalance->available;
            }
        } else {
            $total_days = 0;
        }
        $leaveSetting = Setting::where('key','allow_negative_leave')->first();
        if ($leaveSetting && $leaveSetting->value == 0) {
            // if ($count > $total_days) {
            //     //$fail(__trans('you_have_already_used_allowed_leaves'));
            //     $fail(__trans('You have reached to the maximum limit of sending leave request. Please direct contact with HR regarding you leave.'));
            // }
            $currentLeaveDays = leaveDaysBewteenDate(request()->start_date, request()->end_date, $type);
            //dd($count,$type->days,$total_days,$currentLeaveDays);
            if ($currentLeaveDays > $total_days) {
                $fail(__trans('you_dont_have_enough_leaves'));
            }
        }
    }

    /* OLD FUNCTION
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type = LeaveType::find($value);
        $count = Leave::my()->whereYear('start_date', date('Y'))->whereYear('end_date', date('Y'))
            ->whereIn('status', [LeaveStatus::Pending->value, LeaveStatus::Approved->value])
            // ->when($this->except, function ($query) {
            //     return $query->whereNotIn('id', [$this->except]);
            // })
            ->where('leave_type_id',$this->except)
            ->sum('total_leave_days');

        if ($count > $type->days) {
            //$fail(__trans('you_have_already_used_allowed_leaves'));
            $fail(__trans('You have reached to the maximum limit of sending leave request. Please direct contact with HR regarding you leave.'));
        }
        $currentLeaveDays = leaveDaysBewteenDate(request()->start_date, request()->end_date, $type);

        if (($count + $currentLeaveDays) > $type->days) {
            $fail(__trans('you_dont_have_enough_leaves'));
        }
    }
    */
}
