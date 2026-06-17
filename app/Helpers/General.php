<?php

use App\Enums\Status;
use App\Models\EmployeeWorkingDay;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeaveBalanceTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\Attendance\Entities\Holiday;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Modules\MultiLingual\Entities\Translation;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserPaySlip;
use Modules\Payroll\Traits\SalaryCalculation;
use Nwidart\Modules\Laravel\Module;

if (! function_exists('getUserTotalNetSalary')) {
    function getUserTotalNetSalary($user, $month, $year, $start_date, $end_date)
    {
        $calculator = new class {
            use SalaryCalculation;
        };
        $total_net_salary = 0;
        $answer           = UserPaySlip::exists($user->id, $month, $year);
        if ($answer == 'true') {
            if (getSetting('attendance_base_payroll') == 'true') {
                $user = User::with(['attendances' => function ($query) use ($month, $year) {
                    $query->whereMonth('date', $month)->whereYear('date', $year);
                }])->with('salary')->where('id', $user->id)->first();

                $total_net_salary = $calculator->getTotalNetSalary($user, $month, $year, $start_date, $end_date);
            } else {
                if (getSetting('payroll_calculation') == 'hourly') {

                    $working_days = $user->attendances()
                        ->whereIn('status', [
                            \Modules\Attendance\Enums\AttendanceStatus::Present,
                            \Modules\Attendance\Enums\AttendanceStatus::Late,
                            \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                            \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        ])
                        ->whereBetween('date', [$start_date, $end_date])
                        ->sum('total_worked');
                    $working_days     = $working_days / 60;
                    $basic            = isset($user->salary) ? $user->salary->basic : 0;
                    $total_net_salary = $basic * $working_days;
                } else {
                    $working_days     = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $user->id])->value('total_working_days');
                    $total_net_salary = $calculator->getTotalNetSalary_EXTRA($user, $month, $year, $start_date, $end_date, $working_days);
                }
            }
        }
        $total_net_salary = round(floatval($total_net_salary), 2);
        return $total_net_salary;
    }
}

if (! function_exists('__trans')) {
    function __trans($pharse)
    {
        if (\Module::collections()->has('MultiLingual')) {
            return Translation::getPhrase($pharse);
        }

        return convertPhrase($pharse);
    }

    function convertPhrase($pharse)
    {
        $pharse = (str_replace(['.', '_', ' and '], ['.', ' ', '&'], $pharse));

        if (Str::wordCount($pharse) > 3) {
            return Str::ucfirst($pharse);
        }
        return Str::title($pharse);
    }
}

if (! function_exists('getSetting')) {

    function getSetting($key)
    {
        // $result = Cache::remember("settings", 86400, function () use ($key) {
        //     return Setting::all()->keyBy('key');
        // });
        // return $result[$key]->value ?? null;

        // Above code commented on date 22-07-2024
        // Reason : another dev from prabeer team directly push above changes on server that create an issue
        // commit id : f4750a1cf466a8a60f6cd07583bc1ad782d68f39
        // return Setting::where('key',$key)->first()->value;

        return Setting::where('key', $key)->value('value');
    }
    // function getSetting($key)
    // {
    //     $setting = Setting::where('key', $key)->first();
    //     return $setting->{$key} ?? null;

    //     $result = Cache::remember("settings", 86400, function () use ($key) {
    //         return Setting::all()->keyBy('key');
    //     });
    //     return $result[$key]->value ?? null;
    // }
}

if (! function_exists('my_dd')) {
    function my_dd($query)
    {
        echo "<pre>";
        print_r($query);
        echo "</pre>";
        die();
    }
}

if (! function_exists('pp')) {
    function pp($query)
    {
        if ($query instanceof Builder) {
            $sql = str_replace(['?'], ['\'%s\''], $query->toSql());
            $sql = vsprintf($sql, $query->getBindings());
            return dd($sql);
        }

        return dd($query);
    }
}

if (! function_exists('my_dd')) {
    function my_dd($query)
    {
        echo "<pre>";
        print_r($query);
        echo "</pre>";
        die();
    }
}

if (! function_exists('getErrorResponse')) {
    function getErrorResponse($message = "Something went wrong. Please try again later", $error = null)
    {
        $response = [
            'success' => false,
            'message' => __trans($message),
        ];
        if ($error) {
            $response['error'] = $error;
        }
        return $response;
    }
}

if (! function_exists('getSuccessResponse')) {
    function getSuccessResponse($message)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        return $response;
    }
}
if (! function_exists('getFailureResponse')) {
    function getFailureResponse($message)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        return $response;
    }
}

if (! function_exists('flashSuccess')) {
    function successMessage($message, $translate = true)
    {
        createMessage('success', $message, $translate);
    }
}
if (! function_exists('errorMessage')) {
    function errorMessage($message, $translate = true)
    {
        createMessage('error', $message, $translate);
    }
}

if (! function_exists('createMessage')) {
    function createMessage($type, $message, $translate = true)
    {
        $message = $translate ? __trans($message) : $message;
        Session::flash($type, $message);
    }
}

if (! function_exists('createFlashMessage')) {

    function createFlashMessage($label, $action = 'created')
    {
        $text = str_replace([' '], ['_'], strtolower($label)) . "_has_been_" . strtolower($action) . "_successfully";
        return __trans($text);
    }
}

if (! function_exists('createToggleButton')) {

    function createToggleButton($column, $action, $checked, $message)
    {
        $html  = '<div class="toggle-switch"><label class="switch">';
        $html .= '<a href="' . $action . '" datatable="true" class="action-button" method="POST" data-alert="' . $message . '">';
        $html .= '<input name="' . $column . '" type="checkbox" ' . $checked . '><span class="slider round"></span>';
        $html .= '</a></label><span class="toggle-text"></span></div>';

        return $html;
    }
}

if (! function_exists('getLogo')) {
    function getLogo()
    {
        $path = asset('assets/default/logo.png');

        $logo = getSetting('logo');
        if ($logo && file_exists(public_path($logo))) {
            $path = asset($logo);
        }
        return $path;
    }
}

