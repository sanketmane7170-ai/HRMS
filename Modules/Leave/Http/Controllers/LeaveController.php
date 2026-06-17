<?php
namespace Modules\Leave\Http\Controllers;

use App\Exports\FailedRowsLeaveUpdateExport;
use App\Exports\LeaveExport;
use App\Exports\LeaveUpdateSampleExport;
use App\Exports\PHLeaveExport;
use App\Imports\LeaveUpdateImport;
use App\Models\Department;
use App\Models\LeaveApprovalSetting;
use App\Models\PHLeaveReport;
use App\Models\PreviousLeaveBalance;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeaveBalanceTransaction;
use App\Models\UserWorkDetail;
use App\Notifications\Leave\GenerateNotification;
use App\Services\FirebaseService;
use App\Traits\File;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Attendance\Entities\Holiday;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveBalanceUpdateLog;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveStatus;
use Modules\Leave\Exports\LeaveReportExport;
use Modules\Leave\Rules\HalfDayLeave;
use Modules\NotificationManager\Emails\LeaveRequestMail;
use Modules\NotificationManager\Emails\NotificationMail;
use Modules\NotificationManager\Entities\AlertRecipient;
use Modules\NotificationManager\Entities\EmailAlertLog;
use Yajra\DataTables\Facades\DataTables;

