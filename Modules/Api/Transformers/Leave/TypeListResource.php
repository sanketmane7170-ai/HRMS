<?php

namespace Modules\Api\Transformers\Leave;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;
use Carbon\Carbon;
use App\Models\PreviousLeaveBalance;
use Modules\Leave\Entities\LeaveBalance as EntitiesLeaveBalance;
use Modules\Attendance\Entities\Holiday;
use Modules\Leave\Entities\LeaveBalanceUpdateLog;
use App\Models\UserWorkDetail;
use App\Models\Setting;
use App\Models\User;
use App\Models\PreviousYearLeave;
use App\Models\PHLeaveReport;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Entities\Attendance;
use App\Models\extraWorkRequest;
use App\Models\Shifts;
use Modules\Shift\Entities\UsersShift;
use App\Models\ShiftSchedule;
use App\Models\UserLeaveBalanceTransaction;

class TypeListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $userId = $request->user_id ?? auth()->id();
        $balance = LeaveBalance::where(
            [
                'user_id' => $userId,
                'year' => date('Y'),
                'leave_type_id' => $this->id
            ]
        )->latest('updated_at')->first();
        $checkmonthwise = Setting::where('key','is_month_wise_show_leave')->value('value');

        if(!$balance){
            $yearMonth = 12;
            $userfromtable = User::find($userId);
            $joining_date = Carbon::parse($userfromtable->workDetail?->joining_date);
            $currentYearDate = Carbon::now();
            $daysDiff = $currentYearDate->diffInDays($joining_date);
            $leaveType = LeaveType::find($this->id);
            $total_days = $leaveType->days;
            $user_id = $userId;
            $date = Carbon::now()->toDateString();
            $currentMonth = Carbon::now();

            // get vacation leave
            $keywords = ['Vacation', 'Annual Leave', 'AnnualLeave'];
            $is_vacation_leave = LeaveType::where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%$keyword%");
                }
            })->first();
            $keywords = ['DIL Leave', 'dil Leave', 'dilLeave'];
            $is_dil_leave = LeaveType::where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%$keyword%");
                }
            })->first();
            //end
           
            $getDailyLeavePolicy = Setting::where('key','daily_leave_policy')->value('value');
            $getMonthlyLeavePolicy = Setting::where('key','is_month_wise_show_leave')->value('value');
            $getAnnualLeavePolicy = Setting::where('key','annual_leave_policy')->value('value');
            $newUserDailyLeavePolicy = Setting::where('key','new_user_daily_leave_policy')->value('value');
            $newUserMonthlyLeavePolicy = Setting::where('key','new_user_monthly_leave_policy')->value('value');

            // Daily leave policy
            if($getDailyLeavePolicy == 1){
                if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                    if($daysDiff <= 365){

                        $leaveDay = $leaveType->days / 12;
                        $innerpolicy = '';

                        if($daysDiff <= 365){
                            $joiningDate = Carbon::parse($joining_date);
                            $monthsDiff = $currentMonth->diffInMonths($joiningDate);// + 1;
                            // for 6 month policy
                            $after6month = 0;
                            $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                            if($monthwise2leave==1){
                                if($is_vacation_leave->id == $leaveType->id){
                                    if($monthsDiff <= 6){
                                        $leaveDay = 2;
                                        $innerpolicy = ' (Within 6 month accrual of 2 days)';
                                    } else {
                                        $after6month = 3;
                                    }
                                }
                            }
                            // end
                            // for 1 year policy
                            $yearGiven2Leave = Setting::where('key','is_year_given_2_leave')->value('value');
                            if($yearGiven2Leave==1){
                                if($is_vacation_leave->id == $leaveType->id){
                                    if($monthsDiff <= 12){
                                        $leaveDay = 2;
                                        $innerpolicy = ' (Within 1 year accrual of 2 days)';
                                    }
                                }
                            }
                        }
                        $dayLeave = $leaveDay / Carbon::now()->daysInMonth;
                        $totalLeaveDay = ($dayLeave * $daysDiff) - $after6month;

                        $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                        ->where('user_id', $user_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->where('description', 'LIKE', '%Add Leave on profile create By Daily Leave Policy%')
                                        ->first();
                        if(!$isaddransaction){
                            $addtransaction = UserLeaveBalanceTransaction::create([
                                'user_id' => $user_id,
                                'leave_type_id' => $leaveType->id,
                                'transaction_type' => 'add',
                                'old_balance' => $totalLeaveDay,
                                'update_balance' => $totalLeaveDay,
                                'new_balance' => $totalLeaveDay,
                                'transaction_date' => $date,
                                'description' => 'Add Leave on profile create By Daily Leave Policy'.$innerpolicy.': ' . $leaveType->name,
                            ]);
                            $total_days = $totalLeaveDay;
                            $availableDay = $total_days;
                            $balance = LeaveBalance::updateOrCreate(
                                [
                                    'user_id' => $user_id,
                                    'year' => date('Y'),
                                    'leave_type_id' => $leaveType->id
                                ],
                                [
                                    'available' => $availableDay,
                                    'monthwiseDay' => $availableDay,
                                    'thisYearAvailableLeave' => $availableDay
                                ]
                            );
                        }
                    } else {
                        $balance = LeaveBalance::updateOrCreate(
                            [
                                'user_id' => $user_id,
                                'year' => date('Y'),
                                'leave_type_id' => $leaveType->id
                            ],
                            [
                                'available' => 0,
                                'monthwiseDay' => 0,
                                'thisYearAvailableLeave' => 0
                            ]
                        );
                    }
                } else {
                    // previous leave balance
                    $total_days = $total_days;
                    // end
                    $availableDay = $total_days;
                    $balance = LeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user_id,
                            'year' => date('Y'),
                            'leave_type_id' => $leaveType->id
                        ],
                        [
                            'available' => $availableDay,
                            'monthwiseDay' => $availableDay,
                            'thisYearAvailableLeave' => $availableDay
                        ]
                    );
                }
            }

            // Monthly leave policy
            if($getMonthlyLeavePolicy == 1){
                if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                    if($daysDiff <= 365){
                        $joiningDate = Carbon::parse($joining_date)->startOfDay();
                        $today = Carbon::today();
                        $totalMonths = $joiningDate->diffInMonths($today);
                        $monthsDiff = $today->diffInMonths($joiningDate);// + 1;
                        $leaveTotal = $leaveType->days / $yearMonth;
                        $innerpolicy = '';

                        // for 6 month policy
                        $after6month = 0;
                        $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                        if($monthwise2leave==1){
                            if($is_vacation_leave){
                                if($is_vacation_leave->id == $leaveType->id){
                                    if($monthsDiff <= 6){
                                        $leaveTotal = 2;
                                        $innerpolicy = ' (Within 6 months accrual of 2 days)';
                                    } else {
                                        $after6month = 3;
                                    }
                                }
                            }
                        }
                        // end
                        // for 1 year policy
                        $yearGiven2Leave = Setting::where('key','is_year_given_2_leave')->value('value');
                        if($yearGiven2Leave==1){
                            if($is_vacation_leave){
                                if($is_vacation_leave->id == $leaveType->id){
                                    if($monthsDiff <= 12){
                                        $leaveTotal = 2;
                                        $innerpolicy = ' (Within 1 year accrual of 2 days)';
                                    }
                                }
                            }
                        }
                        // end
                        $totalLeaveDay = ($totalMonths * $leaveTotal) - $after6month;
                        $isaddransaction = UserLeaveBalanceTransaction::where('user_id', $user_id)
                                            ->where('transaction_date', $date)
                                            ->where('leave_type_id', $leaveType->id)
                                            ->where('description', 'LIKE', '%Add leave when create profile on month wise leave policy%')
                                            ->first();
                        if(!$isaddransaction){
                            $monthlyLeave = $leaveTotal;
                            $daysInMonth  = Carbon::now()->daysInMonth;
                            $perDayLeave  = $monthlyLeave / $daysInMonth;
                            $remainingDays = $daysInMonth - $joiningDate->day;
                            $isJoiningMonthCounted = $joiningDate->day <= 15;
                            $dayLeaveTotal = 0;
                            if (! $isJoiningMonthCounted) {
                                $monthlyLeave = $leaveTotal; // leave per month
                                $daysInMonth  = $joiningDate->daysInMonth;
                                $perDayLeave = $monthlyLeave / $daysInMonth;
                                // Remaining days INCLUDING joining day
                                $remainingDays = $daysInMonth - $joiningDate->day + 1;
                                $dayLeaveTotal = round($remainingDays * $perDayLeave, 3);
                            }
                            $totalLeaveDay = $totalLeaveDay + $dayLeaveTotal;

                            $addtransaction = UserLeaveBalanceTransaction::create([
                                'user_id' => $user_id,
                                'leave_type_id' => $leaveType->id,
                                'transaction_type' => 'add',
                                'old_balance' => $totalLeaveDay,
                                'update_balance' => $totalLeaveDay,
                                'new_balance' => $totalLeaveDay,
                                'transaction_date' => $date,
                                'description' => 'Add leave when create profile on month wise leave policy'.$innerpolicy,
                            ]);
                            $total_days = $totalLeaveDay;
                            $availableDay = $total_days;
                            $balance = LeaveBalance::updateOrCreate(
                                [
                                    'user_id' => $user_id,
                                    'year' => date('Y'),
                                    'leave_type_id' => $leaveType->id
                                ],
                                [
                                    'available' => $availableDay,
                                    'monthwiseDay' => $availableDay,
                                    'thisYearAvailableLeave' => $availableDay
                                ]
                            );
                        }
                    } else {
                        $balance = LeaveBalance::updateOrCreate(
                            [
                                'user_id' => $user_id,
                                'year' => date('Y'),
                                'leave_type_id' => $leaveType->id
                            ],
                            [
                                'available' => 0,
                                'monthwiseDay' => 0,
                                'thisYearAvailableLeave' => 0
                            ]
                        );
                    }
                } else {
                    // previous leave balance
                    $total_days = $total_days;
                    // end
                    $availableDay = $total_days;
                    $balance = LeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user_id,
                            'year' => date('Y'),
                            'leave_type_id' => $leaveType->id
                        ],
                        [
                            'available' => $availableDay,
                            'monthwiseDay' => $availableDay,
                            'thisYearAvailableLeave' => $availableDay
                        ]
                    );
                }
            }

            // Annual leave policy
            if($getAnnualLeavePolicy == 1){
                if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                    if($daysDiff <= 365){
                        //daily accrual for new user
                        if($newUserDailyLeavePolicy==1){
                            $leaveDay = $leaveType->days / 12;

                            $joiningDate = Carbon::parse($joining_date);
                            $monthsDiff = $currentMonth->diffInMonths($joiningDate);// + 1;
                            // for 6 month policy
                            $after6month = 0;
                            $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                            if($monthwise2leave==1){
                                if($is_vacation_leave->id == $leaveType->id){
                                    if($monthsDiff <= 6){
                                        $leaveDay = 2;
                                    } else {
                                        $after6month = 3;
                                    }
                                }
                            }
                            // end
                            // for 1 year policy
                            $yearGiven2Leave = Setting::where('key','is_year_given_2_leave')->value('value');
                            if($yearGiven2Leave==1){
                                if($is_vacation_leave->id == $leaveType->id){
                                    if($monthsDiff <= 12){
                                        $leaveDay = 2;
                                    }
                                }
                            }
                            $dayLeave = $leaveDay / Carbon::now()->daysInMonth;
                            $totalLeaveDay = ($daysDiff * $dayLeave) - $after6month;

                            $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                            ->where('user_id', $user_id)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('description', 'LIKE', '%Add leave when create profile, new user annual leave policy(daily policy)%')
                            ->first();
                            if(!$isaddransaction){
                                $addtransaction = UserLeaveBalanceTransaction::create([
                                    'user_id' => $user_id,
                                    'leave_type_id' => $leaveType->id,
                                    'transaction_type' => 'add',
                                    'old_balance' => $totalLeaveDay,
                                    'update_balance' => $totalLeaveDay,
                                    'new_balance' => $totalLeaveDay,
                                    'transaction_date' => $date,
                                    'description' => 'Add leave when create profile, new user annual leave policy(daily policy): ' . $leaveType->name,
                                ]);
                                $total_days = $totalLeaveDay;
                                $availableDay = $total_days;
                                $balance = LeaveBalance::updateOrCreate(
                                    [
                                        'user_id' => $user_id,
                                        'year' => date('Y'),
                                        'leave_type_id' => $leaveType->id
                                    ],
                                    [
                                        'available' => $availableDay,
                                        'monthwiseDay' => $availableDay,
                                        'thisYearAvailableLeave' => $availableDay
                                    ]
                                );
                            }
                        }
                        //monthly accrual for new user
                        if($newUserMonthlyLeavePolicy==1){
                            $joiningDate = Carbon::parse($joining_date)->startOfDay();
                            $today = Carbon::today();
                            $totalMonths = $joiningDate->diffInMonths($today);
                            $monthsDiff = $today->diffInMonths($joiningDate);// + 1;
                            $leaveTotal = $leaveType->days / $yearMonth;
                            $innerpolicy = '';

                            // for 6 month policy
                            $after6month = 0;
                            $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                            if($monthwise2leave==1){
                                if($is_vacation_leave){
                                    if($is_vacation_leave->id == $leaveType->id){
                                        if($monthsDiff <= 6){
                                            $leaveTotal = 2;
                                            $innerpolicy = ' (Within 6 months accrual of 2 days)';
                                        } else {
                                            $after6month = 3;
                                        }
                                    }
                                }
                            }
                            // end
                            // for 1 year policy
                            $yearGiven2Leave = Setting::where('key','is_year_given_2_leave')->value('value');
                            if($yearGiven2Leave==1){
                                if($is_vacation_leave){
                                    if($is_vacation_leave->id == $leaveType->id){
                                        if($monthsDiff <= 12){
                                            $leaveTotal = 2;
                                            $innerpolicy = ' (Within 1 year accrual of 2 days)';
                                        }
                                    }
                                }
                            }
                            // end
                            $totalLeaveDay = ($totalMonths * $leaveTotal) - $after6month;
                            $isaddransaction = UserLeaveBalanceTransaction::where('user_id', $user_id)
                                                ->where('transaction_date', $date)
                                                ->where('leave_type_id', $leaveType->id)
                                                ->where('description', 'LIKE', '%Add leave when create profile on annual leave policy(monthly policy)%')
                                                ->first();
                            if(!$isaddransaction){
                                $monthlyLeave = $leaveTotal;
                                $daysInMonth  = Carbon::now()->daysInMonth;
                                $perDayLeave  = $monthlyLeave / $daysInMonth;
                                $remainingDays = $daysInMonth - $joiningDate->day;
                                $isJoiningMonthCounted = $joiningDate->day <= 15;
                                $dayLeaveTotal = 0;
                                if (! $isJoiningMonthCounted) {
                                    $monthlyLeave = $leaveTotal; // leave per month
                                    $daysInMonth  = $joiningDate->daysInMonth;
                                    $perDayLeave = $monthlyLeave / $daysInMonth;
                                    // Remaining days INCLUDING joining day
                                    $remainingDays = $daysInMonth - $joiningDate->day + 1;
                                    $dayLeaveTotal = round($remainingDays * $perDayLeave, 3);
                                }
                                $totalLeaveDay = $totalLeaveDay + $dayLeaveTotal;

                                $addtransaction = UserLeaveBalanceTransaction::create([
                                    'user_id' => $user_id,
                                    'leave_type_id' => $leaveType->id,
                                    'transaction_type' => 'add',
                                    'old_balance' => $totalLeaveDay,
                                    'update_balance' => $totalLeaveDay,
                                    'new_balance' => $totalLeaveDay,
                                    'transaction_date' => $date,
                                    'description' => 'Add leave when create profile on annual leave policy(monthly policy)'.$innerpolicy,
                                ]);
                                $total_days = $totalLeaveDay;
                                $availableDay = $total_days;
                                $balance = LeaveBalance::updateOrCreate(
                                    [
                                        'user_id' => $user_id,
                                        'year' => date('Y'),
                                        'leave_type_id' => $leaveType->id
                                    ],
                                    [
                                        'available' => $availableDay,
                                        'monthwiseDay' => $availableDay,
                                        'thisYearAvailableLeave' => $availableDay
                                    ]
                                );
                            }
                        }
                        if($newUserDailyLeavePolicy==0 && $newUserMonthlyLeavePolicy==0){
                            $balance = LeaveBalance::updateOrCreate(
                                [
                                    'user_id' => $user_id,
                                    'year' => date('Y'),
                                    'leave_type_id' => $leaveType->id
                                ],
                                [
                                    'available' => 0,
                                    'monthwiseDay' => 0,
                                    'thisYearAvailableLeave' => 0
                                ]
                            );
                        }
                    } else {
                        $balance = LeaveBalance::updateOrCreate(
                            [
                                'user_id' => $user_id,
                                'year' => date('Y'),
                                'leave_type_id' => $leaveType->id
                            ],
                            [
                                'available' => 0,
                                'monthwiseDay' => 0,
                                'thisYearAvailableLeave' => 0
                            ]
                        );
                    }
                } else {
                    // previous leave balance
                    $total_days = $total_days;
                    // end
                    $availableDay = $total_days;
                    $balance = LeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user_id,
                            'year' => date('Y'),
                            'leave_type_id' => $leaveType->id
                        ],
                        [
                            'available' => $availableDay,
                            'monthwiseDay' => $availableDay,
                            'thisYearAvailableLeave' => $availableDay
                        ]
                    );
                }
            }
        }
        
        $availableBalance = $balance->available??0;
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'days' => $this->days,
            'available' => $availableBalance
        ];
        // $oneYearBack = Carbon::now()->subYear()->year;
        // $extra_days = 0;
        // $total_days = $this->days;
        // $current_date = now(); 
        // $created_at = auth()->user()->created_at;
        // $userfromtable = User::find(auth()->id());
        // $currentYearDate = Carbon::now();
        // $startYear = '01-01-'.date('Y');
        // $newYear = Carbon::now()->format('d-m-Y');

        // $joining_date = Carbon::parse($userfromtable->workDetail?->joining_date);
        // $daysDiff = $currentYearDate->diffInDays($joining_date);

        // // add previous year leave balance
        // if($this->is_recurring == '1'){

        //     if($daysDiff > 365){
        //         if($newYear == $startYear){
        //             $previous_year_leave = PreviousYearLeave::where([
        //                 ['user_id',auth()->id()],
        //                 ['year', $oneYearBack],
        //                 ['leave_type_id',$this->id],
        //             ])->first();
        //             if(!$previous_year_leave && empty($previous_year_leave)){
        //                 $checkLeaveBalance = LeaveBalance::where([
        //                     'user_id' => auth()->id(),
        //                     'leave_type_id' => $this->id,
        //                     'year' => $oneYearBack
        //                 ])->first();
        //                 if($checkLeaveBalance){
        //                     if($checkLeaveBalance->available >= $this->no_of_leaves){
        //                         $added_day = $this->no_of_leaves;
        //                     } else {
        //                         $added_day = $checkLeaveBalance->available;
        //                     }
        //                     $previous_year_leave = PreviousYearLeave::create([
        //                         'user_id' => auth()->id(),
        //                         'year' => $oneYearBack,
        //                         'leave_type_id' => $this->id,
        //                         'added_day' => $added_day,
        //                     ]);
        //                 } else {
        //                     $previous_year_leave = PreviousYearLeave::create([
        //                         'user_id' => auth()->id(),
        //                         'year' => $oneYearBack,
        //                         'leave_type_id' => $this->id,
        //                         'added_day' => 0,
        //                     ]);
        //                 }
        //             }
        //             $extra_days = $previous_year_leave->added_day;
        //         } else {
        //             $previous_year_leave = PreviousYearLeave::where([
        //                 ['user_id', auth()->id()],
        //                 ['year', $oneYearBack],
        //                 ['leave_type_id', $this->id],
        //             ])->first();
        //             if(!empty($previous_year_leave)){
        //                 $extra_days = $previous_year_leave->added_day;
        //             } else {
        //                 $extra_days = 0;
        //             }
        //         }
        //     }
        // }
        // // end
        // $total_days = $total_days;// + $extra_days;
        
        // $nextYear = Carbon::now()->addYear()->year;
        // // approved leaves
        // $total_pre_approved_leaves = Leave::where([
        //     ['user_id', '=', auth()->id()],
        //     ['leave_type_id', '=', $this->id],
        //     ['status', '=', LeaveStatus::Approved],
        // ])
        // ->whereRaw('YEAR(start_date) != YEAR(end_date)')
        // ->get();
        // $pre_approved_leaves_day = 0;
        // foreach($total_pre_approved_leaves as $pre_leave){
        //     $startDate = Carbon::parse($pre_leave->start_date);
        //     $startDateyear = Carbon::parse($pre_leave->start_date)->year;
        //     $endDate = Carbon::parse($pre_leave->end_date);
        //     $endDateyear = Carbon::parse($pre_leave->end_date)->year;

        //     $endOfStartYear = $startDate->copy()->endOfYear();
        //     $startOfEndYear = $endDate->copy()->startOfYear();
        //     $daysInStartYear = $startDate->diffInDays($endOfStartYear) + 1;
        //     $daysInEndYear = $startOfEndYear->diffInDays($endDate) + 1;

        //     if($startDateyear==Carbon::now()->year){
        //         $pre_approved_leaves_day = $pre_approved_leaves_day + $daysInStartYear;
        //     }
        //     if($endDateyear==Carbon::now()->year){
        //         $pre_approved_leaves_day = $pre_approved_leaves_day + $daysInEndYear;
        //     }
        // }
        // $total_approved_leaves = Leave::where([
        //     ['user_id', '=', auth()->id()],
        //     ['leave_type_id', '=', $this->id],
        //     ['status', '=', LeaveStatus::Approved],
        // ])
        // ->whereRaw('YEAR(start_date) = YEAR(end_date)')
        // ->whereIn('year', [date('Y'), $nextYear])
        // ->sum('total_leave_days');
        // $total_approved_leaves = $total_approved_leaves + $pre_approved_leaves_day;
        // $currentMonth = Carbon::now();
        // if($daysDiff <= 365){
        //     $joining_next_year = Carbon::parse($joining_date)->addYear()->year;
        //     $joining_date_year = Carbon::parse($joining_date)->year;
        //     $total_approved_leaves = Leave::where([
        //         ['user_id', '=', auth()->id()],
        //         ['leave_type_id', '=', $this->id],
        //         ['status', '=', LeaveStatus::Approved],
        //     ])
        //     ->whereIn('year', [$joining_date_year, $joining_next_year])
        //     ->sum('total_leave_days');
        // }
        // $checkLeaveBalance = EntitiesLeaveBalance::where([
        //     'user_id' => auth()->id(),
        //     'leave_type_id' => $this->id,
        //     'year' => date('Y')
        // ])->first();
        // // get annual leave
        // $keywords = ['Vacation', 'Annual Leave', 'AnnualLeave'];
        // $is_vacation_leave = LeaveType::where(function ($query) use ($keywords) {
        //     foreach ($keywords as $keyword) {
        //         $query->orWhere('name', 'like', "%$keyword%");
        //     }
        // })->first();
        // $yearMonth = 12;

        // // joining date
        // if($daysDiff <= 365){
        //     if($is_vacation_leave && $is_vacation_leave->id == $this->id){
        //         $joiningDate = Carbon::parse($joining_date);
        //         $monthsDiff = $currentMonth->diffInMonths($joiningDate);// + 1;
        //         $leaveTotal = $total_days / $yearMonth;
        //         $totalmonth = $monthsDiff;
        //         // for 6 month policy
        //         $after6month = 0;
        //         $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
        //         if($monthwise2leave==1){
        //             $keywords = ['Vacation', 'Annual Leave', 'AnnualLeave'];
        //             $is_vacation_leave = LeaveType::where(function ($query) use ($keywords) {
        //                 foreach ($keywords as $keyword) {
        //                     $query->orWhere('name', 'like', "%$keyword%");
        //                 }
        //             })->first();
        //             if($is_vacation_leave){
        //                 if($is_vacation_leave->id == $this->id){
        //                     if($monthsDiff <= 6){
        //                         $leaveTotal = 2;
        //                     } else {
        //                         $after6month = 3;
        //                     }
        //                 }
        //             }
        //         }
        //         // end
        //         $total_days = round($leaveTotal * $totalmonth ,1) - $after6month;
        //     }
        // }
        // // end
        // // after 1 year
        // $oneYearAnniversary = Carbon::parse($joining_date)->addYear();
        // if($oneYearAnniversary->year == now()->year){
        //     $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
        //     $todaydate = Carbon::now()->toDateString();
        //     if($todaydate >= $oneYearAnniversary){
        //         $startmonth = $oneYearAnniversary->month;
        //         $joiningDate = Carbon::parse($joining_date);
        //         $monthsDiff = $currentMonth->diffInMonths($joiningDate);
        //         $leaveTotal = $total_days / $yearMonth;
        //         $after6month = 0;
        //         if($monthwise2leave==1){
        //             if($is_vacation_leave->id == $this->id){
        //                 $after6month = 3;
        //                 $total_days = round($leaveTotal * $monthsDiff ,1) - $after6month;
        //             } else {
        //                 $total_days = round($leaveTotal * $monthsDiff ,1) - $after6month;
        //             }
        //         } else {
        //             $total_days = round($leaveTotal * $monthsDiff ,1);
        //         }
        //     }
        // }
        // // end
        // // add ph leave
        // $date = Carbon::now()->toDateString();
        // $currentYear = now()->year;
        // $holidays = Holiday::whereYear('start_date', $currentYear)
        //                     ->orWhereYear('end_date', $currentYear)
        //                     ->orWhere(function ($query) use ($currentYear) {
        //                         $query->where('start_date', '<=', "$currentYear-12-31")
        //                             ->where('end_date', '>=', "$currentYear-01-01");
        //                     })
        //                     // ->orWhere('is_recurring',1)
        //                     ->get();
        // $is_phleave = LeaveType::where('name','like', '%PH%')->first();
        // if($is_phleave && $is_phleave->name == $this->name){
        //     foreach ($holidays as $holiday) {
        //         if($holiday){
        //             $holidayStart = Carbon::parse($holiday->start_date)->toDateString();
        //             $holidayEnd = Carbon::parse($holiday->end_date)->toDateString();
        //             $isCheckin = Attendance::where('user_id', auth()->id())
        //                                 ->where('status', AttendanceStatus::Present)
        //                                 ->whereDate('date', '>=', $holidayStart)
        //                                 ->whereDate('date', '<=', $holidayEnd)
        //                                 ->groupBy('date')
        //                                 ->get();
        //             foreach ($isCheckin as $value) {
        //                 if($is_phleave->name == $this->name){
        //                     $total_days = $total_days + 1;
        //                     $isaddinreport = PHLeaveReport::where([
        //                         'user_id' => auth()->id(),
        //                         'date' => $value->date,
        //                     ])->first();
        //                     if(!$isaddinreport){
        //                         $addinreport = PHLeaveReport::create([
        //                             'user_id' => auth()->id(),
        //                             'holiday_id' => $holiday->id,
        //                             'leave_type_id' => $this->id,
        //                             'date' => $value->date,
        //                         ]);
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
        // //end
        // // add DIL leave
        // $keywords = ['DIL Leave', 'dil Leave', 'dilLeave'];
        // $is_dil_leave = LeaveType::where(function ($query) use ($keywords) {
        //     foreach ($keywords as $keyword) {
        //         $query->orWhere('name', 'like', "%$keyword%");
        //     }
        // })->first();
        // if($is_dil_leave && $is_dil_leave->name == $this->name){
        //     $extraWorkAddToLeaves = extraWorkRequest::where([['user_id',auth()->id()],['status',2]])->where('year', $currentYear)->get();
        //     $availableDay = 0;
        //     foreach($extraWorkAddToLeaves as $extraWorkAddToLeave){
        //         $addDay = $extraWorkAddToLeave->extra_hours / 10;
        //         $availableDay = $availableDay + $addDay;
        //     }
        //     $total_days = $total_days + $availableDay;
        // }
        // //end
        // // add cancel off leave
        // $checkcanceloffleave = Setting::where('key','cancel_off_leave_module')->value('value');
        // if($checkcanceloffleave==true){
        //     $year = Carbon::now()->year;
        //     $startDate = "$year-01-01";
        //     $endDate = Carbon::now()->toDateString();
        //     // $usershift = UsersShift::where('user_id', auth()->id())->whereBetween('assigned_for_date', [$startDate, $endDate])->get();
        //     $usershift = UsersShift::where('user_id', auth()->id())
        //                     ->whereBetween('assigned_for_date', [$startDate, $endDate])
        //                     ->whereHas('shift_schedule_information.shift', function ($q) {
        //                         $q->where('is_weekend', 1);
        //                     })
        //                     ->with(['shift_schedule_information.shift'])
        //                     ->get();
        //     foreach($usershift as $shift){
        //         $shiftdata = $shift->shift_schedule_information->shift ?? null;
        //         if($shiftdata && $shiftdata->is_weekend==1){
        //             $isCheckin = Attendance::where('user_id', auth()->id())
        //                                 ->whereNotNull('clock_in')
        //                                 // ->where('status', AttendanceStatus::Present)
        //                                 ->whereDate('date', '>=', $shift->assigned_for_date)
        //                                 ->whereDate('date', '<=', $shift->assigned_for_date)
        //                                 ->groupBy('date')
        //                                 ->get();
        //             foreach ($isCheckin as $value) {
        //                 $cankeywords = ['CANCEL OFF', 'cancel off', 'canceloff'];
        //                 $is_canceloff_leave = LeaveType::where(function ($query) use ($cankeywords) {
        //                     foreach ($cankeywords as $cankeyword) {
        //                         $query->orWhere('name', 'like', "%$cankeyword%");
        //                     }
        //                 })->first();
                        
        //                 if($is_canceloff_leave && $is_canceloff_leave->name == $this->name){
        //                     if ($value->clock_in != '00:00:00' && $value->clock_in != null) {
        //                         $total_days = $total_days + 1;
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
        // //end
        // // pro
        // $checkproationleave = Setting::where('key','allow_to_add_probation_leave')->value('value');
        // if($checkproationleave==1){
        //     $probationDate = UserWorkDetail::where('user_id',auth()->id())->first();
        //     if (Carbon::parse($probationDate->probation_end_date)->isCurrentYear()) {
        //         $probationEndDate = Carbon::parse($probationDate->probation_end_date);
        //         if($probationEndDate < now()->toDateString()){
        //             $diffInMonths = Carbon::parse($joining_date)->diffInMonths($probationEndDate);
        //             $removedays = ($total_days / $yearMonth) * $diffInMonths;
        //             $total_days = $total_days - $removedays;
        //         }
        //     }
        // } else {
        //     $total_days = $total_days;
        // }
        // // pro end
        // // update leave
        // $updatebalance = LeaveBalanceUpdateLog::whereYear('updated_at', $currentYear)
        //                                         ->where([
        //                                             'user_id' => auth()->id(),
        //                                             'leave_type_id' => $this->id
        //                                         ])
        //                                         ->get();
        // foreach ($updatebalance as $updatebal) {
        //     if($updatebal){
        //         if($updatebal->leave_type_id==$this->id){
        //             if ($updatebal->previous_balance < 0 && $updatebal->new_balance >= 0) {
        //                 // Negative to zero or positive → remove negative offset
        //                 $diffvalue = abs($updatebal->previous_balance);
        //             } elseif ($updatebal->previous_balance >= 0 && $updatebal->new_balance < 0) {
        //                 // Positive to negative → treat as full deduction
        //                 $diffvalue = abs($updatebal->new_balance);
        //             } else {
        //                 // Normal diff usage
        //                 $diffvalue = $updatebal->diff_value;
        //             }

        //             // Apply diff
        //             if($updatebal->is_less==1){
        //                 $total_days = $total_days + $diffvalue;
        //             }
        //             if($updatebal->is_less==0){
        //                 $total_days = $total_days - $diffvalue;
        //             }
        //         }
        //     }
        // }
        // // end
        // // edit user previous leave balance
        // $previousLeaveBalance = PreviousLeaveBalance::where([
        //     'user_id' => auth()->id(),
        //     'leave_type_id' => $this->id,
        // ])
        // ->whereYear('created_at', $currentYear)
        // ->first();
        // $previosYearDay = 0;
        // if($previousLeaveBalance){
        //     $previosYearDay = $previousLeaveBalance->days;
        // }
        // $previosYearDay = $previosYearDay + $extra_days;
        // // end
        // $checkmonthwise = Setting::where('key','is_month_wise_show_leave')->value('value');
        // if($checkLeaveBalance) {
        //     $current_year = date('Y');
        //     if($checkmonthwise == 1){
        //         if($current_year == $checkLeaveBalance->year){
        //             if($is_vacation_leave && $is_vacation_leave->id == $this->id){
        //                 $backdays = 0;
        //                 $totalmonth = Carbon::parse($startYear)->diffInMonths($newYear);
        //                 $backdays = ($total_days / $yearMonth) * $totalmonth;
        //                 if($daysDiff <= 365){
        //                     $backdays = $total_days;
        //                 }
        //                 $total_days_of_month = $backdays;
        //                 // previous leave balance
        //                 $total_days_of_month = $total_days_of_month + $previosYearDay;
        //                 // end
        //                 $diff = $total_days_of_month - $total_approved_leaves;
        //                 if ($diff == floor($diff) + 0.5) {
        //                     $availableDay = $diff;
        //                 } else {
        //                     $availableDay = round($diff);
        //                 }
        //                 if($is_dil_leave && $is_dil_leave->name == $this->name){
        //                     $availableDay = $diff;
        //                 }
        //                 $checkLeaveBalance->available = $availableDay;
        //                 $checkLeaveBalance->isAddThisMonthLeave = date('m');
        //                 $checkLeaveBalance->save();
        //             } else {
        //                 // previous leave balance
        //                 $total_days = $total_days + $previosYearDay;
        //                 // end
        //                 $diff = $total_days - $total_approved_leaves;
        //                 if ($diff == floor($diff) + 0.5) {
        //                     $availableDay = $diff;
        //                 } else {
        //                     $availableDay = round($diff);
        //                 }
        //                 if($is_dil_leave && $is_dil_leave->name == $this->name){
        //                     $availableDay = $diff;
        //                 }
        //                 $checkLeaveBalance->available = $availableDay;
        //                 $checkLeaveBalance->save();
        //             }
        //         }
        //     } else {
        //         // previous leave balance
        //         $total_days = $total_days + $previosYearDay;
        //         // end
        //         $diff = $total_days - $total_approved_leaves;
        //         if ($diff == floor($diff) + 0.5) {
        //             $availableDay = $diff;
        //         } else {
        //             $availableDay = round($diff);
        //         }
        //         if($is_dil_leave && $is_dil_leave->name == $this->name){
        //             $availableDay = $diff;
        //         }
        //         $checkLeaveBalance->available = $availableDay;
        //         $checkLeaveBalance->save();
        //     }
        //     $balance = $checkLeaveBalance;
        // } else {
        //     if($checkmonthwise == 1){
        //         if($is_vacation_leave && $is_vacation_leave->id == $this->id){
        //             $backdays = 0;
        //             $totalmonth = Carbon::parse($startYear)->diffInMonths($newYear);
        //             $backdays = ($total_days / $yearMonth) * $totalmonth;
        //             if($daysDiff <= 365){
        //                 $backdays = $total_days;
        //             }
        //             $monthdays = $backdays;
        //             // previous leave balance
        //             $total_days = $monthdays + $previosYearDay;
        //             // end
        //             $diff = $total_days - $total_approved_leaves;
        //             if ($diff == floor($diff) + 0.5) {
        //                 $availableDay = $diff;
        //             } else {
        //                 $availableDay = round($diff);
        //             }
        //             if($is_dil_leave && $is_dil_leave->name == $this->name){
        //                 $availableDay = $diff;
        //             }
        //             $balance = EntitiesLeaveBalance::updateOrCreate(
        //                 [
        //                     'user_id' => auth()->id(),
        //                     'year' => date('Y'),
        //                     'leave_type_id' => $this->id
        //                 ],
        //                 [
        //                     'available' => $availableDay,
        //                     'isAddThisMonthLeave' => date('m'),
        //                     'thisYearAvailableLeave' => $availableDay
        //                 ]
        //             );
        //         } else {
        //             // previous leave balance
        //             $total_days = $total_days + $previosYearDay;
        //             // end
        //             $diff = $total_days- $total_approved_leaves;
        //             if ($diff == floor($diff) + 0.5) {
        //                 $availableDay = $diff;
        //             } else {
        //                 $availableDay = round($diff);
        //             }
        //             if($is_dil_leave && $is_dil_leave->name == $this->name){
        //                 $availableDay = $diff;
        //             }
        //             $balance = EntitiesLeaveBalance::updateOrCreate(
        //                 [
        //                     'user_id' => auth()->id(),
        //                     'year' => date('Y'),
        //                     'leave_type_id' => $this->id
        //                 ],
        //                 [
        //                     'available' => $availableDay,
        //                     // 'isAddThisMonthLeave' => date('m'),
        //                     'thisYearAvailableLeave' => $availableDay
        //                 ]
        //             );
        //         }
        //     } else {
        //         // previous leave balance
        //         $total_days = $total_days + $previosYearDay;
        //         // end
        //         $diff = $total_days- $total_approved_leaves;
        //         if ($diff == floor($diff) + 0.5) {
        //             $availableDay = $diff;
        //         } else {
        //             $availableDay = round($diff);
        //         }
        //         if($is_dil_leave && $is_dil_leave->name == $this->name){
        //             $availableDay = $diff;
        //         }
        //         $balance = EntitiesLeaveBalance::updateOrCreate(
        //             [
        //                 'user_id' => auth()->id(),
        //                 'year' => date('Y'),
        //                 'leave_type_id' => $this->id
        //             ],
        //             [
        //                 'available' => $availableDay,
        //                 // 'isAddThisMonthLeave' => date('m'),
        //                 'thisYearAvailableLeave' => $availableDay
        //             ]
        //         );
        //     }
        // }
        // $diffTotal = $total_days;
        // if ($diffTotal == floor($diffTotal) + 0.5) {
        //     $total_days = $diffTotal;
        // } else {
        //     $total_days = round($diffTotal);
        // }
        // if($is_dil_leave && $is_dil_leave->name == $this->name){
        //     $total_days = $diffTotal;
        // }
        
    }
}