if (! function_exists('getSmallLogo')) {
    function getSmallLogo()
    {
        $path = asset('assets/default/small-logo.png');
        $logo = getSetting('small_logo');
        if ($logo && file_exists(public_path($logo))) {
            $path = asset($logo);
        }
        return $path;
    }
}

if (! function_exists('getFavicon')) {
    function getFavicon()
    {
        $path = asset('assets/default/logo.png');
        $logo = getSetting('favicon');
        if ($logo) {
            $path = asset($logo);
        }
        return $path;
    }
}

if (! function_exists('createActionButton')) {
    function createActionButton($url, $title, $class = "btn-warning", $icon = null, $action = '', $message = null, $method = null)
    {
        $title = __trans($title);
        $html  = "<a href='$url' class='btn btn-sm inline-block me-2  $class' $action >";
        if ($message) {
            $html = "<a href='$url' class='btn btn-sm inline-block me-2  $class' $action  data-alert=' $message '>";
        }
        if ($method) {
            $html = "<a href='$url' class='btn btn-sm inline-block me-2  $class' $action  data-alert=' $message ' method='$method'>";
        }
        if ($icon) {
            $html .= "<i class='$icon'></i> ";
        }
        $html .= "$title</a>";
        return $html;
    }
}

if (! function_exists('createActionDropdownList')) {
    function createActionDropdownList(array $actions)
    {
        $html = '<div class="dropdown dropdown-action">
                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></a>
                    <div class="dropdown-menu dropdown-menu-right" style="">';
        foreach ($actions as $action) {
            $html .= createActionDropdownItem(...$action);
        }

        $html .= '</div></div>';
        return $html;
    }
}

if (! function_exists('createActionDropdownItem')) {
    function createActionDropdownItem($url, $title, $icon, $class = null, $action = null)
    {
        return '<a class="dropdown-item ' . $class . '" href="' . $url . '" ' . $action . '><i class="' . $icon . ' me-2"></i>' . $title . '</a>';
    }
}

if (! function_exists('isRecaptchaEnabled')) {
    function isRecaptchaEnabled()
    {
        return (getSetting('google_recaptcha_enable') == Status::Enabled->value);
    }
}

if (! function_exists('renderRecaptchaJs')) {
    function renderRecaptchaJs()
    {
        $script = "";
        if (isRecaptchaEnabled()) {
            $script = view('layouts.partials.recaptcha')->render();
        }
        return $script;
    }
}

if (! function_exists('renderRecaptchaHtml')) {
    function renderRecaptchaHtml()
    {
        $html = "";
        if (isRecaptchaEnabled()) {
            $html = "<input type='hidden' id='recaptcha' name='g-recaptcha-response'>";
            if (getSetting('google_recaptcha_version') == 'v2') {
                $secret = getSetting('google_recaptcha_site_key');
                $html   = '<div class="g-recaptcha mb-2" data-sitekey="' . $secret . '"></div>';
            }
        }
        return $html;
    }
}

if (! function_exists('shorterText')) {
    function shorterText($text, $length = 50)
    {
        $totalLength = Str::length($text);
        $text        = strip_tags($text);
        return substr($text, 0, $length) . ($totalLength > $length ? '...' : '');
    }
}

/**
 * Checks if given module is enabled
 *
 * @param $moduleName
 * @return bool
 */
function isModuleEnabled($moduleName)
{
    $module = \Module::find($moduleName);
    if (! $module) {
        return false;
    }

    return $module->isEnabled();
}