class LeaveController extends Controller
{
    use File;
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        view()->share('activeLink', 'leaves');
        $this->fcmService = $fcmService;
    }

    /**
     * Display a listing of the leave.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $types = LeaveType::get(['id', 'name', 'days']);
        canPerform('Manage Leave');
        $filterEmployees = User::whereIn('id', $request->employee ?? [])->get();
        if ($request->ajax()) {

            $loggedInUserId = auth()->id();

            $query = Leave::with(['type', 'user.roles', 'user.workDetail'])->latest('id');

            // Apply status filter
            if ($request->leave_status_type) {
                $query->where('status', $request->leave_status_type);
            }

            // Admin & HR can see all
            if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
                $leavesQuery = $query;
            } else {
                // Only approvable leaves for current user
                $leavesQuery = $query->whereHas('user.workDetail', function ($q) use ($loggedInUserId) {
                    $q->whereJsonContains('report_to_ids', $loggedInUserId)
                        ->orWhere('approved_first_level', 0);
                });
            }

            return DataTables::of($leavesQuery)
                ->addIndexColumn()
                ->editColumn('status', fn($row) => $row->status->getHtml())
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(route('backend.leaves.show', $row), 'View', 'btn-success', 'fa fa-eye');

                    if (in_array($row->status->value, [LeaveStatus::Pending->value, LeaveStatus::Approved->value])) {
                        if (hasPermission('Edit Leave')) {
                            $btn .= createActionButton(route('backend.leaves.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                        }
                        if (hasPermission('Delete Leave')) {
                            $btn .= createActionButton(route('backend.leaves.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                        }
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);

        }
        return view('leave::leave.index', compact('types', 'filterEmployees'));
    }

    public function leavesReport(Request $request)
    {
        canPerform('View Report Leave');
        // $types = LeaveType::get(['id', 'name', 'days']);
        $types = LeaveType::when($request->type_id, function ($query, $type_id) {
            return $query->whereIn('id', $type_id);
        })->get(['id', 'name', 'days']);

        $filterEmployees = User::whereIn('id', $request->employee ?? [])->get();
        $users           = $dates           = [];
        $departmentId    = '';
        $type_id         = '';
        $searchEmp       = '';
        $search          = false;
        $query           = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
        if ($request->post()) {

            $search       = true;
            $departmentId = $request->department_id;
            $type_id      = $request->type_id;
            $searchEmp    = $request->search_emp;

            if ($departmentId !== 'all') {
                $query->where('department_id', $departmentId);
            }

            if (! empty($searchEmp)) {
                $query->where('name', 'like', '%' . $searchEmp . '%');
            }
        }
        $users = $query->get();

        view()->share('activeLink', 'leaves-report');
        return view('leave::leave.report', compact('types', 'filterEmployees', 'users', 'departmentId', 'searchEmp', 'search', 'type_id'));
    }

    public function phleavesReport(Request $request)
    {

        canPerform('Manage Leave');
        $phleavereport = PHLeaveReport::get();
        $departmentId  = '';
        $searchEmp     = '';
        $type_id       = '';
        $search        = false;
        $data          = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
        if ($request->post()) {
            $search       = true;
            $departmentId = $request->department_id;
            $searchEmp    = $request->search_emp;
            if ($departmentId !== 'all') {
                $data->where('department_id', $departmentId);
            }
            if (! empty($searchEmp)) {
                $data->where('name', 'like', '%' . $searchEmp . '%');
            }
        }
        $currentYear = now()->year;
        $holidays    = Holiday::where(function ($query) use ($currentYear) {
            $query->whereYear('start_date', $currentYear)
                ->orWhereYear('end_date', $currentYear);
        })->orWhere('is_recurring', 1)
            ->get();
        $emp = $data->get();
        view()->share('activeLink', 'ph-leaves-report');
        return view('leave::leave.report', compact('phleavereport', 'emp', 'departmentId', 'searchEmp', 'type_id', 'search', 'holidays'));
    }

    public function leavesReportPrint($departmentId, $typeIds = '', $searchEmp = '')
    {
        canPerform('Manage Leave');
        $type_id = "";
        if (! empty($typeIds)) {
            $type_id = explode(',', $typeIds);
        }

        $data = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });

        if ($departmentId !== 'all') {
            $data->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $data->where('name', 'like', '%' . $searchEmp . '%');
        }

        $users       = $data->get();
        $exportExcel = [];
        $headers     = [];

        // $types = LeaveType::get(['id', 'name', 'days']);

        if ($typeIds == 'all') {
            $types = LeaveType::get(['id', 'name', 'days']);
        } else {
            $types = LeaveType::when($type_id, function ($query, $type_id) {
                return $query->whereIn('id', $type_id);
            })->get(['id', 'name', 'days']);
        }

        foreach ($users as $i => $user) {
            $exportExcel[$i]['Employee Name'] = $user->name . ' (' . $user->department?->name ?? 'NA' . ')';
            if ($i == 0) {
                $headers[] = 'Employee Name';
            }

            foreach ($types as $type) {
                if ($i == 0) {
                    $headers[] = $type->name . '(' . $type->days . ')';
                }
                $exportExcel[$i][$type->name] = calculatePendingLeave($type, $user->id);
            }
        }
        $export = new LeaveExport($exportExcel, $headers);
        if ($departmentId === 'all') {
            return Excel::download($export, 'leave_report_all.xlsx');
        } else {
            if ($departmentId != null) {
                $department = Department::find($departmentId);
                return Excel::download($export, 'leave_report_' . $department->name . '.xlsx');
            }
            return Excel::download($export, 'leave_report_all.xlsx');
        }
    }

    public function phleavesReportPrint($departmentId, $searchEmp = '')
    {
        canPerform('Manage Leave');
        $type_id = "";
        if (! empty($typeIds)) {
            $type_id = explode(',', $typeIds);
        }

        $data = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });

        if ($departmentId !== 'all') {
            $data->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $data->where('name', 'like', '%' . $searchEmp . '%');
        }

        $users       = $data->get();
        $exportExcel = [];
        $headers     = [];

        // $types = LeaveType::get(['id', 'name', 'days']);

        $currentYear = now()->year;
        $holidays    = Holiday::whereYear('start_date', $currentYear)
            ->orWhereYear('end_date', $currentYear)
            ->get();
        $phleavereport = PHLeaveReport::get();
        foreach ($users as $i => $user) {
            $allphcount = 0;
            if ($phleavereport instanceof \Illuminate\Support\Collection) {
                $allphcount = $phleavereport->where('user_id', $user->id)->count();
            }
            $exportExcel[$i]['Employee Name'] = $user->name . ' (' . $user->department?->name ?? 'NA' . ')' . ' (Total: ' . $allphcount . ')';
            if ($i == 0) {
                $headers[] = 'Employee Name';
            }
            foreach ($holidays as $holiday) {
                $holidayStart = Carbon::parse($holiday->start_date)->toDateString();
                $holidayEnd   = Carbon::parse($holiday->end_date)->toDateString();
                $diffInDays   = Carbon::parse($holidayStart)->diffInDays(Carbon::parse($holidayEnd)) + 1;
                if ($i == 0) {
                    $headers[] = $holiday->detail . '(' . $diffInDays . ')';
                }
                $phcount = 0;
                if ($phleavereport instanceof \Illuminate\Support\Collection) {
                    $phcount = $phleavereport->where('user_id', $user->id)->where('holiday_id', $holiday->id)->count();
                }
                $exportExcel[$i][$holiday->detail] = $phcount;
                $phbalance                         = 0;
                $leavetype                         = LeaveType::where('name', 'like', '%PH%')->first();
                if ($leavetype) {
                    $balance = LeaveBalance::where([
                        'user_id'       => $user->id,
                        'year'          => date('Y'),
                        'leave_type_id' => $leavetype->id,
                    ])->first();
                    $phbalance = $balance->available;
                }
            }
            if ($i == 0) {
                $headers[] = 'Previous PH Balance';
                $headers[] = 'Total PH Balance';
            }
            $exportExcel[$i]['Previous PH Balance'] = $phbalance - $allphcount;
            $exportExcel[$i]['Total PH Balance']    = $phbalance;
        }

        $export = new PHLeaveExport($exportExcel, $headers);
        if ($departmentId == 'all') {
            return Excel::download($export, 'leave_report_all.xlsx');
        } else {
            if ($departmentId != null) {
                $department = Department::find($departmentId);
                return Excel::download($export, 'leave_report_' . $department->name . '.xlsx');
            }
            return Excel::download($export, 'leave_report_all.xlsx');
        }
    }
    /**
     * show  the lisitng leave from storage.
     */
    public function show(Leave $leave)
    {
        // return view('leave::leave.show', compact('leave'));
        // $leave = Leave::with(['user'])->find($leave->id);
        $leave_approval_auth = false;
        $level               = $leave->approval_status;
        if ($leave->status->value == "pending") {
            $level = $leave->approval_status + 1;
        }
        // $level = $leave->approval_status + 1;
        $leave_user        = User::with('roles')->find($leave->user_id);
        $leave_userRole_id = $leave_user?->roles->first()?->id;
        $approvalLevel     = LeaveApprovalSetting::where('role_id', $leave_userRole_id)->value('level') ?? 1;
        $approverUserIds   = $this->getReportingChain($leave_user->id, $approvalLevel);
        // $approverUserIds = Arr::flatten($approverUserIds);
        // $approverUserIds = Arr::flatten($approverUserIds);

        if ($leave->status->value == 'pending') {
            $currentStep = $leave->approval_status ?? 0;

            $nextApproverId  = $approverUserIds[$currentStep] ?? null;
            $nextApproverIds = $approverUserIds[$currentStep] ?? []; // will be an array
            if (is_array($nextApproverIds)) {
                $leave_approval_auth = in_array(auth()->id(), $nextApproverIds);
            } else {
                if (auth()->id() == $nextApproverId) {
                    $leave_approval_auth = true;
                }
            }
            //       if ($leave->user_id == 11) {
            //     dd($leave_approval_auth);
            // }
            // if (auth()->id() == $nextApproverId) {
            //     $leave_approval_auth = true;
            // }
            // dd($level);
        }
        // if($leave->status->value == "approved"){
        //       $level = $leave->approval_status;
        // }

        return view('leave::leave.show', compact('leave', 'leave_approval_auth', 'level'));
    }

    public function pdfexport(Request $request, $leave_id)
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
    /**
     * Return Response for pending leave
     */
    public function takeAction(Leave $leave, $action, $level = null)
    {
        // dd($leave);
        $response = getErrorResponse();
        $message  = ($action == 'approve') ? 'Approved' : 'Rejected';
        if ($action == 'approve') {
            canPerform('Approve Leave');
        } else {
            canPerform('Reject Leave');
        }
        if ($leave->status->value == LeaveStatus::Pending->value) {
            // $response =  $this->$action($leave);
            $user = User::find($leave->user_id);
            if ($user) {
                if ($action == "approve") {
                    // if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
                    if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
                        $leave->approval_status = null;
                        $response               = $this->$action($leave);
                    } else {
                        $userRole = Auth::user()->roles->first()?->name ?? null;

                        $leave_user        = User::with('roles')->find($leave->user_id);
                        $leave_userRole_id = $leave_user?->roles->first()?->id ?? null;

                        $totallevel         = LeaveApprovalSetting::where('role_id', $leave_userRole_id)->value('level') ?? 1;
                        $leave_user         = User::with('roles')->find($leave->user_id);
                        $approvedFirstLevel = isset($leave_user->workDetail) ? $leave_user->workDetail->approved_first_level : 0;
                        // if ($approvedFirstLevel && $leave_user->workDetail->report_to_ids == auth()->user()->id) {
                        //     $totallevel = 1;
                        // }

                        if (
                            $approvedFirstLevel &&
                            in_array(auth()->user()->id, json_decode($leave_user->workDetail->report_to_ids ?? '[]', true))
                        ) {
                            $totallevel = 1;
                        }

                        $leave_user        = User::with('roles')->find($leave->user_id);
                        $leave_userRole_id = $leave_user?->roles->first()?->id;
                        $approvalLevel     = LeaveApprovalSetting::where('role_id', $leave_userRole_id)->value('level') ?? 1;
                        $approverUserIds   = $this->getReportingChain($leave_user->id, $approvalLevel);
                        $approverUserIds   = Arr::flatten($approverUserIds);

                        foreach ($approverUserIds as $key => $approverUserId) {
                            $approverUser = User::where('id', $approverUserId)
                                ->first();
                            Log::info('takeAction-user_id-' . auth()->user()->id, ["approverUser" => $approverUser]);
                            if ($approverUser) {
                                if ($level == 0 || $level == null) {
                                    $level == 1;
                                }
                                $approverUserData = [
                                    'id'      => $user->id,
                                    'name'    => $user->name,
                                    'email'   => $user->email,
                                    'message' => 'Your ' . $leave->start_date . ' leave ' . $level . ' level is ' . $message,
                                    'route'   => route('backend.leaves.show', $leave->id),
                                    // Add any other user data you want to pass...
                                ];
                                Log::info('takeAction-user_id-' . auth()->user()->id, ["approverUserData" => $approverUserData]);
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

                        // $response =  $this->$action($leave);
                        $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                        $userData = [
                            'id'      => $user->id,
                            'name'    => $user->name,
                            'email'   => $user->email,
                            'message' => 'Your ' . $leave->start_date . ' leave is ' . $message,
                            'route'   => route('backend.leaves.show', $leave->id),
                            // Add any other user data you want to pass...
                        ];
                        $user->notify(new GenerateNotification($userData, $admin->id));

                        if ((int) $level === (int) $totallevel) {
                            $leave->approval_status = $level;
                            $response               = $this->$action($leave); // Final approve
                        } else {
                            // Just update status
                            $leave->approval_status = $level;
                            $leave->status          = LeaveStatus::Pending->value;
                            $leave->save();

                            return [
                                'success'  => true,
                                'message'  => createFlashMessage('Leave', $level . ' Level Approved'),
                                'redirect' => route('backend.leaves.show', $leave),
                            ];
                        }
                    }
                } else {
                    $leave->approval_status = null;
                    $response               = $this->$action($leave);
                }
            } else {
                $response = getErrorResponse(__trans('user_is_not_available_currently'));
            }
        } else {
            $response = getErrorResponse(__trans('alredy_leave_status_was_updated'));
        }

        return response()->json($response);
    }

    /**
     * Save rejection remark for the leave
     */
    public function rejectLeave(Leave $leave, Request $request)
    {
        $data = $request->validate([
            'remark' => 'required',
        ]);

        $response = getErrorResponse();
        try {
            $data += ['status' => LeaveStatus::Rejected->value];
            // $leave->update($data);

            $user = User::find($leave->user_id);
            if ($user && $user->ftoken !== null) {
                $leave->update($data);
                $leave_user        = User::with('roles')->find($leave->user_id);
                $leave_userRole_id = $leave_user?->roles->first()?->id;
                $approvalLevel     = LeaveApprovalSetting::where('role_id', $leave_userRole_id)->value('level') ?? 1;
                $approverUserIds   = $this->getReportingChain($leave_user->id, $approvalLevel);
                $approverUserIds   = Arr::flatten($approverUserIds);

                foreach ($approverUserIds as $key => $approverUserId) {
                    $approverUser = User::where('id', $approverUserId)
                        ->first();
                    Log::info('takeAction-user_id-' . auth()->user()->id, ["approverUser" => $approverUser]);
                    if ($approverUser) {

                        $approverUserData = [
                            'id'      => $user->id,
                            'name'    => $user->name,
                            'email'   => $user->email,
                            'message' => 'Your ' . $leave->start_date . ' leave  is rejected',
                            'route'   => route('backend.leaves.show', $leave->id),
                            // Add any other user data you want to pass...
                        ];
                        Log::info('takeAction-user_id-' . auth()->user()->id, ["approverUserData" => $approverUserData]);
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

                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Your ' . $leave->start_date . ' leave is rejected',
                    'route'   => route('backend.leaves.show', $leave->id),
                    // Add any other user data you want to pass...
                ];
                $user->notify(new GenerateNotification($userData, $admin->id));
                $get                  = $this->fcmService->sendFcmMessage($user->ftoken, 'Leave Rejected', $userData['message'], 4);
                $response             = getSuccessResponse(createFlashMessage('Leave', 'rejected'));
                $response['redirect'] = route('backend.leaves.show', $leave);
            } else {
                $response = getErrorResponse(__trans('user_is_not_available_currently'));
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        canPerform('Edit Leave');
        // if (userCanApplyLeave(auth()->user())) {
        $leave = Leave::findOrFail($id);
        $user  = User::where('id', '=', $leave->user_id)->first();

        $leaveTypes = LeaveType::get(['id', 'name']);
        $html       = view('leave::leave.edit', compact('leaveTypes', 'leave', 'user'))->render();

        $response = [
            'success' => true,
            'html'    => $html,
        ];
        // } else {
        //     $response = getErrorResponse(__trans('leaves_are_not_allowed_in_probation_period'));
        // }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        canPerform('Create Leave');
        $leave = Leave::findOrFail($id);
        $data  = $request->validate([
            'reason'        => ['required', 'string'],
            'start_date'    => 'required|date_format:Y-m-d', //|after_or_equal:today',
            'end_date'      => 'required|date_format:Y-m-d', //|after_or_equal:start_at',
            'leave_type_id' => [
                'required',
                'exists:leave_types,id',
                // new LeaveAllowed($id)
            ],
            'is_half_day'   => [new HalfDayLeave],
            'document'      => ['nullable', 'mimes:doc,docx,pdf,jpg,jpeg,png'],
        ]);
        $response = getErrorResponse();
        try {
            if ($request->hasFile('document')) {
                $data['file_path'] = $this->upload($request->document, 'uploads/leave-documents', $leave->file_path);
            }
            $is_half = 0;
            $count   = 1;
            if ($request->is_half_day == 1) {
                $is_half = 1;
            }
            $leaveSetting = Setting::where('key', 'allow_negative_leave')->first();
            if ($leaveSetting && $leaveSetting->value == 0) {
                if ($leave->status->value == LeaveStatus::Approved->value) {
                    $count = $this->checkTotalApprovedLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $leave->user_id, $is_half, $leave);
                } else {
                    $count = $this->checkTotalLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $leave->user_id, $is_half);
                }
                if ($count != 1) {
                    return $response = getErrorResponse($count);
                }
            }
            if ($leave->status->value == LeaveStatus::Approved->value) {
                $count = $this->updateApprovedLeaveDays($request->leave_type_id, $request->start_date, $request->end_date, $leave->user_id, $is_half, $leave);
            }
            if ($count != 1) {
                return $response = getErrorResponse($count);
            }

            $data['is_half_day'] = $request->is_half_day == null ? 0 : 1;
            $leave->update($data);
            $response = getSuccessResponse(createFlashMessage('Leave Request', 'updated'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    /**
     * Remove the specified leave from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Leave $leave)
    {
        if ($leave->status->value == LeaveStatus::Approved->value) {

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
                    'description'      => 'Delete Leave ' . $leave->id,
                ]);

                $balance->available    = ($balance->available + $leave->total_leave_days);
                $balance->monthwiseDay = ($balance->monthwiseDay + $leave->total_leave_days);
                $balance->save();
            }
            $leave->delete();

            $response = getSuccessResponse(createFlashMessage('Leave Request', 'deleted'));
        } else {
            $leave->delete();
            $response = getSuccessResponse(createFlashMessage('Leave Request', 'deleted'));
        }

        return response()->json($response);
    }

    public function createLeaveByAdmin()
    {
        canPerform('Create Leave');
        $users = User::whereNotIn('name', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ])->where('status', 'active')->get();
        // if (userCanApplyLeave(auth()->user())) {
        $leaveTypes = LeaveType::get(['id', 'name']);
        $html       = view('leave::leave.create', compact('leaveTypes', 'users'))->render();
        $response   = [
            'success' => true,
            'html'    => $html,
        ];
        // } else {
        // $response = getErrorResponse(__trans('leaves_are_not_allowed_in_probation_period'));
        // }
        return response()->json($response);
    }

    public function storeAdminLeaveByAdmin(Request $request)
    {
        canPerform('Create Leave');
        $today = now()->startOfDay();
        $data  = $request->validate([
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
        $response = getErrorResponse();
        try {
            $employee = User::find($data['user_id']);
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
                    $count = $this->checkTotalLeaveDay($request->leave_type_id, $request->start_date, $request->end_date, $data['user_id'], $is_half);
                    if ($count != 1) {
                        return $response = getErrorResponse($count);
                    }
                }
                $employee->leaves()->create($data);
                $get = $this->fcmService->sendFcmMessage($employee->ftoken, 'Leave Added', 'Leave created by admin', 4);
                // send notification manager
                $managers = User::permission('Leave Request Manager Access')->where('id', '!=', $employee->id)->get();
                foreach ($managers as $manager) {
                    if (! empty($manager->ftoken)) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Leave Added', 'Leave created', 14);
                    }
                }
                //end
                $userData = [
                    'id'      => $employee->id,
                    'name'    => $employee->name,
                    'email'   => $employee->email,
                    'message' => 'Generated a Leave Request for ' . $request->start_date,
                    // 'route' => route('backend.leaves.show', $request->id),
                    // Add any other user data you want to pass...
                ];
                /* Send Email Notifications which set by admin */
                $this->emailNotification($userData);

                $response = getSuccessResponse(createFlashMessage('Leave Request', 'created'));
            } else {
                $response = getErrorResponse(__trans('employee_is_not_available_currently'));
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
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

    public function checkTotalLeaveDay($id, $start_date, $end_date, $user_id, $is_half)
    {

        $oneYearBack         = Carbon::now()->subYear()->year;
        $type                = LeaveType::find($id);
        $totalapprovedLeaves = Leave::where(
            [
                'user_id'       => $user_id,
                'leave_type_id' => $type->id,
                'status'        => LeaveStatus::Approved,
                'year'          => date('Y'),
            ]
        )->sum('total_leave_days');

        $total_days   = $type->days;
        $user         = User::find($user_id);
        $yearMonth    = 12;
        $joining_date = $user->workDetail?->joining_date->toDateString();
        if (Carbon::parse($joining_date)->isCurrentYear()) {
            $month      = Carbon::parse($joining_date)->format('m');
            $leaveTotal = $total_days / $yearMonth;
            $totalmonth = 12 - $month;
            $total_days = floor($leaveTotal * $totalmonth);
            $yearMonth  = $totalmonth;
        }
        $new_days   = 0;
        $extra_days = 0;
        if ($type->is_recurring == '1') {
            $totalapprovedLeaves = Leave::my()->where(
                [
                    'leave_type_id' => $type->id,
                    'status'        => LeaveStatus::Approved,
                    'year'          => $oneYearBack,
                ]
            )->sum('total_leave_days');
            $total_given_in_year       = $type->days;
            $total_is_recurring_leaves = $type->no_of_leaves;

            $total_carry_forword_leaves = ($total_given_in_year + $total_is_recurring_leaves) - $totalapprovedLeaves;

            if ($total_carry_forword_leaves >= $total_is_recurring_leaves) {
                $extra_days = $total_is_recurring_leaves;
            } else {
                $extra_days = $total_carry_forword_leaves;
            }
        }
        $balance = LeaveBalance::where([
            'user_id'       => $user_id,
            'year'          => date('Y'),
            'leave_type_id' => $type->id,
        ])->first();
        $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');

        if ($balance) {
            // $current_year = date('Y');
            // if($current_year == $balance->year){
            //     if($balance->isAddThisMonthLeave != date('m')){
            //         $total_days_of_month = round($total_days / $yearMonth, 1);
            //         $total_days_of_month = $balance->available + $total_days_of_month;
            //         $balance->available = $total_days_of_month - $totalapprovedLeaves;
            //         $balance->isAddThisMonthLeave = date('m');
            //         $balance->save();
            //     }
            // }
            if ($checkmonthwise == 1) {
                $balance = $balance->monthwiseDay;
            } else {
                $balance = $balance->available;
            }
        } else {
            // $totalapprovedLeaves = Leave::where(
            //     [
            //         'user_id' => $user_id,
            //         'leave_type_id' => $type->id,
            //         'status' => LeaveStatus::Approved,
            //         'year' => date('Y'),
            //     ]
            // )->sum('total_leave_days');
            // $total_days = round($total_days / $yearMonth, 1)- $totalapprovedLeaves;
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

            $addtransaction = UserLeaveBalanceTransaction::create([
                'user_id' => $leave->user_id,
                'leave_type_id' => $leave->leave_type_id,
                'transaction_type' => 'add',
                'old_balance' => $balance->available,
                'update_balance' => $addapproveday,
                'new_balance' => ($balance->available + $addapproveday),
                'transaction_date' => Carbon::now()->toDateString(),
                'description' => 'Leave update from approved leave edit entry (add old approved day) : ' . $leave->id,
            ]);

            $addtransaction = UserLeaveBalanceTransaction::create([
                'user_id' => $leave->user_id,
                'leave_type_id' => $leave->leave_type_id,
                'transaction_type' => 'add',
                'old_balance' => $balance->available,
                'update_balance' => $addapproveday,
                'new_balance' => ($balance->available + $addapproveday),
                'transaction_date' => Carbon::now()->toDateString(),
                'description' => 'Leave update from approved leave edit entry (add old approved day) : ' . $leave->id,
            ]);

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

    public function approve(Leave $leave)
    {
        $balance = LeaveBalance::where([
            'user_id'       => $leave->user_id,
            'year'          => date('Y'),
            'leave_type_id' => $leave->leave_type_id,
        ])->first();

        $user            = User::find($leave->user_id);
        $startDate       = Carbon::parse($leave->start_date);
        $endDate         = Carbon::parse($leave->end_date);
        $currentYearDate = Carbon::now();
        $joining_date    = Carbon::parse($user->workDetail?->joining_date);
        $daysDiff        = $currentYearDate->diffInDays($joining_date);
        // check two year leave
        if ($startDate->year != $endDate->year) {
            $endOfStartYear   = $startDate->copy()->endOfYear();
            $currentLeaveDays = $startDate->diffInDays($endOfStartYear) + 1;
        } else {
            $currentLeaveDays = $startDate->diffInDays($endDate) + 1;
        }
        if ($daysDiff <= 365) {
            $currentLeaveDays = $startDate->diffInDays($endDate) + 1;
        }
        // end
        if ($leave->is_half_day == 1) {
            $currentLeaveDays = 0.5;
        }
        $leaveSetting   = Setting::where('key', 'allow_negative_leave')->first();
        $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');

        if ($balance) {
            if ($leaveSetting && $leaveSetting->value == 0) {
                if ($checkmonthwise == 1) {
                    $availableBalance = $balance->monthwiseDay;
                } else {
                    $availableBalance = $balance->available ?? 0;
                }

                if ($availableBalance >= $currentLeaveDays) {
                    $addtransaction = UserLeaveBalanceTransaction::create([
                        'user_id'          => $leave->user_id,
                        'leave_type_id'    => $leave->leave_type_id,
                        'transaction_type' => 'remove',
                        'old_balance'      => $balance->available,
                        'update_balance'   => $currentLeaveDays,
                        'new_balance'      => ($balance->available - $currentLeaveDays),
                        'transaction_date' => Carbon::now()->toDateString(),
                        'description'      => 'Approved Leave ' . $leave->id,
                    ]);

                    $leave->status = LeaveStatus::Approved->value;
                    $leave->save();

                    $balance->available    = ($balance->available - $currentLeaveDays);
                    $balance->monthwiseDay = ($balance->monthwiseDay - $currentLeaveDays);
                    $balance->save();

                    $employee = User::find($leave->user_id);
                    if($employee->ftoken){
                        $get      = $this->fcmService->sendFcmMessage($employee->ftoken, 'Leave Approved', 'Leave Approved by admin', 4);
                    }
                    // send notification manager
                    $managers = User::permission('Leave Request Manager Access')->where('id', '!=', $employee->id)->get();
                    foreach ($managers as $manager) {
                        if (! empty($manager->ftoken)) {
                            $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Leave Approved', 'Leave Approved', 14);
                        }
                    }
                    //end
                    return [
                        'success'  => true,
                        'message'  => createFlashMessage('Leave', 'Approved'),
                        'redirect' => route('backend.leaves.show', $leave),
                    ];
                } else {
                    return $response = getErrorResponse(__trans('you_dont_have_enough_leaves'));
                }
            } else {
                $addtransaction = UserLeaveBalanceTransaction::create([
                    'user_id'          => $leave->user_id,
                    'leave_type_id'    => $leave->leave_type_id,
                    'transaction_type' => 'remove',
                    'old_balance'      => $balance->available,
                    'update_balance'   => $currentLeaveDays,
                    'new_balance'      => ($balance->available - $currentLeaveDays),
                    'transaction_date' => Carbon::now()->toDateString(),
                    'description'      => 'Approved Leave ' . $leave->id,
                ]);

                $leave->status = LeaveStatus::Approved->value;
                $leave->save();

                $balance->available    = ($balance->available - $currentLeaveDays);
                $balance->monthwiseDay = ($balance->monthwiseDay - $currentLeaveDays);
                $balance->save();

                $employee = User::find($leave->user_id);
                if($employee->ftoken){
                    $get      = $this->fcmService->sendFcmMessage($employee->ftoken, 'Leave Approved', 'Leave Approved by admin', 4);
                }
                // send notification manager
                $managers = User::permission('Leave Request Manager Access')->where('id', '!=', $employee->id)->get();
                foreach ($managers as $manager) {
                    if (! empty($manager->ftoken)) {
                        $get = $this->fcmService->sendFcmMessage($manager->ftoken, 'Leave Approved', 'Leave Approved', 14);
                    }
                }
                //end
                return [
                    'success'  => true,
                    'message'  => createFlashMessage('Leave', 'Approved'),
                    'redirect' => route('backend.leaves.show', $leave),
                ];
            }

        }
        // else {
        //     $leaveType = LeaveType::whereId($leave->leave_type_id)->first();
        //     $total_leaves = $leaveType->days;
        //     $user = User::find($leave->user_id);
        //     $yearMonth = 12;
        //     $joining_date = $user->workDetail?->joining_date->toDateString();
        //     if (Carbon::parse($joining_date)->isCurrentYear()) {
        //         $month = Carbon::parse($joining_date)->format('m');
        //         $leaveTotal = $total_leaves / $yearMonth;
        //         $totalmonth = 12 - $month;
        //         $total_leaves = floor($leaveTotal * $totalmonth);
        //         $yearMonth = $totalmonth;
        //     }
        //     $total_leaves = round($total_leaves / $yearMonth, 1);
        //     if ($leaveSetting && ($leaveSetting->value === 0 || is_null($leaveSetting->value))) {
        //         if ($total_leaves >= $currentLeaveDays) {
        //             $leave->status = LeaveStatus::Approved->value;
        //             $leave->save();
        //             $employee = User::find($leave->user_id);
        //             $balance = LeaveBalance::firstOrCreate([
        //                 'user_id' => $leave->user_id,
        //                 'leave_type_id' => $leave->leave_type_id,
        //                 'year' => date('Y')
        //             ], [
        //                 'available' => ($total_leaves - $leave->total_leave_days),
        //             ]);
        //             $get = $this->fcmService->sendFcmMessage($employee->ftoken, 'Leave Approved', 'Leave Approved by admin', 4);
        //             return [
        //                 'success' => true,
        //                 'message' => createFlashMessage('Leave', 'Approved'),
        //                 'redirect' => route('backend.leaves.show', $leave)
        //             ];
        //         } else {
        //             return $response = getErrorResponse(__trans('you_dont_have_enough_leaves'));
        //         }
        //     } else {
        //         $leave->status = LeaveStatus::Approved->value;
        //         $leave->save();
        //         $employee = User::find($leave->user_id);
        //         $get = $this->fcmService->sendFcmMessage($employee->ftoken, 'Leave Approved', 'Leave Approved by admin', 4);
        //         return [
        //             'success' => true,
        //             'message' => createFlashMessage('Leave', 'Approved'),
        //             'redirect' => route('backend.leaves.show', $leave)
        //         ];
        //     }

        // }
    }

    protected function reject(Leave $leave)
    {
        $html = view('leave::leave.cancel', compact('leave'))->render();

        return [
            'success' => true,
            'html'    => $html,
        ];
    }

    /**
     * Generate leave Report
     */
    public function generateLeaveReport(Request $selection)
    {
        canPerform('Export Leave Report');

        return Excel::download(new LeaveReportExport($selection), 'leave_report_list_' . time() . '.xlsx');
    }

    public function getLeaveBalanceEditModal(User $user, LeaveType $leaveType)
    {
        canPerform('Edit Update Leave Balance EditUpdateLeave');
        $balance  = $this->RecalculateLeaveBalance($user, $leaveType);
        $response = [
            'user'      => $user,
            'leaveType' => $leaveType,
            'balance'   => $balance,
        ];
        $html = view('leave::leave.balance-edit', compact('response'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function updateLeaveBalance(Request $request, User $user, LeaveType $leaveType)
    {
        // $balance = $this->RecalculateLeaveBalance($user, $leaveType);
        // $data = $request->validate([
        //     'available' => ['required', function ($attribute, $value, $fail) use ($balance) {
        //         if ($value > $balance) {
        //             $fail("The $attribute cannot be greater than the current balance of $balance.");
        //         }
        //     }],
        // ]);
        $response = getErrorResponse();
        try {
            $getbalance = LeaveBalance::where(
                [
                    'user_id'       => $user->id,
                    'year'          => date('Y'),
                    'leave_type_id' => $leaveType->id,
                ],
            )->first();
            $oldvalue   = $getbalance->available;
            $newValue   = $request->available;
            $difference = abs($oldvalue - $newValue);

            $addtransaction = UserLeaveBalanceTransaction::create([
                'user_id'          => $user->id,
                'leave_type_id'    => $leaveType->id,
                'transaction_type' => 'remove',
                'old_balance'      => empty($oldvalue) ? 0 : $oldvalue,
                'update_balance'   => $difference,
                'new_balance'      => $newValue,
                'transaction_date' => Carbon::now()->toDateString(),
                'description'      => 'Update Leave Balance from leave edit option',
            ]);
            if ($getbalance) {
                $getbalance->available    = $newValue;
                $getbalance->monthwiseDay = $newValue;
                $getbalance->save();
            }
            if ($newValue > $oldvalue) {
                $is_less = 1;
            } elseif ($newValue < $oldvalue) {
                $is_less = 0;
            } else {
                $is_less = null;
            }
            // Log the update
            LeaveBalanceUpdateLog::create([
                'user_id'          => $user->id,
                'leave_type_id'    => $leaveType->id,
                'previous_balance' => $oldvalue,
                'new_balance'      => $request->available,
                'diff_value'       => $difference,
                'is_less'          => $is_less,
                'updated_by'       => auth()->user()->id,
                'updated_at'       => now(),
                'description'      => 'this updated by admin using edit balance option.',
            ]);
            $response = getSuccessResponse(createFlashMessage('Leave Balance', 'updated'));
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    private function RecalculateLeaveBalance($user, $leaveType)
    {
        $total_days   = $leaveType->days;
        $oneYearBack  = Carbon::now()->subYear()->year;
        $new_days     = 0;
        $extra_days   = 0;
        $current_date = now();
        $created_at   = $user->created_at;

        if ($created_at->diffInYears($current_date) < 1) {
            $extra_days          = PreviousLeaveBalance::where(['user_id' => $user->id, 'leave_type_id' => $leaveType->id])->sum('days');
            $totalapprovedLeaves = Leave::where(
                [
                    'user_id'       => $user->id,
                    'leave_type_id' => $leaveType->id,
                    'status'        => LeaveStatus::Approved,
                ]
            )->sum('total_leave_days');
            $total_given_in_year = $leaveType->days;
        } else {
            if ($leaveType->is_recurring == '1') {
                $totalapprovedLeaves = Leave::where(
                    [
                        'user_id'       => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'status'        => LeaveStatus::Approved,
                        'year'          => $oneYearBack,
                    ]
                )->sum('total_leave_days');
                $total_given_in_year       = $leaveType->days;
                $total_is_recurring_leaves = $leaveType->no_of_leaves;

                $total_carry_forword_leaves = ($total_given_in_year + $total_is_recurring_leaves) - $totalapprovedLeaves;

                if ($total_carry_forword_leaves >= $total_is_recurring_leaves) {
                    $extra_days = $total_is_recurring_leaves;
                } else {
                    $extra_days = $total_carry_forword_leaves;
                }
            }
        }

        $total_days = $total_days + $extra_days;

        $balance = LeaveBalance::firstOrNew(
            [
                'user_id'       => $user->id,
                'year'          => date('Y'),
                'leave_type_id' => $leaveType->id,
            ],
            [
                'available' => $total_days,
            ]
        );
        return $balance->available;
    }

    public function getLeaveBalanceUpdateLogs(Request $request)
    {
        if ($request->ajax()) {
            $data = LeaveBalanceUpdateLog::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('employee_name', function ($row) {
                    return User::where('id', $row->user_id)->value('name');
                })
                ->addColumn('leave_type', function ($row) {
                    return LeaveType::where('id', $row->leave_type_id)->value('name');
                })
                ->addColumn('previous_balance', function ($row) {
                    return $row->previous_balance;
                })
                ->addColumn('updated_balance', function ($row) {
                    return $row->new_balance;
                })
                ->addColumn('updated_by', function ($row) {
                    return User::where('id', $row->updated_by)->value('name');
                })
                ->addColumn('updated_at', function ($row) {
                    return $row->created_at;
                })
                ->make(true);
        }
        return view('leave::leave.leave-update-logs');
    }

    public function getLeaveBalanceUpdateTransaction(Request $request)
    {
        if ($request->ajax()) {
            $data = UserLeaveBalanceTransaction::query()
                ->select(
                    'user_leave_balance_transactions.*',
                    'users.name as employee_name',
                    'leave_types.name as leave_type'
                )
                ->leftJoin('users', 'users.id', '=', 'user_leave_balance_transactions.user_id')
                ->leftJoin('leave_types', 'leave_types.id', '=', 'user_leave_balance_transactions.leave_type_id')
                ->orderBy('user_leave_balance_transactions.id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('transaction_date', function ($row) {
                    return Carbon::parse($row->transaction_date)->format('d-m-Y');
                })
                ->editColumn('updated_at', function ($row) {
                    return Carbon::parse($row->updated_at)->format('d-m-Y H:i:s');
                })
                ->make(true);
        }
        return view('leave::leave.leave-update-transaction');
    }

    public function openCalendar(Request $request)
    {
        $departmentId = '';
        $searchEmp    = '';
        $search       = false;
        if ($request->post()) {
            $search       = true;
            $departmentId = $request->department_id;
            $searchEmp    = $request->search_emp;
            $leaves       = Leave::with('type', 'user')
                ->whereIn('status', ['approved', 'pending'])
                ->whereHas('user', function ($query) use ($departmentId, $searchEmp) {
                    if (! empty($departmentId) && $departmentId !== 'all') {
                        $query->where('department_id', $departmentId);
                    }
                    if (! empty($searchEmp)) {
                        $query->where('name', 'like', '%' . $searchEmp . '%');
                    }
                })
                ->get();
        } else {
            $leaves = Leave::with('type', 'user')->whereIn('status', ['approved', 'pending'])->get();
        }
        view()->share('activeLink', 'leaves-planner');

        $formattedEvents = [];
        foreach ($leaves as $leave) {
            $startDate = Carbon::parse($leave->start_date . 'T00:00:00')->format('Y-m-d\TH:i:s');
            $endDate   = Carbon::parse($leave->end_date . 'T23:59:59')->format('Y-m-d\TH:i:s');

            $status = 'badge-warning';
            if ($leave->status->value == 'approved') {
                $status = 'badge-success';
            }
            $formattedEvents[] = [
                'title'  => $leave->user->name . ' (' . $leave->status->name . ')',
                'status' => $status,
                'start'  => $startDate,
                'end'    => $endDate,
                'uqid'   => $leave->user_id,
            ];
        }
        return view('leave::leave.calendar-view', compact('leaves', 'formattedEvents', 'departmentId', 'searchEmp'));
    }

    public function isAllowNegativeLeave(Request $request)
    {

        $isAllowed = $request->input('allow');

        if ($isAllowed) {
            $settingadd = Setting::where('key', 'allow_negative_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'allow_negative_leave',
                    'value' => true,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'allow_negative_leave',
                    'value' => true,
                ]);
            }
        } else {
            $settingadd = Setting::where('key', 'allow_negative_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'allow_negative_leave',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'allow_negative_leave',
                    'value' => false,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function isAllowAddProbationLeave(Request $request)
    {

        $isAllowed = $request->input('allow');

        if ($isAllowed) {
            $settingadd = Setting::where('key', 'allow_to_add_probation_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'allow_to_add_probation_leave',
                    'value' => true,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'allow_to_add_probation_leave',
                    'value' => true,
                ]);
            }
        } else {
            $settingadd = Setting::where('key', 'allow_to_add_probation_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'allow_to_add_probation_leave',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'allow_to_add_probation_leave',
                    'value' => false,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function dailyLeavePolicy(Request $request)
    {
        $isAllowed = $request->input('allow');
        if ($isAllowed) {
            $settingadd = Setting::where('key', 'daily_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'daily_leave_policy',
                    'value' => true,
                ]);
                $monthlyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'is_month_wise_show_leave',
                    ],
                    [
                        'value' => false,
                    ]
                );
                $annualLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'annual_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            } else {
                $settingadd = Setting::create([
                    'key'   => 'daily_leave_policy',
                    'value' => true,
                ]);
                $monthlyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'is_month_wise_show_leave',
                    ],
                    [
                        'value' => false,
                    ]
                );
                $annualLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'annual_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            }
        } else {
            $settingadd = Setting::where('key', 'daily_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'daily_leave_policy',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'daily_leave_policy',
                    'value' => false,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function isMonthWiseShowLeave(Request $request)
    {
        $isAllowed = $request->input('allow');
        if ($isAllowed) {
            $settingadd = Setting::where('key', 'is_month_wise_show_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'is_month_wise_show_leave',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'daily_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
                $annualLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'annual_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            } else {
                $settingadd = Setting::create([
                    'key'   => 'is_month_wise_show_leave',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'daily_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
                $annualLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'annual_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            }
        } else {
            $settingadd = Setting::where('key', 'is_month_wise_show_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'is_month_wise_show_leave',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'is_month_wise_show_leave',
                    'value' => false,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function annualLeavePolicy(Request $request)
    {
        $isAllowed = $request->input('allow');
        if ($isAllowed) {
            $settingadd = Setting::where('key', 'annual_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'annual_leave_policy',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'daily_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
                $monthlyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'is_month_wise_show_leave',
                    ],
                    [
                        'value' => false,
                    ]
                );
            } else {
                $settingadd = Setting::create([
                    'key'   => 'annual_leave_policy',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'daily_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
                $monthlyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'is_month_wise_show_leave',
                    ],
                    [
                        'value' => false,
                    ]
                );
            }
        } else {
            $settingadd = Setting::where('key', 'annual_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'annual_leave_policy',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'annual_leave_policy',
                    'value' => false,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function newUserDailyLeavePolicy(Request $request)
    {
        $isAllowed = $request->input('allow');
        if ($isAllowed) {
            $settingadd = Setting::where('key', 'new_user_daily_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'new_user_daily_leave_policy',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'new_user_monthly_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            } else {
                $settingadd = Setting::create([
                    'key'   => 'new_user_daily_leave_policy',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'new_user_monthly_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            }
        } else {
            $settingadd = Setting::where('key', 'new_user_daily_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'new_user_daily_leave_policy',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'new_user_daily_leave_policy',
                    'value' => false,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function newUserMonthlyLeavePolicy(Request $request)
    {
        $isAllowed = $request->input('allow');
        if ($isAllowed) {
            $settingadd = Setting::where('key', 'new_user_monthly_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'new_user_monthly_leave_policy',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'new_user_daily_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            } else {
                $settingadd = Setting::create([
                    'key'   => 'new_user_monthly_leave_policy',
                    'value' => true,
                ]);
                $dailyLeavePolicy = Setting::updateOrCreate(
                    [
                        'key' => 'new_user_daily_leave_policy',
                    ],
                    [
                        'value' => false,
                    ]
                );
            }
        } else {
            $settingadd = Setting::where('key', 'new_user_monthly_leave_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'new_user_monthly_leave_policy',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'new_user_monthly_leave_policy',
                    'value' => false,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function leaveAllowInProbation(Request $request)
    {
        $isAllowed = $request->input('allow');
        if ($isAllowed) {
            $settingadd = Setting::where('key', 'leave_probation_module')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'leave_probation_module',
                    'value' => true,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'leave_probation_module',
                    'value' => true,
                ]);
            }

        } else {
            $settingadd = Setting::where('key', 'leave_probation_module')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'leave_probation_module',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'leave_probation_module',
                    'value' => false,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function leaveRecurringPolicy(Request $request)
    {
        $isAllowed = $request->input('allow');
        if ($isAllowed) {
            $settingadd = Setting::where('key', 'leave_recurring_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'leave_recurring_policy',
                    'value' => $request->recurring_policy,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'leave_recurring_policy',
                    'value' => $request->recurring_policy,
                ]);
            }
        } else {
            $settingadd = Setting::where('key', 'leave_recurring_policy')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'leave_recurring_policy',
                    'value' => $request->recurring_policy,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'leave_recurring_policy',
                    'value' => $request->recurring_policy,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function updateToMonthWiseLeave($user, $leaveType)
    {

        try {
            $user_id         = $user->id;
            $total_days      = $leaveType->days;
            $oneYearBack     = Carbon::now()->subYear()->year;
            $startYear       = '01-01-' . date('Y');
            $newYear         = Carbon::now()->format('d-m-Y');
            $yearMonth       = 12;
            $currentYearDate = Carbon::now();
            $joining_date    = Carbon::parse($user->workDetail?->joining_date);
            $daysDiff        = $currentYearDate->diffInDays($joining_date);

            $checkLeaveBalance = LeaveBalance::where([
                'user_id'       => $user_id,
                'leave_type_id' => $leaveType->id,
                'year'          => date('Y'),
            ])->first();

            $checkmonthwise = Setting::where('key', 'is_month_wise_show_leave')->value('value');

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

            if ($checkLeaveBalance) {
                $total_days = $checkLeaveBalance->available;
                if ($checkmonthwise == 1) {
                    if ($is_vacation_leave && $is_vacation_leave->id == $leaveType->id) {
                        $backdays          = 0;
                        $totalmonth        = Carbon::parse($startYear)->diffInMonths($newYear);
                        $backdays          = ($total_days / $yearMonth) * $totalmonth;
                        $totalAvailableDay = $total_days;

                        if ($daysDiff <= 365) {
                            $leavewiseday    = ($total_days / $yearMonth);
                            $totalmonth      = Carbon::parse($joining_date)->diffInMonths($newYear);
                            $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');
                            if ($monthwise2leave == 1) {
                                if ($totalmonth <= 6) {
                                    $leavewiseday = 2;
                                }
                            }
                            $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                            if ($yearGiven2Leave == 1) {
                                if ($totalmonth <= 12) {
                                    $leavewiseday = 2;
                                }
                            }
                            $backdays = $leavewiseday * $totalmonth;
                        }
                        $total_days_of_month = $backdays;

                        $diff = $total_days_of_month;
                        if ($diff == floor($diff) + 0.5) {
                            $availableDay = $diff;
                        } else {
                            $availableDay = round($diff);
                        }
                        if ($is_dil_leave && $is_dil_leave->name == $leaveType->name) {
                            $availableDay = $diff;
                        }
                        $checkLeaveBalance->available    = $totalAvailableDay;
                        $checkLeaveBalance->monthwiseDay = $availableDay;
                        $checkLeaveBalance->save();
                    } else {

                        $diff = $total_days;
                        if ($diff == floor($diff) + 0.5) {
                            $availableDay = $diff;
                        } else {
                            $availableDay = round($diff);
                        }
                        if ($is_dil_leave && $is_dil_leave->name == $leaveType->name) {
                            $availableDay = $diff;
                        }
                        $checkLeaveBalance->available    = $availableDay;
                        $checkLeaveBalance->monthwiseDay = $availableDay;
                        $checkLeaveBalance->save();
                    }

                } else {

                    $diff = $total_days;
                    if ($diff == floor($diff) + 0.5) {
                        $availableDay = $diff;
                    } else {
                        $availableDay = round($diff);
                    }
                    if ($is_dil_leave && $is_dil_leave->name == $leaveType->name) {
                        $availableDay = $diff;
                    }
                    $checkLeaveBalance->available    = $availableDay;
                    $checkLeaveBalance->monthwiseDay = $availableDay;
                    $checkLeaveBalance->save();
                }
            } else {
                if ($checkmonthwise == 1) {
                    if ($is_vacation_leave && $is_vacation_leave->id == $leaveType->id) {
                        $backdays          = 0;
                        $totalmonth        = Carbon::parse($startYear)->diffInMonths($newYear);
                        $backdays          = ($total_days / $yearMonth) * $totalmonth;
                        $totalAvailableDay = $total_days;
                        if ($daysDiff <= 365) {
                            $leavewiseday    = ($total_days / $yearMonth);
                            $totalmonth      = Carbon::parse($joining_date)->diffInMonths($newYear);
                            $monthwise2leave = Setting::where('key', 'is_month_wise_2_leave')->value('value');
                            if ($monthwise2leave == 1) {
                                if ($totalmonth <= 6) {
                                    $leavewiseday = 2;
                                }
                            }
                            $yearGiven2Leave = Setting::where('key', 'is_year_given_2_leave')->value('value');
                            if ($yearGiven2Leave == 1) {
                                if ($totalmonth <= 12) {
                                    $leavewiseday = 2;
                                }
                            }
                            $backdays = $leavewiseday * $totalmonth;
                        }
                        $monthdays = $backdays;

                        $diff = $monthdays;
                        if ($diff == floor($diff) + 0.5) {
                            $availableDay = $diff;
                        } else {
                            $availableDay = round($diff);
                        }
                        if ($is_dil_leave && $is_dil_leave->name == $leaveType->name) {
                            $availableDay = $diff;
                        }
                        $balance = LeaveBalance::updateOrCreate(
                            [
                                'user_id'       => $user_id,
                                'year'          => date('Y'),
                                'leave_type_id' => $leaveType->id,
                            ],
                            [
                                'available'    => $totalAvailableDay,
                                'monthwiseDay' => $availableDay,
                            ]
                        );
                    } else {

                        $diff = $total_days;
                        if ($diff == floor($diff) + 0.5) {
                            $availableDay = $diff;
                        } else {
                            $availableDay = round($diff);
                        }
                        if ($is_dil_leave && $is_dil_leave->name == $leaveType->name) {
                            $availableDay = $diff;
                        }
                        $balance = LeaveBalance::updateOrCreate(
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

                    $diff = $total_days;
                    if ($diff == floor($diff) + 0.5) {
                        $availableDay = $diff;
                    } else {
                        $availableDay = round($diff);
                    }
                    if ($is_dil_leave && $is_dil_leave->name == $leaveType->name) {
                        $availableDay = $diff;
                    }
                    $balance = LeaveBalance::updateOrCreate(
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

            return true;
        } catch (\Exception $e) {
            \Log::error('Error in updateToMonthWiseLeave: ' . $e->getMessage());
            return false;
        }
    }

    public function isMonthWise2LeaveAdd(Request $request)
    {

        $isAllowed = $request->input('allow');

        if ($isAllowed) {
            $settingadd  = Setting::where('key', 'is_month_wise_2_leave')->first();
            $yearsetting = Setting::where('key', 'is_year_given_2_leave')->first();

            if ($settingadd) {
                if ($yearsetting) {
                    $yearsetting->update([
                        'key'   => 'is_year_given_2_leave',
                        'value' => false,
                    ]);
                }
                $settingadd->update([
                    'key'   => 'is_month_wise_2_leave',
                    'value' => true,
                ]);
            } else {
                if ($yearsetting) {
                    $yearsetting->update([
                        'key'   => 'is_year_given_2_leave',
                        'value' => false,
                    ]);
                }
                $settingadd = Setting::create([
                    'key'   => 'is_month_wise_2_leave',
                    'value' => true,
                ]);
            }
        } else {
            $settingadd = Setting::where('key', 'is_month_wise_2_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'is_month_wise_2_leave',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'is_month_wise_2_leave',
                    'value' => false,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function yearGiven2Leave(Request $request)
    {

        $isAllowed = $request->input('allow');

        if ($isAllowed) {
            $settingadd   = Setting::where('key', 'is_year_given_2_leave')->first();
            $monthsetting = Setting::where('key', 'is_month_wise_2_leave')->first();

            if ($settingadd) {
                if ($monthsetting) {
                    $monthsetting->update([
                        'key'   => 'is_month_wise_2_leave',
                        'value' => false,
                    ]);
                }
                $settingadd->update([
                    'key'   => 'is_year_given_2_leave',
                    'value' => true,
                ]);
            } else {
                if ($monthsetting) {
                    $monthsetting->update([
                        'key'   => 'is_month_wise_2_leave',
                        'value' => false,
                    ]);
                }
                $settingadd = Setting::create([
                    'key'   => 'is_year_given_2_leave',
                    'value' => true,
                ]);
            }

        } else {
            $settingadd = Setting::where('key', 'is_year_given_2_leave')->first();
            if ($settingadd) {
                $settingadd->update([
                    'key'   => 'is_year_given_2_leave',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key'   => 'is_year_given_2_leave',
                    'value' => false,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function previousYearLeavesReport(Request $request)
    {

        $types = LeaveType::when($request->type_id, function ($query, $type_id) {
            return $query->whereIn('id', $type_id);
        })->get();

        canPerform('Manage Leave');
        $filterEmployees = User::whereIn('id', $request->employee ?? [])->get();
        $users           = $dates           = [];
        $departmentId    = '';
        $type_id         = '';
        $searchEmp       = '';
        $search          = false;
        $query           = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });
        if ($request->post()) {

            $search       = true;
            $departmentId = $request->department_id;
            $type_id      = $request->type_id;
            $searchEmp    = $request->search_emp;

            if ($departmentId !== 'all') {
                $query->where('department_id', $departmentId);
            }

            if (! empty($searchEmp)) {
                $query->where('name', 'like', '%' . $searchEmp . '%');
            }
        }
        $users = $query->get();

        view()->share('activeLink', 'previousyear-leaves-report');
        return view('leave::leave.previousreport', compact('types', 'filterEmployees', 'users', 'departmentId', 'searchEmp', 'search', 'type_id'));
    }

    public function sampleUpdateleaveToExcel(Request $request)
    {

        canPerform('Manage Leave');

        $users = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->get();
        $exportExcel = [];
        $headers     = [];

        $types = LeaveType::get(['id', 'name', 'days']);

        foreach ($users as $i => $user) {
            $exportExcel[$i]['Employee ID']   = $user->employee_id;
            $exportExcel[$i]['Employee Name'] = $user->name;
            if ($i == 0) {
                $headers[] = 'Employee ID';
                $headers[] = 'Employee Name';
            }

            foreach ($types as $type) {
                if ($i == 0) {
                    $headers[] = $type->name;
                }
                $exportExcel[$i][$type->name] = calculatePendingLeave($type, $user->id);
            }
        }
        $export = new LeaveUpdateSampleExport($exportExcel, $headers);
        return Excel::download($export, 'sample_for_update_leave.xlsx');
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

    public function updateLeaveToExcel(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:xlsx',
        ], [
            'file.required' => __trans('Please upload file to import users'),
        ]);
        $response = getErrorResponse();
        try {
            $import = new LeaveUpdateImport();
            $import->import($request->file);

            $failedRows = $import->getFailedRows();

            if (! empty($failedRows)) {
                $filePath = 'uploads/failedexport/employee_leave_update_import_failed.xlsx';
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                try {
                    Excel::store(new FailedRowsLeaveUpdateExport($failedRows), $filePath, 'real_public');
                } catch (\Exception $e) {
                    \Log::error('Error storing Excel file: ' . $e->getMessage());
                    print_r($e->getMessage());
                    die();
                }
                $response                 = getSuccessResponse(createFlashMessage('File', 'imported partially'));
                $response['download_url'] = asset($filePath);
            } else {
                $response = getSuccessResponse(createFlashMessage('File', 'imported'));
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
