<?php
namespace Modules\Api\Http\Controllers\Attendance;

use App\Models\Department;
use App\Models\PHLeaveReport;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeaveBalanceTransaction;
use App\Models\UserShift;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Api\Transformers\TimelineResource;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Services\CheckinService;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Payroll\Entities\UserSalary;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Shift\Entities\UsersShift;

/**
 * @group 3. Attendance
 */
class CheckinController extends Controller
{
    /**
     * Handle Request for the checkin page data of loggedin user
     *
     * @authenticated
     * @response status=200 scenario="Data Load Successfully"{
     *    "success": true,
     *    "message": "Checkin data loaded successfully",
     *    "data": {
     *       "total_worked_today_in_hours": 6,
     *       "total_worked_this_week_in_hours": 6,
     *       "is_currently_check_in": false,
     * }
     * @response status=401 scenario="Unauthenticated" {
     *     "message": "Unauthenticated."
     * }
     */
    public function __construct()
    {
        $setting  = Setting::select('key', 'value')->whereIn('key', ['radius', 'longitude', 'latitude'])->get();
        $location = [];
        foreach ($setting as $result) {
            if ($result->key == 'radius') {
                $location['radius'] = $result->value;
            } else if ($result->key == 'latitude') {
                $location['latitude'] = $result->value;
            } else {
                $location['longitude'] = $result->value;
            }
        }
        $this->company_radius    = $location['radius'];
        $this->company_longitude = floatval($location['longitude']);
        $this->company_latitude  = floatval($location['latitude']);
    }

    public function index(Request $request)
    {

        Log::info('CheckinController', ["index" => $request]);
        $totalWorkedToday    = '00:00';
        $totalWorkedThisWeek = '00:00';

        $totalWorkedTodayMinutes = Attendance::my()->where('date', now()->toDateString())
            ->sum('total_worked') ?? 0;
        $totalWorkedToday = $this->timeConversion($totalWorkedTodayMinutes);

        //Old Logic
        // $totalWorkedToday = Attendance::my()->where('date', now()->toDateString())
        //     ->firstOrNew()->getTotalWorkedInHours();
        // $totalWorkedToday = self::timeConversion($totalWorkedToday);

        $totalWorkedThisWeekMinutes = Attendance::my()->where('date', '>=', now()->startOfWeek())
            ->sum('total_worked') ?? 0;
        $totalWorkedThisWeek = $this->timeConversion($totalWorkedThisWeekMinutes);
        $user_id             = auth()->id();
        $department_id       = User::where('id', $user_id)->pluck('department_id')->first();
        $branch              = Department::where('id', $department_id)->first();
        $data                = [
            'total_worked_today_in_hours'     => $totalWorkedToday,
            // 'total_worked_this_week_in_hours' => round($totalWorkedThisWeek, 2),
            'total_worked_this_week_in_hours' => $totalWorkedThisWeek,
            'is_currently_check_in'           => ! isUserCheckedIn(auth()->id(), \Modules\Attendance\Enums\CheckinType::OUT),
            'is_currently_break_in'           => ! isUserBreakedIn(auth()->id(), \Modules\Attendance\Enums\BreakinType::OUT),
            'is_currently_visit_in'           => ! isUserVisitedIn(auth()->id(), \Modules\Attendance\Enums\VisitinType::OUT),
            //'radius' => $branch->login_radius,
            //'latitude' => floatval($branch->latitude),
            //'longitude' => floatval($branch->longitude)
        ];
        Log::info('CheckinController', ["data" => $data]);

        return response()->success(__trans('checkin_data_loaded_successfully'), $data);
    }

