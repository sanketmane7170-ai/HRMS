<?php

namespace Modules\Shift\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Designation;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use App\Models\ShiftSchedule;
use Modules\Shift\Traits\AssignShiftFunctions;
use Modules\Shift\Entities\UsersShift;
use App\Models\Shifts;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \App\Models\Department;
use App\Models\Setting;

class ShiftController extends Controller
{
    use AssignShiftFunctions;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        canPerform('Manage Shift Shift');


        view()->share('activeLink', 'create-shift');
        if ($request->ajax()) {
           if (! auth()->user()->hasAnyRole([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
                $shift_show_to_all = Setting::where("key", "shift_show_to_all")->first();
                if($shift_show_to_all && $shift_show_to_all->value==true){
                    $data = Shifts::all();
                } else {
                    $is_admin_shift_show_to_manager = Setting::where("key", "is_admin_shift_show_to_manager")->first();
                    $departmentId = auth()->user()->department_id;
                    $userIdsOfDepartment = User::where('department_id', $departmentId)->pluck('id');
                    if($is_admin_shift_show_to_manager && $is_admin_shift_show_to_manager->value==true){
                        $data = Shifts::where('department_id', $departmentId)->orWhere('created_by', 1)->get();
                    } else {
                        $data = Shifts::where('department_id', $departmentId)->get();
                    }                    
                }
            } else {
                $data = Shifts::all();
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('type', function ($row) {
                    if ($row->type == 'SS') {
                        return 'Single Shift';
                    }
                    if ($row->type == 'MS') {
                        return 'Split/Multiple Shift';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn = createActionButton(route('backend.assign_shift.toMultipleUser', $row->id), 'Assign To User', 'btn-success edit-button', 'fa fa-plus');
                    // if (hasPermission('Edit Designation')) {
                    $btn .= createActionButton(route('backend.shift.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    // }
                    // if (hasPermission('Delete Designation')) {
                    $btn .= createActionButton(route('backend.schedule.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    // }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('shift::shift.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        canPerform('Manage Shift Shift');
        $shift_types  = [
            [
                'id' => 'SS',
                'name' => 'Single Shift',
            ],
            [
                'id' => 'MS',
                'name' => 'Split/Multiple Shift',
            ]
        ];
        $html = view('shift::shift.create', compact('shift_types'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        canPerform('Manage Shift Shift');
        $data = $request->validate([
            'title' => [
                'required',
                Rule::unique('shift_schedules', 'title')
            ],
            'type' => [
                'required',
            ]
        ]);

        $response = getErrorResponse();
        try {
            $valid = true;
            foreach ($request->input('shifts') as $shiftData) {
                if (empty($shiftData['shift_start']) || empty($shiftData['shift_end'])) {
                    $valid = false;
                    break;
                }
            }

            $department_id = auth()->user()->department_id;
            $isAdmin = auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN);
            if ($isAdmin) {
                $department_id = '0';
            }

            if (is_null($department_id)) {
                return response()->json(['error' => 'Department ID cannot be null.'], 400);
            }
            if ($valid) {
                // return auth()->user()->id;
                $shift = Shifts::create([
                    'department_id' => $department_id,
                    'title' => $request->title,
                    'type' => $request->type,
                    'created_by' => auth()->user()->id,
                    'is_weekend' => $request->is_weekend ? 1 : 0,
                ]);
                foreach ($request->input('shifts') as $shiftData) {
                    $data['shift_start'] = $shiftData['shift_start'];
                    $data['shift_end'] = $shiftData['shift_end'];
                    $data['shift_id'] = $shift->id;

                    ShiftSchedule::create($data);
                }
                $response = getSuccessResponse(createFlashMessage('Shift', 'Created'));
            } else {
                return response()->json(['error' => 'Shift start and end times cannot be empty.'], 400);
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('shift::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Shifts $shift)
    {
        // dd($shift);
        $isAdmin = auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN);
        $user = Auth::user();

        $hasPermissions_edit_shift = DB::table('role_has_permissions')
            ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', auth()->user()->id)
            ->whereIn('role_has_permissions.permission_id', [97, 98])
            ->exists();
        $isDept_Manager = auth()->user()->hasRole(User::ROLE_DEPT_MANAGER);

        $shift_schedules = ShiftSchedule::where('shift_id', $shift->id)->get();
        canPerform('Manage Shift Shift');
        $shift_types  = [
            [
                'id' => 'SS',
                'name' => 'Single Shift',
            ],
            [
                'id' => 'MS',
                'name' => 'Split/Multiple Shift',
            ]
        ];

        $html = view('shift::shift.edit', compact('shift', 'shift_types', 'shift_schedules', 'isAdmin', 'hasPermissions_edit_shift'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Shifts $shift)
    {
        canPerform('Manage Shift Shift');
        $data = $request->validate([
            'title' => [
                'required'
            ],
            'type' => [
                'required',
            ]
        ]);
        $response = getErrorResponse();
        try {
            $shiftDataArray = $request->input('shifts');
            $shift_schedules = ShiftSchedule::where('shift_id', $shift->id)->get();


            $shift->title = $request->get('title');
            $shift->type = $request->get('type');
            $shift->is_weekend = $request->get('is_weekend') ? 1 : 0;

            $shift->update();
            foreach ($shiftDataArray as $index => $shiftData) {
                // Check if shift data exists in the collection
                $existingSchedule = $shift_schedules->get($index);

                if ($existingSchedule) {
                    // Update existing shift schedule
                    $existingSchedule->update([
                        'title'       => $request->get('title'),
                        'type'       => $request->get('type'),
                        'shift_start' => $shiftData['shift_start'],
                        'shift_end'   => $shiftData['shift_end'],
                    ]);
                } else {
                    // Insert new shift schedule
                    ShiftSchedule::create([
                        'shift_id'    => $shift->id,
                        'title'       => $request->get('title'),
                        'type'       => $request->get('type'),
                        'shift_start' => $shiftData['shift_start'],
                        'shift_end'   => $shiftData['shift_end'],
                    ]);
                }
            }


            // foreach ($shift_schedules as $index => $schedule) {
            //     if (isset($shiftDataArray[$index])) {
            //         echo "Index: $index\n";

            //         $schedule->update([
            //             'title'     => $request->get('title'),
            //             'shift_start' => $shiftDataArray[$index]['shift_start'],
            //             'shift_end' => $shiftDataArray[$index]['shift_end'],
            //         ]);
            //     }
            // }
            // Create New Shift On Edit Page
            if ($request->input('create_shifts')) {
                foreach ($request->input('create_shifts') as $shiftData) {
                    $data['shift_start'] = $shiftData['shift_start'];
                    $data['shift_end'] = $shiftData['shift_end'];
                    $data['shift_id'] = $shift->id;
                    $data['title']  = $request->get('title');
                    ShiftSchedule::create($data);
                }
            }
            $response = getSuccessResponse(createFlashMessage('Shift', 'Updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(UsersShift $shift)
    {
        $shift->delete();
        return response()->json(['success' => true]);
    }

    public function destroy_schedule(Shifts $shift)
    {
        $shift_schedules = ShiftSchedule::where('shift_id', $shift->id)->get();

        $shift_exist = false;

        foreach ($shift_schedules as $schedule) {
            $shift_exist = UsersShift::where('schedule_id', $schedule->id)->exists();
            if ($shift_exist) {
                return response()->json(['success' => false, 'message' => 'Cannot delete this shift because it is assigned to an employee.']);
            }
        }

        foreach ($shift_schedules as $schedule) {
            $schedule->delete();
        }
        $shift->delete();
        return response()->json(['success' => true]);
    }
    public function getEmployees($departmentId, $search = null)
    {
        // $employees = User::where('department_id', $departmentId)->get(); // Fetch employees
        // $employees = User::where('department_id', $departmentId)
        // ->when($search, function ($query, $search) {
        //     $query->where('name', 'LIKE', "%$search%");
        // })
        // ->get();
        $query = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
            })
            ->when($search, function ($query, $search) {
                $query->where('name', 'LIKE', "%$search%");
            })->when($departmentId, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            });

        $employees = $query->get();
        return response()->json($employees); // Return JSON response
    }
}
