<?php
namespace Modules\Performance\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Performance\Entities\AppraisalCriterion;
use Modules\Performance\Entities\AppraisalReviewer;
use Modules\Performance\Entities\AppraisalTemplate;
use Modules\Performance\Entities\PerformanceAppraisal;
use Yajra\DataTables\Facades\DataTables;

class PerformanceAppraisalController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'performance');
    }

    // public function index(Request $request)
    // {
    //     //canPerform('Manage Performance');

    //     if ($request->ajax()) {
    //         if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
    //             $data = PerformanceAppraisal::with('employee', 'reviewer')->get();
    //         } else {
    //             $data = PerformanceAppraisal::with('employee', 'reviewer')->where('employee_id', auth()->user()->id)->get();
    //         }

    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('employee_name', fn($row) => $row->employee->name ?? '-')
    //             ->addColumn('reviewer_name', fn($row) => $row->reviewer->name ?? '-')
    //             ->addColumn('status', fn($row) => ucfirst($row->status))
    //             ->addColumn('action', function ($row) {
    //                 $btn = createActionButton(route('performance.show', $row->id), 'View', 'btn-success edit-button', 'fa fa-eye');
    //                 $btn .= createActionButton(route('performance.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');

    //                 if (hasPermission('Delete Performance') && ! $row->is_locked) {
    //                     $btn .= createActionButton(route('performance.destroy', $row->id), 'Delete', 'btn-danger', 'fa fa-trash');
    //                 }
    //                 return $btn;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('performance::index');
    // }
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $authUser   = auth()->user();
            $authUserId = $authUser->id;

            // $query = PerformanceAppraisal::with(['employee', 'reviewer']);
            $query = PerformanceAppraisal::with(['employee', 'reviewer', 'criteria']);

            // ✅ Admin & Super Admin → see all
            if (auth()->user()->hasRole(User::ROLE_ADMIN) ||  auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {

                // no filter

            }
            elseif ($authUser->hasRole("Area Director")) {

              
            }   
            elseif (
                \App\Models\UserWorkDetail::whereRaw(
                    "JSON_CONTAINS(report_to_ids, ?)",
                    ['"' . $authUserId . '"']
                )->exists()
            ) {

                $downlineEmployeeIds = \App\Models\User::where('status', "active")
                    ->whereHas('workDetail', function ($q) use ($authUserId) {
                        $q->whereRaw(
                            "JSON_CONTAINS(report_to_ids, ?)",
                            ['"' . $authUserId . '"']
                        );
                    })
                    ->pluck('id');

                $query->whereIn('employee_id', $downlineEmployeeIds);
            }
            // ✅ Normal Employee → only own appraisal
            else {
                $query->where('employee_id', $authUserId);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee_name', fn($row) => $row->employee->name ?? '-')
                ->addColumn('reviewer_name', fn($row) => $row->reviewer->name ?? '-')
                ->addColumn('appraisal_date', fn($row) =>
                    optional($row->appraisal_date)->format('d-m-Y') ?? '-'
                )
                ->addColumn('weighted', function ($row) {

                    $totalScore  = 0;
                    $totalWeight = 0;

                    foreach ($row->criteria as $c) {
                        if (! is_null($c->score)) {
                            $totalScore  += $c->score * $c->weight;
                            $totalWeight += $c->weight;
                        }
                    }

                    return $totalWeight > 0
                        ? round($totalScore / $totalWeight, 2)
                        : '-';
                })->addColumn('comments', function ($row) {
                return $row->reviewer_comments ?? '-';
            })
                ->addColumn('status', fn($row) => ucfirst($row->status))
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(
                        route('performance.show', $row->id),
                        'View',
                        'btn-success edit-button',
                        'fa fa-eye'
                    );

                    $btn .= createActionButton(
                        route('performance.edit', $row->id),
                        'Edit',
                        'btn-warning edit-button',
                        'fa fa-edit'
                    );

                    if (hasPermission('Delete Performance') && ! $row->is_locked) {
                        $btn .= createActionButton(
                            route('performance.destroy', $row->id),
                            'Delete',
                            'btn-danger action-button',
                            'fa fa-trash'
                        );
                    }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performance::index');
    }

    public function create()
    {
        // $employees = User::all();

        $authUser = auth()->user();

        if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {

            // Admin & Super Admin
            $employees = User::where('status', "active")
                ->visibleForAuthUser(auth()->user())
                ->get();

        } else {

            $authUserId = auth()->id();

            $employees = User::where('status', "active")
                ->whereHas('workDetail', function ($q) use ($authUserId) {
                    $q->whereRaw(
                        "JSON_CONTAINS(report_to_ids, ?)",
                        ['"' . $authUserId . '"']
                    );
                })
                ->get();

        }

        $templates = AppraisalTemplate::with('criteria')->get();
        $branches  = Department::select('id', 'name')->get();

        $managers = User::where('status', 'active')
            ->whereHas('workDetail', function ($q) {
                $q->whereNotNull('report_to_ids');
            })
            ->select('id', 'name')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'html'    => view('performance::create', compact('employees', 'templates', 'branches', 'managers'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'                     => 'required|exists:users,id',
            'template_id'                     => 'required|exists:appraisal_templates,id',
            'appraisal_date'                  => 'required|date',
            'criteria'                        => 'nullable|array',
            'criteria.*.template_criteria_id' => 'required|exists:appraisal_template_criteria,id',
            'criteria.*.score'                => 'nullable|numeric|min:0|max:10',
            'criteria.*.self_score'           => 'nullable|numeric|min:0|max:10',
            'reporting_manager_id'            => 'nullable|exists:users,id',
            'reviewer_comments'             => 'nullable|string',

        ]);
        DB::beginTransaction();
        try {
            $template   = AppraisalTemplate::with('criteria')->find($data['template_id']);
            $reviewerId = $data['reporting_manager_id'] ?? auth()->id();

            $appraisal = PerformanceAppraisal::create([
                'employee_id'    => $data['employee_id'],
                'template_id'    => $data['template_id'],
                'reviewer_id'    => $reviewerId,
                'period'         => $template->period_type ?? "",
                'status'         => 'approved',
                'appraisal_date' => $data['appraisal_date'],
                'reviewer_comments' => $data['reviewer_comments'] ?? null,
            ]);

            // Save criteria from template
            // foreach ($template->criteria as $row => $c) {
            //     AppraisalCriterion::create([
            //         'appraisal_id'  => $appraisal->id,
            //         'template_criteria_id' => $templateCriteria->id,
            //         'criteria_name' => $c->criteria_name,
            //         'weight'        => $c->weight,
            //         'score'         => $data['criteria'][$row]['score'] ?? $c->score ?? 0,
            //         'self_score'    => $data['criteria'][$row]['self_score'] ?? $data['criteria'][$row]['score'] ?? $c->self_score ?? 0, // if employee not set, same as reviewer
            //     ]);
            // }
            if (! empty($data['criteria'])) {

                foreach ($data['criteria'] as $item) {

                    // Find template criteria from DB
                    $templateCriteria = $template->criteria
                        ->where('id', $item['template_criteria_id'])
                        ->first();

                    if (! $templateCriteria) {
                        continue; // skip invalid
                    }

                    $return = AppraisalCriterion::create([
                        'appraisal_id'         => $appraisal->id,
                        'template_criteria_id' => $templateCriteria->id,
                        'criteria_name'        => $templateCriteria->criteria_name,
                        'weight'               => $templateCriteria->weight,
                        'max_score'            => $templateCriteria->max_score ?? 10,

                        'score'                => $item['score'] ?? 0,
                        'self_score'           => $item['self_score'] ?? $item['score'] ?? 0,
                    ]);

                }
            }

            // Reviewer
            AppraisalReviewer::create([
                'appraisal_id' => $appraisal->id,
                'reviewer_id'  => $reviewerId,
                'level'        => 1,
                'status'       => 'pending',
            ]);

            DB::commit();

            return response()->json(getSuccessResponse("Appraisal created from template."));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        //canPerform('Edit Performance');
        $appraisal = PerformanceAppraisal::with('criteria')->findOrFail($id);
        $managers  = User::where('status', 'active')
            ->whereHas('workDetail', function ($q) {
                $q->whereNotNull('report_to_ids');
            })
            ->select('id', 'name')
            ->distinct()
            ->get();

        if ($appraisal->is_locked) {
            return response()->json(getErrorResponse('Appraisal is locked.'));
        }

        return response()->json([
            'success' => true,
            'html'    => view('performance::edit', compact('appraisal', 'managers'))->render(),
        ]);
    }
    public function update(Request $request, $id)
    {
        $appraisal = PerformanceAppraisal::with('criteria')->findOrFail($id);

        if ($appraisal->is_locked) {
            return response()->json(getErrorResponse('Appraisal is locked.'));
        }

        $isEmployee = auth()->id() === $appraisal->employee_id;
        $isReviewer = auth()->id() === $appraisal->reviewer_id || auth()->user()->hasRole('Admin');
        $reviewerId = $request->filled('reporting_manager_id')
            ? $request->reporting_manager_id
            : $appraisal->reviewer_id; // keep existing reviewer

        if (! $isEmployee && ! $isReviewer) {
            return response()->json(getErrorResponse("Unauthorized."), 403);
        }

        // Dynamic validation rules
        $rules = ['criteria' => 'required|array'];
        foreach ($appraisal->criteria as $criterion) {
            if ($isEmployee) {
                $rules["criteria.{$criterion->id}.self_score"] = 'nullable|numeric|min:0|max:10';
            } else {
                $rules["criteria.{$criterion->id}.score"] = 'nullable|numeric|min:0|max:10';
            }
        }

        if ($isReviewer) {
            $rules['reviewer_comments'] = 'nullable|string';
            $rules['status']            = 'required|in:draft,submitted,approved,rejected';
        }

        $data = $request->validate($rules);

        foreach ($appraisal->criteria as $criterion) {
            $cid = $criterion->id;
            if ($isEmployee && isset($data['criteria'][$cid]['self_score'])) {
                $criterion->self_score = $data['criteria'][$cid]['self_score'];
            }
            if ($isReviewer && isset($data['criteria'][$cid]['score'])) {
                $criterion->score = $data['criteria'][$cid]['score'];
            }
            $criterion->save();
        }

        if ($isReviewer) {
            $appraisal->update([
                'reviewer_comments' => $data['reviewer_comments'] ?? null,
                'status'            => $data['status'],
                'is_locked'         => in_array($data['status'], ['submitted', 'approved']),
            ]);
        }

        return response()->json(getSuccessResponse("Appraisal updated."));
    }

    public function show($id)
    {
        //canPerform('Manage Performance');
        $appraisal = PerformanceAppraisal::with(['criteria', 'employee', 'reviewer', 'reviewers.reviewer'])->findOrFail($id);

        // Final weighted score calculation
        $totalWeighted = 0;
        $totalWeight   = 0;
        foreach ($appraisal->criteria as $c) {
            if ($c->score !== null) {
                $totalWeighted += $c->score * $c->weight;
                $totalWeight   += $c->weight;
            }
        }
        $finalScore = $totalWeight > 0 ? round($totalWeighted / $totalWeight, 2) : 0;

        return response()->json([
            'success' => true,
            'html'    => view('performance::show', compact('appraisal', 'finalScore'))->render(),
        ]);
    }

    public function destroy($id)
    {
        //canPerform('Delete Performance');
        $appraisal = PerformanceAppraisal::find($id);

        if ($appraisal->is_locked) {
            return response()->json(getErrorResponse("Appraisal is locked."));
        }

        $appraisal->delete();
        return response()->json(getSuccessResponse("Appraisal deleted."));
    }

    public function exportPdf()
    {
        //canPerform('Manage Performance');
        $data = PerformanceAppraisal::with('employee', 'reviewer', 'criteria')->get();
        $pdf  = Pdf::loadView('performance::exportPDF', compact('data'))->setPaper('a4', 'landscape');
        return $pdf->download('performance_appraisal_' . now()->format('Y-m-d') . '.pdf');
    }

    public function getEmployeesByDepartment($departmentId)
    {
        $employees = User::with("workDetail")->where('department_id', $departmentId)->get();
        return response()->json($employees);
    }
    public function downloadCertificate($id)
    {
        $appraisal = PerformanceAppraisal::with('employee', 'criteria')->findOrFail($id);

        if ($appraisal->status !== 'approved') {
            abort(403, 'Certificate only available after approval.');
        }

        $pdf = Pdf::loadView('performance::certificate', compact('appraisal'));
        return $pdf->download("certificate_{$appraisal->employee->name}.pdf");
    }
    public function getTemplateCriteria($templateId)
    {
        $template = AppraisalTemplate::with('criteria')->findOrFail($templateId);

        return response()->json([
            'success'  => true,
            'criteria' => $template->criteria->map(fn($c) => [
                'id'          => $c->id,
                'name'        => $c->criteria_name,
                'description' => $c->description,
                'weight'      => $c->weight,
                'max_score'   => $c->max_score,
                'comments'    => $c->comments,
            ]),
        ]);
    }
    public function performancelist(Request $request)
    {
        if ($request->ajax()) {

            $query = PerformanceAppraisal::with(['employee', 'reviewer']);
            $query->where('employee_id', auth()->user()->id);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee_name', fn($row) => $row->employee->name ?? '-')
                ->addColumn('reviewer_name', fn($row) => $row->reviewer->name ?? '-')
                ->addColumn('appraisal_date', fn($row) =>
                    optional($row->appraisal_date)->format('d-m-Y') ?? '-'
                )
                ->addColumn('status', fn($row) => ucfirst($row->status))
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(
                        route('performance.show', $row->id),
                        'View',
                        'btn-success edit-button',
                        'fa fa-eye'
                    );

                    $btn .= createActionButton(
                        route('performance.edit', $row->id),
                        'Edit',
                        'btn-warning edit-button',
                        'fa fa-edit'
                    );

                    // if (hasPermission('Delete Performance') && ! $row->is_locked) {
                    //     $btn .= createActionButton(
                    //         route('performance.destroy', $row->id),
                    //         'Delete',
                    //         'btn-danger',
                    //         'fa fa-trash'
                    //     );
                    // }

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performance::performancelist');
    }

    // public function monthlyReport(Request $request)
    // {
    //     if ($request->ajax()) {

    //         $month = $request->get('month'); // YYYY-MM

    //         $query = PerformanceAppraisal::with(['employee', 'reviewer', 'criteria'])
    //             ->whereNotNull('appraisal_date')
    //             ->where('status', 'approved');

    //         if ($month) {
    //             $query->whereYear('appraisal_date', Carbon::parse($month)->year)
    //                 ->whereMonth('appraisal_date', Carbon::parse($month)->month);
    //         }

    //         $appraisals = $query->get();

    //         $rows = [];

    //         foreach ($appraisals as $appraisal) {

    //             $totalWeighted = 0;
    //             $totalWeight   = 0;

    //             foreach ($appraisal->criteria as $c) {
    //                 if ($c->score !== null) {
    //                     $totalWeighted += $c->score * $c->weight;
    //                     $totalWeight += $c->weight;
    //                 }
    //             }

    //             if ($totalWeight === 0) {
    //                 continue;
    //             }

    //             $monthKey = $appraisal->appraisal_date->format('Y-m');

    //             $key = implode('_', [
    //                 $appraisal->employee_id,
    //                 $appraisal->reviewer_id,
    //                 $monthKey,
    //             ]);

    //             if (! isset($rows[$key])) {
    //                 $rows[$key] = [
    //                     'employee_name' => $appraisal->employee->name ?? '-',
    //                     'reviewer_name' => $appraisal->reviewer->name ?? '-',
    //                     'month'         => Carbon::parse($monthKey)->format('M Y'),
    //                     'total_score'   => 0,
    //                     'count'         => 0,
    //                 ];
    //             }

    //             $rows[$key]['total_score'] += round($totalWeighted / $totalWeight, 2);
    //             $rows[$key]['count']++;
    //         }

    //         // Final average
    //         $finalData = collect($rows)->map(function ($row) {
    //             return [
    //                 'employee_name' => $row['employee_name'],
    //                 'reviewer_name' => $row['reviewer_name'],
    //                 'month'         => $row['month'],
    //                 'monthly_score' => round($row['total_score'] / $row['count'], 2),
    //             ];
    //         })->values();

    //         return DataTables::of($finalData)
    //             ->addIndexColumn()
    //             ->make(true);
    //     }

    //     return view('performance::monthly');
    // }
    public function monthlyReport(Request $request)
    {
        if ($request->ajax()) {

            $month      = $request->get('month'); // YYYY-MM
            $templateId = $request->get('template_id');
            $authUser   = auth()->user();
            $authUserId = $authUser->id;

            $query = PerformanceAppraisal::with(['employee', 'reviewer', 'criteria'])
                ->whereNotNull('appraisal_date')
                ->where('status', 'approved');

            /* =========================================
         | ROLE / VISIBILITY CONDITIONS
         ========================================= */

            // ✅ Admin & Super Admin → all records
            if ($authUser->hasRole(User::ROLE_ADMIN) || $authUser->hasRole(User::ROLE_SUPER_ADMIN)) {

                // No filter

            }
            elseif ($authUser->hasRole("Area Director")) {

              
            }   
            // ✅ Reviewer / Manager → downline employees
            elseif (
                \App\Models\UserWorkDetail::whereRaw(
                    "JSON_CONTAINS(report_to_ids, ?)",
                    ['"' . $authUserId . '"']
                )->exists()
            ) {

                $downlineEmployeeIds = User::where('status', 'active')
                    ->whereHas('workDetail', function ($q) use ($authUserId) {
                        $q->whereRaw(
                            "JSON_CONTAINS(report_to_ids, ?)",
                            ['"' . $authUserId . '"']
                        );
                    })
                    ->pluck('id');

                $query->whereIn('employee_id', $downlineEmployeeIds);
            }
            // ✅ Normal Employee → only self
            else {
                $query->where('employee_id', $authUserId);
            }

            /* =========================================
         | MONTH FILTER
         ========================================= */
            if ($month) {
                $query->whereYear('appraisal_date', Carbon::parse($month)->year)
                    ->whereMonth('appraisal_date', Carbon::parse($month)->month);
            }
            if ($templateId) {
                $query->where('template_id', $templateId);
            }
            if ($request->filled('branch_id')) {
                $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('department_id', $request->branch_id);
                });
            }

            $appraisals = $query->get();

            /* =========================================
         | MONTHLY SCORE CALCULATION
         ========================================= */
            $rows = [];

            foreach ($appraisals as $appraisal) {

                $totalWeighted = 0;
                $totalWeight   = 0;

                foreach ($appraisal->criteria as $c) {
                    if ($c->score !== null) {
                        $totalWeighted += $c->score * $c->weight;
                        $totalWeight   += $c->weight;
                    }
                }

                if ($totalWeight === 0) {
                    continue;
                }

                $monthKey = $appraisal->appraisal_date->format('Y-m');

                $key = implode('_', [
                    $appraisal->employee_id,
                    $appraisal->reviewer_id,
                    $monthKey,
                ]);

                if (! isset($rows[$key])) {
                    $rows[$key] = [
                        'employee_name' => $appraisal->employee->name ?? '-',
                        'reviewer_name' => $appraisal->reviewer->name ?? '-',
                        'month'         => Carbon::parse($monthKey)->format('M Y'),
                        'total_score'   => 0,
                        'count'         => 0,
                    ];
                }

                $rows[$key]['total_score'] += round($totalWeighted / $totalWeight, 2);
                $rows[$key]['count']++;
            }

            /* =========================================
         | FINAL MONTHLY AVERAGE
         ========================================= */
            $finalData = collect($rows)->map(function ($row) {
                return [
                    'employee_name' => $row['employee_name'],
                    'reviewer_name' => $row['reviewer_name'],
                    'month'         => $row['month'],
                    'monthly_score' => round($row['total_score'] / $row['count'], 2),
                ];
            })->values();

            return DataTables::of($finalData)
                ->addIndexColumn()
                ->make(true);
        }
        $branches = Department::select('id', 'name')->get();

        return view('performance::monthly', compact('branches'));
    }

    public function getTemplatesByBranch(Request $request)
    {
        $templates = AppraisalTemplate::query()
            ->when($request->branch_id, function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($templates);
    }

    public function getEmployeesByManager($managerId)
    {
        $employees = User::where('status', 'active')
            ->whereHas('workDetail', function ($q) use ($managerId) {
                $q->whereRaw(
                    "JSON_CONTAINS(report_to_ids, ?)",
                    ['"' . $managerId . '"']
                );
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($employees);
    }

}
