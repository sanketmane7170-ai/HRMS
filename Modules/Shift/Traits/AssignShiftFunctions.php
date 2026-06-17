<?php
namespace Modules\Shift\Traits;

use App\Exports\RosterExport;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Shifts;
use App\Models\ShiftSchedule;
use App\Models\User;
use App\Notifications\ServiceRequest\GenerateNotification;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Shift\Entities\UsersShift;
use Yajra\DataTables\Facades\DataTables;

trait AssignShiftFunctions
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function roster(Request $request)
    {
        canPerform('Manage Roster Roster'); // TODO DO Only Admin can access
        $startDate    = now()->toDateString();
        $users        = $dates        = [];
        $departmentId = '';
        if ($request->post()) {
            if (! empty($request->department_id) || $request->department_id == 0) {
                $departmentId = $request->department_id;
                $startDate    = $request->start_date;

                $newStartDate = Carbon::createFromFormat('Y-m-d', $startDate);
                $newEndDate   = Carbon::createFromFormat('Y-m-d', $startDate);
                $newEndDate   = $newEndDate->addDays(6);

                $users = User::where('status', User::STATUS_ACTIVE)
                    ->whereDoesntHave('roles', function ($query) {
                        $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                    })
                    ->with(['assigned_shifts' => function ($query) use ($newStartDate, $newEndDate) {
                        $query->whereBetween('assigned_for_date', [$newStartDate->toDateString(), $newEndDate->toDateString()]);
                    }])
                    ->when($departmentId, function ($query, $departmentId) {
                        $query->where('department_id', $departmentId);
                    })
                // ->where('department_id', $departmentId)

                    ->get();

                $dates = [];
                for ($date = $newStartDate; $date <= $newEndDate; $date->addDay()) {
                    $dates[] = $date->toDateString();
                }
            }
        }

        view()->share('activeLink', 'shift-roster');
        return view('shift::shift.roster', compact('users', 'dates', 'startDate', 'departmentId'))->render();
    }

    public function printRoster($departmentId, $startDate)
    {
        canPerform('Manage Roster Roster'); // // TODO DO Only Admin can access

        $newStartDate = Carbon::createFromFormat('Y-m-d', $startDate);
        $newEndDate   = Carbon::createFromFormat('Y-m-d', $startDate);
        $newEndDate   = $newEndDate->addDays(6);

        $users = User::with(['assigned_shifts' => function ($query) use ($newStartDate, $newEndDate) {
            $query->whereBetween('assigned_for_date', [$newStartDate->toDateString(), $newEndDate->toDateString()]);
        }])
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->where('department_id', $departmentId)->get();

        // $dates = [];
        for ($date = $newStartDate; $date <= $newEndDate; $date->addDay()) {
            $dates[] = $date->toDateString();
        }
        $exportExcel = [];
        $headers     = [];
        $department  = Department::find($departmentId);

        foreach ($users as $i => $user) {
            $exportExcel[$i]['Employee Name'] = $user->name . ' (' . $department->name . ' ' . $department->code . ')';
            if ($i == 0) {
                $headers[] = 'Employee Name';
            }

            foreach ($dates as $date) {
                if ($i == 0) {
                    $headers[] = $date;
                }
                $shiftData   = '';
                $shiftRecord = [];
                foreach ($user->assigned_shifts as $shift) {
                    if ($shift->assigned_for_date === $date) {
                        $shiftRecord[] = $shift->shift_schedule_information->title . '(' . substr($shift->shift_schedule_information->shift_start, 0, 5) . '-' . substr($shift->shift_schedule_information->shift_end, 0, 5) . ')';
                    }
                }
                if (! empty($shiftRecord)) {
                    $shiftData = implode(', ', $shiftRecord);
                }

                $exportExcel[$i][$date] = $shiftData;
            }
        }

        $export = new RosterExport($exportExcel, $headers);

        return Excel::download($export, 'roster_' . $department->name . '_' . $department->code . '.xlsx');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    // AS Represent to Assign Shift
    // public function as_index(Request $request)
    // {
    //     view()->share('activeLink', 'assign-shift');
    //     if ($request->ajax()) {
    //        $currentUser = auth()->user();
    //         $roleId = isset($currentUser->getCurrentRole()->id) ? $currentUser->getCurrentRole()->id : null;
    //         if (!isset($roleId)) {
    //             $roleId = 1;
    //             //return DataTables::of(collect())->make(true);
    //         }

    //         $strRoleId = getSetting('shift_hierarchy_roles');
    //         $shiftHierarchyRoles = explode(",", $strRoleId);

    //         $data = User::where('status', User::STATUS_ACTIVE)->with(['salary', 'department'])
    //             ->whereHas('workDetail', function ($query) use ($roleId, $shiftHierarchyRoles, $currentUser) {
    //                 if ($roleId != 1) {
    //                     $query->whereJsonContains('report_to_ids', (string) $currentUser->id);
    //                     // $query->where('report_to_id', $currentUser->id);
    //                 }
    //             })
    //             ->whereDoesntHave('roles', function ($query) {
    //                 return $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
    //             })->get();

    //         return DataTables::of($data)
    //             ->editColumn('id', function ($user) {
    //                 $html = '<a href=' . route('backend.users.show', $user) . '><span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span></a>';
    //                 return $html;
    //             })
    //             ->addColumn('assign_shift', function ($user) {
    //                 $imgTag = '<img src="' . asset('assets/backend/img/timetable.jpg') . '">';
    //                 $btn = createActionButton(route('backend.assign_shift.openCalendar', [$user->id]), $imgTag, 'view-button');
    //                 return $btn;
    //             })
    //             ->rawColumns(['assign_shift', 'id'])
    //             ->make(true);
    //     }
    //     return view('shift::shift.assign.index');
    // }
    public function as_index(Request $request)
    {
        view()->share('activeLink', 'assign-shift');

        if ($request->ajax()) {

            $currentUser = auth()->user();
            $roleName    = optional($currentUser->getCurrentRole())->name;

            $strRoleId           = getSetting('shift_hierarchy_roles');
            $shiftHierarchyRoles = explode(",", $strRoleId);

            $data = User::where('status', User::STATUS_ACTIVE)
                ->with(['salary', 'department'])
                ->whereHas('workDetail', function ($query) use ($roleName, $currentUser) {

                    // ✅ Apply hierarchy ONLY for non-admin users
                    if (! in_array($roleName, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
                        $query->whereJsonContains(
                            'report_to_ids',
                            (string) $currentUser->id
                        );
                    }
                })
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [
                        User::ROLE_ADMIN,
                        User::ROLE_SUPER_ADMIN,
                    ]);
                })
                ->get();

            return DataTables::of($data)
                ->editColumn('id', function ($user) {
                    return '<a href="' . route('backend.users.show', $user) . '">
                            <span class="badge badge-pill bg-success-light">' . $user->employee_id . '</span>
                        </a>';
                })
                ->addColumn('assign_shift', function ($user) {
                    $imgTag = '<img src="' . asset('assets/backend/img/timetable.jpg') . '">';
                    return createActionButton(
                        route('backend.assign_shift.openCalendar', [$user->id]),
                        $imgTag,
                        'view-button'
                    );
                })
                ->rawColumns(['assign_shift', 'id'])
                ->make(true);
        }

        return view('shift::shift.assign.index');
    }

    public function as_create(User $user)
    {
        $isAdmin = auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN);
        canPerform('Manage Scheduling Shift');
        if ($isAdmin) {
            $shifts = Shifts::addSelect([
                'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                'id',
                'title',
            ])->get();
        } else {
            $shift_show_to_all = Setting::where("key", "shift_show_to_all")->first();
            if ($shift_show_to_all && $shift_show_to_all->value == true) {
                $shifts = Shifts::addSelect([
                    'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                    'id',
                    'title',
                ])->get();
            } else {
                $is_admin_shift_show_to_manager = Setting::where("key", "is_admin_shift_show_to_manager")->first();
                if ($is_admin_shift_show_to_manager && $is_admin_shift_show_to_manager->value == true) {
                    $shifts = Shifts::addSelect([
                        'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                        'id',
                        'title',
                    ])->where('department_id', auth()->user()->department_id)->orWhere('created_by', 1)->get();
                } else {
                    $shifts = Shifts::addSelect([
                        'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                        'id',
                        'title',
                    ])->where('department_id', auth()->user()->department_id)->get();
                }
            }

        }
        $html = view('shift::shift.assign.multi-shift', compact('user', 'shifts'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function openCalendar(User $user)
    {

        if (! auth()->user()->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            $shift_show_to_all = Setting::where("key", "shift_show_to_all")->first();
            if ($shift_show_to_all && $shift_show_to_all->value == true) {
                $shifts = Shifts::addSelect([
                    'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                    'id',
                    'title',
                ])->get();
            } else {
                $is_admin_shift_show_to_manager = Setting::where("key", "is_admin_shift_show_to_manager")->first();
                if ($is_admin_shift_show_to_manager && $is_admin_shift_show_to_manager->value == true) {
                    $shifts = Shifts::addSelect([
                        'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                        'id',
                        'title',
                    ])->where('department_id', auth()->user()->department_id)->orWhere('created_by', 1)->get();
                } else {
                    $shifts = Shifts::addSelect([
                        'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                        'id',
                        'title',
                    ])->where('department_id', auth()->user()->department_id)->get();
                }
            }
        } else {
            $shifts = Shifts::addSelect([
                'type' => DB::raw("CASE WHEN type = 'SS' THEN 'Single' ELSE 'Multiple' END AS shift_type"),
                'id',
                'title',
            ])->get();
        }

        view()->share('activeLink', 'assign-shift');

        $events     = ShiftSchedule::all();
        $userShifts = $user->assigned_shifts()->with('shift_schedule_information')->get();

        //my_dd($userShifts);

        $formattedEvents = [];
        if (isset($userShifts)) {
            foreach ($userShifts as $userShift) {
                $shiftData = $userShift->shift_schedule_information;
                $startDate = Carbon::parse($userShift->assigned_for_date . ' ' . $shiftData->shift_start)->format('Y-m-d\TH:i:s');
                $endDate   = Carbon::parse($userShift->assigned_for_date . ' ' . $shiftData->shift_end)->format('Y-m-d\TH:i:s');
                if ($shiftData) {
                    $formattedEvents[] = [
                        'title' => $shiftData->title,
                        'start' => $startDate,
                        'end'   => $endDate,
                        'uqid'  => $userShift->id,
                    ];
                }
            }
        }
        return view('shift::shift.assign.calendar-view', compact('user', 'shifts', 'formattedEvents'));
    }

    public function assignShift(Request $request, User $user)
    {
        canPerform('Manage Scheduling Shift');

        $data = $request->validate([
            'assigned_for_date' => ['required'],
            'schedule_id'       => ['required', 'exists:shifts,id'],
        ]);

        $response = getErrorResponse();
        try {
            $data['assigned_by_id'] = auth()->id();
            $shift_schedules        = ShiftSchedule::where('shift_id', $data['schedule_id'])->get();
            $shiftSchedules         = ShiftSchedule::where('shift_id', $data['schedule_id'])->first();
            $fixed_shift_id         = $data['schedule_id'];
            $existingShift          = $user->user_shift()
                ->whereIn('schedule_id', $shift_schedules->pluck('id'))
                ->where('assigned_for_date', $data['assigned_for_date'])
                ->first();

            if ($existingShift) {
                $response['message'] = 'Shift with this schedule/time already exists for the user.';
            } else {
                $isweekendshift = Shifts::where('id', $shiftSchedules?->shift_id)->first();
                if ($isweekendshift && $isweekendshift->is_weekend == 1) {
                    $isweekendshift = true;
                    $assignedDate   = Carbon::parse($data['assigned_for_date']);

                    $weekStart = $assignedDate->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                    $weekEnd   = $assignedDate->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
                    $work_week = $user->workDetail->work_week;

                    $usershift    = UsersShift::where('user_id', $user->id)->whereBetween('assigned_for_date', [$weekStart, $weekEnd])->get();
                    $weekendshift = 0;
                    foreach ($usershift as $shift) {
                        $shiftschdata = ShiftSchedule::where('id', $shift->schedule_id)->first();
                        $shiftdata    = Shifts::where('id', $shiftschdata->shift_id)->first();

                        if ($shiftdata && $shiftdata->is_weekend == 1) {
                            $weekendshift++;
                        }
                    }
                    $givenweekend = 7 - $work_week;
                } else {
                    $isweekendshift = false;
                }
                if ($isweekendshift && $weekendshift >= $givenweekend) {
                    $response['message'] = 'You can not assign more than ' . $givenweekend . ' weekend shifts in a week.';
                } else {
                    foreach ($shift_schedules as $shifts) {
                        $data['schedule_id'] = $shifts->id;
                        $user->user_shift()->create($data);
                    }
                    $admin      = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                    $shift_info = ShiftSchedule::select('title', 'shift_start', 'shift_end')->where('shift_id', $fixed_shift_id)->first();
                    //Log::info('Starting Contact Sync..');
                    if (! empty($user->ftoken)) {
                        $userData = [
                            'id'      => $user->id,
                            'name'    => $user->name,
                            'email'   => $user->email,
                            'message' => $shift_info->title . '(' . $data['assigned_for_date'] . ')',
                            'route'   => route('backend.employee.document-requests.index'),
                            // Add any other user data you want to pass...
                        ];
                        $user->notify(new GenerateNotification($userData, $admin->id));
                        $notification = $this->fcmService->sendFcmMessage($user->ftoken, 'Shift Notification', $userData['message'], 6);
                    }

                    $response = getSuccessResponse(createFlashMessage('Shift', 'Assigned'));
                }
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function assignMultipleShift(Request $request, User $user)
    {

        canPerform('Manage Scheduling Shift');

        $data = $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'schedule_id' => ['required', 'exists:shifts,id'],
        ]);

        $response = getErrorResponse();

        try {
            $data['assigned_by_id'] = auth()->id();
            $startDate              = Carbon::parse($data['start_date']);
            $endDate                = Carbon::parse($data['end_date']);
            $full_date_string       = $data['start_date'] . ' To ' . $data['end_date'];
            $admin                  = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
            $fixed_shift_id         = $data['schedule_id'];
            while ($startDate->lte($endDate)) {
                $currentDate     = $startDate->toDateString();
                $shift_schedules = ShiftSchedule::select('id')->where('shift_id', $data['schedule_id'])->get();
                $shiftSchedules  = ShiftSchedule::where('shift_id', $data['schedule_id'])->first();
                $existingShift   = $user->user_shift()
                    ->whereIn('schedule_id', $shift_schedules->pluck('id'))
                    ->where('assigned_for_date', $currentDate)
                    ->first();

                if ($existingShift) {
                    $response['message'] = 'Shift with this schedule/time already exists for the user on $currentDate.';
                } else {
                    $isweekendshift = Shifts::where('id', $shiftSchedules->shift_id)->first();
                    if ($isweekendshift && $isweekendshift->is_weekend == 1) {
                        $isweekendshift = true;
                        $assignedDate   = Carbon::parse($currentDate);

                        $weekStart = $assignedDate->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                        $weekEnd   = $assignedDate->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
                        $work_week = $user->workDetail->work_week;

                        $usershift    = UsersShift::where('user_id', $user->id)->whereBetween('assigned_for_date', [$weekStart, $weekEnd])->get();
                        $weekendshift = 0;
                        foreach ($usershift as $shift) {
                            $shiftschdata = ShiftSchedule::where('id', $shift->schedule_id)->first();
                            $shiftdata    = Shifts::where('id', $shiftschdata->shift_id)->first();

                            if ($shiftdata && $shiftdata->is_weekend == 1) {
                                $weekendshift++;
                            }
                        }
                        $givenweekend = 7 - $work_week;
                    } else {
                        $isweekendshift = false;
                    }
                    if ($isweekendshift) {
                        if ($weekendshift >= $givenweekend) {
                            // return 'false';
                        } else {
                            // Assign the shift
                            $shiftData = $data;
                            foreach ($shift_schedules as $shifts) {
                                $shiftData['schedule_id'] = $shifts->id;
                                $user->user_shift()->create(array_merge($shiftData, ['assigned_for_date' => $currentDate]));
                            }
                        }
                    } else {
                        $shiftData = $data;
                        foreach ($shift_schedules as $shifts) {
                            $shiftData['schedule_id'] = $shifts->id;
                            $user->user_shift()->create(array_merge($shiftData, ['assigned_for_date' => $currentDate]));
                        }
                    }
                }
                $startDate->addDay();
            }

            $shift_info = ShiftSchedule::select('title', 'shift_start', 'shift_end')->where('shift_id', $fixed_shift_id)->first();
            $userData   = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => $shift_info->title . '(' . $full_date_string . ')',
                'route'   => route('backend.employee.document-requests.index'),
                // Add any other user data you want to pass...
            ];
            $user->notify(new GenerateNotification($userData, $admin->id));
            if (isset($user->ftoken) && ! empty($user->ftoken)) {
                $notification = $this->fcmService->sendFcmMessage($user->ftoken, 'Shift Notification', $userData['message'], 6);
            }

            $response = getSuccessResponse(createFlashMessage('Shift', 'Assigned'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function assign_shifts_to_multiple_user(Request $request, $shift_id)
    {

        if ($request->post()) {
            $data = $request->validate([
                'start_date'          => 'required|date',
                'end_date'            => 'required|date|after_or_equal:start_date',
                'shift_id'            => ['required', 'exists:shifts,id'],
                                                                // 'department_id' => ['required', 'exists:departments,id'],
                'shift_schedule_id'   => ['required', 'array'], // Must be an array
                'shift_schedule_id.*' => ['required', 'integer', 'exists:shift_schedules,id'],
                'employee_ids'        => ['required', 'array'], // Must be an array
                'employee_ids.*'      => ['required', 'integer', 'exists:users,id'],
            ]);
            // dd($data);
            $response = getErrorResponse();

            try {
                $data['assigned_by_id'] = auth()->id();
                $employeeIds            = $data['employee_ids'];

                foreach ($employeeIds as $id) {
                    $startDate        = Carbon::parse($data['start_date']);
                    $endDate          = Carbon::parse($data['end_date']);
                    $full_date_string = $data['start_date'] . ' To ' . $data['end_date'];
                    $user             = User::find($id);
                    while ($startDate->lte($endDate)) {
                        $currentDate     = $startDate->toDateString();
                        $shift_schedules = ShiftSchedule::select('id')->where('shift_id', $data['shift_id'])->get();
                        $shiftSchedules  = ShiftSchedule::where('shift_id', $data['shift_id'])->first();
                        $existingShift   = $user->user_shift()
                            ->whereIn('schedule_id', $shift_schedules->pluck('id'))
                            ->where('assigned_for_date', $currentDate)
                            ->first();

                        if ($existingShift) {
                            $response['message'] = 'Shift with this schedule/time already exists for the user on $currentDate.';
                        } else {
                            $isweekendshift = Shifts::where('id', $shiftSchedules->shift_id)->first();
                            if ($isweekendshift && $isweekendshift->is_weekend == 1) {
                                $isweekendshift = true;
                                $assignedDate   = Carbon::parse($currentDate);

                                $weekStart = $assignedDate->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                                $weekEnd   = $assignedDate->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
                                $work_week = $user->workDetail->work_week;

                                $usershift    = UsersShift::where('user_id', $user->id)->whereBetween('assigned_for_date', [$weekStart, $weekEnd])->get();
                                $weekendshift = 0;
                                foreach ($usershift as $shift) {
                                    $shiftschdata = ShiftSchedule::where('id', $shift->schedule_id)->first();
                                    $shiftdata    = Shifts::where('id', $shiftschdata->shift_id)->first();

                                    if ($shiftdata && $shiftdata->is_weekend == 1) {
                                        $weekendshift++;
                                    }
                                }
                                $givenweekend = 7 - $work_week;
                            } else {
                                $isweekendshift = false;
                            }
                            if ($isweekendshift && $weekendshift >= $givenweekend) {
                                // return 'false';
                            } else {
                                $shiftData = $data;
                                foreach ($shift_schedules as $shifts) {
                                    $shiftData['schedule_id'] = $shifts->id;
                                    $user->user_shift()->create(array_merge($shiftData, ['assigned_for_date' => $currentDate]));
                                }
                            }
                        }
                        $startDate->addDay();
                    }
                    $shift_info = ShiftSchedule::select('title', 'shift_start', 'shift_end')->where('shift_id', $data['shift_id'])->first();
                    $userData   = [
                        'id'      => $user->id,
                        'name'    => $user->name,
                        'email'   => $user->email,
                        'message' => $shift_info->title . '(' . $full_date_string . ')',
                        'route'   => route('backend.employee.document-requests.index'),
                        // Add any other user data you want to pass...
                    ];

                    $admin = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();

                    $user->notify(new GenerateNotification($userData, $admin->id));

                    if (isset($user->ftoken) && ! empty($user->ftoken)) {
                        $notification = $this->fcmService->sendFcmMessage($user->ftoken, 'Shift Notification', $userData['message'], 6);
                    }
                }
                $response = getSuccessResponse(createFlashMessage('Shift', 'Assigned'));
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
            return response()->json($response);
        }
        $shifts = Shifts::where('id', $shift_id)->first();
        if ($shifts) {
            if ($shifts->type == 'SS') {
                $shifts->type = 'Single Shift';
            }
            if ($shifts->type == 'MS') {
                $shifts->type = 'Split/Multiple Shift';
            }
        }
        $shift_schedules = ShiftSchedule::where('shift_id', $shift_id)->get();
        // dd($shifts);
        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(User::ROLE_HR)) {
            $departments = Department::get();
        } else {

            $departments = DB::table('departments')
                ->join('users', 'departments.id', '=', 'users.department_id')
                ->where('users.id', auth()->user()->id)
                ->select('departments.*')
                ->get();
        }
        canPerform('Manage Scheduling Shift');
        $html = view('shift::shift.assign.multi-user-shift', compact('shift_id', 'shifts', 'shift_schedules', 'departments'))->render();
        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }
}
