<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\KeyPerformanceIndicator;
use Modules\PerformanceReview\Entities\ReviewDuration;
use Illuminate\Contracts\Support\Renderable;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KeyPerformanceIndicatorController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'Key Performance Indicator');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = KeyPerformanceIndicator::with('duration')->select('key_performance_indicators.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('duration', function ($row) {
                    return $row->duration ? $row->duration->label . ' (' . $row->duration->months . ' months)' : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(route('kpi.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('kpi.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::kpi.index');
    }

    public function create()
    {
        $durations = ReviewDuration::all();
        return response()->json([
            'success' => true,
            'html' => view('performancereview::kpi.create', compact('durations'))->render()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'duration_id' => 'required|exists:review_durations,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            KeyPerformanceIndicator::create($data);
            DB::commit();
            return response()->json(getSuccessResponse("KPI created successfully."));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        $kpi = KeyPerformanceIndicator::findOrFail($id);
        $durations = ReviewDuration::all();

        return response()->json([
            'success' => true,
            'html' => view('performancereview::kpi.edit', compact('kpi', 'durations'))->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $kpi = KeyPerformanceIndicator::findOrFail($id);

        $data = $request->validate([
            'duration_id' => 'required|exists:review_durations,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kpi->update($data);

        return response()->json(getSuccessResponse("KPI updated successfully."));
    }

    public function destroy($id)
    {
        $kpi = KeyPerformanceIndicator::findOrFail($id);
        $kpi->delete();

        return response()->json(getSuccessResponse("KPI deleted successfully."));
    }
}
