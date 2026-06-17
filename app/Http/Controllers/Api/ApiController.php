<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\allTypeOfTransaction;
use App\Models\ApiErrorLog;
use App\Models\Department;
use App\Models\LeaveApprovalSetting;
use App\Models\NotificationData;
use App\Models\PHLeaveReport;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeaveBalanceTransaction;
use App\Models\UserWorkDetail;
use App\Notifications\Leave\GenerateNotification;
use App\Services\FirebaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Api\Transformers\Leave\TypeListResource;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Enums\BreakinType;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Exports\Attendance as AttendanceExport;
use Modules\Document\Entities\DocumentRequest;
use Modules\Document\Enums\DocumentRequestStatus;
use Modules\GeneralRequest\Entities\GeneralRequest;
use Modules\GeneralRequest\Entities\GeneralRequestType;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Leave\Exports\LeaveReportExport;
use Modules\Leave\Rules\HalfDayLeave;
use Modules\Leave\View\Components\UserLeaveBalance;
use Modules\NotificationManager\Emails\LeaveRequestMail;
use Modules\NotificationManager\Entities\AlertRecipient;
use Modules\NotificationManager\Entities\EmailAlertLog;
use Modules\Shift\Entities\UsersShift;
use Str;

class ApiController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'leaves');
        $this->fcmService = $fcmService;
    }

    public function getEmployees(Request $request)
    {
        $query = User::query()
            ->notAdmin()
            ->select('users.id', 'users.name', 'users.email', 'users.employee_id', 'users.department_id', 'users.phone', 'users.profile_image as photo')
            ->whereNotNull('users.profile_image')
            ->where('users.status', '!=', 'in-active');
        if (isset($request->department_id) && ! empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }
        if (! empty($request->branch_code)) {
            $query->whereHas('department', function ($q) use ($request) {
                $q->where('code', $request->branch_code);
            });
        }
        if (! empty($request->email)) {
            $query->where(function ($q) use ($request) {
                $q->where('email', $request->email)
                    ->orWhere('employee_id', $request->email)
                    ->orWhere('phone', $request->email);
            });
        }
        $data = $query->get();
        foreach ($data as $user) {
            $latestCheckin = Checkin::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $latestBreakin = Breakin::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            $userShifts    = UsersShift::where('user_id', $user->id)
                ->whereDate('assigned_for_date', Carbon::today()->toDateString())
                ->orderBy('id', 'desc')
                ->get();

            $user->status       = 'checkout';
            $user->break_status = 'breakout';
            $user->datetime     = '';
            // $checkin->shift = count($userShifts) > 0 ? $userShifts : 'no shift';

            if ($latestCheckin) {
                $user->status   = $latestCheckin->type == 'in' ? 'checkin' : 'checkout';
                $user->datetime = $latestCheckin->date . ' ' . $latestCheckin->time;
            }
            if ($latestBreakin) {
                $user->break_status   = $latestBreakin->type == 'in' ? 'breakin' : 'breakout';
                $user->break_datetime = $latestBreakin->date . ' ' . $latestBreakin->time;
            }

            // Assigned Shifts
            $formattedShifts = [];
            foreach ($userShifts as $shift) {
                $shiftStart = $shift['shift_schedule_information']['shift_start'] ?? $shift['shift_schedule_information']['shift_start'];
                $shiftEnd   = $shift['shift_schedule_information']['shift_end'] ?? $shift['shift_schedule_information']['shift_end'];
                $shiftTitle = $shift['shift_schedule_information']['title'] ?? $shift['shift_schedule_information']['title'];

                $formattedShifts[] = [
                    'title'       => $shiftTitle,
                    'shift_start' => $shiftStart,
                    'shift_end'   => $shiftEnd,
                ];
            }
            $user->shift = $formattedShifts;
        }

        if ($data) {
            $response['success']  = true;
            $response['base_url'] = url('/');
            $response['data']     = $data;
        } else {
            $response['success'] = false;
            $response['data']    = [];
        }
        return response()->json($response);
    }

    // public function getDepartments()
    // {

    //     $data = Department::query()
    //         ->select('id', 'name', 'code', 'address')
    //         ->get();
    //     if ($data) {
    //         $response['success'] = true;
    //         $response['base_url'] = url('/');
    //         $response['data'] = $data;
    //     } else {
    //         $response['success'] = false;
    //         $response['data'] = [];
    //     }
    //     return response()->json($response);
    // }
    public function getDepartments(Request $request)
    {
        $query = Department::query()
            ->select('id', 'name', 'code', 'address');

        if (! empty($request->branch_code)) {
            $query->where('code', $request->branch_code);
        }

        $data = $query->get();

        return response()->json([
            'success'  => true,
            'base_url' => url('/'),
            'data'     => $data,
        ]);
    }

    // public function performCheckInCheckOut(Request $request){
    //     $checkuserExist = User::where([
    //         'id' => $request->user_id
    //     ])->exists();
    //     if(!$checkuserExist){
    //         $response['success'] = false;
    //         $response['message'] = __trans('user not found.');
    //         $response['data'] = [];
    //         return response()->json($response);
    //     }
    //     // AutoClockout Issue on Koisk APP 05-08-2024
    //     //  User::where('id',$request->user_id)->update([
    //     //      'longitude' => NULL,
    //     //      'latitude' => NULL
    //     //  ]);
    //     $checkinExist = Checkin::where([
    //         'date' => now()->toDateString(),
    //         'type' => 'in',
    //         'user_id' => $request->user_id
    //     ])->exists();

    //     $latestCheckOUT = Checkin::where([
    //         // 'date' => now()->toDateString(),
    //         'user_id' => $request->user_id,
    //     ])->orderBy('id', 'desc')->first();

    //     $lateComment = $this->getShiftDeduction($request->user_id);
    //     $shiftTime = date('H:i:s');
    //     $type = CheckinType::IN;
    //     if (count($lateComment) != 0) {
    //         $shiftTime = $lateComment['deductedTime'];
    //         $type = CheckinType::IN;
    //     }
    //     if (($latestCheckOUT && $latestCheckOUT->type == 'out') || !$latestCheckOUT) {
    //         $event = Checkin::create([
    //             'user_id' => $request->user_id,
    //             'date' => now()->toDateString(),
    //             'time' => $shiftTime,
    //             'type' => $type,
    //             'lateComment' => json_encode($lateComment),
    //             'face_attendance' => 1
    //         ]);
    //         $response['success'] = true;
    //         $response['message'] = __trans('check_in_created');
    //         $response['data'] = $event;
    //     } else {
    //         $latestCheckIN = Checkin::where([
    //             // 'date' => now()->toDateString(),
    //             'user_id' => $request->user_id,
    //         ])->orderBy('id', 'desc')->first();

    //         if ($latestCheckIN && $latestCheckIN->face_attendance ==1 && $latestCheckOUT->type == 'in' ) {
    //             $event = Checkin::create([
    //                 'user_id' => $request->user_id,
    //                 'date' => now()->toDateString(),
    //                 'time' => date('H:i:s'),
    //                 'type' => CheckinType::OUT,
    //                 'face_attendance' =>1
    //             ]);
    //             $response['success'] = true;
    //             $response['message'] = __trans('check_out_created');
    //             $response['data'] = $event;
    //         } else {
    //             // throw new Exception(__trans('you_already_have_clocked_out'));
    //             $response['success'] = false;
    //             $response['message'] = __trans('either_already_clockout_or_did_not_use_face_method');
    //             $response['data'] = [];
    //         }
    //     }
    //     return response()->json($response);

    //     // return $event;
    // }

    // public function performCheckInCheckOut(Request $request)
    // {
    //     Log::info('performCheckInCheckOut started', ['request' => $request->all()]);

    //     $checkuserExist = User::where([
    //         'id' => $request->user_id,
    //     ])->exists();
    //     Log::info('User existence check', ['user_id' => $request->user_id, 'exists' => $checkuserExist]);

    //     if (! $checkuserExist) {
    //         Log::warning('User not found', ['user_id' => $request->user_id]);
    //         $response['success'] = false;
    //         $response['message'] = __trans('user not found.');
    //         $response['data']    = [];
    //         return response()->json($response);
    //     }

    //     // AutoClockout Issue on Koisk APP 05-08-2024
    //     // User::where('id',$request->user_id)->update([
    //     //     'longitude' => NULL,
    //     //     'latitude' => NULL
    //     // ]);

    //     $checkinExist = Checkin::where([
    //         'date'    => now()->toDateString(),
    //         'type'    => 'in',
    //         'user_id' => $request->user_id,
    //     ])->exists();
    //     Log::info('Checkin exist check', ['checkinExist' => $checkinExist]);

    //     $latestCheckOUT = Checkin::where([
    //         'user_id' => $request->user_id,
    //     ])->orderBy('id', 'desc')->first();
    //     Log::info('Latest check record', ['latestCheckOUT' => $latestCheckOUT]);

    //     $lateComment = $this->getShiftDeduction($request->user_id);
    //     Log::info('Late comment (shift deduction)', ['lateComment' => $lateComment]);

    //     $shiftTime = date('H:i:s');
    //     $type      = CheckinType::IN;

    //     if (count($lateComment) != 0) {
    //         $shiftTime = $lateComment['deductedTime'];
    //         $type      = CheckinType::IN;
    //         Log::info('Shift deduction applied', ['shiftTime' => $shiftTime, 'type' => $type]);
    //     } else {
    //         Log::info('No shift deduction applied', ['shiftTime' => $shiftTime, 'type' => $type]);
    //     }

    //     if (($latestCheckOUT && $latestCheckOUT->type == 'out') || ! $latestCheckOUT) {
    //         Log::info('Creating Check-IN record');
    //         $branchId = Cache::get('last_checkin_branch_' . $request->user_id);
    //         Log::info('Retrieved branch ID from cache', ['branchId' => $branchId]);
    //         $event = Checkin::create([
    //             'user_id'         => $request->user_id,
    //             'date'            => now()->toDateString(),
    //             'time'            => $shiftTime,
    //             'type'            => $type,
    //             'lateComment'     => json_encode($lateComment),
    //             'face_attendance' => 1,
    //             'branch_id'       => $branchId,

    //         ]);
    //         // if today is holoday than add PH leave
    //         $date    = Carbon::now()->toDateString();
    //         $user_id = $request->user_id;
    //         $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
    //         if ($holiday) {
    //             $is_phleave = LeaveType::where('name', 'like', '%PH%')->first();
    //             if ($is_phleave) {
    //                 $isleaveBL = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user_id], ['leave_type_id', $is_phleave->id]])->first();
    //                 if ($isleaveBL) {
    //                     $settingadd = Setting::where('key', 'is_given_without_attend_PH')->first();
    //                     $isCheckin  = Attendance::where('user_id', $user_id)
    //                         ->whereIn('status', [
    //                             AttendanceStatus::Present,
    //                             AttendanceStatus::Late,
    //                             AttendanceStatus::EarlyOut,
    //                         ])
    //                         ->whereDate('date', $date)
    //                         ->latest()
    //                         ->first();
    //                     if ($isCheckin && $isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) {
    //                         $checkindata = 1;
    //                     } else {
    //                         $checkindata = 0;
    //                     }
    //                     if ($settingadd && $settingadd->value == 1) {
    //                         $checkindata = 1;
    //                     }
    //                     if ($checkindata == 1) {

    //                         $isaddinreport = PHLeaveReport::where([
    //                             'user_id' => $user_id,
    //                             'date'    => $date,
    //                         ])->first();
    //                         if (! $isaddinreport) {
    //                             $addinreport = PHLeaveReport::create([
    //                                 'user_id'       => $user_id,
    //                                 'holiday_id'    => $holiday->id,
    //                                 'leave_type_id' => $is_phleave->id,
    //                                 'date'          => $date,
    //                             ]);
    //                             $addtransaction = UserLeaveBalanceTransaction::create([
    //                                 'user_id'          => $user_id,
    //                                 'leave_type_id'    => $is_phleave->id,
    //                                 'transaction_type' => 'add',
    //                                 'old_balance'      => $isleaveBL->available,
    //                                 'update_balance'   => 1,
    //                                 'new_balance'      => ($isleaveBL->available + 1),
    //                                 'transaction_date' => $date,
    //                                 'description'      => 'Check in time, Add 1 PH Leave for holiday: ' . $holiday->detail,
    //                             ]);
    //                             $isleaveBL->update([
    //                                 'available'    => $isleaveBL->available + 1,
    //                                 'monthwiseDay' => $isleaveBL->monthwiseDay + 1,
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         // end
    //         // add cancel off leave
    //         $checkcanceloffleave = Setting::where('key', 'cancel_off_leave_module')->value('value');
    //         if ($checkcanceloffleave == true) {
    //             $usershift = UsersShift::where('user_id', $user_id)
    //                 ->whereDate('assigned_for_date', $date)
    //                 ->whereHas('shift_schedule_information.shift', function ($q) {
    //                     $q->where('is_weekend', 1);
    //                 })
    //                 ->with(['shift_schedule_information.shift'])
    //                 ->first();
    //             if ($usershift) {
    //                 $shiftdata = $usershift->shift_schedule_information->shift ?? null;
    //                 if ($shiftdata && $shiftdata->is_weekend == 1) {
    //                     $isCheckin = Attendance::where('user_id', $user_id)
    //                         ->whereIn('status', [
    //                             AttendanceStatus::Present,
    //                             AttendanceStatus::Late,
    //                             AttendanceStatus::EarlyOut,
    //                             AttendanceStatus::Weekend,
    //                         ])
    //                         ->whereDate('date', $date)
    //                         ->latest()
    //                         ->first();
    //                     if ($isCheckin) {
    //                         $cankeywords        = ['CANCEL OFF', 'cancel off', 'canceloff'];
    //                         $is_canceloff_leave = LeaveType::where(function ($query) use ($cankeywords) {
    //                             foreach ($cankeywords as $cankeyword) {
    //                                 $query->orWhere('name', 'like', "%$cankeyword%");
    //                             }
    //                         })->first();
    //                         $is_cancel_off = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user_id], ['leave_type_id', $is_canceloff_leave->id]])->first();
    //                         if ($is_canceloff_leave) {
    //                             if (($isCheckin->clock_in != '00:00:00' && $isCheckin->clock_in != null) || ($isCheckin->visit_in != null && $isCheckin->visit_in != '00:00:00')) {
    //                                 if ($is_cancel_off) {
    //                                     $addtransaction = UserLeaveBalanceTransaction::where([
    //                                         'user_id'          => $user_id,
    //                                         'leave_type_id'    => $is_canceloff_leave->id,
    //                                         'transaction_date' => $date,
    //                                     ])
    //                                         ->where(function ($q) {
    //                                             $q->where('description', 'LIKE', '%Add CANCEL OFF Leave%')
    //                                                 ->orWhere('description', 'LIKE', '%Add CANCEL OFF Leave From CheckIn Time%');
    //                                         })
    //                                         ->first();
    //                                     if (! $addtransaction) {
    //                                         $addtransaction = UserLeaveBalanceTransaction::create([
    //                                             'user_id'          => $user_id,
    //                                             'leave_type_id'    => $is_canceloff_leave->id,
    //                                             'transaction_type' => 'add',
    //                                             'old_balance'      => $is_cancel_off->available,
    //                                             'update_balance'   => 1,
    //                                             'new_balance'      => ($is_cancel_off->available + 1),
    //                                             'transaction_date' => $date,
    //                                             'description'      => 'Add CANCEL OFF Leave From CheckIn Time: ' . $is_canceloff_leave->name,
    //                                         ]);
    //                                         $is_cancel_off->update([
    //                                             'available'    => $is_cancel_off->available + 1,
    //                                             'monthwiseDay' => $is_cancel_off->monthwiseDay + 1,
    //                                         ]);
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //         // end
    //         Log::info('Check-IN created successfully', ['event' => $event]);

    //         $response['success'] = true;
    //         $response['message'] = __trans('check_in_created');
    //         $response['data']    = $event;
    //     } else {
    //         $latestCheckIN = Checkin::where([
    //             'user_id' => $request->user_id,
    //         ])->orderBy('id', 'desc')->first();
    //         Log::info('Latest check-IN fetched', ['latestCheckIN' => $latestCheckIN]);

    //         if ($latestCheckIN && $latestCheckIN->face_attendance == 1 && $latestCheckOUT->type == 'in') {
    //             Log::info('Creating Check-OUT record');
    //             $branchId = Cache::get('last_checkin_branch_' . $request->user_id);
    //             Log::info('Retrieved branch ID from cache', ['branchId' => $branchId]);
    //             $event = Checkin::create([
    //                 'user_id'         => $request->user_id,
    //                 'date'            => now()->toDateString(),
    //                 'time'            => date('H:i:s'),
    //                 'type'            => CheckinType::OUT,
    //                 'face_attendance' => 1,
    //                 'branch_id'       => $branchId,

    //             ]);
    //             Log::info('Check-OUT created successfully', ['event' => $event]);

    //             $response['success'] = true;
    //             $response['message'] = __trans('check_out_created');
    //             $response['data']    = $event;
    //         } else {
    //             Log::warning('Invalid check-OUT attempt', [
    //                 'latestCheckIN'  => $latestCheckIN,
    //                 'latestCheckOUT' => $latestCheckOUT,
    //             ]);

    //             $response['success'] = false;
    //             $response['message'] = __trans('either_already_clockout_or_did_not_use_face_method');
    //             $response['data']    = [];
    //         }
    //     }

    //     Log::info('performCheckInCheckOut finished', ['response' => $response]);
    //     return response()->json($response);

    //     // return $event;
    // }
    public function performCheckInCheckOut(Request $request)
    {
        Log::info('performCheckInCheckOut started', ['request' => $request->all()]);

        $user = User::find($request->user_id);

        if (! $user) {
            Log::warning('User not found', ['user_id' => $request->user_id]);

            return response()->json([
                'success' => false,
                'message' => __trans('user not found.'),
                'data'    => [],
            ]);
        }

        Log::info('User exists', ['user_id' => $request->user_id]);

        // Get latest check record
        $latestRecord = Checkin::where('user_id', $request->user_id)
            ->latest('id')
            ->first();

        Log::info('Latest check record', ['latestRecord' => $latestRecord]);

        $lateComment = $this->getShiftDeduction($request->user_id);
        Log::info('Late comment (shift deduction)', ['lateComment' => $lateComment]);

        $shiftTime = date('H:i:s');

        if (! empty($lateComment)) {
            $shiftTime = $lateComment['deductedTime'];
        }

        // ------------------------------------------------
        // CHECK IN
        // ------------------------------------------------
        if (! $latestRecord || $latestRecord->type == 'out') {

            Log::info('Creating Check-IN record');

            $branchId = Cache::get('last_checkin_branch_' . $request->user_id);

            $event = Checkin::create([
                'user_id'         => $request->user_id,
                'date'            => now()->toDateString(),
                'time'            => $shiftTime,
                'type'            => CheckinType::IN,
                'lateComment'     => json_encode($lateComment),
                'face_attendance' => 1,
                'branch_id'       => $branchId,
            ]);

            // -----------------------------
            // HOLIDAY PH LEAVE LOGIC
            // -----------------------------

            $date    = Carbon::now()->toDateString();
            $user_id = $request->user_id;

            $holiday = Holiday::whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();

            if ($holiday) {

                $phLeave = LeaveType::where('name', 'like', '%PH%')->first();

                if ($phLeave) {

                    $leaveBalance = LeaveBalance::where([
                        ['year', Carbon::now()->year],
                        ['user_id', $user_id],
                        ['leave_type_id', $phLeave->id],
                    ])->first();

                    if ($leaveBalance) {

                        $alreadyAdded = PHLeaveReport::where([
                            'user_id' => $user_id,
                            'date'    => $date,
                        ])->exists();

                        if (! $alreadyAdded) {

                            PHLeaveReport::create([
                                'user_id'       => $user_id,
                                'holiday_id'    => $holiday->id,
                                'leave_type_id' => $phLeave->id,
                                'date'          => $date,
                            ]);

                            UserLeaveBalanceTransaction::create([
                                'user_id'          => $user_id,
                                'leave_type_id'    => $phLeave->id,
                                'transaction_type' => 'add',
                                'old_balance'      => $leaveBalance->available,
                                'update_balance'   => 1,
                                'new_balance'      => $leaveBalance->available + 1,
                                'transaction_date' => $date,
                                'description'      => 'Check in time, Add 1 PH Leave for holiday: ' . $holiday->detail,
                            ]);

                            $leaveBalance->increment('available');
                            $leaveBalance->increment('monthwiseDay');
                        }
                    }
                }
            }

            Log::info('Check-IN created successfully', ['event' => $event]);

            return response()->json([
                'success' => true,
                'message' => __trans('check_in_created'),
                'data'    => $event,
            ]);
        }

        // ------------------------------------------------
        // CHECK OUT
        // ------------------------------------------------

        if ($latestRecord->type == 'in') {

            Log::info('Creating Check-OUT record');

            $branchId = Cache::get('last_checkin_branch_' . $request->user_id);

            $event = Checkin::create([
                'user_id'         => $request->user_id,
                'date'            => now()->toDateString(),
                'time'            => date('H:i:s'),
                'type'            => CheckinType::OUT,
                'face_attendance' => 1,
                'branch_id'       => $branchId,
            ]);

            Log::info('Check-OUT created successfully', ['event' => $event]);

            return response()->json([
                'success' => true,
                'message' => __trans('check_out_created'),
                'data'    => $event,
            ]);
        }

        // ------------------------------------------------
        // INVALID CASE
        // ------------------------------------------------

        Log::warning('Invalid check attempt', ['latestRecord' => $latestRecord]);

        return response()->json([
            'success' => false,
            'message' => __trans('either_already_clockout_or_did_not_use_face_method'),
            'data'    => [],
        ]);
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

    // public function portalSettings()
    // {
    //     $settings = Setting::select('key', 'value')->whereIn('key', [
    //         'site_title',
    //         'site_email',
    //         'site_phone',
    //         'site_address',
    //         'site_support_email',
    //         'site_short_description',
    //         'logo',
    //         'favicon',
    //         'small_logo',
    //         'radius',
    //         'latitude',
    //         'longitude',
    //         'is_check_location_radius',
    //         'shouldPerformLivenessCheck',
    //         'branch_wise_login',
    //         'user_wise_login',
    //         'break_in_out',
    //     ])->get();

    //     $data = [];
    //     foreach ($settings as $setting) {
    //         $data[$setting->key] = $setting->value;
    //     }
    //     if ($data) {
    //         $response['success']  = true;
    //         $response['base_url'] = url('/');
    //         $response['data']     = $data;
    //     } else {
    //         $response['success'] = false;
    //         $response['data']    = [];
    //     }
    //     return response()->json($response);
    // }
    public function portalSettings()
    {
        $requiredKeys = [
            'site_title',
            'site_email',
            'site_phone',
            'site_address',
            'site_support_email',
            'site_short_description',
            'logo',
            'favicon',
            'small_logo',
            'radius',
            'latitude',
            'longitude',
            'is_check_location_radius',
            'shouldPerformLivenessCheck',
            'branch_wise_login',
            'user_wise_login',
            'break_in_out',
            'auto_face_scan',
            'auto_face_scan_with_list'
        ];

        $settings = Setting::whereIn('key', $requiredKeys)
            ->pluck('value', 'key')
            ->toArray();

        // Ensure all required keys exist
        $data = [];
        foreach ($requiredKeys as $key) {
            $data[$key] = $settings[$key] ?? null;
        }

        return response()->json([
            'success'  => true,
            'base_url' => url('/'),
            'data'     => $data,
        ]);
    }

    private function deductHoursFromTime($time, $hoursToDeduct)
    {
        // Convert the input time to a Carbon object
        $timeObj = Carbon::createFromFormat('H:i:s', $time);

        // Deduct the specified number of hours
        $deductedTimeObj = $timeObj->addHours($hoursToDeduct);

        // Format the deducted time as 'H:i:s'
        $deductedTime = $deductedTimeObj->format('H:i:s');

        return $deductedTime;
    }

    public function logError(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'api_name' => 'required|string',
            'response' => 'required|array', // Expecting JSON format
            'status'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid data'], 422);
        }

        // Store the error in the database
        $api_error_log = ApiErrorLog::create([
            'api_name' => $request->input('api_name'),
            'response' => json_encode($request->input('response')), // Store response as JSON
            'status'   => $request->input('status'),
            'user_id'  => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Error logged successfully', 'data' => $api_error_log]);
    }

    public function getEmployeeList(Request $request)
    {
        $query = User::query()
            ->notAdmin()
            ->select('users.id', 'users.name', 'users.email', 'users.employee_id', 'users.phone', 'users.profile_image as photo')
        // ->whereNotNull('users.profile_image')
            ->where('users.status', '!=', 'in-active')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('users.name', 'Like', "%$request->search%")
                    ->orWhere('users.email', 'Like', "%$request->search%");
            });

        $data = $query->paginate();

        if ($data) {
            $response['success'] = true;
            $response['message'] = 'Employees list retrieved successfully';
            $response['data']    = $data;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function getDepartmentList(Request $request)
    {

        $data = Department::query()
            ->select('id', 'name', 'code', 'address')
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%")
                    ->orWhere('code', 'Like', "%$request->search%")
                    ->orWhere('address', 'Like', "%$request->search%");
            })->get();

        if ($data) {
            $response['success'] = true;
            $response['message'] = 'Department list retrieved successfully';
            $response['data']    = $data;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function expiryDocumentList(Request $request)
    {

        $data = getUserDocumentExpiredQuery()
            ->with(['user' => ['department']]);
        if ($request->search) {
            $data->where(function ($query) use ($request) {
                $query->where('type', 'like', '%' . $request->search . '%')
                    ->orWhere('expiry_date', 'like', '%' . $request->search . '%');
            });
        }
        $data = $data->get();
        if ($data) {
            $response['success'] = true;
            $response['message'] = 'Expiry document list retrieved successfully';
            $response['data']    = $data;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function leaveTypeList(Request $request)
    {
        $data = LeaveType::when($request->search, function ($query) use ($request) {
            return $query->where('name', 'like', '%' . $request->search . '%');
        })
            ->get();
        if ($data) {
            $response['success'] = true;
            $response['message'] = 'Leave type list retrieved successfully';
            $response['data']    = $data;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function userLeaveBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user         = User::find($request->user_id);
        $leaveBalance = TypeListResource::collection(LeaveType::get())->additional(['user_id' => $request->user_id ?? auth()->id()]);

        $response['success'] = true;
        $response['message'] = 'User leave balance retrieved successfully';
        $response['data']    = $leaveBalance;

        return response()->json($response);
    }

    public function addLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'days'         => 'required|integer',
            'is_paid'      => 'required',
            'is_recurring' => 'required',
            'no_of_leaves' => 'required|integer',
            'type'         => 'required|in:calendar,working',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $leaveType = LeaveType::create($request->only('name', 'days', 'is_paid', 'is_recurring', 'no_of_leaves', 'type'));

        if ($leaveType) {
            $response['success'] = true;
            $response['message'] = 'Leave type created successfully';
            $response['data']    = $leaveType;
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to create leave type';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function updateLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'           => 'required|integer|exists:leave_types,id',
            'name'         => 'required|string|max:255',
            'days'         => 'required|integer',
            'is_paid'      => 'required',
            'is_recurring' => 'required',
            'no_of_leaves' => 'required|integer',
            'type'         => 'required|in:calendar,working',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $leaveType = LeaveType::find($request->id);
        if (! $leaveType) {
            return response()->json(['error' => 'Leave type not found'], 404);
        }

        $leaveType->update($request->only('name', 'days', 'is_paid', 'is_recurring', 'no_of_leaves', 'type'));

        if ($leaveType) {
            $response['success'] = true;
            $response['message'] = 'Leave type updated successfully';
            $response['data']    = $leaveType;
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to update leave type';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function deleteLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:leave_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $leaveType = LeaveType::find($request->id);
        if (! $leaveType) {
            return response()->json(['error' => 'Leave type not found'], 404);
        }

        $leaveType->delete();

        $response['success'] = true;
        $response['message'] = 'Leave type deleted successfully';
        $response['data']    = null;

        return response()->json($response);
    }

    public function leaveList(Request $request)
    {
        $allleave = Leave::with('type', 'user')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                })->orWhereHas('type', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                })->orWhere('status', 'like', "%{$request->search}%");
            });

        if ($request->start_date && $request->end_date) {
            $allleave->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            });
        }

        $allleave = $allleave->latest()->paginate(10);

        if ($allleave->count() > 0) {
            $response['success'] = true;
            $response['message'] = 'Leave list retrieved successfully';
            $response['data']    = $allleave;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    // public function leaveDetails(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required|integer|exists:leaves,id',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 422);
    //     }

    //     $leave = Leave::with('type', 'user')->find($request->id);
    //     if (! $leave) {
    //         return response()->json(['error' => 'Leave not found'], 404);
    //     }

    //     $response['success'] = true;
    //     $response['message'] = 'Leave details retrieved successfully';
    //     $response['data']    = $leave;

    //     return response()->json($response);
    // }
    public function leaveDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:leaves,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $leave = Leave::with(['type', 'user.roles', 'user.workDetail'])->find($request->id);

        if (! $leave) {
            return response()->json(['error' => 'Leave not found'], 404);
        }

        $user = $leave->user;

        /** ---------------- CURRENT LEVEL ---------------- */
        $currentLevel = $leave->approval_status ?? 0;

        /** ---------------- TOTAL LEVEL ---------------- */
        $leaveUserRoleId = $user->roles->first()?->id;

        $totalLevel = LeaveApprovalSetting::where('role_id', $leaveUserRoleId)
            ->value('level') ?? 1;

        /** First-level override */
        if (
            isset($user->workDetail) &&
            $user->workDetail->approved_first_level &&
            in_array(
                auth()->id(),
                json_decode($user->workDetail->report_to_ids ?? '[]', true)
            )
        ) {
            $totalLevel = 1;
        }

        /** ---------------- AUTH USER LEVEL ---------------- */
        $authUserLevel = null;
        $canApprove    = false;

        if (
            auth()->user()->hasRole(User::ROLE_ADMIN) ||
            auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)
        ) {
            $authUserLevel = 'ADMIN';
            $canApprove    = true;
        } else {
            $approverChain = $this->getReportingChain($user->id, $totalLevel);

            foreach ($approverChain as $level => $approvers) {
                if (in_array(auth()->id(), (array) $approvers)) {
                    $authUserLevel = $level + 1; // human readable
                    break;
                }
            }

            $allowedApprovers = $approverChain[$currentLevel] ?? [];
            $canApprove       = in_array(auth()->id(), (array) $allowedApprovers);
        }

        /** ---------------- RESPONSE ---------------- */
        // return response()->json([
        //     'success' => true,
        //     'message' => 'Leave details retrieved successfully',
        //     'data'    => [
        //         'leave'           => $leave,
        //         'total_level'     => $totalLevel,
        //         'current_level'   => $currentLevel,
        //         'auth_user_level' => $authUserLevel,
        //         'can_approve'     => $canApprove,
        //     ],
        // ]);
        return response()->json([
            'success' => true,
            'message' => 'Leave details retrieved successfully',
            'data'    => array_merge(
                $leave->toArray(),
                [
                    'total_level'     => $totalLevel,
                    'current_level'   => $currentLevel,
                    'auth_user_level' => $authUserLevel,
                    'can_approve'     => $canApprove,
                ]
            ),
        ]);

    }

    public function addLeave(Request $request)
    {
        // canPerform('Create Leave');
        $today     = now()->startOfDay();
        $validator = Validator::make($request->all(), [
            'user_id'       => ['required', 'exists:users,id'],
            'reason'        => ['required', 'string'],
            'start_date'    => ['required', 'date_format:Y-m-d'],
            'end_date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'leave_type_id' => [
                // 'required', 'exists:leave_types,id', new LeaveAllowed
                'required',
                'exists:leave_types,id',
            ],
            'is_half_day'   => [new HalfDayLeave],
            'document'      => ['nullable', 'mimes:doc,docx,pdf,jpg,jpeg,png'],
            'status'        => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->all();
        try {

            $employee = User::find($request->user_id);
            if ($employee) {
                if ($request->hasFile('document')) {
                    $data['file_path'] = $this->upload($request->document, 'uploads/leave-documents');
                }
                $is_half = 0;
                if ($request->is_half_day) {
                    $is_half = 1;
                }
                $probationDate = UserWorkDetail::where('user_id', $employee->id)->first();
                $checkpro      = Setting::where('key', 'leave_probation_module')->value('value');
                if ($checkpro == false) {
                    if ($probationDate->probation_end_date >= now()->toDateString()) {
                        $response = getErrorResponse(__trans('employee_leave_can_not_add_on_probation'));
                        return response()->json($response);
                    }
                }
                $leaveSetting = Setting::where('key', 'allow_negative_leave')->first();
                if ($leaveSetting && ($leaveSetting->value == 0)) {
                    $leaveController = app()->make(\Modules\Leave\Http\Controllers\LeaveController::class);
                    $count           = $leaveController->checkTotalLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $data['user_id'], $is_half);
                    if ($count != 1) {
                        return $response = getErrorResponse($count);
                    }
                }
                $employee->leaves()->create($data);
                $get      = $this->fcmService->sendFcmMessage($employee->ftoken, 'Leave Added', 'Leave created by admin', 14);
                $userData = [
                    'id'      => $employee->id,
                    'name'    => $employee->name,
                    'email'   => $employee->email,
                    'message' => 'Generated a Leave Request for ' . $request->start_date,
                ];
                /* Send Email Notifications which set by admin */
                // send notification manager
                $managers = User::permission('Leave Request Manager Access')->where('id', '!=', $employee->id)->get();
                foreach ($managers as $manager) {
                    if ($manager->ftoken) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Leave Added', 'Leave created', 14);
                    }
                }
                //end
                $this->emailNotification($userData);

                $response['success'] = true;
                $response['message'] = 'Leave Request created successfully';
                $response['data']    = $data;
            } else {
                $response = getErrorResponse(__trans('employee_is_not_available_currently'));
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function updateLeave(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'            => ['required', 'exists:leaves,id'],
            'user_id'       => ['required', 'exists:users,id'],
            'reason'        => ['required', 'string'],
            'start_date'    => ['required', 'date_format:Y-m-d'],
            'end_date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'leave_type_id' => [
                // 'required', 'exists:leave_types,id', new LeaveAllowed
                'required',
                'exists:leave_types,id',
            ],
            'is_half_day'   => [new HalfDayLeave],
            'document'      => ['nullable', 'mimes:doc,docx,pdf,jpg,jpeg,png'],
            'status'        => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data  = $request->all();
        $leave = Leave::findOrFail($request->id);

        if ($leave->status->value == LeaveStatus::Approved->value) {
            return $response = getErrorResponse(__trans('can not update approved leave'));
        }

        try {
            if ($request->hasFile('document')) {
                $data['file_path'] = $this->upload($request->document, 'uploads/leave-documents', $leave->file_path);
            }
            $is_half = 0;
            if ($request->is_half_day == 1) {
                $is_half = 1;
            }
            $leaveSetting = Setting::where('key', 'allow_negative_leave')->first();
            if ($leaveSetting && $leaveSetting->value == 0) {
                if ($leave->status->value == LeaveStatus::Approved->value) {
                    $count = $this->checkTotalApprovedLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $leave->user_id, $is_half, $leave);
                } else {
                    $leaveController = app()->make(\Modules\Leave\Http\Controllers\LeaveController::class);
                    $count           = $leaveController->checkTotalLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $leave->user_id, $is_half);
                }
                if ($count != 1) {
                    return $response = getErrorResponse($count);
                }
            }
            if ($leave->status->value == LeaveStatus::Approved->value) {
                $count = $this->updateApprovedLeaveDays($request->leave_type_id, $request->start_date, $request->end_date, $leave->user_id, $is_half, $leave);
                if ($count != 1) {
                    return $response = getErrorResponse($count);
                }
            }
            $data['is_half_day'] = $request->is_half_day;
            $leave->update($data);

            $response         = getSuccessResponse(createFlashMessage('Leave Request', 'updated'));
            $response['data'] = $data;
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    protected function checkTotalApprovedLeaveDay($id, $start_date, $end_date, $user_id, $is_half, $leave)
    {
        $type    = LeaveType::find($id);
        $balance = LeaveBalance::where([
            'user_id'       => $user_id,
            'year'          => date('Y'),
            'leave_type_id' => $type->id,
        ])->first();
        $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');

        if ($balance) {
            $addapproveday = $leave->total_leave_days;
            if ($checkmonthwise == 1) {
                $balance = $balance->monthwiseDay + $addapproveday;
            } else {
                $balance = $balance->available + $addapproveday;
            }
        } else {
            $balance = 0;
        }

        $startDate = Carbon::parse($start_date);
        $endDate   = Carbon::parse($end_date);

        // Get the total number of days
        $currentLeaveDays = $startDate->diffInDays($endDate) + 1;
        if ($is_half == 1) {
            $currentLeaveDays = 0.5;
        }

        if ($currentLeaveDays > $balance) {
            return $message = __trans('You have reached to the maximum limit of sending leave request. Please direct contact with HR regarding you leave.');
        }
        return $message = 1;
    }

    protected function updateApprovedLeaveDays($id, $start_date, $end_date, $user_id, $is_half, $leave)
    {
        $type    = LeaveType::find($id);
        $balance = LeaveBalance::where([
            'user_id'       => $user_id,
            'year'          => date('Y'),
            'leave_type_id' => $type->id,
        ])->first();

        $startDate        = Carbon::parse($start_date);
        $endDate          = Carbon::parse($end_date);
        $checkmonthwise   = Setting::where('key', 'is_month_wise_show_leave')->value('value');
        $currentLeaveDays = $startDate->diffInDays($endDate) + 1;

        if ($balance) {
            $addapproveday = $leave->total_leave_days;

            $balance->available    = ($balance->available + $addapproveday);
            $balance->monthwiseDay = ($balance->monthwiseDay + $addapproveday);
            $balance->save();

            $addtransaction = UserLeaveBalanceTransaction::create([
                'user_id'          => $leave->user_id,
                'leave_type_id'    => $leave->leave_type_id,
                'transaction_type' => 'remove',
                'old_balance'      => $balance->available,
                'update_balance'   => $currentLeaveDays,
                'new_balance'      => ($balance->available - $currentLeaveDays),
                'transaction_date' => Carbon::now()->toDateString(),
                'description'      => 'Leave update from approved leave edit entry : ' . $leave->id,
            ]);

            $balance->available    = ($balance->available - $currentLeaveDays);
            $balance->monthwiseDay = ($balance->monthwiseDay - $currentLeaveDays);
            $balance->save();
        } else {
            return $message = __trans('No leave balance found for this user.');
        }
        return $message = 1;
    }

    public function deleteLeave(Request $request)
    {
        $leave = Leave::findOrFail($request->id);

        $balance = LeaveBalance::where(
            [
                'user_id'       => $leave->user_id,
                'year'          => date('Y'),
                'leave_type_id' => $leave->leave_type_id,
            ],
        )->first();

        if ($balance) {
            $addtransaction = UserLeaveBalanceTransaction::create([
                'user_id'          => $leave->user_id,
                'leave_type_id'    => $leave->leave_type_id,
                'transaction_type' => 'add',
                'old_balance'      => $balance->available,
                'update_balance'   => $leave->total_leave_days,
                'new_balance'      => ($balance->available + $leave->total_leave_days),
                'transaction_date' => Carbon::now()->toDateString(),
                'description'      => 'Delete Leave from app side ' . $leave->id,
            ]);

            $balance->available    = ($balance->available + $leave->total_leave_days);
            $balance->monthwiseDay = ($balance->monthwiseDay + $leave->total_leave_days);
            $balance->save();
        }
        $leave->delete();

        $response         = getSuccessResponse(createFlashMessage('Leave Request', 'deleted'));
        $response['data'] = $leave;
        return response()->json($response);
    }

    public function emailNotification($userData)
    {
        $alertRecipients = AlertRecipient::with('user')->where('alert_status', 1)->get();

        foreach ($alertRecipients as $alertRecipient) {
            $userEmail = $alertRecipient->user->email;
            //Mail::to($userEmail)->send(new LeaveRequestMail($userData));
            try {
                Mail::to($userEmail)->send(new LeaveRequestMail($userData));
                EmailAlertLog::create([
                    'email'      => $userEmail,
                    'status'     => 'success',
                    'alert_type' => 'Leave Request',
                    'message'    => 'Email sent successfully.',
                ]);
            } catch (Exception $e) {
                EmailAlertLog::create([
                    'email'   => $userEmail,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    public function leaveExport($leave_id)
    {
        $leave = Leave::find($leave_id);
        if ($leave->status->value == LeaveStatus::Approved->value || $leave->status->value == LeaveStatus::Rejected->value) {

            $leaveTypes = LeaveType::get();
            $balance    = LeaveBalance::where([
                'user_id' => $leave->user_id,
                'year'    => date('Y'),
            ])->get();

            $pdf = Pdf::loadView('leave::leave.leavePDF', [
                'leave'      => $leave,
                'leaveTypes' => $leaveTypes,
                'balance'    => $balance,
            ])->setPaper('tabloid', 'landscape');

            return $pdf->download('leave' . date('Y-m-d') . '.pdf');
        }
    }

    public function leaveListExport(Request $request)
    {

        return Excel::download(new LeaveReportExport($request), 'leave_report_list_' . time() . '.xlsx');
    }

    // public function leaveApproveReject(Request $request)
    // {
    //     Log::info("leaveApproveReject user_id:-".auth()->id(),$request->all());
    //             dd("here");

    //     $validator = Validator::make($request->all(), [
    //         'leave_id' => 'required|integer|exists:leaves,id',
    //         'action' => 'required|in:1,2', // 1 for approve, 2 for reject
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 422);
    //     }

    //     $message = ($request->action == 1) ? 'Approved' : 'Rejected';
    //     $action = ($request->action == 1) ? 'approve' : 'reject';

    //     $leave = Leave::find($request->leave_id);
    //     if ($leave && $leave->status->value == LeaveStatus::Pending->value) {
    //         $user = User::find($leave->user_id);
    //         if ($user) {
    //             if ($action == 'approve') {
    //                 $leaveController = app()->make(\Modules\Leave\Http\Controllers\LeaveController::class);
    //                 $actiondata = $leaveController->approve($leave);

    //                 if ($actiondata['success'] == true) {

    //                     $response['success'] = true;
    //                     $response['message'] = $actiondata['message'];
    //                 } else {
    //                     $response['success'] = false;
    //                     $response['message'] = __trans('you_dont_have_enough_leaves');
    //                     $response['data'] = [];
    //                 }
    //             } else {
    //                 $data = [
    //                     'remark' => $request->remark,
    //                     'status' => LeaveStatus::Rejected->value
    //                 ];

    //                 $leave->update($data);
    //                 $admin = User::whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN])->first();
    //                 $userData = [
    //                     'id' => $user->id,
    //                     'name' => $user->name,
    //                     'email' => $user->email,
    //                     'message' => 'Your ' . $leave->start_date . ' leave is rejected',
    //                     'route' => route('backend.leaves.show', $leave->id),
    //                     // Add any other user data you want to pass...
    //                 ];
    //                 $user->notify(new GenerateNotification($userData, $admin->id));
    //                 $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Leave Rejected', $userData['message'], 14);
    //                 $response = getSuccessResponse(createFlashMessage('Leave', 'rejected'));
    //             }
    //         } else {
    //             $response = getErrorResponse(__trans('user_is_not_available_currently'));
    //         }
    //     } else {
    //         $response = getErrorResponse(__trans('alredy_leave_status_was_updated'));
    //     }

    //     $response['data'] = $leave;

    //     return response()->json($response);
    // }
    public function leaveApproveReject(Request $request)
    {
        Log::info("leaveApproveReject user_id:-" . auth()->id(), $request->all());

        $validator = Validator::make($request->all(), [
            'leave_id' => 'required|integer|exists:leaves,id',
            'action'   => 'required|in:1,2', // 1 approve, 2 reject
            'remark'   => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $message = $request->action == 1 ? 'Approved' : 'Rejected';
        $action  = $request->action == 1 ? 'approve' : 'reject';

        $leave = Leave::find($request->leave_id);

        if (! $leave || $leave->status->value !== LeaveStatus::Pending->value) {
            return response()->json(
                getErrorResponse(__trans('alredy_leave_status_was_updated'))
            );
        }

        $user = User::with(['roles', 'workDetail'])->find($leave->user_id);
        if (! $user) {
            return response()->json(
                getErrorResponse(__trans('user_is_not_available_currently'))
            );
        }

        /** ---------------- CURRENT LEVEL ---------------- */
        $currentLevel = $leave->approval_status ?? 0;
        $nextLevel    = $currentLevel + 1;

        /** ---------------- ADMIN / SUPER ADMIN ---------------- */
        if (
            auth()->user()->hasRole(User::ROLE_ADMIN) ||
            auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)
        ) {
            $leave->approval_status = null;
            $leaveController        = app(\Modules\Leave\Http\Controllers\LeaveController::class);
            $response               = $leaveController->$action($leave);
            $response['data']       = $leave;

            return response()->json($response);
        }

        /** ---------------- APPROVAL SETTINGS ---------------- */
        $leaveUserRoleId = $user->roles->first()?->id;

        $totalLevel = LeaveApprovalSetting::where('role_id', $leaveUserRoleId)
            ->value('level') ?? 1;

        /** First level direct reporting override */
        if (
            isset($user->workDetail) &&
            $user->workDetail->approved_first_level &&
            in_array(
                auth()->id(),
                json_decode($user->workDetail->report_to_ids ?? '[]', true)
            )
        ) {
            $totalLevel = 1;
        }

        /** ---------------- CHECK AUTHORIZATION ---------------- */
        $approverChain    = $this->getReportingChain($user->id, $totalLevel);
        $allowedApprovers = $approverChain[$currentLevel] ?? [];

        if (! in_array(auth()->id(), (array) $allowedApprovers)) {
            return response()->json(
                getErrorResponse(__trans('you_are_not_authorized_to_approve'))
            );
        }

        /** ---------------- REJECT ---------------- */
        if ($action === 'reject') {
            $leave->update([
                'remark' => $request->remark,
                'status' => LeaveStatus::Rejected->value,
            ]);

            $admin = User::whereIn('name', [
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])->first();

            $notifyData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Your ' . $leave->start_date . ' leave is rejected',
                'route'   => route('backend.leaves.show', $leave->id),
            ];

            $user->notify(new GenerateNotification($notifyData, $admin->id));

            if (! empty($user->ftoken)) {
                $this->fcmService->sendFcmMessage(
                    $user->ftoken,
                    'Leave Rejected',
                    $notifyData['message'],
                    14
                );
            }

            return response()->json(
                getSuccessResponse(createFlashMessage('Leave', 'rejected'))
            );
        }

        /** ---------------- APPROVE ---------------- */
        if ($nextLevel == $totalLevel) {
            // FINAL APPROVAL
            $leave->approval_status = $nextLevel;
            $leaveController        = app(\Modules\Leave\Http\Controllers\LeaveController::class);
            $response               = $leaveController->approve($leave);
        } else {
            // PARTIAL APPROVAL
            $leave->approval_status = $nextLevel;
            $leave->status          = LeaveStatus::Pending->value;
            $leave->save();

            $response = [
                'success' => true,
                'message' => createFlashMessage('Leave', $nextLevel . ' Level Approved'),
                // 'redirect' => route('backend.leaves.show', $leave->id),
            ];

            $nextApprovers = $approverChain[$nextLevel] ?? [];

            if (! empty($nextApprovers)) {
                $approvers = User::whereIn('id', (array) $nextApprovers)->get();

                foreach ($approvers as $approver) {
                    $notifyData = [
                        'id'      => $approver->id,
                        'name'    => $approver->name,
                        'email'   => $approver->email,
                        'message' => $user->name . ' has a leave request pending for your approval',
                        'route'   => route('backend.leaves.show', $leave->id),
                    ];

                    // DB / bell notification
                    $approver->notify(
                        new GenerateNotification($notifyData, auth()->id())
                    );

                    // FCM notification
                    if (! empty($approver->ftoken)) {
                        $this->fcmService->sendFcmMessage(
                            $approver->ftoken,
                            'Next Level Leave Approval Pending',
                            $notifyData['message'],
                            14
                        );
                    }
                }
            }
        }

        $response['data'] = $leave;
        return response()->json($response);
    }

    public function listofGeneralRequestType(Request $request)
    {

        $data = GeneralRequestType::when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', "%{$request->search}%");
        })->select(['id', 'name'])->paginate(10);

        if ($data->count() > 0) {
            $response['success'] = true;
            $response['message'] = 'General Request Type list retrieved successfully';
            $response['data']    = $data;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function GeneralRequestTypeStore(Request $request)
    {
        // canPerform('Create General Request');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:general_request_types,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $generalType      = GeneralRequestType::create($request->all());
            $response         = getSuccessResponse(createFlashMessage('General Request', 'created'));
            $response['data'] = $generalType;
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function GeneralRequestTypeUpdate(Request $request)
    {
        // canPerform('Edit General Request');
        $validator = Validator::make($request->all(), [
            'id'   => 'required|integer|exists:general_request_types,id',
            'name' => 'required|string|unique:general_request_types,name,' . $request->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $data = ['name' => $request->name];

            $general = GeneralRequestType::find($request->id);
            if ($general) {
                $general->update($data);
                $response         = getSuccessResponse(createFlashMessage('General Request', 'updated'));
                $response['data'] = $general;
            } else {
                $response['error'] = $e->getMessage();
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function GeneralRequestTypeDelete(Request $request)
    {
        // canPerform('Manage General Request');
        $response = getErrorResponse();
        try {
            $general = GeneralRequestType::find($request->id);
            $general->delete();
            $response         = getSuccessResponse(createFlashMessage('General Request', 'Deleted'));
            $response['data'] = $general;
        } catch (Exception $e) {
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "This service is already associated, cannot be removed.";
                $response['error']   = "This service is already associated, cannot be removed.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }

    public function listofGeneralRequest(Request $request)
    {

        $data = GeneralRequest::with('type', 'user')->orderBy('created_at', 'desc')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                })->orWhereHas('type', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                });
            })->paginate(10);

        if ($data->count() > 0) {
            $response['success'] = true;
            $response['message'] = 'General Request list retrieved successfully';
            $response['data']    = $data;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function addGeneralRequest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id'      => 'required',
            'general_type' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $generalRe = GeneralRequest::create([
                'user_id' => $request->user_id,
                'type_id' => $request->general_type,
                'date'    => date('Y-m-d', strtotime($request->date)),
                'note'    => $request->note,
            ]);

            $user     = User::find(auth()->id());
            $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Generated a General Request for ' . $user->name,
                'route'   => route('backend.apparel-request'),
            ];
            // $admin->notify(new GenerateNotification($userData, $admin->id));
            $admins = User::withoutGlobalScopes()
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', [
                        User::ROLE_ADMIN,
                        User::ROLE_SUPER_ADMIN,
                    ]);
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new GenerateNotification($userData, $admin->id));
            }
            // send notification manager
            $managers = User::permission('General Request Manager Access')->where('id', '!=', $user->id)->get();
            foreach ($managers as $manager) {
                if ($manager->ftoken) {
                    $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'General Request Added', 'General Request created', 24);
                }
            }
            if (auth()->user()->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage(auth()->user()->ftoken, 'General Request Added', "General Request Added", 24);
            }
            //end
            $response['success'] = true;
            $response['message'] = 'General request created successfully.';
            $response['data']    = $generalRe;
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Something went wrong while creating the general request.';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function updateGeneralRequest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'           => 'required|integer|exists:general_requests,id',
            'user_id'      => 'required',
            'general_type' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $generalRe = GeneralRequest::find($request->id);
            $generalRe->update([
                'user_id' => $request->user_id,
                'type_id' => $request->general_type,
                'date'    => date('Y-m-d', strtotime($request->date)),
                'note'    => $request->note,
            ]);

            $user     = User::find(auth()->id());
            $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Generated a General Request for ' . $user->name,
                'route'   => route('backend.apparel-request'),
            ];
            // $admin->notify(new GenerateNotification($userData, $admin->id));
            $admins = User::withoutGlobalScopes()
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', [
                        User::ROLE_ADMIN,
                        User::ROLE_SUPER_ADMIN,
                    ]);
                })
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new GenerateNotification($userData, $admin->id));
            }
            // send notification manager
            $managers = User::permission('General Request Manager Access')->where('id', '!=', $user->id)->get();
            foreach ($managers as $manager) {
                if ($manager->ftoken) {
                    $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'General Request Updated', 'General Request updated', 24);
                }
            }
            //end
            $response['success'] = true;
            $response['message'] = 'General request created successfully.';
            $response['data']    = $generalRe;
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Something went wrong while creating the general request.';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function deleteGeneralRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:general_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $generalRequest = GeneralRequest::find($request->id);
        if (! $generalRequest) {
            return response()->json(['error' => 'General Request not found'], 404);
        }

        $generalRequest->delete();

        $response['success'] = true;
        $response['message'] = 'General Request deleted successfully';
        $response['data']    = null;

        return response()->json($response);
    }

    public function GeneralRequestApproveReject(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer|exists:general_requests,id',
            'action' => 'required|in:1,2', // 1 for approve, 2 for reject
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $apparel = GeneralRequest::find($request->id);
        if ($apparel) {
            $action = ($request->action == 1) ? 'approve' : 'reject';
            if ($action == 'approve') {
                $addtransaction = allTypeOfTransaction::create([
                    'user_id'          => $apparel->user_id,
                    'transaction_type' => 'general_request',
                    'old_value'        => null,
                    'update_value'     => null,
                    'new_value'        => null,
                    'transaction_date' => Carbon::now(),
                    'description'      => 'Approved this ' . $apparel->id . ' general request by : ' . auth()->user()->name,
                ]);

                $apparel->update([
                    'status' => 1,
                ]);

                $user     = User::find($apparel->user_id);
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'General Request is approved by ' . $admin->name,
                    'route'   => route('backend.employee.my-apparel'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
                // send notification manager
                $managers = User::permission('General Request Manager Access')->where('id', '!=', $user->id)->get();
                foreach ($managers as $manager) {
                    if ($manager->ftoken) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'General Request is approved', 'General Request is approved', 24);
                    }
                }
                //end
                $response['success'] = true;
                $response['message'] = 'General Request has been approved successfully.';
                $response['data']    = $apparel;
            }

            if ($action == 'reject') {
                $addtransaction = allTypeOfTransaction::create([
                    'user_id'          => $apparel->user_id,
                    'transaction_type' => 'general_request',
                    'old_value'        => null,
                    'update_value'     => null,
                    'new_value'        => null,
                    'transaction_date' => Carbon::now(),
                    'description'      => 'Rejected this ' . $apparel->id . ' general request by : ' . auth()->user()->name,
                ]);

                $apparel->update([
                    'status' => 2,
                ]);

                $user     = User::find($apparel->user_id);
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'General Request was rejected by ' . $admin->name,
                    'route'   => route('backend.employee.my-apparel'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
                // send notification manager
                $managers = User::permission('General Request Manager Access')->where('id', '!=', $user->id)->get();
                foreach ($managers as $manager) {
                    if ($manager->ftoken) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'General Request was rejected', 'General Request was rejected', 24);
                    }
                }
                //end
                $response['success'] = true;
                $response['message'] = 'General Request has been rejected successfully.';
                $response['data']    = $apparel;
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }

        return response()->json($response);
    }

    public function documentlRequestList(Request $request)
    {
        $data = DocumentRequest::with(['type', 'user'])
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                })->orWhereHas('type', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->paginate(10);

        if ($data->count() > 0) {
            $response['success'] = true;
            $response['message'] = 'Document Request list retrieved successfully';
            $response['data']    = $data;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function documentlRequestDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:document_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $DocumentRequest = DocumentRequest::find($request->id);
        if ($DocumentRequest) {
            $response['success'] = true;
            $response['message'] = 'Document Request details successfully';
            $response['data']    = $DocumentRequest;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }

        return response()->json($response);
    }

    public function previewDocumentRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:document_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $documentRequest = DocumentRequest::find($request->id);
        if ($documentRequest) {
            $documentRequest->load(['user', 'type']);
            $template  = $documentRequest->type->template;
            $todayDate = \Carbon\Carbon::now()->format('d/m/Y');
            $template  = str_replace('[[today]]', $todayDate, $template);
            $html      = $documentRequest->parseHtml($template);

            return response()->json([
                'success' => true,
                'message' => 'Document Request preview generated successfully',
                'data'    => ['html' => $html, 'document_request' => $documentRequest],
            ]);
        } else {
            return response()->json(['error' => 'Document Request not found'], 404);
        }
    }

    public function generateDocumentRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:document_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $documentRequest = DocumentRequest::find($request->id);
        if ($documentRequest) {
            $documentRequest->load(['user', 'type']);
            $template  = $documentRequest->type->template;
            $todayDate = \Carbon\Carbon::now()->format('d/m/Y');
            $template  = str_replace('[[today]]', $todayDate, $template);

            $html = $documentRequest->parseHtml($template);
            $user = User::find($documentRequest->user_id);
            if ($user) {
                $documentRequest->generateDocumentPdf($html);
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Your Document Has Been Generated ',
                    'route'   => route('backend.employee.document-requests.index'),
                    // Add any other user data you want to pass...
                ];
                $user->notify(new GenerateNotification($userData, $admin->id));
                if ($user && $user->ftoken !== null) {
                    $response = $this->fcmService->sendFcmMessage($user->ftoken, 'Document Request', $userData['message'], 15);
                }
                // send notification manager
                $managers = User::permission('Document Request Manager Access')->where('id', '!=', $user->id)->get();
                foreach ($managers as $manager) {
                    if ($manager->ftoken) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Document Request is approved', 'Document Request is approved', 15);

                    }
                }
                //end
            }
            return response()->json([
                'success' => true,
                'message' => 'Document generated successfully',
                'data'    => $documentRequest,
            ]);
        } else {
            return response()->json(['error' => 'Document Request not found'], 404);
        }
    }

    public function DocumentRequestDownload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:document_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $documentRequest = DocumentRequest::find($request->id);

        return response()->download(public_path($documentRequest->file_path), $documentRequest->getFileName());
    }
    public function rejectDocumentRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer|exists:document_requests,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error'   => $validator->errors(),
            ], 422);
        }

        $documentRequest = DocumentRequest::find($request->id);

        if (! $documentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Document request not found',
                'error'   => 'Document request not found',
            ], 404);
        }

        // Check if user has manager permission
        $currentUser = auth()->user();
        if (! $currentUser->hasPermissionTo('Document Request Manager Access')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions',
                'error'   => 'You do not have permission to reject document requests',
            ], 403);
        }

        // Check if document request is in pending status
        if ($documentRequest->status !== DocumentRequestStatus::Pending) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document status',
                'error'   => 'Document request can only be rejected when in pending status',
            ], 400);
        }

        try {
            // Update document request status to rejected
            $documentRequest->status           = DocumentRequestStatus::Rejected;
            $documentRequest->rejection_reason = $request->reason ?? 'Rejected by manager';
            $documentRequest->rejected_by      = $currentUser->id;
            $documentRequest->rejected_at      = now();
            $documentRequest->save();

            // Notify the user who made the request
            $user = User::find($documentRequest->user_id);
            if ($user) {
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Your Document Request has been rejected',
                    'route'   => route('backend.employee.document-requests.index'),
                ];

                $admin = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $user->notify(new GenerateNotification($userData, $admin->id));

                // Send FCM notification if user has token
                if ($user->ftoken !== null) {
                    $this->fcmService->sendFcmMessage(
                        $user->ftoken,
                        'Document Request Rejected',
                        $userData['message'],
                        5
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Document request rejected successfully',
                'data'    => [
                    'id'               => $documentRequest->id,
                    'status'           => $documentRequest->status->value,
                    'rejection_reason' => $documentRequest->rejection_reason,
                    'rejected_by'      => $currentUser->name,
                    'rejected_at'      => $documentRequest->rejected_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject document request',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function attendancesList(Request $request)
    {
        $year  = $request->year ? $request->year : date('Y');
        $month = $request->month ? $request->month : date('m');
        $users = User::query()->where('status', User::STATUS_ACTIVE)->notAdmin()
            ->with(['attendances' => function ($query) use ($month, $year, $request) {
                $query->whereMonth('date', $month)->whereYear('date', $year);
            }])
            ->when($request->search, function ($query) use ($request) {
                return $query->where('users.name', 'Like', "%$request->search%")
                    ->orWhere('users.email', 'Like', "%$request->search%");
            })
            ->when($request->employee_id, function ($query) use ($request) {
                $query->where('id', $request->employee_id);
            })
            ->when($request->department_id, function ($query) use ($request) {
                $query->where('department_id', $request->department_id);
            });

        $users = $users->paginate(10);

        if ($users->count() > 0) {
            $response['success'] = true;
            $response['message'] = 'Attendance list retrieved successfully';
            $response['data']    = $users;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function attendancesDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:attendances,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $attendance = Attendance::where('id', $request->id)->with('user')->first();

        if ($attendance) {
            $response['success'] = true;
            $response['message'] = 'Attendance details retrieved successfully';
            $response['data']    = $attendance;
        } else {
            $response['success'] = false;
            $response['message'] = 'No data found!';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function attendanceReport(Request $request)
    {
        $attendance = User::query()->where('status', User::STATUS_ACTIVE)->notAdmin()
            ->withWhereHas('attendances', function ($query) use ($request) {
                if ($request->start_date && $request->end_date) {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                }
                $query->orderBy('date', 'desc');
            })
            ->when($request->employee_id, function ($query) use ($request) {
                $query->where('id', $request->employee_id);
            })
            ->when($request->department_id, function ($query) use ($request) {
                $query->where('department_id', $request->department_id);
            })
            ->paginate(10);

        if ($attendance->count() > 0) {
            $response['success'] = true;
            $response['message'] = 'Attendance retrieved successfully';
            $response['data']    = $attendance;
        } else {
            $response['success'] = false;
            $response['message'] = 'No attendance found for this user on this date';
            $response['data']    = [];
        }
        return response()->json($response);
    }

    public function exportAttendanceReport(Request $request)
    {
        return Excel::download(new AttendanceExport($request), 'attendance' . date('Y-m-d') . '.xlsx');
    }

    public function exportPDFAttendanceReport(Request $request)
    {
        // canPerform('Export Attendance');
        $attendanceExport = new AttendanceExport($request);
        $users            = $attendanceExport->query()->get();

        if ($request->start_date != '') {
            $period = new \DatePeriod(
                new \DateTime($request->start_date),
                new \DateInterval('P1D'),
                (new \DateTime($request->end_date))->modify('+1 day')
            );
        } else {
            $yeardata  = $request->year;
            $monthdata = $request->month;

            $startDate = Carbon::create($yeardata, $monthdata, 1)->startOfMonth()->toDateString();
            $endDate   = Carbon::create($yeardata, $monthdata, 1)->endOfMonth()->toDateString();

            $period = new \DatePeriod(
                new \DateTime($startDate),
                new \DateInterval('P1D'),
                (new \DateTime($endDate))->modify('+1 day')
            );
        }
        $pdf = Pdf::loadView('attendance::attendance.exportPDF', [
            'monthdata'   => $attendanceExport->monthdata,
            'departname'  => $attendanceExport->departname,
            'data'        => $users,
            'dateHeaders' => $period,
        ])->setPaper('tabloid', 'landscape');

        return $pdf->download('attendance' . date('Y-m-d') . '.pdf');
    }

    public function getManagerRolePermissions()
    {
        $managerRole = User::with('roles')->find(auth()->id());

        if ($managerRole) {
            $role = $managerRole->roles->first();
            return response()->json([
                'success' => true,
                'message' => 'Role permissions list retrieved successfully',
                'data'    => $role ? $role->permissions->pluck('name') : [],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data'    => [],
            ]);
        }
    }

    public function getAllNotification(Request $request)
    {

        //Sanket Mane: Added pagination parameters support
        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);

        $notificationList = NotificationData::where('receiver_id', auth()->id())->orderBy('created_at', 'desc')->paginate($perPage);

        if ($notificationList->isNotEmpty()) {
            //Sanket Mane: Updated response structure to match consistent format across all notification APIs
            return response()->json([
                'success' => true,
                'status'  => 200,          //Sanket Mane: Added status field for consistency
                'message' => 'List found', //Sanket Mane: Consistent message format
                                           //Sanket Mane: Data transformation to return clean structured array
                'data'    => $notificationList->map(function ($item) {
                    return [
                        'id'          => $item->id,
                        'enum'        => $item->enum,
                        'title'       => $item->title,
                        'message'     => $item->message,
                        'date'        => $item->date,
                        'time'        => $item->time,
                        'sender_id'   => $item->sender_id,
                        'receiver_id' => $item->receiver_id,
                        'status'      => $item->status, //Sanket Mane: Include status in getAllNotification response
                    ];
                }),
                //Sanket Mane: Added meta pagination object for consistency
                'meta'    => [
                    'current_page' => $notificationList->currentPage(),
                    'last_page'    => $notificationList->lastPage(),
                    'per_page'     => $notificationList->perPage(),
                    'total'        => $notificationList->total(),
                ],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'status'  => 200,
                'message' => 'List not found',
                'data'    => null,
            ]);
        }
    }

    public function getUnreadNotification(Request $request)
    {

        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);

        $notificationList = NotificationData::where([['status', 1], ['receiver_id', auth()->id()]])->orderBy('created_at', 'desc')->paginate(10);

        if ($notificationList->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification list retrieved successfully',
                'data'    => $notificationList,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No unread notifications found',
                'data'    => [],
            ]);
        }
    }

    public function markNotificationAsRead(Request $request)
    {
        $notificationIds = $request->input('ids');

        if (! $notificationIds) {
            return response()->json([
                'success' => false,
                'message' => 'No notification IDs provided.',
            ], 400);
        }

        $ids = is_array($notificationIds) ? $notificationIds : [$notificationIds];

        $updatedCount = NotificationData::where('receiver_id', auth()->id())
            ->whereIn('id', $ids)
            ->update(['status' => 2]);

        if ($updatedCount > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Notifications marked as read successfully.',
                'data'    => [],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No matching notifications found for the user.',
            'data'    => [],
        ]);
    }
    /**
     * Get user profile information
     * Mobile App API: /api/v1/profile
     */
    public function getUserProfile()
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'User not authenticated.',
                    'data'    => [],
                ]);
            }

            // Load user with relationships and optional profile
            $user->load(['department', 'designation', 'roles', 'workDetail', 'profile']);

            $profileData = [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'employee_id'      => $user->employee_id,
                'phone'            => $user->phone,
                'profile_image'    => $user->profile_image ? asset($user->profile_image) : null,
                'department'       => $user->department ? [
                    'id'   => $user->department->id,
                    'name' => $user->department->name,
                ] : null,
                'designation'      => $user->designation ? [
                    'id'   => $user->designation->id,
                    'name' => $user->designation->name,
                ] : null,
                'role'             => $user->roles->first() ? $user->roles->first()->name : null,
                'work_details'     => $user->workDetail ? [
                    'designation'  => $user->workDetail->designation,
                    'branch'       => $user->workDetail->branch,
                    'joining_date' => $user->workDetail?->joining_date ? $user->workDetail?->joining_date->toDateString() : null,
                ] : null,
                'personal_details' => $user->profile ? [
                    'gender'         => $user->profile->gender,
                    'personal_email' => $user->profile->personal_email,
                    'personal_phone' => $user->profile->personal_phone,
                    'date_of_birth'  => $user->profile->date_of_birth ? $user->profile->date_of_birth->toDateString() : null,
                    'country_id'     => $user->profile->country_id,
                    'martial_status' => $user->profile->martial_status,
                    'address'        => $user->profile->address,
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'User profile retrieved successfully.',
                'data'    => $profileData,
            ]);
        } catch (\Exception $e) {
            Log::error('Get user profile failed', [
                'user_id'       => auth()->id(),
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
                'request_route' => request()->route()->getName(),
                'request_url'   => request()->fullUrl(),
            ]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to retrieve user profile. Error: ' . $e->getMessage(),
                'data'    => [],
            ]);
        }
    }

    /**
     * Get user department information
     * Mobile App API: /api/v1/user/department
     */
    public function getUserDepartment()
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'status'  => 401,
                    'message' => 'User not authenticated.',
                    'data'    => [],
                ]);
            }

            $user->load('department');

            if (! $user->department) {
                return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'message' => 'User department not found.',
                    'data'    => [],
                ]);
            }

            $departmentData = [
                'id'                 => $user->department->id,
                'name'               => $user->department->name,
                'description'        => $user->department->description ?? '',
                'user_department_id' => $user->department_id,
            ];

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'User department retrieved successfully.',
                'data'    => $departmentData,
            ]);
        } catch (\Exception $e) {
            Log::error('Get user department failed', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Failed to retrieve user department.',
                'data'    => [],
            ]);
        }
    }

    public function getReportingChain($userId, $levels)
    {
        $currentLevelUserIds = [$userId];
        $allLevels           = [];

        for ($i = 0; $i < $levels; $i++) {
            $nextLevelUserIds = [];

            foreach ($currentLevelUserIds as $uid) {
                $reportToIdsJson = DB::table('user_work_details')
                    ->where('user_id', $uid)
                    ->value('report_to_ids');

                $reportToIds = json_decode($reportToIdsJson, true);

                if (is_array($reportToIds) && ! empty($reportToIds)) {
                    $nextLevelUserIds = array_merge($nextLevelUserIds, $reportToIds);
                }
            }

            $nextLevelUserIds = array_unique($nextLevelUserIds);

            if (empty($nextLevelUserIds)) {
                break;
            }

            $allLevels[]         = $nextLevelUserIds;
            $currentLevelUserIds = $nextLevelUserIds;
        }

        return $allLevels;
    }
    public function performBreakInBreakOut(Request $request)
    {
        $traceId = Str::uuid()->toString(); // Unique trace id per request

        Log::info('BreakInBreakOut: START', [
            'trace_id'        => $traceId,
            'request_payload' => $request->all(),
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent(),
            'timestamp'       => now()->toDateTimeString(),
        ]);

        try {

            $userId = $request->user_id;

            Log::info('BreakInBreakOut: Checking user existence', [
                'trace_id' => $traceId,
                'user_id'  => $userId,
            ]);

            // 1️⃣ Check user exists
            if (! User::where('id', $userId)->exists()) {

                Log::warning('BreakInBreakOut: User not found', [
                    'trace_id' => $traceId,
                    'user_id'  => $userId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => __trans('user not found'),
                    'data'    => [],
                ]);
            }

            // 2️⃣ Get latest check-in
            $latestCheck = Checkin::where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();

            Log::info('BreakInBreakOut: Latest Checkin fetched', [
                'trace_id'       => $traceId,
                'latest_checkin' => $latestCheck,
            ]);

            if (! $latestCheck || $latestCheck->type !== "in") {

                Log::warning('BreakInBreakOut: User not currently checked-in', [
                    'trace_id'            => $traceId,
                    'latest_checkin_type' => $latestCheck->type ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => __trans('user_not_checked_in'),
                    'data'    => [],
                ]);
            }

            // 3️⃣ Get latest break record
            $latestBreak = Breakin::where('user_id', $userId)
                ->whereDate('date', now()->toDateString())
                ->orderBy('id', 'desc')
                ->first();

            Log::info('BreakInBreakOut: Latest Break record', [
                'trace_id'     => $traceId,
                'latest_break' => $latestBreak,
            ]);

            // 4️⃣ Decision Logic
            if (! $latestBreak || $latestBreak->type === "out") {

                Log::info('BreakInBreakOut: Decided BREAK-IN', [
                    'trace_id' => $traceId,
                    'reason'   => ! $latestBreak ? 'No previous break today' : 'Last break was OUT',
                ]);

                $event = Breakin::create([
                    'user_id' => $userId,
                    'date'    => now()->toDateString(),
                    'time'    => now()->format('H:i:s'),
                    'type'    => BreakinType::IN,
                ]);

                Log::info('BreakInBreakOut: Break-IN created successfully', [
                    'trace_id'   => $traceId,
                    'event_id'   => $event->id,
                    'created_at' => $event->created_at,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => __trans('break_in_created'),
                    'data'    => $event,
                ]);
            }

            if ($latestBreak->type === "in") {

                Log::info('BreakInBreakOut: Decided BREAK-OUT', [
                    'trace_id' => $traceId,
                    'reason'   => 'Last break was IN',
                ]);

                $event = Breakin::create([
                    'user_id' => $userId,
                    'date'    => now()->toDateString(),
                    'time'    => now()->format('H:i:s'),
                    'type'    => BreakinType::OUT,
                ]);

                Log::info('BreakInBreakOut: Break-OUT created successfully', [
                    'trace_id'   => $traceId,
                    'event_id'   => $event->id,
                    'created_at' => $event->created_at,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => __trans('break_out_created'),
                    'data'    => $event,
                ]);
            }

            Log::error('BreakInBreakOut: Invalid state reached', [
                'trace_id'          => $traceId,
                'latest_break_type' => $latestBreak->type ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => __trans('invalid_break_operation'),
                'data'    => [],
            ]);

        } catch (\Exception $e) {

            Log::error('BreakInBreakOut: EXCEPTION OCCURRED', [
                'trace_id'      => $traceId,
                'error_message' => $e->getMessage(),
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data'    => [],
            ]);
        }
    }
}
