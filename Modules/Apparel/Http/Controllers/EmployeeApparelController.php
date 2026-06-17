<?php
namespace Modules\Apparel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Leave\GenerateNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Apparel\Entities\Apparel;
use Modules\Apparel\Entities\ApparelRequest;
use Yajra\DataTables\Facades\DataTables;

class EmployeeApparelController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'my-apparel');
    }

    public function myApparel(Request $request)
    {
        if ($request->ajax()) {

            $data = ApparelRequest::with('apparel')->where('user_id', auth()->user()->id)->get();

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
                            $btn .= createActionButton(route('backend.employee.myApparel.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                            $btn .= createActionButton(route('backend.employee.myApparel.remove', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                            break;
                        case 1:
                            $btn .= '<span class="badge badge-success">Approved</span>';
                            break;
                        case 2:
                            $btn .= '<span class="badge badge-danger">Rejected</span>';
                            break;
                    }

                    return $btn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('apparel::employee.index');
    }

    public function create()
    {
        $requestApp = Apparel::get();
        $html       = view('apparel::employee.create', compact('requestApp'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
                    'user_id'           => auth()->user()->id,
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

    public function edit($id)
    {
        $requestApp = ApparelRequest::find($id);
        $apparel    = Apparel::get();

        $html = view('apparel::employee.edit', compact('apparel', 'requestApp'))->render();

        return response()->json([
            'success' => true,
            'html'    => $html,
        ]);
    }

    public function update(Request $request, $id)
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

    public function destroy($id)
    {
        $response = getErrorResponse();
        try {
            $apparel = ApparelRequest::find($id);
            $apparel->delete();

            $response = getSuccessResponse(createFlashMessage('Apparel Request', 'Deleted'));
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
}
