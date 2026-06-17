<?php
namespace Modules\GeneralRequest\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\allTypeOfTransaction;
use App\Models\User;
use App\Notifications\Leave\GenerateNotification;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\GeneralRequest\Entities\GeneralRequest;
use Modules\GeneralRequest\Entities\GeneralRequestType;
use Yajra\DataTables\Facades\DataTables;

class GeneralRequestController extends Controller
{
    protected $fcmService;

    public function __construct(FirebaseService $fcmService)
    {
        $this->fcmService = $fcmService;
        view()->share('activeLink', 'general-request');
    }
    public function show(Request $request)
    {
        view()->share('activeLink', "General Request type");

        canPerform('Manage General Request');
        if ($request->ajax()) {
            $data = GeneralRequestType::orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit General')) {
                        $btn = createActionButton(route('backend.general.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete General')) {
                        $btn .= createActionButton(route('backend.general.remove', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('generalrequest::index');
    }

    public function create()
    {
        canPerform('Create General Request');
        $html = view('generalrequest::create')->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function store(Request $request)
    {
        canPerform('Create General Request');
        $data = $request->validate([
            'name' => 'required|string|unique:general_request_types,name',
        ]);

        $response = getErrorResponse();

        try {
            $leaveType = GeneralRequestType::create($data);
            $response  = getSuccessResponse(createFlashMessage('General Request', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function edit($id)
    {
        canPerform('Manage General Request');
        $general = GeneralRequestType::find($id);
        $html    = view('generalrequest::edit', compact('general'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function update(Request $request, $id)
    {
        canPerform('Edit General Request');
        $data = $request->validate([
            'name' => 'required|string|unique:general_request_types,name,' . $id,
        ]);

        $response = getErrorResponse();

        try {
            $apparel = GeneralRequestType::find($id);
            if ($apparel) {
                $apparel->update($data);
                $response = getSuccessResponse(createFlashMessage('General Request', 'updated'));
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
        canPerform('Manage General Request');
        $response = getErrorResponse();
        try {
            $apparel = GeneralRequestType::find($id);
            $apparel->delete();
            $response = getSuccessResponse(createFlashMessage('General Request', 'Deleted'));
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

        view()->share('activeLink', "General Request");
        canPerform('Manage General Request');
        if ($request->ajax()) {
            $data = GeneralRequest::with('type', 'user')->orderBy('created_at', 'desc')->get();
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
                            if (hasPermission('Edit General Request')) {
                                $btn .= createActionButton(route('backend.admin.generalRequest.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                            }
                            if (hasPermission('Edit General Request')) {
                                $url  = route('backend.admin.generalRequest.approved', $row->id);
                                $btn .= "<a href='$url' class='btn btn-sm inline-block me-2  btn-success reqApproved'><i class='fa fa-write'></i>Approved</a>";
                            }
                            if (hasPermission('Delete General Request')) {
                                $url  = route('backend.admin.generalRequest.rejected', $row->id);
                                $btn .= "<a href='$url' class='btn btn-sm inline-block me-2  btn-danger reqRejected'>Rejected</a>";
                            }
                            break;
                        case 1:
                            $btn .= createActionButton(route('backend.admin.generalRequest.remove', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');

                            // $url = route('backend.admin.generalRequest.remove', $row->id);
                            // $btn .= "<a href='$url' class='btn btn-sm inline-block me-2  btn-danger reqRemove'>Delete</a>";
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

        return view('generalrequest::request.showRequest');
    }

    public function createRequest(Request $request)
    {

        $requestApp = GeneralRequestType::get(['id', 'name']);
        $users      = User::whereNotIn('name', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ])->where('status', 'active')->get();

        $html = view('generalrequest::request.createRequest', compact('requestApp', 'users'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function storeRequest(Request $request)
    {

        $data = $request->validate([
            'user_id'      => 'required',
            'general_type' => 'required',
            'date'         => 'nullable|date',
        ]);
        $response = getErrorResponse();
        try {

            $leaveType = GeneralRequest::create([
                'user_id' => $request->user_id,
                'type_id' => $request->general_type,
                'date'    => $request->date ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d'),
                'note'    => $request->note,
                'status'  => 0, // 0 = pending
            ]);

            $user = User::find(auth()->id());

            // Try to find admin user safely
            $admin = null;
            try {
                $admin = User::wherein('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first(); // Assuming ID 1 is admin, adjust as needed
            } catch (Exception $e) {
                // If admin lookup fails, continue without notification
            }

            if ($admin && $user) {
                try {
                    $userData = [
                        'id'      => $user->id,
                        'name'    => $user->name,
                        'email'   => $user->email,
                        'message' => 'Generated a General Request for ' . $user->name,
                        'route'   => route('backend.admin.generalRequest'),
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
                    if ($user->ftoken != null) {

                        $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "Generated a General Request", 19);
                    }
                } catch (Exception $e) {
                    // If notification fails, continue without it
                }
            }

            // send notification manager
            try {
                $managers = User::permission('General Request Manager Access')->where('id', '!=', auth()->id())->get();
                foreach ($managers as $manager) {
                    if ($manager->ftoken) {
                        $fcmService = new FirebaseService();
                        $fcmService->sendFcmMessage($manager->ftoken, 'General Request was Generated', 'General Request was Generated', 24);
                    }
                }
            } catch (Exception $e) {
                // If FCM notifications fail, continue without them
            }
            //end
            $response = getSuccessResponse('General request created successfully.');
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function editRequest($id)
    {

        $requestApp = GeneralRequest::with('type', 'user')->find($id);
        $general    = GeneralRequestType::get(['id', 'name']);
        $users      = User::whereNotIn('name', [
            User::ROLE_ADMIN,
            User::ROLE_SUPER_ADMIN,
        ])->where('status', 'active')->get();

        $html = view('generalrequest::request.edit', compact('general', 'requestApp', 'users'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function updateRequest(Request $request, $id)
    {

        $data = $request->validate([
            'user_id'      => 'required',
            'general_type' => 'required',
        ]);

        $response = getErrorResponse();
        try {
            $apparel = GeneralRequest::find($id);
            if ($apparel) {
                $apparel->update([
                    'user_id' => $request->user_id,
                    'type_id' => $request->general_type,
                    'date'    => date('Y-m-d', strtotime($request->date)),
                    'note'    => $request->note,
                ]);

                $user     = User::find($apparel->user_id);
                $admin    = User::whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->first();
                $userData = [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'message' => 'General Request was update by ' . $admin->name,
                    'route'   => route('backend.employee.my-apparel'),
                ];
                $user->notify(new GenerateNotification($userData, $user->id));
                // send notification manager
                $managers = User::permission('General Request Manager Access')->where('id', '!=', $user->id)->get();
                foreach ($managers as $manager) {
                    if (! empty($manager->ftoken)) {
                        $fcmService = new FirebaseService();
                        $get        = $fcmService->sendFcmMessage($manager->ftoken, 'General Request was Updated', 'General Request was Updated', 24);
                    }
                }
                //end
                $response = getSuccessResponse(createFlashMessage('General Request', 'updated'));

            } else {
                $response = getErrorResponse('Can Not Update General Request!');
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function approvedRequest($id)
    {

        canPerform('Manage General Request');
        $apparel = GeneralRequest::find($id);
        if ($apparel) {

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
                if (! empty($manager->ftoken)) {
                    $fcmService = new FirebaseService();
                    $get        = $fcmService->sendFcmMessage($manager->ftoken, 'General Request is approved', 'General Request is approved', 24);
                }
            }
            if ($user->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "General Request is approved by " . $admin->name, 19);
            }
            //end
            return response()->json(['success' => 'General Request has been approved successfully.']);

        } else {
            return response()->json(['error', 'Error occurred while approved the General Request:']);
        }
    }

    public function rejectedRequest($id)
    {
        canPerform('Manage General Request');
        $apparel = GeneralRequest::find($id);
        if ($apparel) {

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
                $fcmService = new FirebaseService();
                if ($manager->ftoken) {
                    $get = $fcmService->sendFcmMessage($manager->ftoken, 'General Request was rejected', 'General Request was rejected', 24);
                }

            }
            if ($user->ftoken != null) {

                $get = $this->fcmService->sendFcmMessage($user->ftoken, 'Information', "General Request was rejected by " . $admin->name, 19);
            }
            //end
            return response()->json(['success' => 'General Request has been rejected successfully.']);
        } else {
            return response()->json(['error', 'Error occurred while rejecting the General Request:']);
        }
    }

    public function removeRequest($id)
    {
        canPerform('Manage General Request');
        $response = getErrorResponse();
        try {
            $apparel = GeneralRequest::find($id);
            $apparel->delete();
            $response = getSuccessResponse(createFlashMessage('General Request', 'Deleted'));
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

    public function generalRequestTransaction(Request $request)
    {

        if ($request->ajax()) {
            $data = allTypeOfTransaction::where('transaction_type', 'general_request')->orderBy('id', 'desc');
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
        return view('generalrequest::request.generalrequest-transaction');
    }
}
