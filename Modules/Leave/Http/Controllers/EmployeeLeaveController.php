<?php
namespace Modules\Leave\Http\Controllers;

use App\Models\LeaveApprovalSetting;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserWorkDetail;
use App\Notifications\Leave\GenerateNotification;
use App\Services\FirebaseService;
use App\Traits\File;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Leave\Rules\HalfDayLeave;
use Modules\Leave\Rules\LeaveAllowed;
use Modules\NotificationManager\Emails\NotificationMail;
use Modules\NotificationManager\Entities\EmailAlertLog;
use Yajra\DataTables\Facades\DataTables;

class EmployeeLeaveController extends Controller
{
    use File;

    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'leaves');
        $this->fcmService = $fcmService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Leave::with('type')->my()->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status->getHtml();
                })
                ->addColumn('action', function ($row) {
                    $btn  = '';
                    $btn .= createActionButton(route('backend.employee.leaves.show', $row), 'View', 'btn-success', 'fa fa-eye');
                    if ($row->status->value == LeaveStatus::Pending->value) {
                        if (hasPermission('Edit Leave')) {
                            $btn = createActionButton(route('backend.employee.leaves.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                        }
                        if (hasPermission('Delete Leave')) {
                            $btn .= createActionButton(route('backend.employee.leaves.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                        }
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        $types = LeaveType::get(['id', 'name']);
        return view('leave::employee.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        canPerform('Create Leave');
        if (userCanApplyLeave(auth()->user())) {
            $leaveTypes = LeaveType::get(['id', 'name']);
            $html       = view('leave::employee.create', compact('leaveTypes'))->render();
            $response   = [
                'success' => true,
                'html'    => $html,
            ];
        } else {
            $response = getErrorResponse(__trans('leaves_are_not_allowed_in_probation_period'));
        }
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        canPerform('Create Leave');
        $today = now()->startOfDay();
        $data  = $request->validate([
            'reason'        => ['required', 'string'],
            'start_date'    => ['required', 'date_format:Y-m-d'],
            'end_date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'], //|after_or_equal:start_at',
            'leave_type_id' => [
                'required',
                'exists:leave_types,id',
                new LeaveAllowed($request->leave_type_id),
            ],
            'is_half_day'   => [new HalfDayLeave],
            'document'      => ['nullable', 'mimes:doc,docx,pdf,jpg,jpeg,png'],
        ]);
        $response = getErrorResponse();
        try {
            $user = User::find(auth()->id());
            if ($user) {
                if ($request->hasFile('document')) {
                    $data['file_path'] = $this->upload($request->document, 'uploads/leave-documents');
                }
                // $leaveType = LeaveType::whereId($request->leave_type_id)->first();
                // $LeaveBalance = LeaveBalance::where('user_id',auth()->user()->id)
                //                             ->where('leave_type_id',$request->leave_type_id)
                //                             ->where('year',date('Y'))
                //                             ->first();
                // $startDate = new DateTime($request->start_date);
                // $endDate = new DateTime($request->end_date);

                // $interval = $startDate->diff($endDate);
                // $totalDays = $interval->days + 1;
                // if($LeaveBalance){
                //     if($LeaveBalance->available < $totalDays){
                //         $message = 'You dont have enough leave in this type.';
                //         $response['message'] = $message;
                //         return response()->json($response);
                //     }
                // }else{
                //     if ($totalDays > $leaveType->days) {
                //         $message = 'You dont have enough leave in this type.';
                //         $response['message'] = $message;
                //         return response()->json($response);
                //     }
                // }
                $is_half = 0;
                if ($request->is_half_day) {
                    $is_half = 1;
                }
                $probationDate = UserWorkDetail::where('user_id', $user->id)->first();
                $checkpro      = Setting::where('key', 'leave_probation_module')->value('value');
                if ($checkpro == false) {
                    if ($probationDate->probation_end_date >= now()->toDateString()) {
                        $response = getErrorResponse(__trans('employee_leave_can_not_add_on_probation'));
                        return response()->json($response);
                    }
                }
                $leaveSetting = Setting::where('key', 'allow_negative_leave')->first();
                if ($leaveSetting && $leaveSetting->value == 0) {
                    $count = $this->checkTotalLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $user->id, $is_half);
                    if ($count != 1) {
                        return $response = getErrorResponse($count);
                    }
                }
                // $count = $this->checkTotalLeaveDay($request->leave_type_id,$request->start_date,$request->end_date,$user->id,$is_half);
                // if($count!=1){
                //     return $response = getErrorResponse($count);
                // }

                $leave = auth()->user()->leaves()->create($data);
                try {
                    $leave_user        = User::withoutGlobalScopes()->with('roles')->find($leave->user_id);
                    $leave_userRole_id = $leave_user?->roles->first()?->id;
                    $approvalLevel     = LeaveApprovalSetting::where('role_id', $leave_userRole_id)->value('level') ?? 1;
                    $approverUserIds   = $this->getReportingChain($leave_user->id, $approvalLevel);
                    $approverUserIds   = Arr::flatten($approverUserIds);
                    // dd($approverUserIds);

                    foreach ($approverUserIds as $key => $approverUserId) {
                        $approverUser = User::withoutGlobalScopes()
                            ->where('id', $approverUserId)
                            ->first();
                        Log::info('emp-leave-store-user_id-' . auth()->user()->id, ["approverUser" => $approverUser]);
                        if ($approverUser) {
                            $approverUserData = [
                                'id'      => $user->id,
                                'name'    => $user->name,
                                'email'   => $user->email,
                                'message' => 'Generated a Leave Request for ' . $leave->start_date,
                                'route'   => route('backend.leaves.show', $leave->id),
                                // Add any other user data you want to pass...
                            ];
                            Log::info('emp-leave-store-user_id-' . auth()->user()->id, ["approverUserData" => $approverUserData]);
                            if ($approverUserData) {
                                $approverUser->notify(new GenerateNotification($approverUserData, $approverUser->id));
                                try {

                                    Mail::to($approverUser->email)->send(new NotificationMail($approverUserData, "Leave Request Email"));
                                    $log = EmailAlertLog::create([
                                        'email'      => $approverUser->email,
                                        'status'     => 'success',
                                        'alert_type' => 'Leave Request',
                                        'message'    => 'Email sent successfully.',
                                    ]);
                                } catch (Exception $e) {
                                    EmailAlertLog::create([
                                        'email'   => $approverUser->email,
                                        'status'  => 'failed',
                                        'message' => $e->getMessage(),
                                    ]);
                                }
                                if (isset($approverUser->ftoken) && ! empty($approverUser->ftoken) && $approverUser->ftoken !== null) {
                                    $get = $this->fcmService->sendFcmMessage($approverUser->ftoken, 'Leave Request', $approverUserData['message'], 4);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::error('Error storing Excel file: ' . $e->getMessage());
                }
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Generated a Leave Request for ' . $leave->start_date,
                    'route'   => route('backend.leaves.show', $leave->id),
                    // Add any other user data you want to pass...
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
                $managers = User::permission('Leave Request Manager Access')->where('id', '!=', $user->id)->get();
                foreach ($managers as $manager) {
                    if ($manager->ftoken) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Leave Added', 'Leave created', 14);
                    }
                }
                //end
                // Send Leave notification to users who can Manage Leave
                $usersWithPermission = User::permission('Manage Leave')->where('id', '!=', $user->id)->get();
                foreach ($usersWithPermission as $userManageLeave) {
                    $userManageLeave->notify(new GenerateNotification($userData, $userManageLeave->id));
                }

                if (isset($user->ftoken) && ! empty($user->ftoken) && $user->ftoken !== null) {
                    $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Leave Request', $userData['message'], 4);
                }
                $response = getSuccessResponse(createFlashMessage('Leave Request', 'created'));
            } else {
                $response = getErrorResponse(__trans('user_is_not_available_currently'));
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    protected function checkTotalLeaveDay($id, $start_date, $end_date, $user_id, $is_half)
    {

        // $oneYearBack = Carbon::now()->subYear()->year;
        // $totalapprovedLeaves = Leave::where(
        //     [
        //         'user_id' => $user_id,
        //         'leave_type_id' => $type->id,
        //         'status' => LeaveStatus::Approved,
        //         'year' => date('Y'),
        //     ]
        // )->sum('total_leave_days');

        // $total_days = $type->days;
        // $user = User::find($user_id);
        // $yearMonth = 12;
        // $joining_date = $user->workDetail?->joining_date->toDateString();
        // if (Carbon::parse($joining_date)->isCurrentYear()) {
        //     $month = Carbon::parse($joining_date)->format('m');
        //     $leaveTotal = $total_days / $yearMonth;
        //     $totalmonth = 12 - ($month-1);
        //     $total_days = floor($leaveTotal * $totalmonth);
        //     $yearMonth = $totalmonth;
        // }
        // $new_days = 0;
        // $extra_days = 0;
        // if($type->is_recurring == '1'){
        //     $totalapprovedLeaves = Leave::my()->where(
        //         [
        //             'leave_type_id' => $type->id,
        //             'status' => LeaveStatus::Approved,
        //             'year' => $oneYearBack
        //         ]
        //     )->sum('total_leave_days');
        //     $total_given_in_year = $type->days;
        //     $total_is_recurring_leaves = $type->no_of_leaves;

        //     $total_carry_forword_leaves = ($total_given_in_year + $total_is_recurring_leaves) - $totalapprovedLeaves;

        //     if($total_carry_forword_leaves >= $total_is_recurring_leaves){
        //         $extra_days = $total_is_recurring_leaves;
        //     } else {
        //         $extra_days = $total_carry_forword_leaves;
        //     }
        // }
        // $balance = LeaveBalance::where([
        //     'user_id' => $user_id,
        //     'year' => date('Y'),
        //     'leave_type_id' => $type->id
        // ])->first();

        // if($balance){
        //     $current_year = date('Y');
        //     if($current_year == $balance->year){
        //         if($balance->isAddThisMonthLeave != date('m')){
        //             $total_days_of_month = round($total_days / $yearMonth, 1);
        //             $total_days_of_month = $balance->available + $total_days_of_month;
        //             $balance->available = $total_days_of_month - $totalapprovedLeaves;
        //             $balance->isAddThisMonthLeave = date('m');
        //             $balance->save();
        //         }
        //     }
        //     $balance = $balance->available;
        // } else {
        //     $totalapprovedLeaves = Leave::where(
        //         [
        //             'user_id' => $user_id,
        //             'leave_type_id' => $type->id,
        //             'status' => LeaveStatus::Approved,
        //             'year' => date('Y'),
        //         ]
        //     )->sum('total_leave_days');
        //     $total_days = round($total_days / 12, 1)- $totalapprovedLeaves;
        //     $balance = $total_days;
        // }

        $type    = LeaveType::find($id);
        $balance = LeaveBalance::where([
            'user_id'       => $user_id,
            'year'          => date('Y'),
            'leave_type_id' => $type->id,
        ])->first();

        if ($balance) {
            $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');

            if ($checkmonthwise == 1) {
                $balance = $balance->monthwiseDay;
            } else {
                $balance = $balance->available;
            }
        } else {
            $balance = 0;
        }

        // if ($count > $balance) {
        //     //$fail(__trans('you_have_already_used_allowed_leaves'));
        //     return $message = __trans('You have reached to the maximum limit of sending leave request. Please direct contact with HR regarding you leave.');
        // }
        $startDate = Carbon::parse($start_date);
        $endDate   = Carbon::parse($end_date);

        // Get the total number of days
        $currentLeaveDays = $startDate->diffInDays($endDate) + 1;
        if ($is_half == 1) {
            $currentLeaveDays = 0.5;
        }
        // if (($count + $currentLeaveDays) > $balance) {
        //     return $message = __trans('you_dont_have_enough_leaves');
        // }
        if ($currentLeaveDays > $balance) {
            return $message = __trans('You have reached to the maximum limit of sending leave request. Please direct contact with HR regarding you leave.');
        }
        return $message = 1;
    }

    /**
     * show  the lisitng leave from storage.
     */
    public function show(Leave $leave)
    {
        if ($leave->user_id != auth()->id()) {
            errorMessage(__trans('permission_denied'));
            return back();
        }
        return view('leave::employee.show', compact('leave'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        canPerform('Edit Leave');
        if (userCanApplyLeave(auth()->user())) {
            $leave      = Leave::my()->findOrFail($id);
            $leaveTypes = LeaveType::get(['id', 'name']);
            $html       = view('leave::employee.edit', compact('leaveTypes', 'leave'))->render();

            $response = [
                'success' => true,
                'html'    => $html,
            ];
        } else {
            $response = getErrorResponse(__trans('leaves_are_not_allowed_in_probation_period'));
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        canPerform('Create Leave');
        $leave = Leave::my()->findOrFail($id);
        $data  = $request->validate([
            'reason'        => ['required', 'string'],
            'start_date'    => 'required|date_format:Y-m-d', //|after_or_equal:today',
            'end_date'      => 'required|date_format:Y-m-d', //|after_or_equal:start_at',
            'leave_type_id' => [
                'required',
                'exists:leave_types,id',
                new LeaveAllowed($id),
            ],
            'is_half_day'   => [new HalfDayLeave],
            'document'      => ['nullable', 'mimes:doc,docx,pdf,jpg,jpeg,png'],
        ]);
        $response = getErrorResponse();
        try {
            $user = User::find(auth()->id());

            if ($request->hasFile('document')) {
                $data['file_path'] = $this->upload($request->document, 'uploads/leave-documents', $leave->file_path);
            }
            $is_half = 0;
            if ($request->is_half_day) {
                $is_half = 1;
            }
            $leaveSetting = Setting::where('key', 'allow_negative_leave')->first();
            if ($leaveSetting && $leaveSetting->value == 0) {
                $count = $this->checkTotalLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $user->id, $is_half);
                if ($count != 1) {
                    return $response = getErrorResponse($count);
                }
            }
            // $count = $this->checkTotalLeaveDay($request->leave_type_id,$request->start_date,$request->end_date,$user->id,$is_half);
            // if($count!=1){
            //     return $response = getErrorResponse($count);
            // }
            $leave->update($data);
            $response = getSuccessResponse(createFlashMessage('Leave Request', 'created'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $leave    = Leave::my()->findOrFail($id);
        $response = getErrorResponse();
        if ($leave->user_id == auth()->id() && $leave->status->value == LeaveStatus::Pending->value) {
            $leave->delete();
            $response = getSuccessResponse(createFlashMessage('Leave Request', 'deleted'));
        } else {
            $response['message'] = __trans('permission_denied');
        }

        return response()->json($response);
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
}
