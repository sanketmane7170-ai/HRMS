<?php

namespace Modules\Leave\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rules\Enum;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveType as EnumsLeaveType;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Setting;

class LeaveTypeController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'leave-types');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        canPerform('Manage Leave Type');
        if ($request->ajax()) {
            $data = LeaveType::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('type', function ($data) {
                    return $data->type->getHtml();
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Leave Type')) {
                        $btn = createActionButton(route('backend.leave-types.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Leave Type')) {
                        $btn .= createActionButton(route('backend.leave-types.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action', 'type'])
                ->make(true);
        }
        $leaveSetting = Setting::where('key','allow_negative_leave')->first();
        $leaveProSetting = Setting::where('key','allow_to_add_probation_leave')->first();
        $monthwiseLeaveSetting = Setting::where('key','is_month_wise_show_leave')->first();
        $monthwise2leave = Setting::where('key','is_month_wise_2_leave')->first();
        $yearGiven2Leave = Setting::where('key','is_year_given_2_leave')->first();
        return view('leave::type.index',compact('leaveSetting','monthwise2leave','monthwiseLeaveSetting','yearGiven2Leave'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        canPerform('Create Leave Type');
        $html = view('leave::type.create')->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        canPerform('Create Leave Type');
        $data = $request->validate([
            'name' => 'required|string|unique:leave_types,name',
            'days' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'type' => ['required', new Enum(EnumsLeaveType::class)],
        ]);

        // if ($data['is_recurring']) {
        //     //$data['no_of_leaves'] = $request->input('no_of_leaves');
        // }

        $response = getErrorResponse();

        try {
            $leaveType = LeaveType::create($data);
            $response = getSuccessResponse(createFlashMessage('Leave Type', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(LeaveType $leaveType)
    {
        canPerform('Create Leave Type');
        $html = view('leave::type.edit', compact('leaveType'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:leave_types,name,' . $leaveType->id,
            'days' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'type' => ['required', new Enum(EnumsLeaveType::class)],
        ]);

        // Commented out is_recurring logic since column doesn't exist
        // if ($data['is_recurring']) {
        //     $data['no_of_leaves'] = $request->input('no_of_leaves');
        // } else {
        //     $data['no_of_leaves'] = null;
        // }

        $response = getErrorResponse();

        try {
            $leaveType->update($data);
            $response = getSuccessResponse(createFlashMessage('Leave Type', 'updated'));
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
    public function destroy(LeaveType $leaveType)
    {
         $response = getErrorResponse();
        try {
            $leaveType->delete();
            $response = getSuccessResponse(createFlashMessage('Leave Type', 'Deleted'));
        } catch (Exception $e) {
            // $response['error'] = $e->getMessage();
            if ($e->errorInfo[1] === 1451) {
                $response['message'] = "This service is already associated, cannot be removed.";
                $response['error'] = "This service is already associated, cannot be removed.";
            } else {
                $response['error'] = $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