    private function timeConversion($timeInMinutes)
    {
        $hours   = floor($timeInMinutes / 60);
        $minutes = $timeInMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Handle logged in User Checkin and checkout request
     *
     * @authenticated
     *
     * @response status=200 scenario="When User has checked out"{
     *    "success": true,
     *    "message": "You has been clock out successfully",
     *    "data": {
     *       "is_currently_check_in": false,
     * }
     *
     * @response status=200 scenario="When User has checked in"{
     *    "success": true,
     *    "message": "You has been clock in successfully",
     *    "data": {
     *       "is_currently_check_in": true,
     * }
     *
     * @response status=401 scenario="Unauthenticated" {
     *     "message": "Unauthenticated."
     * }
     */
    public function handleMultiCheckIns(CheckinService $checkinService)
    {
        $user_id = auth()->id();
        Log::info('handleMultiCheckIns-user_id-' . $user_id, ["handleMultiCheckIns" => "handleMultiCheckIns function in"]);
        Log::info('handleMultiCheckIns-user_id-' . $user_id, ["checkinService" => $checkinService]);
        $user_id            = auth()->id();
        $location_parameter = User::select('department_id', 'assigned_branch_id', 'longitude as user_longitude', 'latitude as user_latitude')->where('id', $user_id)->first();

        $homebranch = Department::where('id', $location_parameter->department_id)->first();
        if ($homebranch) {
            $company_lat = floatval($homebranch->latitude);
            $company_lng = floatval($homebranch->longitude);

            $user_lat = floatval($location_parameter->user_latitude);
            $user_lng = floatval($location_parameter->user_longitude);

            $unit              = "M"; //M = miles
            $getdistance       = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
            $getdistance       = str_replace(',', '', $getdistance);
            $getdistance_float = (float) $getdistance;

            $radius = $homebranch->login_radius;
            if ($getdistance_float <= $radius) {
                Log::info('handleMultiCheckIns', ["branch_id" => $homebranch->id]);

                try {
                    $checkin = $checkinService->performCheckInCheckOut($homebranch->id);
                    $data    = [
                        'is_currently_check_in' => isUserCheckedIn(auth()->id()),
                        //'required_radius' => $radius,
                        'user_radius'           => $getdistance,
                        'status'                => 'Under Radius',
                        'message'               => 'You are under your company radius',
                    ];

                    return response()->success(createFlashMessage('you', 'Clock ' . $checkin->type->name), $data);
                } catch (Exception $e) {
                    return response()->error($e->getMessage());
                }
            }
        }

        $branchIds = explode(',', $location_parameter->assigned_branch_id);
        $branches  = Department::whereIn('id', $branchIds)->get();
        foreach ($branches as $branch) {
            $company_lat = floatval($branch->latitude);
            $company_lng = floatval($branch->longitude);

            $user_lat = floatval($location_parameter->user_latitude);
            $user_lng = floatval($location_parameter->user_longitude);

            $unit              = "M"; //M = miles
            $getdistance       = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
            $getdistance       = str_replace(',', '', $getdistance);
            $getdistance_float = (float) $getdistance;

            $radius = $branch->login_radius;
            if ($getdistance_float <= $radius) {
                Log::info('handleMultiCheckIns', ["branch_id" => $branch->id]);

                try {
                    $checkin = $checkinService->performCheckInCheckOut($branch->id);
                    $data    = [
                        'is_currently_check_in' => isUserCheckedIn(auth()->id()),
                        //'required_radius' => $radius,
                        'user_radius'           => $getdistance,
                        'status'                => 'Under Radius',
                        'message'               => 'You are under your company radius',
                    ];

                    return response()->success(createFlashMessage('you', 'Clock ' . $checkin->type->name), $data);
                } catch (Exception $e) {
                    return response()->error($e->getMessage());
                }
            }
        }
        //  else {
        $type   = CheckinType::IN;
        $record = Checkin::my()->where([
            'date'    => now()->toDateString(),
            'user_id' => auth()->id(),
        ])->orderByDesc('id')->limit(1)->first();

        if ($record) {
            if ($record->type == CheckinType::IN->value) {
                $type    = CheckinType::OUT;
                $checkin = Checkin::create([
                    'user_id' => auth()->id(),
                    'date'    => now()->toDateString(),
                    'time'    => date('H:i:s'),
                    'type'    => $type,
                ]);
            }
        }

        $data = [
            'is_currently_check_in' => false,
            //'required_radius' => $radius,
            'user_radius'           => $getdistance,
            'status'                => 'Out Side Radius',
            'message'               => 'You are outside login radius',
        ];
        Log::info('handleMultiCheckIns-user_id-' . $user_id, ["data" => $data]);
        Log::info('handleMultiCheckIns-user_id-' . $user_id, ["handleMultiCheckIns" => "handleMultiCheckIns function out"]);
        return response()->success('', $data);
        // }
    }

    public function handleMultiCheckInswithlocation(Request $request, CheckinService $checkinService)
    {
        $user_id = auth()->id();

        Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["request" => $request]);
        Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["checkinService" => $checkinService]);

        $validator = Validator::make($request->all(), [
            'longitude' => ['required', 'string'],
            'latitude'  => ['required', 'string'],
        ]);

        Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["validator" => $validator]);
        if ($validator->fails()) {
            return response()->error(__trans('validation_failed'), $validator->errors());
        }
        if (isset($request->longitude) && isset($request->latitude)) {
            try {
                $data            = $validator->validated();
                $data['user_id'] = auth()->id();
                // $data['longitude'] = $request->longitude;
                // $data['latitude'] = $request->latitude;
                // $data['user_id'] = auth()->id();
                // auth()->user()->update($data);
                auth()->user()->update([
                    'longitude' => $data['longitude'],
                    'latitude'  => $data['latitude'],
                ]);

                Log::info('updateuserlocation', ["data" => $data]);
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }
        }

        $location_parameter = User::select('department_id', 'assigned_branch_id', 'longitude as user_longitude', 'latitude as user_latitude')->where('id', $user_id)->first();

        $homebranch = Department::where('id', $location_parameter->department_id)->first();
        if ($homebranch) {

            $company_lat = floatval($homebranch->latitude);
            $company_lng = floatval($homebranch->longitude);
            $user_lat    = floatval($location_parameter->user_latitude);
            $user_lng    = floatval($location_parameter->user_longitude);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["location_parameter" => $location_parameter]);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["branch" => $homebranch]);
            $unit = "M"; //M = miles
                         // $getdistance = $this->distanceInMeters($company_lat, $company_lng, $user_lat, $user_lng);
            $getdistance = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["getdistance" => $getdistance]);
            $getdistance       = str_replace(',', '', $getdistance);
            $getdistance_float = (float) $getdistance;
            // $radius = $this->company_radius;
            $radius = $homebranch->login_radius;
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["radius" => $radius]);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["request-radius" => $request->radius]);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["getdistance_float" => $getdistance_float]);
            $user     = auth()->user();
            $is_rider = isset($user->workDetail) ? $user->workDetail->is_rider : 0;
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["is_rider" => $is_rider]);

            if (($request->radius <= $radius && $getdistance_float <= $radius) || $is_rider == 1) {
                Log::info('handleMultiCheckInswithlocation', ["branch_id" => $homebranch->id]);
                $checkin = $checkinService->performCheckInCheckOut($homebranch->id);
                $data    = [
                    'is_currently_check_in' => isUserCheckedIn(auth()->id()),
                    //'required_radius' => $radius,
                    'user_radius'           => $getdistance,
                    'status'                => 'Under Radius',
                    'message'               => 'You are under your company radius',
                ];
                // if today is holoday than add PH leave
                $date    = Carbon::now()->toDateString();
                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $is_phleave = LeaveType::where('name', 'like', '%PH%')->first();
                    if ($is_phleave) {
                        $isleaveBL = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user_id], ['leave_type_id', $is_phleave->id]])->first();
                        if ($isleaveBL) {
                            $settingadd = Setting::where('key', 'is_given_without_attend_PH')->first();
                            $isCheckin  = Attendance::where('user_id', $user_id)
                                ->whereIn('status', [
                                    AttendanceStatus::Present,
                                    AttendanceStatus::Late,
                                    AttendanceStatus::EarlyOut,
                                ])
                                ->whereDate('date', $date)
                                ->latest()
                                ->first();
                            if ($isCheckin && $isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) {
                                $checkindata = 1;
                            } else {
                                $checkindata = 0;
                            }
                            if ($settingadd && $settingadd->value == 1) {
                                $checkindata = 1;
                            }
                            if ($checkindata == 1) {

                                if (getSetting('multi_branch_wise_payroll') == 'true' && $homebranch->cancel_off_credit === 'amount') {
                                    $branch_id           = $homebranch->id;
                                    $amount              = $homebranch->cancel_off_amount;
                                    $salaryid            = UserSalary::where('user_id', $user->id)->first();
                                    $userSalaryAllowance = UserSalaryAllowance::firstOrCreate(
                                        [
                                            'user_id'    => $user->id,
                                            'branch_id'  => $branch_id, // branch == department
                                            'salary_id'  => $salaryid ? $salaryid->id : null,
                                            'month_code' => Carbon::now()->format('m'),
                                            'year'       => Carbon::now()->format('Y'),
                                            'title'      => "Cancel Off Allowance",
                                        ],
                                        [
                                            'amount'                     => $amount,
                                            'allowance_type'             => 'fixed',
                                            'percentage_amount'          => 0.00,
                                            'date'                       => now()->toDateString(),
                                            'is_fixed_for_current_month' => 1,
                                            'created_by'                 => auth()->id(),
                                        ]
                                    );

                                } else {
                                    $isaddinreport = PHLeaveReport::where([
                                        'user_id' => $user_id,
                                        'date'    => $date,
                                    ])->first();
                                    if (! $isaddinreport) {
                                        $addinreport = PHLeaveReport::create([
                                            'user_id'       => $user_id,
                                            'holiday_id'    => $holiday->id,
                                            'leave_type_id' => $is_phleave->id,
                                            'date'          => $date,
                                        ]);
                                        $addPHtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id'          => $user_id,
                                            'leave_type_id'    => $is_phleave->id,
                                            'transaction_type' => 'add',
                                            'old_balance'      => $isleaveBL->available,
                                            'update_balance'   => 1,
                                            'new_balance'      => ($isleaveBL->available + 1),
                                            'transaction_date' => $date,
                                            'description'      => 'Check in time, Add 1 PH Leave for holiday: ' . $holiday->detail,
                                        ]);
                                        $isleaveBL->update([
                                            'available'    => $isleaveBL->available + 1,
                                            'monthwiseDay' => $isleaveBL->monthwiseDay + 1,
                                        ]);
                                    }
                                }

                            }
                        }
                    }
                }
                // end
                // add cancel off leave
                // $checkcanceloffleave = Setting::where('key', 'cancel_off_leave_module')->value('value');
                // if ($checkcanceloffleave == true) {
                //     $usershift = UsersShift::where('user_id', $user_id)
                //         ->whereDate('assigned_for_date', $date)
                //         ->whereHas('shift_schedule_information.shift', function ($q) {
                //             $q->where('is_weekend', 1);
                //         })
                //         ->with(['shift_schedule_information.shift'])
                //         ->first();
                //     if ($usershift) {
                //         $shiftdata = $usershift->shift_schedule_information->shift ?? null;
                //         if ($shiftdata && $shiftdata->is_weekend == 1) {
                //             $isCheckin = Attendance::where('user_id', $user_id)
                //                 ->whereIn('status', [
                //                     AttendanceStatus::Present,
                //                     AttendanceStatus::Late,
                //                     AttendanceStatus::EarlyOut,
                //                     AttendanceStatus::Weekend,
                //                 ])
                //                 ->whereDate('date', $date)
                //                 ->latest()
                //                 ->first();
                //             if ($isCheckin) {
                //                 $cankeywords        = ['CANCEL OFF', 'cancel off', 'canceloff'];
                //                 $is_canceloff_leave = LeaveType::where(function ($query) use ($cankeywords) {
                //                     foreach ($cankeywords as $cankeyword) {
                //                         $query->orWhere('name', 'like', "%$cankeyword%");
                //                     }
                //                 })->first();

                //                 if ($is_canceloff_leave) {
                //                     $is_cancel_off = LeaveBalance::where([
                //                         ['year', Carbon::now()->year],
                //                         ['user_id', $user_id],
                //                         ['leave_type_id', $is_canceloff_leave->id],
                //                     ])->first();
                //                     if (($isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) || ($isCheckin->visit_in != null && $isCheckin->visit_in != '00:00:00')) {
                //                         if ($is_cancel_off) {
                //                             if (getSetting('multi_branch_wise_payroll') == 'true' && $homebranch->cancel_off_credit === 'amount') {
                //                                 $branch_id           = $homebranch->id;
                //                                 $amount              = $homebranch->cancel_off_amount;
                //                                 $salaryid            = UserSalary::where('user_id', $user->id)->first();
                //                                 $userSalaryAllowance = UserSalaryAllowance::firstOrCreate(
                //                                     [
                //                                         'user_id'    => $user->id,
                //                                         'branch_id'  => $branch_id, // branch == department
                //                                         'salary_id'  => $salaryid ? $salaryid->id : null,
                //                                         'month_code' => Carbon::now()->format('m'),
                //                                         'year'       => Carbon::now()->format('Y'),
                //                                         'title'      => "Cancel Off Allowance",
                //                                     ],
                //                                     [
                //                                         'amount'                     => $amount,
                //                                         'allowance_type'             => 'fixed',
                //                                         'percentage_amount'          => 0.00,
                //                                         'date'                       => now()->toDateString(),
                //                                         'is_fixed_for_current_month' => 1,
                //                                         'created_by'                 => auth()->id(),
                //                                     ]
                //                                 );

                //                             } else {
                //                                 $addtransaction = UserLeaveBalanceTransaction::where([
                //                                     'user_id'          => $user_id,
                //                                     'leave_type_id'    => $is_canceloff_leave->id,
                //                                     'transaction_date' => $date,
                //                                 ])
                //                                     ->where(function ($q) {
                //                                         $q->where('description', 'LIKE', '%Add CANCEL OFF Leave%')
                //                                             ->orWhere('description', 'LIKE', '%Add CANCEL OFF Leave From CheckIn Time%');
                //                                     })
                //                                     ->first();
                //                                 if (! $addtransaction) {
                //                                     $addCancelOffTransaction = UserLeaveBalanceTransaction::create([
                //                                         'user_id'          => $user_id,
                //                                         'leave_type_id'    => $is_canceloff_leave->id,
                //                                         'transaction_type' => 'add',
                //                                         'old_balance'      => $is_cancel_off->available,
                //                                         'update_balance'   => 1,
                //                                         'new_balance'      => ($is_cancel_off->available + 1),
                //                                         'transaction_date' => $date,
                //                                         'description'      => 'Add CANCEL OFF Leave From CheckIn Time: ' . $is_canceloff_leave->name,
                //                                     ]);
                //                                     $is_cancel_off->update([
                //                                         'available'    => $is_cancel_off->available + 1,
                //                                         'monthwiseDay' => $is_cancel_off->monthwiseDay + 1,
                //                                     ]);
                //                                 }
                //                             }
                //                         }
                //                     }
                //                 }
                //             }
                //         }
                //     }
                // }
                Log::info('Cancel Off Process Started', ['user_id' => $user_id, 'date' => $date]);

                $checkcanceloffleave = Setting::where('key', 'cancel_off_leave_module')->value('value');
                Log::info('Cancel Off Setting Value', ['value' => $checkcanceloffleave]);

                if ($checkcanceloffleave == true) {

                    Log::info('Cancel Off Module Enabled');

                    $usershift = UsersShift::where('user_id', $user_id)
                        ->whereDate('assigned_for_date', $date)
                        ->whereHas('shift_schedule_information.shift', function ($q) {
                            $q->where('is_weekend', 1);
                        })
                        ->with(['shift_schedule_information.shift'])
                        ->first();

                    Log::info('User Shift Data', ['shift' => $usershift]);

                    if ($usershift) {

                        $shiftdata = $usershift->shift_schedule_information->shift ?? null;
                        Log::info('Shift Data', ['shiftdata' => $shiftdata]);

                        if ($shiftdata && $shiftdata->is_weekend == 1) {

                            Log::info('Weekend Shift Confirmed');

                            $isCheckin = Attendance::where('user_id', $user_id)
                                ->whereIn('status', [
                                    AttendanceStatus::Present,
                                    AttendanceStatus::Late,
                                    AttendanceStatus::EarlyOut,
                                    AttendanceStatus::Weekend,
                                ])
                                ->whereDate('date', $date)
                                ->latest()
                                ->first();

                            Log::info('Attendance Check', ['attendance' => $isCheckin]);

                            if ($isCheckin) {

                                $cankeywords = ['CANCEL OFF', 'cancel off', 'canceloff'];

                                $is_canceloff_leave = LeaveType::where(function ($query) use ($cankeywords) {
                                    foreach ($cankeywords as $cankeyword) {
                                        $query->orWhere('name', 'like', "%$cankeyword%");
                                    }
                                })->first();

                                Log::info('Cancel Off Leave Type', ['leave_type' => $is_canceloff_leave]);

                                if ($is_canceloff_leave) {

                                    // $is_cancel_off = LeaveBalance::where([
                                    //     ['year', Carbon::now()->year],
                                    //     ['user_id', $user_id],
                                    //     ['leave_type_id', $is_canceloff_leave->id],
                                    // ])->first();
                                    $is_cancel_off = LeaveBalance::firstOrCreate(
                                        [
                                            'year'          => Carbon::now()->year,
                                            'user_id'       => $user_id,
                                            'leave_type_id' => $is_canceloff_leave->id,
                                        ],
                                        [
                                            'available'    => 0,
                                            'monthwiseDay' => 0,
                                        ]
                                    );

                                    Log::info('Leave Balance Record', ['leave_balance' => $is_cancel_off]);

                                    if (
                                        ($isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) ||
                                        ($isCheckin->visit_in != null && $isCheckin->visit_in != '00:00:00')
                                    ) {

                                        Log::info('Valid Check-in Found');

                                        if ($is_cancel_off) {

                                            if (getSetting('multi_branch_wise_payroll') == 'true' && $homebranch->cancel_off_credit === 'amount') {

                                                Log::info('Cancel Off Credit Type: Amount');

                                                $branch_id = $homebranch->id;
                                                $amount    = $homebranch->cancel_off_amount;

                                                $salaryid = UserSalary::where('user_id', $user->id)->first();

                                                Log::info('Salary Info', ['salary' => $salaryid]);

                                                $userSalaryAllowance = UserSalaryAllowance::firstOrCreate(
                                                    [
                                                        'user_id'    => $user->id,
                                                        'branch_id'  => $branch_id,
                                                        'salary_id'  => $salaryid ? $salaryid->id : null,
                                                        'month_code' => Carbon::now()->format('m'),
                                                        'year'       => Carbon::now()->format('Y'),
                                                        'title'      => "Cancel Off Allowance",
                                                    ],
                                                    [
                                                        'amount'                     => $amount,
                                                        'allowance_type'             => 'fixed',
                                                        'percentage_amount'          => 0.00,
                                                        'date'                       => now()->toDateString(),
                                                        'is_fixed_for_current_month' => 1,
                                                        'created_by'                 => auth()->id(),
                                                    ]
                                                );

                                                Log::info('Allowance Created or Found', ['allowance' => $userSalaryAllowance]);

                                            } else {

                                                Log::info('Cancel Off Credit Type: Leave');

                                                $addtransaction = UserLeaveBalanceTransaction::where([
                                                    'user_id'          => $user_id,
                                                    'leave_type_id'    => $is_canceloff_leave->id,
                                                    'transaction_date' => $date,
                                                ])
                                                    ->where(function ($q) {
                                                        $q->where('description', 'LIKE', '%Add CANCEL OFF Leave%')
                                                            ->orWhere('description', 'LIKE', '%Add CANCEL OFF Leave From CheckIn Time%');
                                                    })
                                                    ->first();

                                                Log::info('Existing Transaction Check', ['transaction' => $addtransaction]);

                                                if (! $addtransaction) {

                                                    Log::info('Creating Cancel Off Leave Transaction');

                                                    $addCancelOffTransaction = UserLeaveBalanceTransaction::create([
                                                        'user_id'          => $user_id,
                                                        'leave_type_id'    => $is_canceloff_leave->id,
                                                        'transaction_type' => 'add',
                                                        'old_balance'      => $is_cancel_off->available,
                                                        'update_balance'   => 1,
                                                        'new_balance'      => ($is_cancel_off->available + 1),
                                                        'transaction_date' => $date,
                                                        'description'      => 'Add CANCEL OFF Leave From CheckIn Time: ' . $is_canceloff_leave->name,
                                                    ]);

                                                    Log::info('Transaction Created', ['transaction' => $addCancelOffTransaction]);

                                                    $is_cancel_off->update([
                                                        'available'    => $is_cancel_off->available + 1,
                                                        'monthwiseDay' => $is_cancel_off->monthwiseDay + 1,
                                                    ]);

                                                    Log::info('Leave Balance Updated', [
                                                        'new_available' => $is_cancel_off->available,
                                                        'new_monthwise' => $is_cancel_off->monthwiseDay,
                                                    ]);

                                                } else {
                                                    Log::info('Transaction Already Exists, Skipping');
                                                }
                                            }

                                        } else {
                                            Log::warning('Leave Balance Record Not Found');
                                        }

                                    } else {
                                        Log::warning('No Valid Check-in Time');
                                    }

                                } else {
                                    Log::warning('Cancel Off Leave Type Not Found');
                                }

                            } else {
                                Log::warning('Attendance Not Found');
                            }

                        } else {
                            Log::warning('Shift is not weekend');
                        }

                    } else {
                        Log::warning('User Shift Not Found');
                    }

                } else {
                    Log::warning('Cancel Off Module Disabled');
                }

                Log::info('Cancel Off Process Completed');
                // end
                return response()->success(createFlashMessage('you', 'Clock ' . $checkin->type->name), $data);
            }
        }

        $branchIds = explode(',', $location_parameter->assigned_branch_id);
        $branches  = Department::whereIn('id', $branchIds)->get();

        foreach ($branches as $branch) {
            $company_lat = floatval($branch->latitude);
            $company_lng = floatval($branch->longitude);
            $user_lat    = floatval($location_parameter->user_latitude);
            $user_lng    = floatval($location_parameter->user_longitude);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["location_parameter" => $location_parameter]);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["branch" => $branch]);
            $unit = "M"; //M = miles
                         // $getdistance = $this->distanceInMeters($company_lat, $company_lng, $user_lat, $user_lng);
            $getdistance = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["getdistance" => $getdistance]);
            $getdistance       = str_replace(',', '', $getdistance);
            $getdistance_float = (float) $getdistance;
            // $radius = $this->company_radius;
            $radius = $branch->login_radius;
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["radius" => $radius]);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["request-radius" => $request->radius]);
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["getdistance_float" => $getdistance_float]);
            $user     = auth()->user();
            $is_rider = isset($user->workDetail) ? $user->workDetail->is_rider : 0;
            Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["is_rider" => $is_rider]);

            if (($request->radius <= $radius && $getdistance_float <= $radius) || $is_rider == 1) {
                Log::info('handleMultiCheckInswithlocation', ["branch_id" => $branch->id]);

                $checkin = $checkinService->performCheckInCheckOut($branch->id);
                $data    = [
                    'is_currently_check_in' => isUserCheckedIn(auth()->id()),
                    //'required_radius' => $radius,
                    'user_radius'           => $getdistance,
                    'status'                => 'Under Radius',
                    'message'               => 'You are under your company radius',
                ];
                // if today is holoday than add PH leave
                $date    = Carbon::now()->toDateString();
                $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                if ($holiday) {
                    $is_phleave = LeaveType::where('name', 'like', '%PH%')->first();
                    if ($is_phleave) {
                        $isleaveBL = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user_id], ['leave_type_id', $is_phleave->id]])->first();
                        if ($isleaveBL) {
                            $settingadd = Setting::where('key', 'is_given_without_attend_PH')->first();
                            $isCheckin  = Attendance::where('user_id', $user_id)
                                ->whereIn('status', [
                                    AttendanceStatus::Present,
                                    AttendanceStatus::Late,
                                    AttendanceStatus::EarlyOut,
                                ])
                                ->whereDate('date', $date)
                                ->latest()
                                ->first();
                            if ($isCheckin && $isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) {
                                $checkindata = 1;
                            } else {
                                $checkindata = 0;
                            }
                            if ($settingadd && $settingadd->value == 1) {
                                $checkindata = 1;
                            }
                            if ($checkindata == 1) {
                                if (getSetting('multi_branch_wise_payroll') == 'true' && $homebranch->cancel_off_credit === 'amount') {
                                    $branch_id           = $homebranch->id;
                                    $amount              = $homebranch->cancel_off_amount;
                                    $salaryid            = UserSalary::where('user_id', $user->id)->first();
                                    $userSalaryAllowance = UserSalaryAllowance::firstOrCreate(
                                        [
                                            'user_id'    => $user->id,
                                            'branch_id'  => $branch_id, // branch == department
                                            'salary_id'  => $salaryid ? $salaryid->id : null,
                                            'month_code' => Carbon::now()->format('m'),
                                            'year'       => Carbon::now()->format('Y'),
                                            'title'      => "Cancel Off Allowance",
                                        ],
                                        [
                                            'amount'                     => $amount,
                                            'allowance_type'             => 'fixed',
                                            'percentage_amount'          => 0.00,
                                            'date'                       => now()->toDateString(),
                                            'is_fixed_for_current_month' => 1,
                                            'created_by'                 => auth()->id(),
                                        ]
                                    );

                                } else {
                                    $isaddinreport = PHLeaveReport::where([
                                        'user_id' => $user_id,
                                        'date'    => $date,
                                    ])->first();
                                    if (! $isaddinreport) {
                                        $addinreport = PHLeaveReport::create([
                                            'user_id'       => $user_id,
                                            'holiday_id'    => $holiday->id,
                                            'leave_type_id' => $is_phleave->id,
                                            'date'          => $date,
                                        ]);
                                        $addtransaction = UserLeaveBalanceTransaction::create([
                                            'user_id'          => $user_id,
                                            'leave_type_id'    => $is_phleave->id,
                                            'transaction_type' => 'add',
                                            'old_balance'      => $isleaveBL->available,
                                            'update_balance'   => 1,
                                            'new_balance'      => ($isleaveBL->available + 1),
                                            'transaction_date' => $date,
                                            'description'      => 'Check in time, Add 1 PH Leave for holiday: ' . $holiday->detail,
                                        ]);
                                        $isleaveBL->update([
                                            'available'    => $isleaveBL->available + 1,
                                            'monthwiseDay' => $isleaveBL->monthwiseDay + 1,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                // end
                // add cancel off leave
                $checkcanceloffleave = Setting::where('key', 'cancel_off_leave_module')->value('value');
                if ($checkcanceloffleave == true) {
                    $usershift = UsersShift::where('user_id', $user_id)
                        ->whereDate('assigned_for_date', $date)
                        ->whereHas('shift_schedule_information.shift', function ($q) {
                            $q->where('is_weekend', 1);
                        })
                        ->with(['shift_schedule_information.shift'])
                        ->first();
                    if ($usershift) {
                        $shiftdata = $usershift->shift_schedule_information->shift ?? null;
                        if ($shiftdata && $shiftdata->is_weekend == 1) {
                            $isCheckin = Attendance::where('user_id', $user_id)
                                ->whereIn('status', [
                                    AttendanceStatus::Present,
                                    AttendanceStatus::Late,
                                    AttendanceStatus::EarlyOut,
                                    AttendanceStatus::Weekend,
                                ])
                                ->whereDate('date', $date)
                                ->latest()
                                ->first();
                            if ($isCheckin) {
                                $cankeywords        = ['CANCEL OFF', 'cancel off', 'canceloff'];
                                $is_canceloff_leave = LeaveType::where(function ($query) use ($cankeywords) {
                                    foreach ($cankeywords as $cankeyword) {
                                        $query->orWhere('name', 'like', "%$cankeyword%");
                                    }
                                })->first();
                                // $is_cancel_off = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user_id], ['leave_type_id', $is_canceloff_leave->id]])->first();
                                 $is_cancel_off = LeaveBalance::firstOrCreate(
                                        [
                                            'year'          => Carbon::now()->year,
                                            'user_id'       => $user_id,
                                            'leave_type_id' => $is_canceloff_leave->id,
                                        ],
                                        [
                                            'available'    => 0,
                                            'monthwiseDay' => 0,
                                        ]
                                    );
                                if ($is_canceloff_leave) {
                                    if (getSetting('multi_branch_wise_payroll') == 'true' && $homebranch->cancel_off_credit === 'amount') {
                                        $branch_id           = $homebranch->id;
                                        $amount              = $homebranch->cancel_off_amount;
                                        $salaryid            = UserSalary::where('user_id', $user->id)->first();
                                        $userSalaryAllowance = UserSalaryAllowance::firstOrCreate(
                                            [
                                                'user_id'    => $user->id,
                                                'branch_id'  => $branch_id, // branch == department
                                                'salary_id'  => $salaryid ? $salaryid->id : null,
                                                'month_code' => Carbon::now()->format('m'),
                                                'year'       => Carbon::now()->format('Y'),
                                                'title'      => "Cancel Off Allowance",
                                            ],
                                            [
                                                'amount'                     => $amount,
                                                'allowance_type'             => 'fixed',
                                                'percentage_amount'          => 0.00,
                                                'date'                       => now()->toDateString(),
                                                'is_fixed_for_current_month' => 1,
                                                'created_by'                 => auth()->id(),
                                            ]
                                        );

                                    } else {
                                        if (($isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) || ($isCheckin->visit_in != null && $isCheckin->visit_in != '00:00:00')) {
                                            if ($is_cancel_off) {
                                                $addtransaction = UserLeaveBalanceTransaction::where([
                                                    'user_id'          => $user_id,
                                                    'leave_type_id'    => $is_canceloff_leave->id,
                                                    'transaction_date' => $date,
                                                ])
                                                    ->where(function ($q) {
                                                        $q->where('description', 'LIKE', '%Add CANCEL OFF Leave%')
                                                            ->orWhere('description', 'LIKE', '%Add CANCEL OFF Leave From CheckIn Time%');
                                                    })
                                                    ->first();
                                                if (! $addtransaction) {
                                                    $addtransaction = UserLeaveBalanceTransaction::create([
                                                        'user_id'          => $user_id,
                                                        'leave_type_id'    => $is_canceloff_leave->id,
                                                        'transaction_type' => 'add',
                                                        'old_balance'      => $is_cancel_off->available,
                                                        'update_balance'   => 1,
                                                        'new_balance'      => ($is_cancel_off->available + 1),
                                                        'transaction_date' => $date,
                                                        'description'      => 'Add CANCEL OFF Leave From CheckIn Time: ' . $is_canceloff_leave->name,
                                                    ]);
                                                    $is_cancel_off->update([
                                                        'available'    => $is_cancel_off->available + 1,
                                                        'monthwiseDay' => $is_cancel_off->monthwiseDay + 1,
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // end
                return response()->success(createFlashMessage('you', 'Clock ' . $checkin->type->name), $data);
            }
        }

        $type   = CheckinType::IN;
        $record = Checkin::my()->where([
            'date'    => now()->toDateString(),
            'user_id' => auth()->id(),
        ])->orderByDesc('id')->limit(1)->first();
        if ($record) {
            if ($record->type == CheckinType::IN->value) {
                $type = CheckinType::OUT;
                if ($request->radius > $radius && $request->action == "auto" && $request->type == "out") {
                    $checkin = Checkin::create([
                        'user_id'         => auth()->id(),
                        'date'            => now()->toDateString(),
                        'time'            => date('H:i:s'),
                        'type'            => $type,
                        'latecomment'     => 'AUTO_RADIUSOUT-2',
                        'checkout_reason' => 'OUT OF RADIUS',
                        'is_auto_update'  => 1,
                    ]);
                } else {
                    $checkin = Checkin::create([
                        'user_id' => auth()->id(),
                        'date'    => now()->toDateString(),
                        'time'    => date('H:i:s'),
                        'type'    => $type,
                    ]);
                }
                /*  $checkin = Checkin::create([
                    'user_id' => auth()->id(),
                    'date' => now()->toDateString(),
                    'time' => date('H:i:s'),
                    'type' => $type
                ]);*/
            }
        }

        $data = [
            'is_currently_check_in' => false,
            //'required_radius' => $radius,
            'user_radius'           => $getdistance,
            'status'                => 'Out Side Radius',
            'message'               => 'You are outside login radius',
        ];
        Log::info('handleMultiCheckInswithlocation-user_id-' . $user_id, ["data" => $data]);
        return response()->success('', $data);
    }

    // public function getAttendanceByID(Request $request, $month)
    // {
    //     $month = $request->route('month');
    //     // $requests = AttendanceResource::collection(Attendance::my()->whereMonth('date',$month)->get());
    //     // Changes BY CLIENT THEN API UPDATED 12-MARCH-2024
    //     //$checkInOut_data = Checkin::my()->whereMonth('date',$month)->whereNotIn('is_auto_update', ['1'])->get();
    //     // Again Client changed condition 11-06-2024
    //     $checkInOut_data = Checkin::my()->whereMonth('date', $month)->whereYear('date', now()->year)->get();
    //     foreach ($checkInOut_data as $data) {
    //         if ($data->type == 'in') {
    //             $data['action'] = 'Clock In';
    //         } else {
    //             $data['action'] = 'Clock Out';
    //         }
    //     }
    //     $breakInOut_data = Breakin::my()->whereMonth('date', $month)->get();
    //     foreach ($breakInOut_data as $data) {
    //         if ($data->type == 'in') {
    //             $data['action'] = 'Break In';
    //         } else {
    //             $data['action'] = 'Break Out';
    //         }
    //     }
    //     // $mergedData = $checkInOut_data->merge($breakInOut_data);
    //     // $requests = TimelineResource::collection($mergedData);
    //     $mergedData       = $checkInOut_data->merge($breakInOut_data);
    //     $sortedMergedData = $mergedData->sortBy('date');
    //     $requests         = TimelineResource::collection($sortedMergedData);
    //     return response()->success(__trans('user_attendance_list_fetched_successfully'), $requests);
    // }
    public function getAttendanceByID(Request $request, $month = null, $year = null)
    {

        $month   = $request->route('month') ?? now()->month;
        $year    = $request->route('year') ?? now()->year;
        $user_id = $request->route('user_id');

        Log::info('Attendance Debug -2', [
            'route_user_id' => $user_id,
            'auth_id'       => auth()->id(),
            'month'         => $month,
            'year'          => $year,
            'request'       => $request,
        ]);

        $checkInOut_data = Checkin::my()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        foreach ($checkInOut_data as $data) {
            $data['action'] = $data->type === 'in' ? 'Clock In' : 'Clock Out';
        }

        $breakInOut_data = Breakin::my()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        foreach ($breakInOut_data as $data) {
            $data['action'] = $data->type === 'in' ? 'Break In' : 'Break Out';
        }

        $mergedData = $checkInOut_data->merge($breakInOut_data)
            ->sortBy('date');

        $requests = TimelineResource::collection($mergedData);

        return response()->success(
            __trans('user_attendance_list_fetched_successfully'),
            $requests
        );
    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist  = acos($dist);
            $dist  = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit  = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }

    public function distanceInMeters($lat1, $lon1, $lat2, $lon2)
    {
        $theta      = $lon1 - $lon2;
        $dist       = sin(($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist       = acos($dist);
        $dist       = rad2deg($dist);
        $miles      = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        $meters     = $kilometers * 1000;

        return number_format((float) $meters, 2, '.', '');
    }

    public function haversine($lat1, $lon1, $lat2, $lon2)
    {
        // Convert latitude and longitude from degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $dlat     = $lat2 - $lat1;
        $dlon     = $lon2 - $lon1;
        $a        = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c        = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = 6371 * $c; // Radius of Earth in kilometers

        return $distance;
    }
    public function distanceInMeters2($company_lat, $company_lon, $user_lat, $user_lon)
    {
        return number_format($this->haversine($company_lat, $company_lon, $user_lat, $user_lon) * 1000, 2);
    }

    public function checkShiftTiming()
    {
        $user_id   = auth()->id();
        $userShift = UserShift::select('shift_start', 'shift_end', 'user_id')->where('user_id', $user_id)->first();
    }

    private function getShiftDeduction($user_id)
    {
        $lateComment = [];
        return $lateComment;

        $deductionRules = [
            ['minutes' => 60, 'deduction' => 3],
            ['minutes' => 30, 'deduction' => 2],
            ['minutes' => 15, 'deduction' => 1],
        ];

        $userShifts = UsersShift::where('user_id', $user_id)
            ->whereDate('assigned_for_date', Carbon::today()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        // $shiftStart = $userShifts->shift_schedule_information->shift_start;
        if (! $userShifts) {
            return $lateComment;
        }
        if (! $userShifts->shift_schedule_information) {
            return $lateComment;
        }
        $shiftStart      = $userShifts->shift_schedule_information->shift_start;
        $arrivalTime     = date('H:i:s');
        $shiftDateTime   = Carbon::today()->setTimeFromTimeString($shiftStart);
        $arrivalDateTime = Carbon::today()->setTimeFromTimeString($arrivalTime);
        $ded             = 0;
        $minutesLate     = $arrivalDateTime->diffInMinutes($shiftDateTime);
        foreach ($deductionRules as $rule) {
            if ($minutesLate >= $rule['minutes']) {
                $ded = $rule['deduction'];
                break;
            }
        }
        $dedTime = $this->deductHoursFromTime(date('H:i:s'), $ded);
        if ($ded != 0) {
            $lateComment['ded']          = $ded;
            $lateComment['deductedTime'] = $dedTime;
            $lateComment['actualTime']   = date('H:i:s');
        }
        Log::info(json_encode($lateComment));
        return $lateComment;
    }

    // public function CheckInWithLocation(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'longitude' => ['required', 'string'],
    //         'latitude' => ['required', 'string']
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->error(__trans('validation_failed'), $validator->errors());
    //     }

    //     $user_id = $request->user_id;

    //     $location_parameter = User::select('department_id', 'assigned_branch_id', 'longitude as user_longitude', 'latitude as user_latitude')->where('id', $user_id)->first();
    //     if (!$location_parameter) {
    //         $data = [
    //             'is_under_radius' => false,
    //             'user_radius' => null,
    //             'status' => 'Out Side Radius',
    //             'message' => 'User not found or location not set'
    //         ];
    //         return response()->success('', $data);
    //     }

    //     $homebranch = Department::where('id', $location_parameter->department_id)->first();
    //     if ($homebranch) {

    //         $company_lat = floatval($homebranch->latitude);
    //         $company_lng = floatval($homebranch->longitude);
    //         $user_lat = floatval($request->latitude);
    //         $user_lng = floatval($request->longitude);

    //         $unit = "M"; //M = miles
    //         $getdistance = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
    //         $getdistance = str_replace(',', '', $getdistance);
    //         $getdistance_float = (float)$getdistance;

    //         $radius = $homebranch->login_radius;

    //         if ($getdistance_float <= $radius) {
    //             $data = [
    //                 'is_under_radius' => true,
    //                 'user_radius' => $getdistance,
    //                 'status' => 'Under Radius',
    //                 'branch' => $homebranch,
    //                 'message' => 'You are under your company radius'
    //             ];
    //             return response()->success('', $data);
    //         }
    //     }

    //     $branchIds = explode(',', $location_parameter->assigned_branch_id);
    //     $branches = Department::whereIn('id', $branchIds)->get();
    //     foreach ($branches as $branch) {

    //         $company_lat = floatval($branch->latitude);
    //         $company_lng = floatval($branch->longitude);
    //         $user_lat = floatval($request->latitude);
    //         $user_lng = floatval($request->longitude);

    //         $unit = "M"; //M = miles
    //         $getdistance = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
    //         $getdistance = str_replace(',', '', $getdistance);
    //         $getdistance_float = (float)$getdistance;

    //         if ($getdistance_float <= $branch->login_radius) {
    //             return response()->success('', [
    //                 'is_under_radius' => true,
    //                 'user_radius' => $getdistance,
    //                 'status' => 'Under Radius',
    //                 'branch' => $branch,
    //                 'message' => 'You are under your company radius'
    //             ]);
    //         }
    //     }

    //     $data = [
    //         'is_under_radius' => false,
    //         'user_radius' => $getdistance,
    //         'status' => 'Out Side Radius',
    //         'message' => 'You are outside login radius'
    //     ];
    //     return response()->success('', $data);
    // }
    public function CheckInWithLocation(Request $request)
    {
        Log::info('CheckInWithLocation started', ['request' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'longitude' => ['required', 'string'],
            'latitude'  => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed in CheckInWithLocation', ['errors' => $validator->errors()]);
            return response()->error(__trans('validation_failed'), $validator->errors());
        }

        $user_id = $request->user_id;
        Log::info('Fetching user location parameters', ['user_id' => $user_id]);

        $location_parameter = User::select('department_id', 'assigned_branch_id', 'longitude as user_longitude', 'latitude as user_latitude')
            ->where('id', $user_id)
            ->first();

        if (! $location_parameter) {
            Log::warning('User not found or location not set', ['user_id' => $user_id]);

            $data = [
                'is_under_radius' => false,
                'user_radius'     => null,
                'status'          => 'Out Side Radius',
                'message'         => 'User not found or location not set',
            ];
            Log::info('Returning failure response', ['response' => $data]);
            return response()->success('', $data);
        }

        Log::info('Checking home branch location', ['department_id' => $location_parameter->department_id]);

        $homebranch = Department::where('id', $location_parameter->department_id)->first();
        if ($homebranch) {
            Log::info('Home branch found', ['homebranch' => $homebranch]);

            $company_lat = floatval($homebranch->latitude);
            $company_lng = floatval($homebranch->longitude);
            $user_lat    = floatval($request->latitude);
            $user_lng    = floatval($request->longitude);

            $getdistance       = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
            $getdistance       = str_replace(',', '', $getdistance);
            $getdistance_float = (float) $getdistance;

            $radius = $homebranch->login_radius;

            Log::info('Calculated distance for home branch', [
                'distance' => $getdistance_float,
                'radius'   => $radius,
            ]);

            if ($getdistance_float <= $radius) {
                $data = [
                    'is_under_radius' => true,
                    'user_radius'     => $getdistance,
                    'status'          => 'Under Radius',
                    'branch'          => $homebranch,
                    'message'         => 'You are under your company radius',
                ];
                Cache::put('last_checkin_branch_' . $user_id, $homebranch->id, now()->addMinutes(1));
                Log::info('User is within home branch radius', ['response' => $data]);
                return response()->success('', $data);
            }
        } else {
            Log::info('No home branch found for user', ['user_id' => $user_id]);
        }

        Log::info('Checking assigned branches', ['assigned_branch_ids' => $location_parameter->assigned_branch_id]);

        $branchIds = explode(',', $location_parameter->assigned_branch_id);
        $branches  = Department::whereIn('id', $branchIds)->get();

        foreach ($branches as $branch) {
            Log::info('Checking branch distance', ['branch_id' => $branch->id]);

            $company_lat = floatval($branch->latitude);
            $company_lng = floatval($branch->longitude);
            $user_lat    = floatval($request->latitude);
            $user_lng    = floatval($request->longitude);

            $getdistance       = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
            $getdistance       = str_replace(',', '', $getdistance);
            $getdistance_float = (float) $getdistance;

            Log::info('Calculated distance for branch', [
                'branch_id' => $branch->id,
                'distance'  => $getdistance_float,
                'radius'    => $branch->login_radius,
            ]);

            if ($getdistance_float <= $branch->login_radius) {
                $data = [
                    'is_under_radius' => true,
                    'user_radius'     => $getdistance,
                    'status'          => 'Under Radius',
                    'branch'          => $branch,
                    'message'         => 'You are under your company radius',
                ];
                Cache::put('last_checkin_branch_' . $user_id, $branch->id, now()->addMinutes(1));

                Log::info('User is within assigned branch radius', ['response' => $data]);
                return response()->success('', $data);
            }
        }

        Log::warning('User is outside all branch radii', ['user_id' => $user_id, 'last_distance' => $getdistance]);

        $data = [
            'is_under_radius' => false,
            'user_radius'     => $getdistance,
            'status'          => 'Out Side Radius',
            'message'         => 'You are outside login radius',
        ];
        Log::info('Returning outside radius response', ['response' => $data]);

        return response()->success('', $data);
    }
}