if (! function_exists('calculatePendingLeave')) {
    function calculatePendingLeave(LeaveType $leaveType, $user_id)
    {
        $leaveType  = LeaveType::find($leaveType->id);
        $total_days = $leaveType->days;

        $balance = LeaveBalance::where([
            'user_id'       => $user_id,
            'year'          => date('Y'),
            'leave_type_id' => $leaveType->id,
        ])->latest('updated_at')->first();

        if ($balance) {
            $balance = $balance;
        } else {
            $yearMonth     = 12;
            $userfromtable = User::find($user_id);
            if (! $userfromtable->workDetail) {
                return 0; // Return 0 if no work details found
            }
            $joining_date    = Carbon::parse($userfromtable->workDetail?->joining_date);
            $currentYearDate = Carbon::now();
            $daysDiff        = $currentYearDate->diffInDays($joining_date);
            $date            = Carbon::now()->toDateString();
            $currentMonth    = Carbon::now();
            // get vacation leave
            $keywords          = ['Vacation', 'Annual Leave', 'AnnualLeave'];
            $is_vacation_leave = LeaveType::where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%$keyword%");
                }
            })->first();
            $keywords     = ['DIL Leave', 'dil Leave', 'dilLeave'];
            $is_dil_leave = LeaveType::where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%$keyword%");
                }
            })->first();
            //end
            $getDailyLeavePolicy       = Setting::where('key', 'daily_leave_policy')->value('value');
            $getMonthlyLeavePolicy     = Setting::where('key', 'is_month_wise_show_leave')->value('value');
            $getAnnualLeavePolicy      = Setting::where('key', 'annual_leave_policy')->value('value');
            $newUserDailyLeavePolicy   = Setting::where('key', 'new_user_daily_leave_policy')->value('value');
            $newUserMonthlyLeavePolicy = Setting::where('key', 'new_user_monthly_leave_policy')->value('value');

            // Daily leave policy
            if ($getDailyLeavePolicy == 1) {
                if ($is_vacation_leave && $is_vacation_leave->id == $leaveType->id) {
                    if ($daysDiff <= 365) {

                        $leaveDay    = $leaveType->days / 12;
                        $innerpolicy = '';

                        if ($daysDiff <= 365) {
                            $joiningDate = Carbon::parse($joining_date);
                            $monthsDiff  = $currentMonth->diffInMonths($joiningDate); // + 1;
                                                                                      // for 6 month policy
                            $after6month     = 0;
                            $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');
                            if ($monthwise2leave == 1) {
                                if ($is_vacation_leave->id == $leaveType->id) {
                                    if ($monthsDiff <= 6) {
                                        $leaveDay    = 2;
                                        $innerpolicy = ' (Within 6 month accrual of 2 days)';
                                    } else {
                                        $after6month = 3;
                                    }
                                }
                            }
                            // end
                            // for 1 year policy
                            $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                            if ($yearGiven2Leave == 1) {
                                if ($is_vacation_leave->id == $leaveType->id) {
                                    if ($monthsDiff <= 12) {
                                        $leaveDay    = 2;
                                        $innerpolicy = ' (Within 1 year accrual of 2 days)';
                                    }
                                }
                            }
                        }
                        $dayLeave      = $leaveDay / Carbon::now()->daysInMonth;
                        $totalLeaveDay = ($dayLeave * $daysDiff) - $after6month;

                        $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                            ->where('user_id', $user_id)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('description', 'LIKE', '%Add Leave on profile create By Daily Leave Policy%')
                            ->first();
                        if (! $isaddransaction) {
                            $addtransaction = UserLeaveBalanceTransaction::create([
                                'user_id'          => $user_id,
                                'leave_type_id'    => $leaveType->id,
                                'transaction_type' => 'add',
                                'old_balance'      => $totalLeaveDay,
                                'update_balance'   => $totalLeaveDay,
                                'new_balance'      => $totalLeaveDay,
                                'transaction_date' => $date,
                                'description'      => 'Add Leave on profile create By Daily Leave Policy' . $innerpolicy . ': ' . $leaveType->name,
                            ]);
                            $total_days   = $totalLeaveDay;
                            $availableDay = $total_days;
                            $balance      = LeaveBalance::updateOrCreate(
                                [
                                    'user_id'       => $user_id,
                                    'year'          => date('Y'),
                                    'leave_type_id' => $leaveType->id,
                                ],
                                [
                                    'available'              => $availableDay,
                                    'monthwiseDay'           => $availableDay,
                                    'thisYearAvailableLeave' => $availableDay,
                                ]
                            );
                        }
                    } else {
                        $balance = LeaveBalance::updateOrCreate(
                            [
                                'user_id'       => $user_id,
                                'year'          => date('Y'),
                                'leave_type_id' => $leaveType->id,
                            ],
                            [
                                'available'              => 0,
                                'monthwiseDay'           => 0,
                                'thisYearAvailableLeave' => 0,
                            ]
                        );
                    }
                } else {
                    // previous leave balance
                    $total_days = $total_days;
                    // end
                    $availableDay = $total_days;
                    $balance      = LeaveBalance::updateOrCreate(
                        [
                            'user_id'       => $user_id,
                            'year'          => date('Y'),
                            'leave_type_id' => $leaveType->id,
                        ],
                        [
                            'available'              => $availableDay,
                            'monthwiseDay'           => $availableDay,
                            'thisYearAvailableLeave' => $availableDay,
                        ]
                    );
                }
            }

            // Monthly leave policy
            if ($getMonthlyLeavePolicy == 1) {
                if ($is_vacation_leave && $is_vacation_leave->id == $leaveType->id) {
                    if ($daysDiff <= 365) {
                        $joiningDate = Carbon::parse($joining_date)->startOfDay();
                        $today       = Carbon::today();
                        $totalMonths = $joiningDate->diffInMonths($today);
                        $monthsDiff  = $today->diffInMonths($joiningDate); // + 1;
                        $leaveTotal  = $leaveType->days / $yearMonth;
                        $innerpolicy = '';

                        // for 6 month policy
                        $after6month     = 0;
                        $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');
                        if ($monthwise2leave == 1) {
                            if ($is_vacation_leave) {
                                if ($is_vacation_leave->id == $leaveType->id) {
                                    if ($monthsDiff <= 6) {
                                        $leaveTotal  = 2;
                                        $innerpolicy = ' (Within 6 months accrual of 2 days)';
                                    } else {
                                        $after6month = 3;
                                    }
                                }
                            }
                        }
                        // end
                        // for 1 year policy
                        $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                        if ($yearGiven2Leave == 1) {
                            if ($is_vacation_leave) {
                                if ($is_vacation_leave->id == $leaveType->id) {
                                    if ($monthsDiff <= 12) {
                                        $leaveTotal  = 2;
                                        $innerpolicy = ' (Within 1 year accrual of 2 days)';
                                    }
                                }
                            }
                        }
                        // end
                        $totalLeaveDay   = ($totalMonths * $leaveTotal) - $after6month;
                        $isaddransaction = UserLeaveBalanceTransaction::where('user_id', $user_id)
                            ->where('transaction_date', $date)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('description', 'LIKE', '%Add leave when create profile on month wise leave policy%')
                            ->first();
                        if (! $isaddransaction) {
                            $monthlyLeave          = $leaveTotal;
                            $daysInMonth           = Carbon::now()->daysInMonth;
                            $perDayLeave           = $monthlyLeave / $daysInMonth;
                            $remainingDays         = $daysInMonth - $joiningDate->day;
                            $isJoiningMonthCounted = $joiningDate->day <= 15;
                            $dayLeaveTotal         = 0;
                            if (! $isJoiningMonthCounted) {
                                $monthlyLeave = $leaveTotal; // leave per month
                                $daysInMonth  = $joiningDate->daysInMonth;
                                $perDayLeave  = $monthlyLeave / $daysInMonth;
                                // Remaining days INCLUDING joining day
                                $remainingDays = $daysInMonth - $joiningDate->day + 1;
                                $dayLeaveTotal = round($remainingDays * $perDayLeave, 3);
                            }
                            $totalLeaveDay = $totalLeaveDay + $dayLeaveTotal;

                            $addtransaction = UserLeaveBalanceTransaction::create([
                                'user_id'          => $user_id,
                                'leave_type_id'    => $leaveType->id,
                                'transaction_type' => 'add',
                                'old_balance'      => $totalLeaveDay,
                                'update_balance'   => $totalLeaveDay,
                                'new_balance'      => $totalLeaveDay,
                                'transaction_date' => $date,
                                'description'      => 'Add leave when create profile on month wise leave policy' . $innerpolicy,
                            ]);
                            $total_days   = $totalLeaveDay;
                            $availableDay = $total_days;
                            $balance      = LeaveBalance::updateOrCreate(
                                [
                                    'user_id'       => $user_id,
                                    'year'          => date('Y'),
                                    'leave_type_id' => $leaveType->id,
                                ],
                                [
                                    'available'              => $availableDay,
                                    'monthwiseDay'           => $availableDay,
                                    'thisYearAvailableLeave' => $availableDay,
                                ]
                            );
                        }
                    } else {
                        $balance = LeaveBalance::updateOrCreate(
                            [
                                'user_id'       => $user_id,
                                'year'          => date('Y'),
                                'leave_type_id' => $leaveType->id,
                            ],
                            [
                                'available'              => 0,
                                'monthwiseDay'           => 0,
                                'thisYearAvailableLeave' => 0,
                            ]
                        );
                    }
                } else {
                    // previous leave balance
                    $total_days = $total_days;
                    // end
                    $availableDay = $total_days;
                    $balance      = LeaveBalance::updateOrCreate(
                        [
                            'user_id'       => $user_id,
                            'year'          => date('Y'),
                            'leave_type_id' => $leaveType->id,
                        ],
                        [
                            'available'              => $availableDay,
                            'monthwiseDay'           => $availableDay,
                            'thisYearAvailableLeave' => $availableDay,
                        ]
                    );
                }
            }

            // Annual leave policy
            if ($getAnnualLeavePolicy == 1) {
                if ($is_vacation_leave && $is_vacation_leave->id == $leaveType->id) {
                    if ($daysDiff <= 365) {
                        //daily accrual for new user
                        if ($newUserDailyLeavePolicy == 1) {
                            $leaveDay = $leaveType->days / 12;

                            $joiningDate = Carbon::parse($joining_date);
                            $monthsDiff  = $currentMonth->diffInMonths($joiningDate); // + 1;
                                                                                      // for 6 month policy
                            $after6month     = 0;
                            $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');
                            if ($monthwise2leave == 1) {
                                if ($is_vacation_leave->id == $leaveType->id) {
                                    if ($monthsDiff <= 6) {
                                        $leaveDay = 2;
                                    } else {
                                        $after6month = 3;
                                    }
                                }
                            }
                            // end
                            // for 1 year policy
                            $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                            if ($yearGiven2Leave == 1) {
                                if ($is_vacation_leave->id == $leaveType->id) {
                                    if ($monthsDiff <= 12) {
                                        $leaveDay = 2;
                                    }
                                }
                            }
                            $dayLeave      = $leaveDay / Carbon::now()->daysInMonth;
                            $totalLeaveDay = ($daysDiff * $dayLeave) - $after6month;

                            $isaddransaction = UserLeaveBalanceTransaction::whereDate('transaction_date', $date)
                                ->where('user_id', $user_id)
                                ->where('leave_type_id', $leaveType->id)
                                ->where('description', 'LIKE', '%Add leave when create profile, new user annual leave policy(daily policy)%')
                                ->first();
                            if (! $isaddransaction) {
                                $addtransaction = UserLeaveBalanceTransaction::create([
                                    'user_id'          => $user_id,
                                    'leave_type_id'    => $leaveType->id,
                                    'transaction_type' => 'add',
                                    'old_balance'      => $totalLeaveDay,
                                    'update_balance'   => $totalLeaveDay,
                                    'new_balance'      => $totalLeaveDay,
                                    'transaction_date' => $date,
                                    'description'      => 'Add leave when create profile, new user annual leave policy(daily policy): ' . $leaveType->name,
                                ]);
                                $total_days   = $totalLeaveDay;
                                $availableDay = $total_days;
                                $balance      = LeaveBalance::updateOrCreate(
                                    [
                                        'user_id'       => $user_id,
                                        'year'          => date('Y'),
                                        'leave_type_id' => $leaveType->id,
                                    ],
                                    [
                                        'available'              => $availableDay,
                                        'monthwiseDay'           => $availableDay,
                                        'thisYearAvailableLeave' => $availableDay,
                                    ]
                                );
                            }
                        }
                        //monthly accrual for new user
                        if ($newUserMonthlyLeavePolicy == 1) {
                            $joiningDate = Carbon::parse($joining_date)->startOfDay();
                            $today       = Carbon::today();
                            $totalMonths = $joiningDate->diffInMonths($today);
                            $monthsDiff  = $today->diffInMonths($joiningDate); // + 1;
                            $leaveTotal  = $leaveType->days / $yearMonth;
                            $innerpolicy = '';

                            // for 6 month policy
                            $after6month     = 0;
                            $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');
                            if ($monthwise2leave == 1) {
                                if ($is_vacation_leave) {
                                    if ($is_vacation_leave->id == $leaveType->id) {
                                        if ($monthsDiff <= 6) {
                                            $leaveTotal  = 2;
                                            $innerpolicy = ' (Within 6 months accrual of 2 days)';
                                        } else {
                                            $after6month = 3;
                                        }
                                    }
                                }
                            }
                            // end
                            // for 1 year policy
                            $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                            if ($yearGiven2Leave == 1) {
                                if ($is_vacation_leave) {
                                    if ($is_vacation_leave->id == $leaveType->id) {
                                        if ($monthsDiff <= 12) {
                                            $leaveTotal  = 2;
                                            $innerpolicy = ' (Within 1 year accrual of 2 days)';
                                        }
                                    }
                                }
                            }
                            // end
                            $totalLeaveDay = ($totalMonths * $leaveTotal) - $after6month;
                            if ($monthwise2leave == 1) {
                                if ($monthsDiff <= 6) {
                                    $totalLeaveDay = $totalMonths * 2;
                                }
                            }
                            $isaddransaction = UserLeaveBalanceTransaction::where('user_id', $user_id)
                                ->where('transaction_date', $date)
                                ->where('leave_type_id', $leaveType->id)
                                ->where('description', 'LIKE', '%Add leave when create profile on annual leave policy(monthly policy)%')
                                ->first();
                            if (! $isaddransaction) {
                                $monthlyLeave          = $leaveTotal;
                                $daysInMonth           = Carbon::now()->daysInMonth;
                                $perDayLeave           = $monthlyLeave / $daysInMonth;
                                $remainingDays         = $daysInMonth - $joiningDate->day;
                                $isJoiningMonthCounted = $joiningDate->day <= 15;
                                $dayLeaveTotal         = 0;
                                if (! $isJoiningMonthCounted) {
                                    $monthlyLeave = $leaveTotal; // leave per month
                                    $daysInMonth  = $joiningDate->daysInMonth;
                                    $perDayLeave  = $monthlyLeave / $daysInMonth;
                                    // Remaining days INCLUDING joining day
                                    $remainingDays = $daysInMonth - $joiningDate->day + 1;
                                    $dayLeaveTotal = round($remainingDays * $perDayLeave, 3);
                                }
                                $totalLeaveDay = $totalLeaveDay + $dayLeaveTotal;

                                $addtransaction = UserLeaveBalanceTransaction::create([
                                    'user_id'          => $user_id,
                                    'leave_type_id'    => $leaveType->id,
                                    'transaction_type' => 'add',
                                    'old_balance'      => $totalLeaveDay,
                                    'update_balance'   => $totalLeaveDay,
                                    'new_balance'      => $totalLeaveDay,
                                    'transaction_date' => $date,
                                    'description'      => 'Add leave when create profile on annual leave policy(monthly policy)' . $innerpolicy,
                                ]);
                                $total_days   = $totalLeaveDay;
                                $availableDay = $total_days;
                                $balance      = LeaveBalance::updateOrCreate(
                                    [
                                        'user_id'       => $user_id,
                                        'year'          => date('Y'),
                                        'leave_type_id' => $leaveType->id,
                                    ],
                                    [
                                        'available'              => $availableDay,
                                        'monthwiseDay'           => $availableDay,
                                        'thisYearAvailableLeave' => $availableDay,
                                    ]
                                );
                            }
                        }

                        if ($newUserDailyLeavePolicy == 0 && $newUserMonthlyLeavePolicy == 0) {
                            $balance = LeaveBalance::updateOrCreate(
                                [
                                    'user_id'       => $user_id,
                                    'year'          => date('Y'),
                                    'leave_type_id' => $leaveType->id,
                                ],
                                [
                                    'available'              => 0,
                                    'monthwiseDay'           => 0,
                                    'thisYearAvailableLeave' => 0,
                                ]
                            );
                        }
                    } else {
                        $balance = LeaveBalance::updateOrCreate(
                            [
                                'user_id'       => $user_id,
                                'year'          => date('Y'),
                                'leave_type_id' => $leaveType->id,
                            ],
                            [
                                'available'              => 0,
                                'monthwiseDay'           => 0,
                                'thisYearAvailableLeave' => 0,
                            ]
                        );
                    }

                } else {
                    // previous leave balance
                    $total_days = $total_days;
                    // end
                    $availableDay = $total_days;
                    $balance      = LeaveBalance::updateOrCreate(
                        [
                            'user_id'       => $user_id,
                            'year'          => date('Y'),
                            'leave_type_id' => $leaveType->id,
                        ],
                        [
                            'available'              => $availableDay,
                            'monthwiseDay'           => $availableDay,
                            'thisYearAvailableLeave' => $availableDay,
                        ]
                    );
                }
            }
        }

        $availableBalance = $balance->available ?? 0;
        return $availableBalance;
    }
}

