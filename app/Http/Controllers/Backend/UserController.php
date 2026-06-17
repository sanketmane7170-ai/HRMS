<?php
namespace App\Http\Controllers\Backend;

use App\Clean\CleanUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Department;
use App\Models\DepartureReason;
use App\Models\Division;
use App\Models\EmployeeWorkingDay;
use App\Models\endOfServicePolicy;
use App\Models\offBoarding;
use App\Models\PreviousLeaveBalance;
use App\Models\User;
use App\Models\UserDocument;
use App\Models\UserSettlement;
use App\Notifications\User\WelcomeNotification;
use App\Services\UserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Payroll\Entities\SetAllowanceDeducation;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Traits\SalaryCalculation;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    use SalaryCalculation;
    public function __construct()
    {
        view()->share('activeLink', 'users');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        canPerform('Manage User');
        if ($request->ajax()) {
            $statuses = $request->statuses ?? ['active', 'in-active'];
            $data     = User::query()
                ->visibleForAuthUser(auth()->user());

            if (! in_array('all', $statuses)) {
                $data->whereIn('users.status', $statuses);
            }

            $data->with(['department:id,name', 'roles', 'aiPhoto'])
                ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
                ->leftJoin('designations', 'users.designation_id', '=', 'designations.id')
                ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('users.*', 'roles.name as role_name', 'departments.name as department_name', 'divisions.name as division_name', 'designations.name as designation_name')
                ->orderByRaw("CASE
                    WHEN users.status = 'active' THEN 1
                    WHEN users.status = 'in-active' THEN 2
                    WHEN users.status = 'resigned' THEN 3
                    WHEN users.status = 'terminated' THEN 4
                    ELSE 5
                END ASC")
                ->groupBy('users.id', 'roles.name', 'departments.name', 'divisions.name', 'designations.name');
            if ($request->has('department_ids') && is_array($request->department_ids)) {
                $data->whereIn('users.department_id', $request->department_ids);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('id', function ($row) {
                    $html = '<a href=' . route('backend.users.show', $row) . '><span class="badge" style="background:#E1F1FF;color: #272727;">' . $row->employee_id . '</span></a>';
                    return $html;
                })
                ->editColumn('status', function ($data) {
                    $checked = $data->status == User::STATUS_ACTIVE ? "checked" : '';
                    $status  = $data->status == User::STATUS_ACTIVE ? "in-active" : 'active';
                    $action  = route('backend.users.update-status', [$data, $status]);

                    $html = createToggleButton('status', $action, $checked, __trans('aer_you_sure_want_to_update_user_status?'));
                    return $html;
                })
                ->addColumn('roles', function ($user) {
                    $html = '';
                    foreach ($user->getRoleNames() as $rolename) {
                        $html .= '<span class="badge text-white me-2" style="background:#' . rand(100000, 999999) . '">' . ucwords($rolename) . '</span>';
                    }
                    return $html;
                })
                ->editColumn('created_at', function ($data) {
                    return $data->created_at->format('d/m/Y'); // Updated by Sanket
                })
                ->editColumn('ai_photo', function ($row) {
                    if ($row->profile_image) {
                        // $assetPath = asset('uploads/profile/'.$row->profile_image);
                        return '<button class="btn btn-success btn-sm" onclick="openProfile(\'' . $row->profile_image . '\')">View Photo</button>';
                    }
                    return 'Not Available';
                })
                ->filterColumn('department_name', function ($query, $keyword) {
                    $query->where('departments.name', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('division_name', function ($query, $keyword) {
                    $query->where('divisions.name', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('designation_name', function ($query, $keyword) {
                    $query->where('designations.name', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('role_name', function ($query, $keyword) {
                    $query->where('roles.name', 'LIKE', "%{$keyword}%");
                })
                ->addColumn('action', function ($row) {
                    $url          = route('backend.users.edit', $row);
                    $deleteUrl    = route('backend.users.destroy', $row);
                    $takePhotoUrl = route('backend.takePhoto', $row);
                    $addBranchUrl = route('backend.assignBranch', $row);
                    $btn          = createActionDropdownList([
                        [$url, "Edit", 'fa fa-edit', ''],
                        [$deleteUrl, "Delete", 'fa fa-trash', 'action-button'],
                        [$addBranchUrl, "Assign other branch", 'fa fa-plus', 'edit-button'],
                        [$takePhotoUrl, "Take Photo", 'fa fa-camera', ''],
                    ]);
                    return $btn;
                })
                ->rawColumns(['action', 'name', 'roles', 'id', 'ai_photo', 'status'])
                ->make(true);
        }
        $departmentId = $request->input('department_id', []);
        if (! is_array($departmentId)) {
            $departmentId = [$departmentId];
        }

        return view('backend.users.index', compact('departmentId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        canPerform('Create User');
        return view('backend.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request, UserService $userService)
    {
        canPerform('Create User');
        $response = getErrorResponse();
        try {
            $user = $userService->add($request);
            if ($request->passport_number || $request->passport_issue_date || $request->passport_expiry_date || $request->passport_place_of_issue || $request->passport_country) {
                $user->documents()->create([
                    'original_name'  => 'Passport',
                    'path'           => '',
                    'serial_number'  => $request->passport_number,
                    'issue_date'     => $request->passport_issue_date,
                    'expiry_date'    => $request->passport_expiry_date,
                    'place_of_issue' => $request->passport_place_of_issue,
                    'country_name'   => $request->passport_country,
                    'type'           => \App\Enums\Document::Passport,
                ]);
            }

            if ($request->hasFile('profile_image')) {
                $user->profile_image = $this->upload($request->profile_image, '/uploads/profile', $user->profile_image);
                $user->save();
            }

            if ($request->pic_issue_date != null && $request->pic_expiry_date != null) {
                $picpath = '';
                if ($request->hasFile('pic_doc')) {
                    $file_obj = $request->pic_doc;
                    $fileName = time() . '.' . $file_obj->extension();
                    $path     = public_path('uploads/users/' . $user->id . '/pic_certification/');
                    if (! file_exists($path)) {
                        mkdir($path, 0775, true);
                    }
                    $file_obj->move($path, $fileName);
                    $picpath = 'uploads/users/' . $user->id . '/pic_certification/' . $fileName;
                }
                $user->documents()->create([
                    'original_name' => 'pic certification',
                    'path'          => $picpath,
                    'issue_date'    => $request->pic_issue_date,
                    'expiry_date'   => $request->pic_expiry_date,
                    'type'          => 'pic_certification',
                ]);
            }
            $response             = getSuccessResponse(createFlashMessage('User', 'created'));
            $response['redirect'] = route('backend.users.show', $user);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        canPerform('Manage User');
        $reasons    = \App\Models\DepartureReason::select('id', 'name')->get();
        $settlement = UserSettlement::where('user_id', $user->id)->first();
        $allowance  = SetAllowanceDeducation::get();
        $offboard   = offBoarding::where('user_id', $user->id)->first();

        // Auto-sync from Resignation Module if offboarding is missing but resignation is approved
        if (! $offboard) {
            $approvedResignation = \Modules\Resignation\Entities\Resignation::where('employee_id', $user->id)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($approvedResignation) {
                $offboard = offBoarding::create([
                    'user_id'             => $user->id,
                    'departure_date'      => $approvedResignation->approved_last_working_date,
                    'resignation_reason'  => $approvedResignation->reason,
                    'departure_reason_id' => 6, // Default to Resignation
                ]);
            }
        }

        $absentstatus = AttendanceStatus::Absent;
        $absent_count = Attendance::where(
            [
                'user_id' => $user->id,
                'status'  => $absentstatus,
            ]
        )->count();
        $gross_value = $this->getGrossSalary($user, '', '', '', '');
        $salary      = $this->allSalaryCalculations($user, '', '', '', '');

        // Eager load relations for settlement PDF
        $user->load(['leaveBalances.leaveType', 'airTicketsDetail', 'salary', 'bankDetail', 'profile.country', 'division', 'department', 'designation']);

        return view('backend.users.show', compact('user', 'reasons', 'gross_value', 'salary', 'settlement', 'allowance', 'offboard', 'absent_count'));
    }

    public function storeAbsentDays(Request $request)
    {
        $userId     = $request->input('user_id');
        $absentDays = $request->input('absent_days');

        $offBoarding = offBoarding::where('user_id', $userId)->first();
        if ($offBoarding) {
            $offBoarding->update([
                'absent_days' => $absentDays,
            ]);
            return response()->json(['success' => true, 'message' => 'Absent days recorded successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Settlement record not found for the user.'], 422);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        canPerform('Edit User');
        $leave_balance        = PreviousLeaveBalance::with('leave_name')->where('user_id', $user->id)->latest()->get();
        $current_role         = optional($user->getCurrentRole());
        $allowance            = SetAllowanceDeducation::get();
        $selectedDepartmentId = $user->department_id;
        $selectedDivisionId   = $user->division_id;
        $picdocuments         = UserDocument::where('user_id', $user->id)->where('type', 'pic_certification')->first();

        return view('backend.users.edit', compact('user', 'leave_balance', 'current_role', 'allowance', 'selectedDepartmentId', 'selectedDivisionId', 'picdocuments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, User $user, UserService $userService)
    {

        canPerform('Edit User');
        if ($request->input('is_previous_leave')) {
            foreach ($request->input('leave_balance') as $data) {
                $leaveType = LeaveType::find($data['leave_type_id']);
                // Check if the leave type is recurring
                if ($data['days'] > 0 && ! $leaveType->is_recurring) {
                    $response = getErrorResponse($leaveType->name . ' Leave type is not recurring enabled.');
                    return response()->json($response);
                }
                $currentYear          = Carbon::now()->year;
                $previousLeaveBalance = PreviousLeaveBalance::where([
                    'user_id'       => $user->id,
                    'leave_type_id' => $data['leave_type_id'],
                ])
                    ->whereYear('created_at', $currentYear)
                    ->first();

                if ($previousLeaveBalance) {
                    $previousLeaveBalance->update([
                        'days'    => $data['days'],
                        'comment' => $data['comment'],
                    ]);
                } else {
                    $previousLeaveBalance = PreviousLeaveBalance::create([
                        'user_id'       => $user->id,
                        'leave_type_id' => $data['leave_type_id'],
                        'created_at'    => date('Y'),
                        'days'          => $data['days'],
                        'comment'       => $data['comment'],
                    ]);
                }
                $user->update(['is_previous_leave' => '1']);
            }
        }
        if ($request->hasFile('profile_image')) {
            $user->profile_image = $this->upload($request->profile_image, '/uploads/profile', $user->profile_image);
        }
        if (isset($request->company_document_id) && $request->company_document_id != "NA") {
            $user->company_document_id = $request->company_document_id;
        }

        if ($request->passport_number || $request->passport_issue_date || $request->passport_expiry_date || $request->passport_place_of_issue || $request->passport_country) {
            $user->documents()->updateOrCreate(
                ['type' => \App\Enums\Document::Passport],
                [
                    'original_name'  => 'Passport',
                    'path'           => '',
                    'serial_number'  => $request->passport_number,
                    'issue_date'     => $request->passport_issue_date,
                    'expiry_date'    => $request->passport_expiry_date,
                    'place_of_issue' => $request->passport_place_of_issue,
                    'country_name'   => $request->passport_country,
                ]
            );
        }
        //update PIC certification
        if ($request->pic_issue_date != null && $request->pic_expiry_date != null) {
            $picdata = $user->documents()->where('type', 'pic_certification')->first();
            if ($picdata) {
                $picpath = $picdata->path;
            } else {
                $picpath = '';
            }
            if ($request->hasFile('pic_doc')) {
                $file     = $request->pic_doc;
                $fileName = time() . '.' . $file->extension();
                $path     = public_path('uploads/users/' . $user->id . '/pic_certification/');
                if (! file_exists($path)) {
                    mkdir($path, 0775, true);
                }
                $ret     = $file->move($path, $fileName);
                $picpath = 'uploads/users/' . $user->id . '/pic_certification/' . $fileName;
            }
            $user->documents()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type'    => 'pic_certification',
                ],
                [
                    'original_name' => 'pic certification',
                    'path'          => $picpath,
                    'issue_date'    => $request->pic_issue_date,
                    'expiry_date'   => $request->pic_expiry_date,
                ]
            );
        }
        // end
        $user->save();
        $response = getErrorResponse();
        // dd(1);
        try {
            $user                 = $userService->update($user, $request);
            $response             = getSuccessResponse(createFlashMessage('User', 'updated'));
            $response['redirect'] = route('backend.users.show', $user);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, CleanUserData $cleanUserData)
    {
        canPerform('Delete User');
        $response = getErrorResponse();
        try {
            $cleanUserData->delete($user->id);
            $response = [
                'success' => true,
                'message' => createFlashMessage('User', 'deleted'),
            ];
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function sendWelcomeNotification(User $user): JsonResponse
    {
        $user->notify(new WelcomeNotification);

        return response()->json(
            getSuccessResponse(createFlashMessage('Notification Email', 'send'))
        );
    }

    public function updateUserStatus(User $user, $status): JsonResponse
    {
        canPerform('Edit User');
        $response = getErrorResponse();
        try {
            $user->status = $status;
            $user->save();

            $response = getSuccessResponse(createFlashMessage('User Status', 'updated'));
        } catch (Exception $e) {
        }

        return response()->json($response);
    }

    public function leaveCalculate(User $user)
    {
        $types = LeaveType::get(['id', 'name', 'days']);
        // dd($types);
        $result        = [];
        $pick_columns  = ['amount', 'percentage_amount', 'title', 'id'];
        $current_month = date('m');
        $current_year  = date('Y');

        $allowance         = UserSalaryAllowance::select($pick_columns)->where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        $notfixedallowance = UserSalaryAllowance::select($pick_columns)->where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->get();
        $allowance         = $allowance->merge($notfixedallowance);

        $deduction         = UserDeduction::select($pick_columns)->where('user_id', $user->id)->where(['month_code' => $current_month, 'year' => $current_year])->get();
        $notfixeddeduction = UserDeduction::select($pick_columns)->where('user_id', $user->id)->where('is_fixed_for_current_month', 0)->get();
        $deduction         = $deduction->merge($notfixeddeduction);
        $remaning_amount   = UserSettlement::where('user_id', $user->id)->first();

        $absentstatus = AttendanceStatus::Absent;
        $absent_count = Attendance::where(
            [
                'user_id' => $user->id,
                'status'  => $absentstatus,
            ]
        )->count();

        $additions  = 0;
        $deductions = 0;
        $leave_name = '';
        if (isset($remaning_amount)) {
            $additions  = $remaning_amount->total_additions;
            $deductions = $remaning_amount->total_deductions;
            $leave_name = $remaning_amount->leave_name;
        }
        foreach ($types as $type) {
            try {
                $balance = LeaveBalance::where(
                    [
                        'user_id'       => $user->id,
                        'year'          => date('Y'),
                        'leave_type_id' => $type->id,
                    ],
                )->first();
                $result[] = [
                    'type_id' => $type->id,
                    'name'    => $type->name,
                    'balance' => $balance ? $balance->available : 0,
                ];
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }
        }
        $additionalData = ['total_additions' => 500, 'total_deductions' => 600];
        return response()->json([
            'message'        => __trans('leave_data_fetched'),
            'data'           => $result,
            'extraPara'      => $additionalData,
            'addition_list'  => $allowance,
            'deduction_list' => $deduction,
            'additions'      => $additions,
            'deductions'     => $deductions,
            'leave_name'     => $leave_name,
        ]);
    }

    private function allSalaryCalculations($user, $month, $year, $start_date, $end_date)
    {
        $gross_salary     = 0;
        $net_salary       = 0;
        $total_net_salary = 0;

        $gross_salary     = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
        $net_salary       = $this->getNetSalaryAsPerAttendance($user, $month, $year, $start_date, $end_date);
        $total_net_salary = $this->getTotalNetSalary($user, $month, $year, $start_date, $end_date);

        $monthly_fixed     = $this->monthlyfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
        $monthly_not_fixed = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);

        $basic_salary = $user->salary ? $user->salary->basic : 0;
        // $overtime_amount = UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
        $overtime_amount = UserOvertime::where('user_id', $user->id)
            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
            ->sum('calculated_amount');

        $total_deduction = $monthly_fixed['total_deduction'] + $monthly_not_fixed['total_deduction'];
        $total_allowance = $monthly_fixed['total_allowance'] + $monthly_not_fixed['total_allowance'];

        $collection = [
            'gross'           => $gross_salary,
            'total_allowance' => $total_allowance,
            'total_overtime'  => $overtime_amount,
            'total_deduction' => $total_deduction,
            'net'             => $net_salary,
            'total_net'       => $total_net_salary,
        ];

        return $collection;
    }

    public function postFinalSettlement(Request $request, User $user)
    {
        canPerform('Create Shift');
        $data            = $request->all_result;
        $data['user_id'] = $user->id;
        if (! empty($data['hire_date'])) {
            try {
                $data['hire_date'] = Carbon::createFromFormat('d/m/Y', $data['hire_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $data['hire_date'] = null;
            }
        }
        $data['leave_name'] = $request->all_result['leave_names'] ?? null;

        $response = getErrorResponse();
        try {
            UserSettlement::create($data);
            $user->settlement_status = 1;
            $user->save();
            $response = getSuccessResponse(createFlashMessage('Transction', 'Successfully'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function getTransactionList(Request $request)
    {
        view()->share('activeLink', 'transaction');
        if ($request->ajax()) {
            $data = UserSettlement::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    $user = User::find($row->user_id);
                    return $user->name;
                })
                ->editColumn('departure_reason', function ($row) {
                    $reason = DepartureReason::find($row->departure_reason_id);
                    return $reason->name;
                })
                ->make(true);
        }
        return view('payroll::settlement.index');
    }

    // public function exportSettlementList(){
    //     return Excel::download(new SettlementListExport, 'settlement_list_' . time() . '.xlsx');
    // }

    public function takePhoto($id)
    {

        return view('backend.users.takephoto', compact(['id']));
    }

    public function submitPhoto(Request $request)
    {
        // return $request->image;
        // if ($request->hasFile('image')) {
        //     $file = $request->file('image');
        //     $name = time() . rand(1, 100) . '.' . $file->getClientOriginalExtension();
        //     $upload_path = 'assets/backend/img/aiphotos/';
        //     $file->move($upload_path, $name);
        // }else{
        //     return "hi";
        // }

        $img = $request->image;

        $folderPath = "uploads/profile/";
        if (! file_exists($folderPath)) {
            // If the folder does not exist, create it
            mkdir($folderPath, 0777, true);
        }

        $image_parts    = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type     = $image_type_aux[1];

        $image_base64 = base64_decode($image_parts[1]);
        $fileName     = uniqid() . '.png';

        $file = $folderPath . $fileName;
        file_put_contents($file, $image_base64);

        // $AiPhoto = AiPhoto::where('user_id', $request->id)->first();
        // if($AiPhoto)
        // {
        //     $AiPhoto = AiPhoto::find($AiPhoto->id);
        //     $oldPhotoPath = "assets/backend/img/aiphotos/" . $AiPhoto->photo;
        //     if (file_exists($oldPhotoPath)) {
        //         unlink($oldPhotoPath);
        //     }
        // }else{
        //     $AiPhoto = new AiPhoto;
        // }
        // $AiPhoto->user_id = $request->id;
        // $AiPhoto->photo = '/uploads/profile/'.$fileName;
        // $AiPhoto->save();

        // $data['profile_image'] = '/uploads/profile/'.$fileName;
        // auth()->user()->id($data);
        $User         = User::find($request->id);
        $oldPhotoPath = $User->profile_image;
        if (file_exists($oldPhotoPath)) {
            unlink($oldPhotoPath);
        }
        $User->profile_image = '/uploads/profile/' . $fileName;
        $User->save();
        $flashMessage = createFlashMessage('Employee', 'Image updated');
        return redirect()->route('backend.users.index')->with('success', $flashMessage);
    }

    public function getWorkingDayPage(Request $request)
    {
        $year  = $request->year ? $request->year : date('Y');
        $month = $request->month ? $request->month : date('m');
        if ($request->ajax()) {
            $data = EmployeeWorkingDay::query()->where(['month_code' => $month, 'year' => $year])
                ->orderBy('id', 'ASC')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('id', function ($row) {
                    $id   = User::where('id', $row->user_id)->value('employee_id');
                    $html = '<a href=' . route('backend.users.show', $row->user_id) . '><span class="badge" style="background:#E1F1FF;color: #272727;">' . $id . '</span></a>';
                    return $html;
                })
                ->editColumn('name', function ($row) {
                    $name = '';
                    $name = User::where('id', $row->user_id)->value('name');
                    return $name;
                })
                ->editColumn('email', function ($row) {
                    $email = '';
                    $email = User::where('id', $row->user_id)->value('email');
                    return $email;
                })
                ->rawColumns(['name', 'id'])
                ->make(true);
        }
        return view('backend.users.working-day', compact('year', 'month'));
    }

    public function showEmployeeHierarchy()
    {
        $root = auth()->user(); // Or use a fixed top node, e.g., User::find(1)
        $tree = $this->buildTree($root->id);

        return view('employee.hierarchy', compact('tree'));
    }
    public function buildTree($rootUserId)
    {
        // Cache for all nodes to prevent duplication
        $nodeCache         = [];
        $parentConnections = [];

        // Fetch all users with work details
        $users = User::with(['roles', 'designation', 'profile', 'workDetail'])->get();

        // Step 1: Build Node Cache (Single Node Creation)
        foreach ($users as $user) {
            if (! isset($nodeCache[$user->id])) {
                $role            = $user->getCurrentRole();
                $designation     = $user->designation ? $user->designation->name : 'No Designation';
                $profileImageUrl = $user->profile_image_url ?: asset('default-profile.png');

                // Create the node
                $nodeCache[$user->id] = [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'role'          => $role ? $role->name : 'No Role',
                    'designation'   => $designation,
                    'profile_image' => $profileImageUrl,
                    'subordinates'  => [],
                ];
            }

            // Handle report_to_ids if workDetail exists
            if ($user->workDetail && $user->workDetail->report_to_ids) {
                $reportToIds = is_string($user->workDetail->report_to_ids)
                    ? json_decode($user->workDetail->report_to_ids, true)
                    : $user->workDetail->report_to_ids;

                // Ensure it's a valid array
                if (is_array($reportToIds)) {
                    foreach ($reportToIds as $parentId) {
                        // Record the parent connection
                        $parentConnections[$parentId][] = $user->id;
                    }
                }
            }
        }

        // Step 2: Build Parent-Child Connections
        foreach ($parentConnections as $parentId => $childIds) {
            if (isset($nodeCache[$parentId])) {
                foreach ($childIds as $childId) {
                    // Avoid self-loop and duplicate connections
                    if ($parentId !== $childId && isset($nodeCache[$childId])) {
                        if (! in_array($nodeCache[$childId], $nodeCache[$parentId]['subordinates'], true)) {
                            $nodeCache[$parentId]['subordinates'][] = &$nodeCache[$childId];
                        }
                    }
                }
            }
        }

        // Return the full tree starting from the root user
        return $nodeCache[$rootUserId] ?? [];
    }

    public function showEmployeeHierarchy1()
    {
        $users = User::with(['workDetail', 'roles', 'designation'])->get();

        $nodes = [];
        $links = [];

        // foreach ($users as $user) {
        //     // if($user->hasRole(User::ROLE_ADMIN)==false && isset($user->workDetail)  && (string)($user->workDetail->report_to_ids[0]) == null); {
        //     //     dd($user);
        //     //  continue;
        //     // }
        //     if (
        //         !$user->hasRole(User::ROLE_ADMIN) &&
        //         isset($user->workDetail) &&
        //         (
        //             empty($user->workDetail->report_to_ids) ||
        //             $user->workDetail->report_to_ids[0] === null
        //         )
        //     ) {
        //         continue;
        //     }

        //     $nodes[] = [
        //         'key' => $user->id,
        //         'name' => $user->name,
        //         'title' => optional($user->designation)->name ?? 'No Designation',
        //     ];

        //     $reportToIds = $user->workDetail->report_to_ids ?? [];
        //     foreach ($reportToIds as $managerId) {
        //         $links[] = [
        //             'from' => (int)$managerId,
        //             'to' => $user->id
        //         ];
        //     }
        // }
        foreach ($users as $user) {
            if (
                ! $user->hasRole(User::ROLE_ADMIN) &&
                isset($user->workDetail) &&
                (
                    empty($user->workDetail->report_to_ids) ||
                    $user->workDetail->report_to_ids[0] === null
                )
            ) {
                continue;
            }

            $reportToIds = $user->workDetail->report_to_ids ?? [];
            // Add this user as a node only if needed (skip if already added)
            $nodes[$user->id] = [
                'key'   => $user->id,
                'name'  => $user->name,
                'title' => optional($user->designation)->name ?? 'No Designation',
            ];

            foreach ($reportToIds as $managerId) {
                // ✅ Add manager as node too if not present
                if (! isset($nodes[$managerId])) {
                    $manager = $users->firstWhere('id', $managerId);
                    if ($manager) {
                        $nodes[$managerId] = [
                            'key'   => $manager->id,
                            'name'  => $manager->name,
                            'title' => optional($manager->designation)->name ?? 'No Designation',
                        ];
                    }
                }

                $links[] = [
                    'from' => (int) $managerId,
                    'to'   => $user->id,
                ];
            }
        }
        // dd($links);

        return view('employee.graph', compact('nodes', 'links'));
    }

    public function showRoleBasedHierarchy()
    {
        view()->share('activeLink', 'teams');
        $tree = getRoleBasedTree();
        return view('backend.users.hierarchy', compact('tree'));
    }

    public function getSettlementLeavePolicy(Request $request)
    {

        $data['spolicy'] = endOfServicePolicy::where('leave_type_id', $request->leave_type_id)->first();

        if ($data['spolicy']) {
            return response()->json([
                'status'  => true,
                'message' => 'End of service policy',
                'data'    => $data,
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No data found!',
                'data'    => null,
            ]);
        }
    }

    public function getoffBoarding(Request $request)
    {

        $data['off'] = offBoarding::where('user_id', $request->user_id)->first();
        $user        = User::where('id', $request->user_id)->first();

        if ($data['off']) {
            $data['settlementSalary'] = 0;
            if ($data['off']->settlement_type == 'settlement') {
                $updateboarding          = $data['off'];
                $salaryData              = json_decode($updateboarding->salary_month_day, true);
                $fullMonthSalary         = 0;
                $departureMonthsalary    = 0;
                $fullMonthAllowance      = 0;
                $fullMonthDeduction      = 0;
                $fullMonthOvertime       = 0;
                $fullMonthMonthlyExpense = 0;
                $fullMonthGrossSalary    = 0;
                $fullMonthWorkingDay     = 0;
                $detotal_allowance       = 0;
                $detotal_deduction       = 0;
                $deovertime_amount       = 0;
                $demonthly_expense       = 0;
                $degross_salary          = 0;
                $deworking_day           = 0;

                $workdetails = $user->workDetail()->first();
                $start_date  = Carbon::parse($data['off']->departure_date)->startOfMonth();
                $end_date    = Carbon::parse($data['off']->departure_date)->endOfDay();

                if ($workdetails->attendance_base == 'no') {
                    if (! empty($salaryData) && is_array($salaryData)) {
                        foreach ($salaryData as $smonth) {
                            $month    = (int) $smonth['month'];
                            $year     = date('Y');
                            $monthday = (int) $smonth['day'];

                            $departure_month = Carbon::parse($updateboarding->departure_date)->format('n');
                            if ($month == $departure_month) {
                                $monthly_not_fixed      = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
                                $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
                                $fixed_entity_deduction = array_sum($fixed_entity_deduction);
                                $total_deduction        = $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;

                                $detotal_deduction += $total_deduction;
                                // $deovertime_amount += UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                                $overtime_amount  = UserOvertime::where('user_id', $user->id)
                                    ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                                    ->sum('calculated_amount');
                                $demonthly_expense += $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);
                                $degross_salary     = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                                $deworking_day     += $monthday;

                                $departureMonthsalary = $this->getTotalNetSalary_byDay($user, $month, $updateboarding->departure_date, $monthday);
                            } else {
                                $monthly_not_fixed      = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
                                $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
                                $fixed_entity_deduction = array_sum($fixed_entity_deduction);
                                $total_deduction        = $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;

                                $fullMonthDeduction += $total_deduction;
                                // $fullMonthOvertime += UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                                $overtime_amount  = UserOvertime::where('user_id', $user->id)
                                    ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                                    ->sum('calculated_amount');
                                $fullMonthMonthlyExpense += $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);
                                $fullMonthGrossSalary     = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                                $fullMonthWorkingDay     += $monthday;

                                $working_days    = $monthday;
                                $fullMonthSalary = $fullMonthSalary + $this->getTotalNetsalaryWithoutAttendance($user, $month, date('Y'), $start_date, $end_date, $working_days);
                            }
                        }
                    }
                } else {
                    $monthString     = $data['off']->salary_month;
                    $months          = explode(',', $monthString);
                    $fullMonthSalary = 0;
                    foreach ($months as $month) {
                        $month           = (int) trim($month);
                        $year            = date('Y');
                        $departure_month = Carbon::parse($data['off']->departure_date)->format('n');
                        if ($month == $departure_month) {
                            $monthly_not_fixed      = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
                            $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
                            $fixed_entity_deduction = array_sum($fixed_entity_deduction);
                            $total_deduction        = $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;

                            $startDate = Carbon::parse($data['off']->departure_date)->startOfMonth();
                            $endDate   = Carbon::parse($data['off']->departure_date)->endOfDay();

                            $total_working_days = $user->attendances()
                                ->whereIn('status', [
                                    \Modules\Attendance\Enums\AttendanceStatus::Present,
                                    \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                ])
                                ->whereBetween('date', [$startDate, $endDate])
                                ->count();

                            $detotal_deduction += $total_deduction;
                            // $deovertime_amount += UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                            $overtime_amount  = UserOvertime::where('user_id', $user->id)
                                ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                                ->sum('calculated_amount');
                            $demonthly_expense += $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);
                            $degross_salary     = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                            $deworking_day     += $total_working_days;

                            $departureMonthsalary = $this->getTotalNetSalary_byDay($user, $month, $data['off']->departure_date);
                        } else {
                            $workdetails = $user->workDetail()->first();
                            if ($workdetails->attendance_base == 'yes') {
                                $monthly_not_fixed      = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
                                $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
                                $fixed_entity_deduction = array_sum($fixed_entity_deduction);
                                $total_deduction        = $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;

                                $paidleave = $this->paidLeaveCount($user, $year, $month, $start_date, $end_date);
                                $holiday   = $this->holdaydayCount($user, $year, $month, $start_date, $end_date);

                                $total_working_days = $user->attendances()
                                    ->whereIn('status', [
                                        \Modules\Attendance\Enums\AttendanceStatus::Present,
                                        \Modules\Attendance\Enums\AttendanceStatus::Weekend,
                                    ])
                                    ->whereBetween('date', [$start_date, $end_date])
                                    ->count();

                                $fullMonthDeduction += $total_deduction;
                                // $fullMonthOvertime += UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                                $overtime_amount  = UserOvertime::where('user_id', $user->id)
                                    ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                                    ->sum('calculated_amount');
                                $fullMonthMonthlyExpense += $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);
                                $fullMonthGrossSalary     = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                                $fullMonthWorkingDay     += $total_working_days + $paidleave + $holiday;

                                $fullMonthSalary = $fullMonthSalary + $this->getTotalNetSalary($user, $month, date('Y'), $start_date, $end_date);
                            }
                        }
                    }
                }

                $data['total_deduction'] = $fullMonthDeduction + $detotal_deduction;
                $data['overtime_amount'] = $fullMonthOvertime + $deovertime_amount;
                $data['monthly_expense'] = $fullMonthMonthlyExpense + $demonthly_expense;
                $data['gross_salary']    = $fullMonthGrossSalary ? $fullMonthGrossSalary : $degross_salary;
                $data['working_day']     = $fullMonthWorkingDay + $deworking_day;

                $data['settlementSalary'] = $fullMonthSalary + $departureMonthsalary;
            }

            return response()->json([
                'status'  => true,
                'message' => 'off boarding data',
                'data'    => $data,
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No data found!',
                'data'    => null,
            ]);
        }
    }

    public function addMonthDay(Request $request)
    {

        $offBoarding = offBoarding::where('user_id', $request->user_id)->first();
        $user        = User::where('id', $request->user_id)->first();

        if ($offBoarding) {
            $salaryData = json_decode($offBoarding->salary_month_day, true);
            $found      = false;
            if (! empty($salaryData) && is_array($salaryData)) {
                foreach ($salaryData as &$entry) {
                    if ($entry['month'] == $request->month) {
                        $entry['day'] = $request->monthday;
                        $found        = true;
                    }
                }
            }
            if (! $found) {
                $salaryData[] = ['month' => $request->month, 'day' => $request->monthday];
            }

            $updateboarding = offBoarding::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                ],
                [
                    'salary_month_day' => json_encode($salaryData),
                ]
            );
            $settlementSalary = 0;
            if ($updateboarding->settlement_type == 'settlement') {
                $salaryData = json_decode($updateboarding->salary_month_day, true);

                $fullMonthSalary      = 0;
                $departureMonthsalary = 0;

                $fullMonthAllowance      = 0;
                $fullMonthDeduction      = 0;
                $fullMonthOvertime       = 0;
                $fullMonthMonthlyExpense = 0;
                $fullMonthGrossSalary    = 0;
                $fullMonthWorkingDay     = 0;
                $detotal_allowance       = 0;
                $detotal_deduction       = 0;
                $deovertime_amount       = 0;
                $demonthly_expense       = 0;
                $degross_salary          = 0;
                $deworking_day           = 0;

                foreach ($salaryData as $smonth) {
                    $month      = (int) $smonth['month'];
                    $year       = date('Y');
                    $start_date = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
                    $end_date   = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
                    $monthday   = (int) $smonth['day'];

                    $departure_month = Carbon::parse($updateboarding->departure_date)->format('n');
                    if ($month == $departure_month) {
                        $monthly_not_fixed      = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
                        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
                        $fixed_entity_deduction = array_sum($fixed_entity_deduction);
                        $total_deduction        = $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;

                        $detotal_deduction += $total_deduction;
                        // $deovertime_amount += UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                        $overtime_amount  = UserOvertime::where('user_id', $user->id)
                            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                            ->sum('calculated_amount');
                        $demonthly_expense += $this->monthlyExpensesCalculation($user, $month, $year);
                        $degross_salary     = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                        $deworking_day     += $monthday;

                        $departureMonthsalary = $this->getTotalNetSalary_byDay($user, $month, $updateboarding->departure_date, $monthday);
                    } else {
                        $monthly_not_fixed      = $this->monthlynotfixedExpensesCalculation($user, $month, $year, $start_date, $end_date);
                        $fixed_entity_deduction = (isset($user->salary->fixed_deductions) && ! empty($user->salary->fixed_deductions)) ? json_decode($user->salary->fixed_deductions, true) : [];
                        $fixed_entity_deduction = array_sum($fixed_entity_deduction);
                        $total_deduction        = $monthly_not_fixed['total_deduction'] + $fixed_entity_deduction;

                        $fullMonthDeduction += $total_deduction;
                        // $fullMonthOvertime += UserOvertime::where('user_id', $user->id)->where(['month_code' => $month, 'year' => $year])->sum('calculated_amount');
                        $overtime_amount  = UserOvertime::where('user_id', $user->id)
                            ->whereBetween('date', [$start_date, $end_date]) // assuming you have a `date` column
                            ->sum('calculated_amount');
                        $fullMonthMonthlyExpense += $this->monthlyExpensesCalculation($user, $month, $year, $start_date, $end_date);
                        $fullMonthGrossSalary     = $this->getGrossSalary($user, $month, $year, $start_date, $end_date);
                        $fullMonthWorkingDay     += $monthday;

                        $working_days    = $monthday;
                        $fullMonthSalary = $fullMonthSalary + $this->getTotalNetsalaryWithoutAttendance($user, $month, date('Y'), $start_date, $end_date, $working_days);
                    }
                }
                $data['off'] = $updateboarding;

                $data['total_deduction'] = $fullMonthDeduction + $detotal_deduction;
                $data['overtime_amount'] = $fullMonthOvertime + $deovertime_amount;
                $data['monthly_expense'] = $fullMonthMonthlyExpense + $demonthly_expense;
                $data['gross_salary']    = $fullMonthGrossSalary ? $fullMonthGrossSalary : $degross_salary;
                $data['working_day']     = $fullMonthWorkingDay + $deworking_day;

                $data['settlementSalary'] = $fullMonthSalary + $departureMonthsalary;
            }

            return response()->json([
                'status'  => true,
                'message' => 'Settlement Salary',
                'data'    => $data,
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No data found!',
                'data'    => null,
            ]);
        }
    }

    public function removeMonthDay(Request $request)
    {

        $offBoarding = offBoarding::where('user_id', $request->user_id)->first();
        $user        = User::where('id', $request->user_id)->first();

        if ($offBoarding) {
            $salaryData = json_decode($offBoarding->salary_month_day, true);

            if (! empty($salaryData) && is_array($salaryData)) {
                // Remove the entry that matches the month
                $salaryData = array_filter($salaryData, function ($entry) use ($request) {
                    return $entry['month'] != $request->month;
                });

                // Optionally reindex the array to avoid gaps in keys
                $salaryData = array_values($salaryData);
            }

            // Save updated data
            $offBoarding->salary_month_day = json_encode($salaryData);
            $offBoarding->save();

            return response()->json([
                'status'  => true,
                'message' => 'Remove month day',
                'data'    => $offBoarding,
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'No data found!',
                'data'    => null,
            ]);
        }
    }

    public function storeOffBoarding(Request $request)
    {
        $data = $request->validate([
            'user_id'             => 'required',
            'departure_date'      => 'required|date',
            'settlement_type'     => 'required',
            'departure_reason_id' => 'required',
            'salary_month'        => 'required|array',
            'salary_month.*'      => 'integer|min:1|max:12',
        ]);

        try {
            $data['departure_date'] = Carbon::parse($request->departure_date)->format('Y-m-d');
            offBoarding::updateOrCreate(
                [
                    'user_id' => $request->user_id,
                ],
                [
                    'departure_date'      => $request->departure_date,
                    'settlement_type'     => $request->settlement_type,
                    'departure_reason_id' => $request->departure_reason_id,
                    'salary_month'        => implode(',', $request->salary_month),
                ]
            );

            $flashMessage = createFlashMessage('Off Boarding');
            return redirect()->route('backend.users.show', ['user' => $request->user_id, 'type' => 'offboarding'])->with('success', $flashMessage);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);
        try {
            Excel::import(new UserImport, $request->file('file'));
            return redirect()->back()->with('success', 'User Imported Successfully');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return redirect()->back()->with('error', $failures[0]->errors()[0]);
        }

    }

    public function rehire($id)
    {
        $off = offBoarding::where('user_id', $id)->first();

        if ($off) {
            $off->delete();
            $flashMessage = createFlashMessage('Off Boarding');
            return redirect()->back()->with('success', $flashMessage);
        } else {
            $flashMessage = createFlashMessage('No data found!', 'Successfully');
            return redirect()->route('backend.users.show', ['user' => $id, 'type' => 'offboarding'])->with('success', $flashMessage);
        }
    }

    public function assignBranch(Request $request, $id)
    {

        $user = User::where('id', $id)->first();
        if ($request->post()) {
            $user->assigned_branch_id = implode(',', $request->input('branches', []));
            $user->save();

            $response = getSuccessResponse(createFlashMessage('Assigned Branch'));
            return response()->json($response);
        }

        $departments      = Department::select('id', 'name')->get();
        $selectedBranches = explode(',', $user->assigned_branch_id);

        $html = view('backend.users.assignBranch', compact('departments', 'user', 'selectedBranches'))->render();

        $response = [
            'success' => true,
            'html'    => $html,
        ];

        return response()->json($response);
    }

    // public function getProbationEndDate(Request $request)
    // {

    //     $joiningDate = Carbon::parse($request->joining_date);

    //     $probationMonths = getSetting('probation_period_month');
    //     if ($probationMonths == '1_month') {
    //         $probationMonths = 1;
    //     } elseif ($probationMonths == '3_month') {
    //         $probationMonths = 3;
    //     } else {
    //         $probationMonths = 6;
    //     }

    //     $probationEndDate = $joiningDate->addMonths($probationMonths)->format('Y-m-d');

    //     return response()->json([
    //         'probation_end_date' => $probationEndDate,
    //     ]);
    // }
    public function getProbationEndDate(Request $request)
    {
        // parse date in d/m/Y format
        $joiningDate = Carbon::createFromFormat('d/m/Y', $request->joining_date);

        $probationMonths = getSetting('probation_period_month');

        if ($probationMonths == '1_month') {
            $probationMonths = 1;
        } elseif ($probationMonths == '3_month') {
            $probationMonths = 3;
        } else {
            $probationMonths = 6;
        }

        $probationEndDate = $joiningDate->addMonths($probationMonths)->format('Y-m-d');

        return response()->json([
            'probation_end_date' => $probationEndDate,
        ]);
    }

    public function getDivisions(Request $request)
    {
        $branchId = $request->get('branch_id');

        // $divisions = Division::where('branch_id', $branchId)->select('id', 'name')->get();
        $divisions = Division::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
                ->orWhere('branch_id', 0);
        })
            ->select('id', 'name')
            ->get();

        return response()->json($divisions);
    }
    public function sendWelcomeNotificationToAll(): JsonResponse
    {
        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        $response = Http::asForm()->post('https://superadmin.WorkPilot.io/api/v1/portal_data', [
            'subdomain' => request()->getHost(),
        ]);

        $unique_code = null;
        if ($response->successful()) {
            // safer extraction
            $unique_code = data_get($response->json(), 'data.data.unique_code');
        } else {
            Log::error('Failed to fetch portal data', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        $password = 'Welcome' . date('Y');

        foreach ($users as $user) {
            try {
                $user->notifyNow(new \App\Notifications\User\WelcomeNotificationImmediately(
                    $user->employee_id,
                    $user->email,
                    $user->phone,
                    $password,
                    $unique_code
                ));

                Log::info('Notification sent', [
                    'email'   => $user->email,
                    'user_id' => $user->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Notification send failed', [
                    'email'   => $user->email,
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return response()->json(
            getSuccessResponse(createFlashMessage('Notification Email', 'send'))
        );
    }
}
