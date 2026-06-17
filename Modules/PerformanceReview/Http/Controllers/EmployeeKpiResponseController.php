<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Modules\PerformanceReview\Entities\EmployeeKpiAssignment;
use Modules\PerformanceReview\Entities\EmployeeKpiItem;
use Illuminate\Support\Facades\Auth;

class EmployeeKpiResponseController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'KPI Response');
    }

    /**
     * List all KPI assignments for the logged-in employee
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = EmployeeKpiAssignment::with('duration')
                ->where('user_id', Auth::id())
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('duration', fn($row) => $row->duration->label ?? '-')
                ->addColumn('duration', function ($row) {
                    if ($row->duration) {
                        $months = $row->duration->months;
                        return $months . ' Month' . ($months > 1 ? 's' : '');
                    }
                    return '-';
                })

                ->addColumn('status', fn($row) => ucfirst($row->status))
                ->addColumn('due_date', fn($row) => $row->due_date ? date('d-m-Y', strtotime($row->due_date)) : '-')
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('backend.employee.kpi.show', $row->id) . '" class="btn btn-sm btn-primary">View & Score</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::employee_kpi.index');
    }

    /**
     * Show KPI items for self evaluation
     */
    public function show($id)
    {
        $assignment = EmployeeKpiAssignment::with(['items.kpi', 'items.reviews', 'duration'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // You may also eager load score level config if needed
        return view('performancereview::employee_kpi.show', compact('assignment'));
    }

    /**
     * Submit employee self-score and remarks
     */
    public function submit(Request $request, $id)
    {
        $assignment = EmployeeKpiAssignment::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->scores as $itemId => $score) {
                EmployeeKpiItem::where('id', $itemId)
                    ->where('employee_kpi_assignment_id', $assignment->id)
                    ->update([
                        'self_score' => $score,
                        'self_remarks' => $request->remarks[$itemId] ?? null,
                    ]);
            }

            $assignment->update(['status' => 'self']);

            DB::commit();
            return redirect()->route('backend.employee.kpiresponse')->with('success', 'Self-evaluation submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("KPI Self Evaluation Error: " . $e->getMessage());
            // return back()->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
    }
}