if (! function_exists('getUserLeaveBalance')) {
    function getUserLeaveBalance(LeaveType $leaveType, $user_id)
    {
        $leaveType   = LeaveType::find($leaveType->id);
        $total_days  = $leaveType->days;
        $oneYearBack = Carbon::now()->subYear()->year;

        $balance = LeaveBalance::where(
            [
                'user_id'       => $user_id,
                'year'          => date('Y'),
                'leave_type_id' => $leaveType->id,
            ],
        )->first();
        return $balance->available;
    }
}
if (! function_exists('parsehtml')) {
    function parsehtml($template, $documentRequest)
    {
        $calculator = new class {
            use SalaryCalculation;
        };
        // $payslip = UserPaySlip::where(['user_id' => $documentRequest->user_id])->get();
        $payslip = UserPaySlip::where('user_id', $documentRequest->user_id)
            ->latest()
            ->first();
        $payslip = UserPaySlip::where('user_id', $documentRequest->user_id)
            ->latest()
            ->first();

        if (! $payslip) {
            // Use current month and year when no payslip exists
            $payslip             = new \stdClass();
            $payslip->month_code = date('m');
            $payslip->year       = date('Y');
            $payslip->start_date = date('Y-m-01');
            $payslip->end_date   = date('Y-m-t');
            $payslip->user_id    = $documentRequest->user_id;
        }
        $user = User::where('id', $documentRequest->user_id)->first();
        if ($payslip) {

            $setting = Setting::whereIn('id', [1, 4])->get();
            // $payslip_date = date('F', strtotime(date('Y') . '-' . $payslip->month_code)) . ' ' . $payslip->year; //date('Y-m-d H:i:s');
            $payslip_date = strtoupper(date('F', strtotime(date('Y') . '-' . $payslip->month_code))) . ' ' . $payslip->year;
            $start_date   = $payslip->start_date ?? date('Y-m-01', strtotime("$payslip->year-$payslip->month_code-01"));
            $end_date     = $payslip->end_date ?? date('Y-m-t', strtotime("$payslip->year-$payslip->month_code-01"));
            $user_salary  = User::with([
                'all_overtime'  => function ($query) use ($payslip) {
                    // $query->whereBetween('date', [$start_date, $end_date]);
                    $query->where([['month_code', $payslip->month_code], ['year', $payslip->year]]);
                },
                'all_allowance' => function ($query) use ($payslip) {

                    $query->where(function ($subquery) use ($payslip) {
                        $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
                            ->orWhere('is_fixed_for_current_month', 0);
                    });
                },
                'all_deduction' => function ($query) use ($payslip) {
                    $query->where(function ($subquery) use ($payslip) {
                        $subquery->where([['month_code', $payslip->month_code], ['year', $payslip->year]])
                            ->orWhere('is_fixed_for_current_month', 0);
                    });
                },
            ])->where('id', $user->id)->get();
            $user = User::with(['attendances' => function ($query) use ($payslip) {
                $query->whereMonth('date', $payslip->month_code)->whereYear('date', $payslip->year);
            }])->with('salary')->where('id', $user->id)->first();
            $working_days         = 0;
            $working_days         = EmployeeWorkingDay::where(['month_code' => $payslip->month_code, 'year' => $payslip->year, 'user_id' => $payslip->user_id])->value('total_working_days');
            $attendance_deduction = 0;
            if (getSetting('payroll_calculation') == 'hourly') {
                $total_working_hour = $user->attendances()
                    ->whereIn('status', [
                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                        \Modules\Attendance\Enums\AttendanceStatus::Late,
                        \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                        \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                    ])
                    ->whereBetween('date', [$start_date, $end_date])
                    ->sum('total_worked');
                $basic            = isset($user->salary) ? $user->salary->basic : 0;
                $net_salary       = $basic * $total_working_hour / 60;
                $total_net_salary = $basic * $total_working_hour / 60;

                $net_salary       = number_format((float) $net_salary, 2, '.', '');
                $total_net_salary = number_format((float) $total_net_salary, 2, '.', '');
            } else {
                if (getSetting('attendance_base_payroll') == 'true') {
                    $working_days = $user->attendances()
                        ->whereIn('status', [
                            \Modules\Attendance\Enums\AttendanceStatus::Present,
                            \Modules\Attendance\Enums\AttendanceStatus::Late,
                            \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                            \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                        ])
                        ->whereBetween('date', [$start_date, $end_date])
                    // ->distinct('date')
                    //->groupby('date')
                        ->count();

                    $net_salary       = $calculator->getNetSalaryAsPerAttendance($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                    $total_net_salary = $calculator->getTotalNetSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
                    // $attendance_deduction = $calculator->getAttendanceDiduction($user, $payslip->month_code, $payslip->year);
                } else {
                    $net_salary       = $calculator->getNetSalaryAsPerAttendance_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
                    $total_net_salary = $calculator->getTotalNetSalary_EXTRA($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date, $working_days);
                }
            }
            $gross_salary = $calculator->getGrossSalary($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

            $expense = $calculator->monthlyExpensesCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);

            $calculations = [
                'attendance_salary'    => $net_salary,
                'total_deduction'      => 0,
                'net_salary'           => $total_net_salary,
                'attendance_deduction' => $gross_salary - $net_salary,
                'gross_salary'         => $gross_salary,
            ];
            // Extra added 18-03-2024
            $fixed_entity_allowance = [];
            if (isset($user->salary->fixed_allowances)) {
                $decoded_allowances = json_decode($user->salary->fixed_allowances, true);
                if (is_array($decoded_allowances)) {
                    $fixed_entity_allowance = $decoded_allowances;
                }
            }

            $fixed_entity_deduction = [];
            if (isset($user->salary->fixed_deductions)) {
                $decoded_deductions = json_decode($user->salary->fixed_deductions, true);
                if (is_array($decoded_deductions)) {
                    $fixed_entity_deduction = $decoded_deductions;
                }
            }

            $all_fixed_entity          = array_merge($fixed_entity_allowance, $fixed_entity_deduction);
            $gettotaladAdvanceSalary   = $calculator->monthlyfixedAdvanceSalaryCalculation($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
            $totaladAdvanceSalary      = $gettotaladAdvanceSalary['AdvanceSalaryAmount'];
            $approvedAdvanceLoanAmount = $gettotaladAdvanceSalary['loanAmount'];
            $totalDeduction            = $calculator->getTotalUserDeduction($user, $payslip->month_code, $payslip->year, $payslip->start_date, $payslip->end_date);
            // dd($expense);

            $total_present = $user->attendances()
                ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Present)
                ->whereYear('date', $payslip->year)
                ->whereMonth('date', $payslip->month_code)
                ->count();

            $total_weekend = $user->attendances()
                ->where('status', \Modules\Attendance\Enums\AttendanceStatus::Weekend)
                ->whereYear('date', $payslip->year)
                ->whereMonth('date', $payslip->month_code)
                ->count();

            $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');
            if ($checkmonthwise == 1) {
                $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
                    ->where('year', $payslip->year)
                    ->whereHas('leaveType', function ($q) {
                        $q->where('name', 'Vacation');
                    })
                    ->value('monthwiseDay');
            } else {
                $total_vacation_leave = LeaveBalance::where('user_id', $user->id)
                    ->where('year', $payslip->year)
                    ->whereHas('leaveType', function ($q) {
                        $q->where('name', 'Vacation');
                    })
                    ->value('available');
            }

            $monthStart           = $payslip->year . '-' . $payslip->month_code . '-01';
            $monthEnd             = date('Y-m-t', strtotime($monthStart));
            $totalTakenSickLeaves = Leave::where('user_id', $user->id)
                ->where('year', $payslip->year)
                ->whereDate('start_date', '<=', $monthEnd)
                ->whereDate('end_date', '>=', $monthStart)
                ->whereHas('type', function ($q) {
                    $q->where('name', 'sick');
                })
                ->sum('total_leave_days');

            $totalTakenCancelOffLeaves = Leave::where('user_id', $user->id)
                ->where('year', $payslip->year)
                ->whereHas('type', function ($q) {
                    $q->where('name', 'cancel off');
                })
                ->sum('total_leave_days');

            $totalTakenExtraLeaves = Leave::where('user_id', $user->id)
                ->where('year', $payslip->year)
                ->whereHas('type', function ($q) {
                    $q->where('name', 'extra')
                        ->orWhere('name', 'extra leave');
                })
                ->sum('total_leave_days');

            $total_ph_leave = LeaveBalance::where('user_id', $user->id)
                ->where('year', $payslip->year)
                ->whereHas('leaveType', function ($q) {
                    $q->where('name', 'ph');
                })
                ->value('available');

            $deduction_allowance_html = view('payroll::payslip.deduction_allowance', [
                'all_fixed_entity'          => $all_fixed_entity,
                'allowances'                => SetAllowanceDeducation::get(),
                'user_salary'               => $user_salary,
                'approvedAdvanceLoanAmount' => $approvedAdvanceLoanAmount,
            ])->render();

            $salary_allowance_html = view('payroll::payslip.salary_allowance', [
                'all_fixed_entity' => $all_fixed_entity,
                'user_salary'      => $user_salary,
                'expense'          => $expense,
                'allowances'       => SetAllowanceDeducation::get(),
            ])->render();

            $dayinMonth              = cal_days_in_month(CAL_GREGORIAN, $payslip->month_code, $payslip->year);
            $housingAllowance        = isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : 0;
            $transportationAllowance = isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : 0;
            $otherAllowance          = isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : 0;

            $payable_basic_salary             = ($user->salary->basic / $dayinMonth) * $working_days;
            $payable_housing_allowance        = ((int) $housingAllowance / $dayinMonth) * $working_days;
            $payable_transportation_allowance = ((int) $transportationAllowance / $dayinMonth) * $working_days;
            $payable_other_allowance          = ((int) $otherAllowance / $dayinMonth) * $working_days;

            $year  = $payslip->year;
            $month = str_pad($payslip->month_code, 2, '0', STR_PAD_LEFT); // Ensure 2-digit month

            // Get start and end dates
            $startDate = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth()->format('d-m-Y');
            $endDate   = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth()->format('d-m-Y');
            if (str_contains(getSetting('currency'), 'AED')) {
                $AEDCurrency = '<img src="' . asset("assets/currency/aedb.png") . '" alt="AED" style="width:18px; height:18px; vertical-align:middle;">';
            } else {
                $AEDCurrency = getSetting('currency');
            }
            $data = [
                '[[company_name]]'                     => $setting[0]['value'],
                '[[company_address]]'                  => $setting[1]['value'],
                '[[currency]]'                         => $AEDCurrency,
                '[[month]]'                            => DateTime::createFromFormat('!m', $payslip->month_code)->format('F'),
                '[[year]]'                             => $payslip->year,
                '[[start_date]]'                       => $startDate,
                '[[end_date]]'                         => $endDate,
                '[[payslip_date]]'                     => $payslip_date,
                '[[username]]'                         => $user->name,
                '[[emp_code]]'                         => $user->employee_id,
                '[[designation]]'                      => $user->designation->name,
                '[[present]]'                          => $total_present,
                '[[joining_date]]'                     => $user->workDetail?->joining_date->format(config('project.date_format')) ?? '',
                '[[department]]'                       => $user->department?->name ?? 'NA' ?? '',
                '[[bank_name]]'                        => $user->bankDetail->bank_name ?? '',
                '[[account_number]]'                   => $user->bankDetail->account_number ?? '',
                '[[off_day]]'                          => $total_weekend,
                '[[sick_leave_balance]]'               => $totalTakenSickLeaves,
                '[[cancel_off_leave_balance]]'         => $totalTakenCancelOffLeaves,
                '[[annual_leave_balance]]'             => $total_vacation_leave,
                '[[extra_leave_taken]]'                => $totalTakenExtraLeaves,
                '[[ph_leave_balance]]'                 => isset($total_ph_leave) ? $total_ph_leave : "0",
                '[[basic_salary]]'                     => $user->salary->basic,

                '[[payable_basic_salary]]'             => round($payable_basic_salary, 2),
                '[[payable_housing_allowance]]'        => round($payable_housing_allowance, 2),
                '[[payable_transportation_allowance]]' => round($payable_transportation_allowance, 2),
                '[[payable_other_allowance]]'          => round($payable_other_allowance, 2),

                '[[housing_allowance]]'                => isset($all_fixed_entity['housing_allowance']) ? $all_fixed_entity['housing_allowance'] : "",
                '[[transportation_allowance]]'         => isset($all_fixed_entity['transportation_allowance']) ? $all_fixed_entity['transportation_allowance'] : "",
                '[[other_allowance]]'                  => isset($all_fixed_entity['other_allowance']) ? $all_fixed_entity['other_allowance'] : "",
                '[[tips]]'                             => "",
                '[[salary_allowances]]'                => $salary_allowance_html,
                '[[deduction_allowances]]'             => $deduction_allowance_html,
                '[[total_working_days]]'               => $working_days,
                '[[total_earning]]'                    => round(floatval($calculations['attendance_salary']), 2),
                '[[net_amount]]'                       => round(floatval($calculations['net_salary']), 2),
                '[[attendance_deduction]]'             => round(floatval($calculations['attendance_deduction']), 2),
                '[[gross_salary]]'                     => round(floatval($calculations['gross_salary']), 2),
                '[[total_deduction]]'                  => round(floatval($totalDeduction), 2),
                '[[total_deduction_with_attendance]]'  => round(floatval($totalDeduction + $calculations['attendance_deduction']), 2),

                '[[logo]]'                             => $user->department->logo ? '<img src="' . asset('storage/' . $user->department->logo) . '" style="max-height: 100px;">' : '',
                '[[small_logo]]'                       => $user->department->small_logo ? '<img src="' . asset('storage/' . $user->department->small_logo) . '" style="max-height: 100px;">' : '',
                '[[sign]]'                             => $user->department->sign ? '<img src="' . asset('storage/' . $user->department->sign) . '" style="max-height: 100px;">' : '',
                '[[header]]'                           => $user->department->header ? '<img src="' . asset('storage/' . $user->department->header) . '" style="max-height: 100px;">' : '',
                '[[footer]]'                           => $user->department->footer ? '<img src="' . asset('storage/' . $user->department->footer) . '" style="max-height: 100px;">' : '',

            ];

            // Replace placeholders with actual data
            foreach ($data as $placeholder => $value) {
                $template = str_replace($placeholder, $value, $template);
            }
        }

        return $template;
    }
    if (!function_exists('userWorkingDays')) {
        function userWorkingDays($user, $month, $year, $start_date = null, $end_date = null) {

            if(($month != null && $year != null) && ($start_date == null && $end_date == null)) {
                // $start_date = Carbon::parse(Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString());
                // $end_date = Carbon::parse(Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString());
                $start_date = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $end_date   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            } else {
                $start_date = Carbon::parse($start_date);
                $end_date   = Carbon::parse($end_date);

                $month = $start_date->month;
                $year  = $start_date->year;
            }
            $total_working_days = 0;
            $userLeave = 0;
            $holidayCount = 0;
            $presentCount = 0;
            if (getSetting('attendance_base_payroll') == 'true') {
                $user = User::with(['attendances' => function ($query) use ($month, $year) {
                    $query->whereMonth('date', $month)->whereYear('date', $year);
                }])->with('salary')->where('id', $user->id)->first();
                
                $daysInMonth = $start_date->diffInDays($end_date) + 1;
                $day = Carbon::parse($start_date)->toDateString();
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    // Check if the current $day falls within any leave period
                    $leave = Leave::where('user_id', $user->id)
                        ->whereDate('start_date', '<=', $day)
                        ->whereDate('end_date', '>=', $day)
                        ->with('type')
                        ->whereIn('status', [LeaveStatus::Approved->value])
                        ->first();
                    if ($leave) {
                        if ($leave->type->is_paid == 1) {
                            if($leave->is_half_day==1){
                                $userLeave += 0.5;
                            } else {
                                $userLeave += 1;
                            }
                        }
                    }
                    // check holiday
                    $hasHoliday = Holiday::where(function ($query) use ($day) {
                                        $query->whereDate('start_date', '<=', $day)
                                            ->whereDate('end_date', '>=', $day);
                                    })->exists();
                    if($hasHoliday){
                        if ($leave) {
                            $leaveType = $leave->type ? $leave->type->type->value : '';
                            if($leaveType == 'working'){
                                $holidayCount += 1;
                                if ($leave->type->is_paid == 1) {
                                    if($leave->is_half_day==1){
                                        $userLeave -= 0.5;
                                    } else {
                                        $userLeave -= 1;
                                    }
                                }
                            }
                        } else {
                            $is_present = $user->attendances()
                                    ->whereIn('status', [
                                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                                        \Modules\Attendance\Enums\AttendanceStatus::Late,
                                        \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                        \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                        \Modules\Attendance\Enums\AttendanceStatus::HalfDay
                                    ])
                                    ->whereDate('date', $day)
                                    ->first();
                            if($is_present && $is_present->status == \Modules\Attendance\Enums\AttendanceStatus::Present){
                                if($is_present->status == \Modules\Attendance\Enums\AttendanceStatus::HalfDay){
                                    $presentCount += 0.5;
                                } else {
                                    $presentCount += 1;
                                }
                            } else {
                                $holidayCount += 1;
                            }
                        }
                    }
                    if(!$hasHoliday){
                        $is_present = $user->attendances()
                                    ->whereIn('status', [
                                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                                        \Modules\Attendance\Enums\AttendanceStatus::Late,
                                        \Modules\Attendance\Enums\AttendanceStatus::EarlyOut,
                                        \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                        \Modules\Attendance\Enums\AttendanceStatus::HalfDay
                                    ])
                                    ->whereDate('date', $day)
                                    ->first();
                        if ($is_present) {
                            if ($leave) {
                                $leaveType = $leave->type ? $leave->type->type->value : '';
                                if($leaveType == 'working'){
                                    if ($leave->type->is_paid == 1) {
                                        if($leave->is_half_day==1){
                                            $userLeave -= 0.5;
                                            $presentCount += 0.5;
                                        } else {
                                            $userLeave -= 1;
                                        }
                                    } else {
                                        if($leave->is_half_day==1){
                                            $presentCount += 0.5;
                                        } else {
                                            if($is_present->status == \Modules\Attendance\Enums\AttendanceStatus::HalfDay){
                                                $presentCount += 0.5;
                                            } else {
                                                $presentCount += 1;
                                            }
                                        }
                                    }
                                }
                                if($leave->is_half_day==1){
                                    $presentCount += 0.5;
                                }
                            } else {
                                if($is_present->status == \Modules\Attendance\Enums\AttendanceStatus::HalfDay){
                                    $presentCount += 0.5;
                                } else {
                                    $presentCount += 1;
                                }
                            }
                        }
                    }
                    // Move to the next day
                    $day = Carbon::parse($start_date)->addDays($i)->toDateString();
                }
                $total_working_days = $presentCount + $holidayCount + $userLeave;
            } else {
                $total_working_days = EmployeeWorkingDay::where(['month_code' => $month, 'year' => $year, 'user_id' => $user->id])->value('total_working_days') ?? 0;
            }
            $data = [
                'total_working_days' => $total_working_days,
                'user_leave' => $userLeave,
                'holiday_count' => $holidayCount,
                'present_count' => $presentCount,
            ];
            return $data;
        }
    }
}
