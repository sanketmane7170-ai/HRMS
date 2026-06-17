<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Entities\LeaveBalance;
use Carbon\Carbon;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Enums\LeaveStatus;
use App\Models\PreviousLeaveBalance;
use App\Models\UserWorkDetail;
use App\Models\Setting;
use App\Models\PreviousYearLeave;
use App\Models\PHLeaveReport;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Leave\Entities\LeaveBalanceUpdateLog;
use App\Models\extraWorkRequest;
use Illuminate\Support\Facades\Log;

use App\Models\Shifts;
use Modules\Shift\Entities\UsersShift;
use App\Models\ShiftSchedule;
use App\Models\UserLeaveBalanceTransaction;

class UpdateLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:update-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update leave balances for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now() . '] leave:update-balance started.');

        $users = User::query() ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->where('status', User::STATUS_ACTIVE)->get();

        Log::info('leave:update-balance', ["users" => $users]);

        $leaveTypes = LeaveType::get();
        Log::info('leave:update-balance', ["leaveTypes" => $leaveTypes]);
        foreach ($leaveTypes as $leaveType) {
            $this->info('[' . now() . '] leaveType: ' . json_encode($leaveType));

            foreach ($users as $user) {

                $this->info('[' . now() . '] user: ' . json_encode($user));
                $user_id = $user->id;
                $leaveType = LeaveType::find($leaveType->id);
                $startYear = '01-01-'.date('Y');
                $newYear = Carbon::now()->format('d-m-Y');
                $userfromtable = User::find($user_id);
                $joining_date = Carbon::parse($userfromtable->workDetail?->joining_date);
                $today = Carbon::now();
                $joiningDate = Carbon::parse($userfromtable->workDetail?->joining_date)->startOfDay();
                $toDayDate = Carbon::today();
                $secondYearStartDate = $joiningDate->copy()->addYear()->addDay();
                $oneYearBack = Carbon::now()->subYear()->year;
                $date = Carbon::now()->toDateString();
                $currentYear = now()->year;
                $yesterday = Carbon::yesterday()->toDateString();
                $new_days = 0;
                $extra_days = 0;
                $total_approved_leaves = 0;
                $pre_approved_leaves_day = 0;
                $currentMonth = Carbon::now();
                $yearMonth = 12;

                $current_date = now();
                $created_at = User::where('id',$user_id)->value('created_at');
                $currentYearDate = Carbon::now();
                $daysDiff = $currentYearDate->diffInDays($joining_date);
                $keywords = ['DIL Leave', 'dil Leave', 'dilLeave'];
                $is_dil_leave = LeaveType::where(function ($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $query->orWhere('name', 'like', "%$keyword%");
                    }
                })->first();
                // get annual leave
                $vkeywords = ['Vacation', 'Annual Leave', 'AnnualLeave'];
                $is_vacation_leave = LeaveType::where(function ($query) use ($vkeywords) {
                    foreach ($vkeywords as $keyword) {
                        $query->orWhere('name', 'like', "%$keyword%");
                    }
                })->first();
                //end
                $getDailyLeavePolicy = Setting::where('key','daily_leave_policy')->value('value');
                $getMonthlyLeavePolicy = Setting::where('key','is_month_wise_show_leave')->value('value');
                $getAnnualLeavePolicy = Setting::where('key','annual_leave_policy')->value('value');
                $newUserDailyLeavePolicy = Setting::where('key','new_user_daily_leave_policy')->value('value');
                $newUserMonthlyLeavePolicy = Setting::where('key','new_user_monthly_leave_policy')->value('value');

                // leave recurring policy
                $leaveRecurringPolicy = Setting::where('key','leave_recurring_policy')->value('value');
                if($getDailyLeavePolicy==1 || $getMonthlyLeavePolicy==1 || $getAnnualLeavePolicy==1){
                    
                    // leave annual calendar recurring policy
                    if($leaveRecurringPolicy=='annual_calendar'){
                        $checkLeaveBalance = LeaveBalance::where([
                            'user_id' => $user_id,
                            'leave_type_id' => $leaveType->id,
                            'year' => date('Y')
                        ])->latest('updated_at')->first();
                        // user leave balance
                        $total_days = $checkLeaveBalance ? $checkLeaveBalance->available : 0;
                        $backdays = $checkLeaveBalance ? $checkLeaveBalance->monthwiseDay : 0;

                        if($newYear == $startYear){
                            if ($toDayDate->lessThanOrEqualTo($joiningDate->copy()->addYear())) {
                                $previousLeaveBalance = LeaveBalance::where([
                                    'user_id' => $user_id,
                                    'leave_type_id' => $leaveType->id,
                                    'year' => $oneYearBack
                                ])->latest('updated_at')->first();
                                $checkLeaveBalance = LeaveBalance::updateOrCreate(
                                    [
                                        'user_id' => $user_id,
                                        'year' => date('Y'),
                                        'leave_type_id' => $leaveType->id
                                    ],
                                    [
                                        'available' => $previousLeaveBalance->available,
                                        'monthwiseDay' => $previousLeaveBalance->monthwiseDay,
                                    ]
                                );
                                $total_days = $checkLeaveBalance->available;
                            } else {
                                // add previous year leave balance
                                if($leaveType->is_recurring == '1'){
                                    if($toDayDate->greaterThan($joiningDate->copy()->addYear())){
                                        $previousLeaveBalance = LeaveBalance::where([
                                            'user_id' => $user_id,
                                            'leave_type_id' => $leaveType->id,
                                            'year' => $oneYearBack
                                        ])->first();
                                        $recurringDay = $leaveType->no_of_leaves;
                                        if($previousLeaveBalance->available > $recurringDay && $leaveType->no_of_leaves != 0){
                                            $previousLeaveDay = $recurringDay;
                                        } else {
                                            $previousLeaveDay = $previousLeaveBalance->available;
                                        }
                                        // add TR
                                        $previous_year_leave = PreviousYearLeave::create([
                                            'user_id' => $user_id,
                                            'year' => $oneYearBack,
                                            'leave_type_id' => $leaveType->id,
                                            'added_day' => $previousLeaveDay,
                                        ]);
                                        $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                        ->where('user_id', $user_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->where('description', 'LIKE', '%Update leave on new year annual calendar recurring policy:%')
                                        ->first();
                                        if(!$isaddransaction){
                                            $addtransaction = UserLeaveBalanceTransaction::create([
                                                'user_id' => $user_id,
                                                'leave_type_id' => $leaveType->id,
                                                'transaction_type' => 'add',
                                                'old_balance' => $previousLeaveBalance->available,
                                                'update_balance' => $previousLeaveDay,
                                                'new_balance' => $previousLeaveDay,
                                                'transaction_date' => $date,
                                                'description' => 'Update leave on new year annual calendar recurring policy:' . $leaveType->name.' on: '. $today,
                                            ]);
                                            $new_days = $previousLeaveDay;
                                            $diff = $new_days;
                                            if ($diff == floor($diff) + 0.5) {
                                                $availableDay = $diff;
                                            } else {
                                                $availableDay = round($diff);
                                            }
                                            if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                                $availableDay = $diff;
                                            }
                                            if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                                $availableDay = $diff;
                                            }
                                            $checkLeaveBalance = LeaveBalance::updateOrCreate(
                                                [
                                                    'user_id' => $user_id,
                                                    'year' => date('Y'),
                                                    'leave_type_id' => $leaveType->id
                                                ],
                                                [
                                                    'available' => $availableDay,
                                                    'monthwiseDay' => $availableDay,
                                                ]
                                            );
                                            $total_days = $checkLeaveBalance->available;
                                        }
                                    }
                                } else {
                                    $checkLeaveBalance = LeaveBalance::updateOrCreate(
                                        [
                                            'user_id' => $user_id,
                                            'year' => date('Y'),
                                            'leave_type_id' => $leaveType->id
                                        ],
                                        [
                                            'available' => 0,
                                            'monthwiseDay' => 0,
                                        ]
                                    );
                                    $total_days = $checkLeaveBalance->available;
                                }
                            }
                            // get two year approved leaves
                            $currentYear = now()->year;
                            if($toDayDate->greaterThan($joiningDate->copy()->addYear())){
                                $total_pre_approved_leaves = Leave::where([
                                    ['user_id', '=', $user_id],
                                    ['leave_type_id', '=', $leaveType->id],
                                    ['status', '=', LeaveStatus::Approved],
                                ])
                                ->whereYear('start_date', $oneYearBack)
                                ->whereYear('end_date', $currentYear)
                                ->get();
                                
                                foreach($total_pre_approved_leaves as $pre_leave){
                                    
                                    $endDate = Carbon::parse($pre_leave->end_date);
                                    $endDateyear = Carbon::parse($pre_leave->end_date)->year;

                                    $startOfEndYear = $endDate->copy()->startOfYear();
                                    $daysInEndYear = $startOfEndYear->diffInDays($endDate) + 1;
                                    
                                    if($endDateyear==Carbon::now()->year){
                                        $pre_approved_leaves_day = $pre_approved_leaves_day + $daysInEndYear;
                                    }
                                }
                                $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                        ->where('user_id', $user_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->where('description', 'LIKE', '%Remove leave days on new year annual calendar policy:%')
                                        ->first();
                                if(!$isaddransaction && $pre_approved_leaves_day > 0){
                                    $addtransaction = UserLeaveBalanceTransaction::create([
                                        'user_id' => $user_id,
                                        'leave_type_id' => $leaveType->id,
                                        'transaction_type' => 'remove',
                                        'old_balance' => $total_days,
                                        'update_balance' => $pre_approved_leaves_day,
                                        'new_balance' => ($total_days - $pre_approved_leaves_day),
                                        'transaction_date' => $date,
                                        'description' => 'Remove leave days on new year annual calendar policy:' . $leaveType->name.' on: '. $today,
                                    ]);
                                    $availableDay = $total_days - $pre_approved_leaves_day;
                                    $checkLeaveBalance->available = $availableDay;
                                    $checkLeaveBalance->monthwiseDay = $availableDay;
                                    $checkLeaveBalance->save();
                                    $total_days = $availableDay;
                                }
                            }
                        } else {
                            $total_days = $checkLeaveBalance?->available;

                            // add previous year leave balance for mid year/after 1 year joined user
                            if ($toDayDate->isSameDay($secondYearStartDate)) {
                                if($leaveType->is_recurring == '1'){
                                    $CurrentLeaveBalance = LeaveBalance::where([
                                        'user_id' => $user_id,
                                        'leave_type_id' => $leaveType->id,
                                        'year' => date('Y')
                                    ])->latest('updated_at')->first();
                                    $recurringDay = $leaveType->no_of_leaves;
                                    if($CurrentLeaveBalance->available > $recurringDay && $leaveType->no_of_leaves != 0){
                                        $previousLeaveDay = $recurringDay;
                                    } else {
                                        $previousLeaveDay = $CurrentLeaveBalance->available;
                                    }
                                    $previous_year_leave = PreviousYearLeave::create([
                                        'user_id' => $user_id,
                                        'year' => date('Y'),
                                        'leave_type_id' => $leaveType->id,
                                        'added_day' => $previousLeaveDay,
                                    ]);
                                    
                                    $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                        ->where('user_id', $user_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->where('description', 'LIKE', '%Update leave on mid year annual calendar policy:%')
                                        ->first();
                                    if(!$isaddransaction){
                                        $addtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id' => $user_id,
                                            'leave_type_id' => $leaveType->id,
                                            'transaction_type' => 'add',
                                            'old_balance' => $CurrentLeaveBalance->available,
                                            'update_balance' => $previousLeaveDay,
                                            'new_balance' => $previousLeaveDay,
                                            'transaction_date' => $date,
                                            'description' => 'Update leave on mid year annual calendar policy:' . $leaveType->name.' on: '. $today,
                                        ]);
                                        $new_days = $previousLeaveDay;
                                        $diff = $new_days;
                                        if ($diff == floor($diff) + 0.5) {
                                            $availableDay = $diff;
                                        } else {
                                            $availableDay = round($diff);
                                        }
                                        if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                            $availableDay = $diff;
                                        }
                                        if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                            $availableDay = $diff;
                                        }
                                        $checkLeaveBalance->available = $availableDay;
                                        $checkLeaveBalance->monthwiseDay = $availableDay;
                                        $checkLeaveBalance->save();
                                        $total_days = $availableDay;
                                    }
                                } else {
                                    $total_days = 0;
                                }
                            }
                        }
                    }
                    // end
                    // leave joining to joining recurring policy
                    if($leaveRecurringPolicy=='joining_to_joining'){
                        $checkLeaveBalance = LeaveBalance::where([
                            'user_id' => $user_id,
                            'leave_type_id' => $leaveType->id,
                            'year' => date('Y')
                        ])->latest('updated_at')->first();
                        if(!$checkLeaveBalance){
                            $previousLeaveBalance = LeaveBalance::where([
                                'user_id' => $user_id,
                                'leave_type_id' => $leaveType->id,
                                'year' => $oneYearBack
                            ])->latest('updated_at')->first();
                            $checkLeaveBalance = LeaveBalance::updateOrCreate(
                                [
                                    'user_id' => $user_id,
                                    'year' => date('Y'),
                                    'leave_type_id' => $leaveType->id
                                ],
                                [
                                    'available' => $previousLeaveBalance->available,
                                    'monthwiseDay' => $previousLeaveBalance->monthwiseDay,
                                ]
                            );
                        }
                        // user leave balance
                        $total_days = $checkLeaveBalance ? $checkLeaveBalance->available : 0;
                        $backdays = $checkLeaveBalance ? $checkLeaveBalance->monthwiseDay : 0;

                        if ($joining_date->format('m-d') === $toDayDate->format('m-d')) {
                            if($toDayDate->greaterThan($joiningDate->copy()->addYear())){
                                if($leaveType->is_recurring == '1'){
                                    $previousLeaveBalance = LeaveBalance::where([
                                        'user_id' => $user_id,
                                        'leave_type_id' => $leaveType->id,
                                        'year' => date('Y'),
                                    ])->latest('updated_at')->first();
                                    $recurringDay = $leaveType->no_of_leaves;
                                    if($previousLeaveBalance->available > $recurringDay && $leaveType->no_of_leaves != 0){
                                        $previousLeaveDay = $recurringDay;
                                    } else {
                                        $previousLeaveDay = $previousLeaveBalance->available;
                                    }
                                    $previous_year_leave = PreviousYearLeave::create([
                                        'user_id' => $user_id,
                                        'year' => date('Y'),
                                        'leave_type_id' => $leaveType->id,
                                        'added_day' => $previousLeaveDay,
                                    ]);
                                    $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                    ->where('user_id', $user_id)
                                    ->where('leave_type_id', $leaveType->id)
                                    ->where('description', 'LIKE', '%Update leave for next year joining to joining recurring policy:%')
                                    ->first();
                                    if(!$isaddransaction){
                                        $addtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id' => $user_id,
                                            'leave_type_id' => $leaveType->id,
                                            'transaction_type' => 'add',
                                            'old_balance' => $previousLeaveBalance->available,
                                            'update_balance' => $previousLeaveDay,
                                            'new_balance' => $previousLeaveDay,
                                            'transaction_date' => $date,
                                            'description' => 'Update leave for next year joining to joining recurring policy:' . $leaveType->name.' on: '. $today,
                                        ]);
                                        $new_days = $previousLeaveDay;
                                        $diff = $new_days;
                                        if ($diff == floor($diff) + 0.5) {
                                            $availableDay = $diff;
                                        } else {
                                            $availableDay = round($diff);
                                        }
                                        if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                            $availableDay = $diff;
                                        }
                                        if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                            $availableDay = $diff;
                                        }
                                        $checkLeaveBalance->available = $availableDay;
                                        $checkLeaveBalance->monthwiseDay = $availableDay;
                                        $checkLeaveBalance->save();
                                        $total_days = $availableDay;
                                    }
                                    
                                } else {
                                    $checkLeaveBalance = LeaveBalance::updateOrCreate(
                                        [
                                            'user_id' => $user_id,
                                            'year' => date('Y'),
                                            'leave_type_id' => $leaveType->id
                                        ],
                                        [
                                            'available' => 0,
                                            'monthwiseDay' => 0,
                                        ]
                                    );
                                    $total_days = $checkLeaveBalance->available;
                                }
                            }
                        }
                    }
                    // end
                }
                // end

                if($checkLeaveBalance) {
                    // Daily leave policy
                    if($getDailyLeavePolicy == 1){
                        if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                            $leaveDay = $leaveType->days / 12;
                            $innerpolicy = '';

                            if($daysDiff <= 365){
                                $joiningDate = Carbon::parse($joining_date);
                                $monthsDiff = $currentMonth->diffInMonths($joiningDate);// + 1;
                                // for 6 month policy
                                $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                                if($monthwise2leave==1){
                                    if($is_vacation_leave->id == $leaveType->id){
                                        if($monthsDiff <= 6){
                                            $leaveDay = 2;
                                            $innerpolicy = ' (Within 6 month accrual of 2 days)';
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
                            
                            $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                            ->where('user_id', $user_id)
                                            ->where('leave_type_id', $leaveType->id)
                                            ->where('description', 'LIKE', '%Update Leave By Daily Leave Policy%')
                                            ->first();
                            if(!$isaddransaction){
                                $addtransaction = UserLeaveBalanceTransaction::create([
                                    'user_id' => $user_id,
                                    'leave_type_id' => $leaveType->id,
                                    'transaction_type' => 'add',
                                    'old_balance' => $total_days,
                                    'update_balance' => $dayLeave,
                                    'new_balance' => ($total_days + $dayLeave),
                                    'transaction_date' => $date,
                                    'description' => 'Update Leave By Daily Leave Policy'.$innerpolicy.': ' . $leaveType->name,
                                ]);
                                $total_days = $total_days + $dayLeave;
                                $diff = $total_days - $total_approved_leaves;
                                if ($diff == floor($diff) + 0.5) {
                                    $availableDay = $diff;
                                } else {
                                    $availableDay = round($diff);
                                }
                                if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                    $availableDay = $diff;
                                }
                                if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                    $availableDay = $diff;
                                }
                                $checkLeaveBalance->available = $availableDay;
                                $checkLeaveBalance->monthwiseDay = $availableDay;
                                $checkLeaveBalance->save();
                            }
                        }
                    }

                    // Monthly leave policy
                    if($getMonthlyLeavePolicy == 1){
                        if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                            if ($today->day == 1) {
                                if($daysDiff > 365){
                                    $leaveTotal = $leaveType->days / $yearMonth;
                                    $addtransaction = UserLeaveBalanceTransaction::create([
                                        'user_id' => $user_id,
                                        'leave_type_id' => $leaveType->id,
                                        'transaction_type' => 'add',
                                        'old_balance' => $total_days,
                                        'update_balance' => $leaveTotal,
                                        'new_balance' => ($total_days + $leaveTotal),
                                        'transaction_date' => $date,
                                        'description' => 'Add ' . $leaveTotal . ' leave days for ' . $today->format('F Y') . ' month wise increment',
                                    ]);
                                    $total_days = $total_days + $leaveTotal;
                                    $total_days_of_month = $total_days;
                                    $diff = $total_days_of_month;
                                    if ($diff == floor($diff) + 0.5) {
                                        $availableDay = $diff;
                                    } else {
                                        $availableDay = round($diff);
                                    }
                                    if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                        $availableDay = $diff;
                                    }
                                    $checkLeaveBalance->available = $availableDay;
                                    $checkLeaveBalance->monthwiseDay = $availableDay;
                                    $checkLeaveBalance->isAddThisMonthLeave = date('m');
                                    $checkLeaveBalance->save();
                                }
                            }
                            
                            if($daysDiff <= 365){
                                $joiningDate = Carbon::parse($joining_date);
                                $monthsDiff = $currentMonth->diffInMonths($joiningDate);// + 1;
                                $leaveTotal = $leaveType->days / $yearMonth;
                                $innerpolicy = '';
                                $today = Carbon::now();
                                // for 6 month policy
                                $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                                if($monthwise2leave==1){
                                    if($is_vacation_leave){
                                        if($is_vacation_leave->id == $leaveType->id){
                                            if($monthsDiff <= 6){
                                                $leaveTotal = 2;
                                                $innerpolicy = ' (Within 6 months accrual of 2 days)';
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
                                $isaddransaction = UserLeaveBalanceTransaction::where('user_id', $user_id)
                                                    ->where('transaction_date', $date)
                                                    ->where('leave_type_id', $leaveType->id)
                                                    ->where('description', 'LIKE', '%Add first month%')
                                                    ->first();
                                if($monthsDiff == 0 && !$isaddransaction){
                                    $monthlyLeave = $leaveTotal;
                                    $daysInMonth  = Carbon::now()->daysInMonth;
                                    $perDayLeave  = $monthlyLeave / $daysInMonth;
                                    $remainingDays = $daysInMonth - $joiningDate->day;
                                    $dayLeaveTotal = round($remainingDays * $perDayLeave, 3);

                                    $addtransaction = UserLeaveBalanceTransaction::create([
                                        'user_id' => $user_id,
                                        'leave_type_id' => $leaveType->id,
                                        'transaction_type' => 'add',
                                        'old_balance' => $total_days,
                                        'update_balance' => $dayLeaveTotal,
                                        'new_balance' => ($total_days + $dayLeaveTotal),
                                        'transaction_date' => $date,
                                        'description' => 'Add first month ' . $dayLeaveTotal . ' days for joining date wise in month wise leave policy'.$innerpolicy,
                                    ]);
                                    $total_days = round($total_days + $dayLeaveTotal,1);
                                    $total_days_of_month = $total_days;
                                    $diff = $total_days_of_month;
                                    if ($diff == floor($diff) + 0.5) {
                                        $availableDay = $diff;
                                    } else {
                                        $availableDay = round($diff);
                                    }
                                    if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                        $availableDay = $diff;
                                    }
                                    $checkLeaveBalance->available = $availableDay;
                                    $checkLeaveBalance->monthwiseDay = $availableDay;
                                    $checkLeaveBalance->isAddThisMonthLeave = date('m');
                                    $checkLeaveBalance->save();
                                }
                                if ($today->day == 1) {
                                    $addtransaction = UserLeaveBalanceTransaction::create([
                                        'user_id' => $user_id,
                                        'leave_type_id' => $leaveType->id,
                                        'transaction_type' => 'add',
                                        'old_balance' => $total_days,
                                        'update_balance' => $leaveTotal,
                                        'new_balance' => ($total_days + $leaveTotal),
                                        'transaction_date' => $today->toDateString(),
                                        'description' => 'Add ' . $leaveTotal . 'days for joining date wise in month wise leave policy'.$innerpolicy,
                                    ]);
                                    $total_days = round($total_days + $leaveTotal,1);
                                    $total_days_of_month = $total_days;
                                    $diff = $total_days_of_month;
                                    if ($diff == floor($diff) + 0.5) {
                                        $availableDay = $diff;
                                    } else {
                                        $availableDay = round($diff);
                                    }
                                    if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                        $availableDay = $diff;
                                    }
                                    $checkLeaveBalance->available = $availableDay;
                                    $checkLeaveBalance->monthwiseDay = $availableDay;
                                    $checkLeaveBalance->isAddThisMonthLeave = date('m');
                                    $checkLeaveBalance->save();
                                }
                            }
                        }
                    }

                    // Annual leave policy
                    if($getAnnualLeavePolicy == 1){
                        if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                            if($leaveRecurringPolicy=='annual_calendar'){
                                //mid year user complate 1 year
                                if ($toDayDate->isSameDay($secondYearStartDate)) {
                                    // add TR
                                    $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                    ->where('user_id', $user_id)
                                    ->where('leave_type_id', $leaveType->id)
                                    ->where('description', 'LIKE', '%Update leave on mid year annual leave policy (annual calendar recurring)%')
                                    ->first();
                                    if(!$isaddransaction){
                                        $endOfYear = $secondYearStartDate->copy()->endOfYear();
                                        $totalMonths = $secondYearStartDate->diffInMonths($endOfYear);
                                        $leaveDay = $leaveType->days / 12;
                                        $leaveTotal = $leaveDay * $totalMonths;
                                        $addtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id' => $user_id,
                                            'leave_type_id' => $leaveType->id,
                                            'transaction_type' => 'add',
                                            'old_balance' => $total_days,
                                            'update_balance' => $leaveTotal,
                                            'new_balance' => ($leaveTotal + $total_days),
                                            'transaction_date' => $date,
                                            'description' => 'Update leave on mid year annual leave policy (annual calendar recurring)' . $leaveType->name.' on: '. $today,
                                        ]);
                                        $new_days = $leaveTotal + $total_days;
                                        $diff = $new_days;
                                        if ($diff == floor($diff) + 0.5) {
                                            $availableDay = $diff;
                                        } else {
                                            $availableDay = round($diff);
                                        }
                                        if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                            $availableDay = $diff;
                                        }
                                        if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                            $availableDay = $diff;
                                        }
                                        $checkLeaveBalance->available = $availableDay;
                                        $checkLeaveBalance->monthwiseDay = $availableDay;
                                        $checkLeaveBalance->save();
                                        $total_days = $availableDay;
                                    }
                                }
                                // old user add leave on new year
                                if($newYear == $startYear){
                                    if($toDayDate->greaterThan($joiningDate->copy()->addYear())){
                                        // add TR
                                        $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                        ->where('user_id', $user_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->where('description', 'LIKE', '%Update leave on new year annual leave policy (annual calendar recurring)%')
                                        ->first();
                                        if(!$isaddransaction){
                                            $addtransaction = UserLeaveBalanceTransaction::create([
                                                'user_id' => $user_id,
                                                'leave_type_id' => $leaveType->id,
                                                'transaction_type' => 'add',
                                                'old_balance' => $total_days,
                                                'update_balance' => $leaveType->days,
                                                'new_balance' => ($leaveType->days + $total_days),
                                                'transaction_date' => $date,
                                                'description' => 'Update leave on new year annual leave policy (annual calendar recurring) ' . $leaveType->name.' on: '. $today,
                                            ]);
                                            $new_days = $leaveType->days + $total_days;
                                            $diff = $new_days;
                                            if ($diff == floor($diff) + 0.5) {
                                                $availableDay = $diff;
                                            } else {
                                                $availableDay = round($diff);
                                            }
                                            if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                                $availableDay = $diff;
                                            }
                                            if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                                $availableDay = $diff;
                                            }
                                            $checkLeaveBalance->available = $availableDay;
                                            $checkLeaveBalance->monthwiseDay = $availableDay;
                                            $checkLeaveBalance->save();
                                            $total_days = $availableDay;
                                        }
                                    }
                                }
                            }

                            if($leaveRecurringPolicy=='joining_to_joining'){
                                if($toDayDate->greaterThan($joiningDate->copy()->addYear())){
                                    if ($joining_date->format('m-d') === $toDayDate->format('m-d')) {
                                        // add TR
                                        $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                        ->where('user_id', $user_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->where('description', 'LIKE', '%Update leave on new year annual leave policy (joining to joining recurring)%')
                                        ->first();
                                        if(!$isaddransaction){
                                            $addtransaction = UserLeaveBalanceTransaction::create([
                                                'user_id' => $user_id,
                                                'leave_type_id' => $leaveType->id,
                                                'transaction_type' => 'add',
                                                'old_balance' => $total_days,
                                                'update_balance' => $leaveType->days,
                                                'new_balance' => ($leaveType->days + $total_days),
                                                'transaction_date' => $date,
                                                'description' => 'Update leave on new year annual leave policy (joining to joining recurring) ' . $leaveType->name.' on: '. $today,
                                            ]);
                                            $new_days = $leaveType->days + $total_days;
                                            $diff = $new_days;
                                            if ($diff == floor($diff) + 0.5) {
                                                $availableDay = $diff;
                                            } else {
                                                $availableDay = round($diff);
                                            }
                                            if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                                $availableDay = $diff;
                                            }
                                            if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                                $availableDay = $diff;
                                            }
                                            $checkLeaveBalance->available = $availableDay;
                                            $checkLeaveBalance->monthwiseDay = $availableDay;
                                            $checkLeaveBalance->save();
                                            $total_days = $availableDay;
                                        }
                                    }
                                }
                            }

                            if($daysDiff <= 365){
                                //daily accrual for new user
                                if($newUserDailyLeavePolicy==1){
                                    $leaveDay = $leaveType->days / 12;

                                    $joiningDate = Carbon::parse($joining_date);
                                    $monthsDiff = $currentMonth->diffInMonths($joiningDate);// + 1;
                                    // for 6 month policy
                                    $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                                    if($monthwise2leave==1){
                                        if($is_vacation_leave->id == $leaveType->id){
                                            if($monthsDiff <= 6){
                                                $leaveDay = 2;
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
                                    
                                    $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                    ->where('user_id', $user_id)
                                    ->where('leave_type_id', $leaveType->id)
                                    ->where('description', 'LIKE', '%Add daily leave for new user annual leave policy%')
                                    ->first();
                                    if(!$isaddransaction){
                                        $addtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id' => $user_id,
                                            'leave_type_id' => $leaveType->id,
                                            'transaction_type' => 'add',
                                            'old_balance' => $total_days,
                                            'update_balance' => $dayLeave,
                                            'new_balance' => ($total_days + $dayLeave),
                                            'transaction_date' => $date,
                                            'description' => 'Add daily leave for new user annual leave policy: ' . $leaveType->name,
                                        ]);
                                        $total_days = $total_days + $dayLeave;
                                        $diff = $total_days - $total_approved_leaves;
                                        if ($diff == floor($diff) + 0.5) {
                                            $availableDay = $diff;
                                        } else {
                                            $availableDay = round($diff);
                                        }
                                        if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                            $availableDay = $diff;
                                        }
                                        $checkLeaveBalance->available = $availableDay;
                                        $checkLeaveBalance->monthwiseDay = $availableDay;
                                        $checkLeaveBalance->save();
                                    }
                                }
                                //monthly accrual for new user
                                if($newUserMonthlyLeavePolicy==1){
                                    if ($today->day == 1) {
                                        // add TR
                                        $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                        ->where('user_id', $user_id)
                                        ->where('leave_type_id', $leaveType->id)
                                        ->where('description', 'LIKE', '%Add monthly leave for new user annual leave policy%')
                                        ->first();
                                        if(!$isaddransaction){
                                            $leaveDay = $leaveType->days / 12;
                                            $joiningDate = Carbon::parse($joining_date);
                                            $monthsDiff = $currentMonth->diffInMonths($joiningDate);// + 1;
                                            // for 6 month policy
                                            $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->value('value');
                                            if($monthwise2leave==1){
                                                if($is_vacation_leave->id == $leaveType->id){
                                                    if($monthsDiff <= 6){
                                                        $leaveDay = 2;
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
                                            // end
                                            $addtransaction = UserLeaveBalanceTransaction::create([
                                                'user_id' => $user_id,
                                                'leave_type_id' => $leaveType->id,
                                                'transaction_type' => 'add',
                                                'old_balance' => $total_days,
                                                'update_balance' => $leaveDay,
                                                'new_balance' => ($total_days + $leaveDay),
                                                'transaction_date' => $date,
                                                'description' => 'Add monthly leave for new user annual leave policy: ' . $leaveType->name,
                                            ]);
                                            $total_days = $total_days + $leaveDay;
                                            $total_days_of_month = $total_days;
                                            $diff = $total_days_of_month;
                                            if ($diff == floor($diff) + 0.5) {
                                                $availableDay = $diff;
                                            } else {
                                                $availableDay = round($diff);
                                            }
                                            if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                                $availableDay = $diff;
                                            }
                                            $checkLeaveBalance->available = $availableDay;
                                            $checkLeaveBalance->monthwiseDay = $availableDay;
                                            $checkLeaveBalance->isAddThisMonthLeave = date('m');
                                            $checkLeaveBalance->save();
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // add ph leave
                    $holiday = Holiday::whereDate('start_date', $yesterday)
                        ->orWhereDate('end_date', $yesterday)
                        ->orWhere(function ($query) use ($yesterday) {
                            $query->where('start_date', '<=', $yesterday)
                                ->where('end_date', '>=', $yesterday);
                        })
                        ->first();
                    $phkeywords = ['PH', 'ph', 'phLeave'];
                    $is_phleave = LeaveType::where(function ($query) use ($phkeywords) {
                        foreach ($phkeywords as $keyword) {
                            $query->orWhere('name', 'like', "%$keyword%");
                        }
                    })->first();
                    if($is_phleave && $is_phleave->name == $leaveType->name){
                        if($holiday){
                            $settingadd = Setting::where('key', 'is_given_without_attend_PH')->first();
                            $isCheckin = Attendance::where('user_id', $user_id)
                                                ->whereIn('status', [
                                                    AttendanceStatus::Present,
                                                    AttendanceStatus::Late,
                                                    AttendanceStatus::EarlyOut,
                                                ])
                                                ->whereDate('date', $yesterday)
                                                ->latest()
                                                ->first();
                            if($isCheckin){
                                $checkindata = 1;
                            } else {
                                $checkindata = 0;
                            }
                            if($settingadd && $settingadd->value==1){
                                $checkindata = 1;
                            }
                            if($checkindata==1){
                                if($is_phleave->name == $leaveType->name){
                                    $isaddinreport = PHLeaveReport::where([
                                        'user_id' => $user_id,
                                        'date' => $yesterday,
                                    ])->first();
                                    if(!$isaddinreport){
                                        $addtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id' => $user_id,
                                            'leave_type_id' => $leaveType->id,
                                            'transaction_type' => 'add',
                                            'old_balance' => $total_days,
                                            'update_balance' => 1,
                                            'new_balance' => ($total_days + 1),
                                            'transaction_date' => $yesterday,
                                            'description' => 'Add 1 PH Leave for holiday: ' . $holiday->detail,
                                        ]);
                                        $addinreport = PHLeaveReport::create([
                                            'user_id' => $user_id,
                                            'holiday_id' => $holiday->id,
                                            'leave_type_id' => $leaveType->id,
                                            'date' => $yesterday,
                                        ]);
                                        $total_days = $total_days + 1;
                                        $diff = $total_days - $total_approved_leaves;
                                        if ($diff == floor($diff) + 0.5) {
                                            $availableDay = $diff;
                                        } else {
                                            $availableDay = round($diff);
                                        }
                                        if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                            $availableDay = $diff;
                                        }
                                        if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                            $availableDay = $diff;
                                        }
                                        $checkLeaveBalance->available = $availableDay;
                                        $checkLeaveBalance->monthwiseDay = $availableDay;
                                        $checkLeaveBalance->save();
                                    }
                                }
                            }
                        }
                    }
                    //end

                    // add cancel off leave
                    $checkcanceloffleave = Setting::where('key','cancel_off_leave_module')->value('value');
                    if($checkcanceloffleave==true){
                        $year = Carbon::now()->year;
                        $startDate = "$year-01-01";
                        $endDate = Carbon::now()->toDateString();//"$year-12-31";
                        $usershift = UsersShift::where('user_id', $user_id)
                                ->whereDate('assigned_for_date', $yesterday)
                                ->whereHas('shift_schedule_information.shift', function ($q) {
                                    $q->where('is_weekend', 1);
                                })
                                ->with(['shift_schedule_information.shift'])
                                ->first();
                        if($usershift){
                            $shiftdata = $usershift->shift_schedule_information->shift ?? null;

                            if($shiftdata && $shiftdata->is_weekend==1){
                                $isCheckin = Attendance::where('user_id', $user_id)
                                                ->whereIn('status', [
                                                    AttendanceStatus::Present,
                                                    AttendanceStatus::Late,
                                                    AttendanceStatus::EarlyOut,
                                                    AttendanceStatus::Weekend,
                                                ])
                                                ->whereDate('date', $yesterday)
                                                ->latest()
                                                ->first();
                                if($isCheckin) {
                                    $cankeywords = ['CANCEL OFF', 'cancel off', 'canceloff'];
                                    $is_canceloff_leave = LeaveType::where(function ($query) use ($cankeywords) {
                                        foreach ($cankeywords as $cankeyword) {
                                            $query->orWhere('name', 'like', "%$cankeyword%");
                                        }
                                    })->first();
                                    
                                    if($is_canceloff_leave && $is_canceloff_leave->name == $leaveType->name){
                                        if (($isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) || ($isCheckin->visit_in != null && $isCheckin->visit_in != '00:00:00')) {
                                            $isaddransaction = UserLeaveBalanceTransaction::where([
                                                'user_id' => $user_id,
                                                'transaction_date' => $yesterday,
                                                'leave_type_id' => $leaveType->id,
                                            ])->first();
                                            if(!$isaddransaction){
                                                $addtransaction = UserLeaveBalanceTransaction::create([
                                                    'user_id' => $user_id,
                                                    'leave_type_id' => $leaveType->id,
                                                    'transaction_type' => 'add',
                                                    'old_balance' => $total_days,
                                                    'update_balance' => 1,
                                                    'new_balance' => ($total_days + 1),
                                                    'transaction_date' => $yesterday,
                                                    'description' => 'Add CANCEL OFF Leave: ' . $leaveType->name,
                                                ]);
                                                $total_days = $total_days + 1;
                                                $diff = $total_days - $total_approved_leaves;
                                                if ($diff == floor($diff) + 0.5) {
                                                    $availableDay = $diff;
                                                } else {
                                                    $availableDay = round($diff);
                                                }
                                                if($is_dil_leave && $is_dil_leave->name == $leaveType->name){
                                                    $availableDay = $diff;
                                                }
                                                if($is_vacation_leave && $is_vacation_leave->id == $leaveType->id){
                                                    $availableDay = $diff;
                                                }
                                                $checkLeaveBalance->available = $availableDay;
                                                $checkLeaveBalance->monthwiseDay = $availableDay;
                                                $checkLeaveBalance->save();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //end
                } else {
                    $checkLeaveBalance = LeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user_id,
                            'year' => date('Y'),
                            'leave_type_id' => $leaveType->id
                        ],
                        [
                            'available' => 0,
                            'monthwiseDay' => 0,
                        ]
                    );
                    $total_days = $checkLeaveBalance->available;
                    $addtransaction = UserLeaveBalanceTransaction::create([
                        'user_id' => $user_id,
                        'leave_type_id' => $leaveType->id,
                        'transaction_type' => 'add',
                        'old_balance' => $total_days,
                        'update_balance' => $total_days,
                        'new_balance' => $total_days,
                        'transaction_date' => $date,
                        'description' => 'Reset leave balance on not set any leave policy'. $today,
                    ]);
                }
            }
        }
        $this->info('[' . now() . '] leave:update-balance ended.');
    }
}
