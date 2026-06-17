<?php

namespace Modules\NotificationManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\NotificationManager\Entities\AlertRecipient;
use Yajra\DataTables\Facades\DataTables;

class NotificationManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        view()->share('activeLink', 'setting-notifications');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = AlertRecipient::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('role', function ($alertRecipient) {
                    $role_name = '';
                    $role_name = $alertRecipient->role->name;
                    return $role_name;
                })
                ->editColumn('name', function ($alertRecipient) {
                    $name = '';
                    $name = $alertRecipient->user->name;
                    return $name;
                })
                ->editColumn('email', function ($alertRecipient) {
                    $email = '';
                    $email = $alertRecipient->user->email;
                    return $email;
                })
                ->editColumn('alert_status', function ($alertRecipient) {
                    $checked = $alertRecipient->alert_status == 1 ? "checked" : '';
                    $status = $alertRecipient->alert_status == 1 ? 0 : 1;
                    $action = route('backend.notification.manager.update-status', [$alertRecipient, $status]);
                    return createToggleButton('status', $action, $checked, __trans('are_you_sure_want_to_update_notification_status?'));
                })
                // ->addColumn('action', function ($row) {
                //     $btn = '';
                //     $btn .= createActionButton(route('backend.schedule.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                //     return $btn;
                // })
                ->rawColumns(['alert_status','action'])
                ->make(true);
        }
        return view('notificationmanager::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //return view('notificationmanager::create');
        $html = view('notificationmanager::create')->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $response = getErrorResponse();

        $existingRecord = AlertRecipient::where('role_id', $request->role_id)
        ->where('user_id', $request->user_id)
        ->first();

        if ($existingRecord) {
            return response()->json([
            'status' => 'error',
            'message' => 'This user is already assigned to this role.'
            ]);
        }
        try {
            AlertRecipient::create([
                'role_id' => $request->role_id,
                'user_id' => $request->user_id,
            ]);

            $response = getSuccessResponse(createFlashMessage('Notification Alert', 'Assigned'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function updateNotificationStatus(AlertRecipient $alertrecipient, $status)
    {
        $response = getErrorResponse();
        try {
            $alertrecipient->alert_status = $status;
            $alertrecipient->save();

            $response = getSuccessResponse(createFlashMessage('Alert Status', 'updated'));
        } catch (Exception $e) {
        }

        return response()->json($response);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('notificationmanager::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('notificationmanager::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
