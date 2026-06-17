<?php

namespace Modules\Attendance\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Attendance\Entities\Holiday;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Setting;

class HolidayController extends Controller
{

    public function __construct()
    {
        view()->share('activeLink', 'holidays');
    }

    /**
     * Display a listing of the holiday.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        canPerform('Manage Holiday');
        if (config('attendance.holiday_use_calendar_view')) {
            return redirect()->route('backend.holidays.calendar');
        }

        if ($request->ajax()) {
            $data = Holiday::latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('description', function ($row) {
                    return shorterText($row->description, 80);
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (hasPermission('Edit Attendance')) {
                        $btn .= createActionButton(route('backend.holidays.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    }
                    if (hasPermission('Delete Attendance')) {
                        $btn .= createActionButton(route('backend.holidays.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $withoutAttendPH = Setting::where('key','is_given_without_attend_PH')->first();
        return view('attendance::holiday.index', compact('withoutAttendPH'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create(): JsonResponse
    {
        canPerform('Create Holiday');
        $html = view('attendance::holiday.create')->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'detail' => 'required',
            'is_recurring' => 'nullable'
        ]);
        $response = getErrorResponse();
        try {
            $data['is_recurring'] = $request->is_recurring ? Holiday::RECURRING : Holiday::NOT_RECURRING;
            Holiday::create($data);
            $response = getSuccessResponse(createFlashMessage('Holiday', 'added'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified holiday.
     */
    public function edit(Holiday $holiday)
    {
        canPerform('Edit Holiday');
        $html = view('attendance::holiday.edit', compact('holiday'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        canPerform('Edit Holiday');
        $data = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'detail' => 'required',
            'is_recurring' => 'nullable'
        ]);
        $response = getErrorResponse();
        try {
            $data['is_recurring'] = $request->is_recurring ? Holiday::RECURRING : Holiday::NOT_RECURRING;
            $holiday->update($data);
            $response = getSuccessResponse(createFlashMessage('Holiday', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        canPerform('Delete Holiday');
        $response = getErrorResponse();
        try {
            $holiday->delete();
            $response = getSuccessResponse(createFlashMessage('Holiday', 'deleted'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function isWithoutAttendPHLeave(Request $request){

        $isAllowed = $request->input('allow');

        if ($isAllowed) {
            $settingadd = Setting::where('key', 'is_given_without_attend_PH')->first();

            if($settingadd){
                $settingadd->update([
                    'key' => 'is_given_without_attend_PH',
                    'value' => true,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key' => 'is_given_without_attend_PH',
                    'value' => true,
                ]);
            }
        } else {
            $settingadd = Setting::where('key', 'is_given_without_attend_PH')->first();
            if($settingadd){
                $settingadd->update([
                    'key' => 'is_given_without_attend_PH',
                    'value' => false,
                ]);
            } else {
                $settingadd = Setting::create([
                    'key' => 'is_given_without_attend_PH',
                    'value' => false,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
                    
}
