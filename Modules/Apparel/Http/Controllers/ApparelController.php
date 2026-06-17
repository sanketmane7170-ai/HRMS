<?php
namespace Modules\Apparel\Http\Controllers;

use App\Exports\ApparelExport;
use App\Http\Controllers\Controller;
use App\Models\allTypeOfTransaction;
use App\Models\Department;
use App\Models\User;
use App\Notifications\Leave\GenerateNotification;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Apparel\Entities\Apparel;
use Modules\Apparel\Entities\ApparelRequest;
use Yajra\DataTables\Facades\DataTables;

class ApparelController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
        view()->share('activeLink', 'general-request');
    }
    public function show(Request $request)
    {
        canPerform('Manage Apparel');
        if ($request->ajax()) {
            $data = Apparel::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Apparel')) {
                        $btn = createActionButton(route('backend.apparel.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Apparel')) {
                        $btn .= createActionButton(route('backend.apparel.remove', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('apparel::index');
    }

    public function create()
    {
        canPerform('Create Apparel');
        $html = view('apparel::create')->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function createRequest(Request $request)
    {

        $requestApp = Apparel::get(['id', 'name']);
        $users      = User::whereNotIn('name', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ])->where('status', 'active')->get();

        $html = view('apparel::createRequest', compact('requestApp', 'users'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function storeRequest(Request $request)
    {

        $data = $request->validate([
            'user_id'           => 'required',
            'apparel_id'        => 'required',
            'number_of_apparel' => 'required|integer|min:0',
        ]);

        $response = getErrorResponse();

        try {
            $ApparelLimit   = Apparel::where('id', $request->apparel_id)->first();
            $getApparelUser = ApparelRequest::where('apparel_id', $request->apparel_id)->where('status', 1)->whereYear('created_at', date('Y'))->sum('number_of_apparel');
            $totalAppare    = ($request->number_of_apparel) + ($getApparelUser ? $getApparelUser : 0);

            if (($ApparelLimit->number_of_given >= $totalAppare) && $request->number_of_apparel > 0) {

                $leaveType = ApparelRequest::create([
                    'user_id'           => $request->user_id,
                    'apparel_id'        => $request->apparel_id,
                    'number_of_apparel' => $request->number_of_apparel,
                ]);

                $user     = User::find(auth()->id());
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Generated a Apparel Request for ' . $ApparelLimit->name,
                    'route'   => route('backend.apparel-request'),
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

                $response = getSuccessResponse('Apparel request created successfully.');
            } else {
                $response = getErrorResponse('Over Limit Apparel Request!');
            }

        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        canPerform('Create Apparel');
        $data = $request->validate([
            'name'            => 'required|string|unique:apparels,name',
            'number_of_given' => 'required|integer|min:0',
        ]);

        $response = getErrorResponse();

        try {
            $leaveType = Apparel::create($data);
            $response  = getSuccessResponse(createFlashMessage('Apparel', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function edit($id)
    {
        canPerform('Manage Apparel');
        $apparel = Apparel::find($id);
        $html    = view('apparel::edit', compact('apparel'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function update(Request $request, $id)
    {
        canPerform('Edit Apparel');
        $data = $request->validate([
            'name'            => 'required|string|unique:apparels,name,' . $id,
            'number_of_given' => 'required|integer|min:0',
        ]);

        $response = getErrorResponse();

        try {
            $apparel = Apparel::find($id);
            if ($apparel) {
                $apparel->update($data);
                $response = getSuccessResponse(createFlashMessage('Apparel', 'updated'));
            } else {
                $response['error'] = $e->getMessage();
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function destroy($id)
    {
        canPerform('Manage Apparel');
        $response = getErrorResponse();
        try {
            $apparel = Apparel::find($id);
            $apparel->delete();
            $response = getSuccessResponse(createFlashMessage('Apparel', 'Deleted'));
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

    public function showRequest(Request $request)
    {

        canPerform('Manage Apparel');
        if ($request->ajax()) {
            $data = ApparelRequest::with('apparel', 'user')->orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    switch ($row->status) {
                        case (0):
                            $class = "<span class='badge badge-warning'>Pending</span>";
                            break;
                        case (1):
                            $class = "<span class='badge badge-success'>Approved</span>";
                            break;
                        case (2):
                            $class = "<span class='badge badge-danger'>Rejected</span>";
                            break;
                    }
                    return $class;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    switch ($row->status) {
                        case 0:
                            if (hasPermission('Edit Apparel')) {
                                $btn .= createActionButton(route('backend.apparel.request.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                            }
                            if (hasPermission('Edit Apparel')) {
                                $url  = route('backend.apparel.approved', $row->id);
                                $btn .= "<a href='$url' class='btn btn-sm inline-block me-2  btn-success reqApproved'><i class='fa fa-write'></i>Approved</a>";
                            }
                            if (hasPermission('Delete Apparel')) {
                                $url  = route('backend.apparel.rejected', $row->id);
                                $btn .= "<a href='$url' class='btn btn-sm inline-block me-2  btn-danger reqRejected'>Rejected</a>";
                            }
                            break;
                        case 1:
                            $url  = route('backend.apparelRequest.remove', $row->id);
                            $btn .= "<a href='$url' class='btn btn-sm inline-block me-2  btn-danger reqRemove'>Delete</a>";
                            // $btn .= '<span><i class="fa fa-check"></i></span>';
                            break;
                        case 2:
                            $btn .= '<span ><i class="fa fa-times"></i></span>';
                            break;
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('apparel::showRequest');
    }

    public function requestEdit($id)
    {

        $requestApp = ApparelRequest::with('user')->find($id);
        $apparel    = Apparel::get();

        $html = view('apparel::request.edit', compact('apparel', 'requestApp'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function updateRequest(Request $request, $id)
    {

        $data = $request->validate([
            'apparel_id'        => 'required',
            'number_of_apparel' => 'required|integer|min:0',
        ]);

        $response = getErrorResponse();

        try {
            $apparel = ApparelRequest::find($id);
            if ($apparel && $apparel->status == 0) {
                $ApparelLimit   = Apparel::where('id', $request->apparel_id)->first();
                $getApparelUser = ApparelRequest::where('id', '!=', $id)->where('apparel_id', $request->apparel_id)->where('status', 1)->whereYear('created_at', date('Y'))->sum('number_of_apparel');
                $totalAppare    = ($request->number_of_apparel) + ($getApparelUser ? $getApparelUser : 0);

                if (($ApparelLimit->number_of_given >= $totalAppare) && $request->number_of_apparel > 0) {
                    $apparel->update([
                        'apparel_id'        => $request->apparel_id,
                        'number_of_apparel' => $request->number_of_apparel,
                    ]);

                    $user     = User::find($apparel->user_id);
                    $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                    $userData = [
                        'id'      => $user->id,
                        'name'    => $user->name,
                        'email'   => $user->email,
                        'message' => 'Apparel Request was update by ' . $admin->name,
                        'route'   => route('backend.employee.my-apparel'),
                    ];
                    $user->notify(new GenerateNotification($userData, $user->id));

                    $response = getSuccessResponse(createFlashMessage('Apparel Request', 'updated'));
                } else {
                    $response = getErrorResponse('Over Limit Apparel Request!');
                }
            } else {
                $response = getErrorResponse('Can Not Update Apparel Request!');
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function requestApproved($id)
    {

        canPerform('Manage Apparel');
        $apparel = ApparelRequest::find($id);
        if ($apparel) {
            $ApparelLimit    = Apparel::where('id', $apparel->apparel_id)->first();
            $totalAppareRequ = ApparelRequest::where('apparel_id', $ApparelLimit->id)->where('status', 1)->whereYear('created_at', date('Y'))->sum('number_of_apparel');
            $totalAppare     = ($apparel->number_of_apparel) + ($totalAppareRequ ? $totalAppareRequ : 0);

            if (($ApparelLimit->number_of_given >= $totalAppare)) {

                $addtransaction = allTypeOfTransaction::create([
                    'user_id'          => $apparel->user_id,
                    'transaction_type' => 'apparel',
                    'old_value'        => $apparel->number_of_apparel,
                    'update_value'     => $apparel->number_of_apparel,
                    'new_value'        => $apparel->number_of_apparel,
                    'transaction_date' => Carbon::now(),
                    'description'      => 'Approved this ' . $apparel->id . ' apparel request by : ' . auth()->user()->name,
                ]);

                $apparel->update([
                    'status' => 1,
                ]);

                $user     = User::find($apparel->user_id);
                // $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $admin    = User::where('id', auth()->user()->id)->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'Apparel Request is approved by ' . $admin->name,
                    'route'   => route('backend.employee.my-apparel'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));

                if ($user->ftoken != null) {

                    $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Apparel Request is approved by " . $admin->name, 21);
                }

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

                return response()->json(['success' => 'Apparel Request has been approved successfully.']);
            } else {
                return response()->json(['error' => 'Can`t approved this request, Apparel was Over Limit!']);
            }

        } else {
            return response()->json(['error', 'Error occurred while approved the Apparel Request:']);
        }
    }

    public function requestRejected($id)
    {
        canPerform('Manage Apparel');
        $apparel = ApparelRequest::find($id);
        if ($apparel) {
            $addtransaction = allTypeOfTransaction::create([
                'user_id'          => $apparel->user_id,
                'transaction_type' => 'apparel',
                'old_value'        => $apparel->number_of_apparel,
                'update_value'     => $apparel->number_of_apparel,
                'new_value'        => $apparel->number_of_apparel,
                'transaction_date' => Carbon::now(),
                'description'      => 'Apparel this ' . $apparel->id . ' request rejected by : ' . auth()->user()->name,
            ]);
            $apparel->update([
                'status' => 2,
            ]);
            $user     = User::find($apparel->user_id);
            // $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
            $admin    = User::where('id', auth()->user()->id)->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Apparel Request was rejected by ' . $admin->name,
                'route'   => route('backend.employee.my-apparel'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));
            if ($user->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Apparel Request is rejected by " . $admin->name, 21);
            }

            $admins = User::withoutGlobalScopes()
                ->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new GenerateNotification($userData, $admin->id));
            }

            return response()->json(['success' => 'Apparel Request has been rejected successfully.']);
        } else {
            return response()->json(['error', 'Error occurred while rejecting the Apparel Request:']);
        }
    }

    public function showApparelReport(Request $request)
    {

        canPerform('Manage Apparel');
        $appareldata = Apparel::get();

        $apparels = Apparel::when($request->apparelsId, function ($query, $apparelsId) {
            return $query->whereIn('id', $apparelsId);
        })->get();

        $users        = [];
        $departmentId = '';
        $apparelsId   = '';
        $searchEmp    = '';
        $search       = false;
        if ($request->post()) {
            $search       = true;
            $departmentId = $request->department_id;
            $apparelsId   = $request->apparelsId;
            $searchEmp    = $request->search_emp;
            $query        = User::where('status', User::STATUS_ACTIVE)
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                });

            if ($departmentId !== 'all') {
                $query->where('department_id', $departmentId);
            }

            if (! empty($searchEmp)) {
                $query->where('name', 'like', '%' . $searchEmp . '%');
            }

            $users = $query->get();
        }

        return view('apparel::showreport', compact('appareldata', 'apparels', 'users', 'departmentId', 'apparelsId', 'searchEmp', 'search'));

    }

    public function showApparelTransaction(Request $request)
    {

        if ($request->ajax()) {
            $data = allTypeOfTransaction::where('transaction_type', 'apparel')->orderBy('id', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('employee_name', function ($row) {
                    return User::where('id', $row->user_id)->value('name');
                })
                ->addColumn('transaction_type', function ($row) {
                    return $row->transaction_type;
                })
                ->addColumn('transaction_date', function ($row) {
                    return Carbon::parse($row->transaction_date)->format('d-m-Y');
                })
                ->addColumn('description', function ($row) {
                    return $row->description;
                })
                ->addColumn('updated_at', function ($row) {
                    return Carbon::parse($row->updated_at)->format('d-m-Y H:i:s');
                })
                ->make(true);
        }
        return view('apparel::apparel-transaction');
    }

    public function apparelReportExport($departmentId, $apparelsTypeId, $searchEmp = '')
    {

        canPerform('Manage Apparel');
        $query = User::where('status', User::STATUS_ACTIVE)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            });

        if ($departmentId !== 'all') {
            $query->where('department_id', $departmentId);
        }

        if (! empty($searchEmp)) {
            $query->where('name', 'like', '%' . $searchEmp . '%');
        }

        $users       = $query->get();
        $exportExcel = [];
        $headers     = [];
        if ($apparelsTypeId == 'all') {
            $apparels = Apparel::get();
        } else {
            if (! empty($apparelsTypeId)) {
                $type_id = explode(',', $apparelsTypeId);
            }

            $apparels = Apparel::when($type_id, function ($query, $type_id) {
                return $query->whereIn('id', $type_id);
            })->get();
        }

        foreach ($users as $i => $user) {
            $exportExcel[$i]['Employee Name'] = $user->name . ' (' . $user->department?->name ?? 'NA' . ')';
            if ($i == 0) {
                $headers[] = 'Employee Name';
            }

            foreach ($apparels as $type) {
                $totalre = ApparelRequest::where('apparel_id', $type->id)->where('status', 1)->sum('number_of_apparel');

                if ($i == 0) {
                    $headers[] = $type->name . '(' . $type->number_of_given - $totalre . ')';
                }
                $total = ApparelRequest::where([['apparel_id', $type->id], ['user_id', $user->id]])->where('status', 1)->sum('number_of_apparel');

                $exportExcel[$i][$type->name] = $total;
            }
        }
        $export = new ApparelExport($exportExcel, $headers);
        if ($departmentId === 'all') {
            return Excel::download($export, 'apparel_report_all.xlsx');
        } else {
            $department = Department::find($departmentId);
            return Excel::download($export, 'apparel_report_' . $department->name . '.xlsx');
        }

    }

    public function removeRequest($id)
    {

        canPerform('Manage Apparel');
        $apparel = ApparelRequest::find($id);
        if ($apparel) {
            $addtransaction = allTypeOfTransaction::create([
                'user_id'          => $apparel->user_id,
                'transaction_type' => 'apparel',
                'old_value'        => $apparel->number_of_apparel,
                'update_value'     => $apparel->number_of_apparel,
                'new_value'        => $apparel->number_of_apparel,
                'transaction_date' => Carbon::now(),
                'description'      => 'Apparel this ' . $apparel->id . ' request delete by : ' . auth()->user()->name,
            ]);
            $apparel->delete();

            $user     = User::find($apparel->user_id);
            $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
            $userData = [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'message' => 'Apparel Request was remove by ' . $admin->name,
                'route'   => route('backend.employee.my-apparel'),
            ];
            $user->notify(new GenerateNotification($userData, $user->id));

            return response()->json(['success' => 'Apparel Request has been remove successfully.']);
        } else {
            return response()->json(['error', 'Error occurred while removeing the Apparel Request:']);
        }
    }

    public function apparelTotal(Request $request)
    {

        $total    = ApparelRequest::where('apparel_id', $request->app_id)->where('status', 1)->sum('number_of_apparel');
        $apparels = Apparel::where('id', $request->app_id)->first();

        return response()->json(['success' => 'get total successfully.', 'limit' => $apparels->number_of_given - $total]);
    }
}
