<?php

namespace Modules\PerformanceReview\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Modules\PerformanceReview\Entities\EmployeeKpiAssignment;
use Modules\PerformanceReview\Entities\EmployeeKpiItemReview;
use Modules\PerformanceReview\Entities\KpiScoreLevel;

class KpiAssignmentController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'KPI Assignments');
    }

    public function index(Request $request)
    {
        // canPerform('View KPI Assignments');

        if ($request->ajax()) {
            $data = EmployeeKpiAssignment::with(['user', 'duration'])->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('employee_name', fn($row) => $row->user->name ?? '-')
                ->addColumn('email', fn($row) => $row->user->email ?? '-')
                // ->addColumn('duration_label', fn($row) => $row->duration->label ?? '-')
                ->addColumn('duration_label', function ($row) {
                    if ($row->duration) {
                        $months = $row->duration->months;
                        return $months . ' Month' . ($months > 1 ? 's' : '');
                    }
                    return '-';
                })
                ->addColumn('assigned_on', fn($row) => $row->created_at->format('d-m-Y'))
                ->addColumn('status', fn($row) => ucfirst($row->status))
                ->addColumn('grade', fn($row) => $row->grade ?? '-')
                ->addColumn('remark', fn($row) => $row->remark ?? '-')

                ->addColumn('actions', function ($row) {
                    $currentUser = auth()->user();
                    // $isAdmin = $currentUser->hasRole('Admin');
                    $isAdmin = auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN);

                    if ($isAdmin && $row->status!="pending") {
                        return '<a href="' . route('kpi.assignments.score', $row->id) . '" class="btn btn-sm btn-primary">Score</a>';
                    }


                    $canScore = false;

                    foreach ($row->items as $item) {

                        // Check if self score is submitted
                        if ($item->self_score !== null) {
                            $scoreLevels = KpiScoreLevel::where('role_id', $row->user->roles->pluck('id')->first())
                                ->orderBy('step_number')
                                ->get();

                            foreach ($scoreLevels as $level) {
                                $stepNumber = $level->step_number;
                                // dd($item->reviews);
                                // If this step already scored, continue
                                // if ($item->reviews->where('step_number', $stepNumber)->count() > 0) {
                                //     continue;
                                // }
                                // Check if user can score this step
                                $isEligible = false;

                                foreach ($level->approvers as $approver) {

                                    if ($approver === "Report To User") {

                                        $reportToIds = $item->assignment->user->workDetail->report_to_ids ?? [];

                                        if (is_string($reportToIds)) {
                                            $reportToIds = json_decode($reportToIds, true);
                                        }

                                        if (is_array($reportToIds) && in_array($currentUser->id, $reportToIds)) {
                                            $isEligible = true;
                                        }
                                    } else {
                                        if ($currentUser->hasRole($approver)) {
                                            $isEligible = true;
                                            break;
                                        }
                                    }
                                }
                                if ($isEligible) {
                                    $canScore = true;
                                    break;
                                }
                            }
                        }
                    }
                    if($row->status=="pending")
                    {
                        $canScore = false;
                    }
                    return $canScore
                        ? '<a href="' . route('kpi.assignments.score', $row->id) . '" class="btn btn-sm btn-primary">Score</a>'
                        : '';
                })

                ->rawColumns(['employee_name', 'email', 'duration_label', 'assigned_on', 'status', 'grade', 'remark', 'actions'])


                ->make(true);
        }

        return view('performancereview::kpi_assignments.index');
    }
    public function score(EmployeeKpiAssignment $assignment)
    {
        // canPerform('Review KPI');

        $currentUser = auth()->user();
        $isAdmin = $currentUser->hasRole(User::ROLE_ADMIN);

        $assignment->load(['user', 'user.workDetail', 'items.kpi', 'items.reviews']);

        // ✅ Use employee's role, not admin's role
        $employeeRoleId = $assignment->user->roles()->first()?->id;

        $scoreLevels = KpiScoreLevel::where('role_id', $employeeRoleId)
            ->orderBy('step_number')
            ->get();

        $eligibleStep = null;
        $allLevelsCompleted = true;

        // Check if all items have been scored at every step
        foreach ($assignment->items as $item) {
            if (is_null($item->self_score)) {
                $allLevelsCompleted = false;
                break;
            }

            foreach ($scoreLevels as $level) {
                $step = $level->step_number;
                if ($item->reviews->where('step_number', $step)->isEmpty()) {
                    $allLevelsCompleted = false;
                    break 2;
                }
            }
        }

        // Now check eligibility
        if (!$isAdmin && !$allLevelsCompleted) {
            foreach ($scoreLevels as $level) {
                $step = $level->step_number;
                $isEligible = false;
                $allPreviousStepsDone = true;

                foreach ($assignment->items as $item) {
                    if (is_null($item->self_score)) {
                        $allPreviousStepsDone = false;
                        break;
                    }

                    if ($step > 1 && $item->reviews->where('step_number', $step - 1)->isEmpty()) {
                        $allPreviousStepsDone = false;
                        break;
                    }

                    $alreadyScoredByMe = $item->reviews->where('step_number', $step)
                        ->where('reviewer_id', $currentUser->id)->isNotEmpty();

                    $alreadyScoredByAnyone = $item->reviews->where('step_number', $step)->isNotEmpty();

                    if ($alreadyScoredByAnyone && !$alreadyScoredByMe) {
                        $allPreviousStepsDone = false;
                        break;
                    }
                }

                if (!$allPreviousStepsDone) continue;

                foreach ($level->approvers as $approver) {
                    if ($approver === "Report To User") {
                        $reportToIds = $assignment->user->workDetail->report_to_ids ?? [];
                        if (is_string($reportToIds)) {
                            $reportToIds = json_decode($reportToIds, true);
                        }

                        if (is_array($reportToIds) && in_array($currentUser->id, $reportToIds)) {
                            $isEligible = true;
                            break;
                        }
                    } else {
                        if ($currentUser->hasRole($approver)) {
                            $isEligible = true;
                            break;
                        }
                    }
                }

                if ($isEligible) {
                    $eligibleStep = $step;
                    break;
                }
            }
        }

        // ✅ At this point:
        // - If $eligibleStep is null and not admin and not completed: block
        if (!$isAdmin && !$eligibleStep && !$allLevelsCompleted) {
            return view('performancereview::kpi_assignments.score', [
                'assignment' => $assignment,
                'eligibleStep' => null,
                'isAdmin' => $isAdmin,
                'levelsList' => collect(),
                'readonly' => true,
                'allLevelsCompleted' => false
            ]);
        }


        // ✅ Build level list for Blade (Self + All Role-Based Steps)
        $levelsList = collect([
            ['label' => 'Self', 'step' => 0]
        ]);

        foreach ($scoreLevels as $level) {
            $levelsList->push([
                'label' => implode(', ', $level->approvers),
                'step' => $level->step_number
            ]);
        }

        return view('performancereview::kpi_assignments.score', compact(
            'assignment',
            'eligibleStep',
            'isAdmin',
            'levelsList',
            'allLevelsCompleted'
        ));
    }


    public function submitScore(Request $request, EmployeeKpiAssignment $assignment)
    {
        $request->validate([
            'scores' => 'required|array',
            'remarks' => 'required|array',
        ]);

        $currentUser = auth()->user();
        $isAdmin = $currentUser->hasRole(User::ROLE_ADMIN);

        $stepToSave = null;

        if (!$isAdmin) {
            $roleId = $assignment->user->roles()->first()?->id;

            $scoreLevels = KpiScoreLevel::where('role_id', $roleId)->orderBy('step_number')->get();
            foreach ($scoreLevels as $level) {
                $step = $level->step_number;
                $assignment->update(['status' => 'level-'.$step]);

                foreach ($level->approvers as $approver) {
                    if ($approver === "Report To User") {

                        $reportToIds = $assignment->user->workDetail->report_to_ids ?? [];

                        if (is_string($reportToIds)) {
                            $reportToIds = json_decode($reportToIds, true);
                        }

                        if (is_array($reportToIds) && in_array($currentUser->id, $reportToIds)) {
                            $stepToSave = $step;
                            break 2;
                        }
                    } else {
                        if ($currentUser->hasRole($approver)) {
                            $stepToSave = $step;
                            break 2;
                        }
                    }
                }
            }
             
            if (!$stepToSave) {
                return back()->with('error', 'You are not authorized to score this KPI.');
            }
        }

        foreach ($request->scores as $itemId => $steps) {
            foreach ($steps as $step => $score) {
                if ((int)$step === 0) continue; // self score can't be updated

                if (!$isAdmin && $step != $stepToSave) continue;

                EmployeeKpiItemReview::updateOrCreate(
                    [
                        'employee_kpi_item_id' => $itemId,
                        'step_number' => $step,
                        'reviewer_id' => auth()->id(),
                    ],
                    [
                        'reviewer_score' => $score,
                        'reviewer_remarks' => $request->remarks[$itemId][$step] ?? null,
                    ]
                );
            }
        }

        // After saving scores
        foreach ($request->scores as $itemId => $steps) {
            $item = \Modules\PerformanceReview\Entities\EmployeeKpiItem::with('reviews')->find($itemId);
            if (!$item) continue;

            $assignment = $item->assignment;
            $employeeRoleId = $assignment->user->roles()->first()?->id;
            $scoreLevels = KpiScoreLevel::where('role_id', $employeeRoleId)->orderBy('step_number')->get();

            $totalLevels = $scoreLevels->count();
            $completedLevels = $item->reviews()->whereIn('step_number', $scoreLevels->pluck('step_number'))->count();

            if ($completedLevels === $totalLevels) {
                $avg = $item->reviews()
                    ->whereIn('step_number', $scoreLevels->pluck('step_number'))
                    ->avg('reviewer_score');

                $item->avg_score = round($avg, 2);
                $item->save();

                $assignment->status = 'completed';

                // Calculate final average across all items (optional but cleaner)
                $finalAvgScore = $assignment->items()->avg('avg_score');

                // Get grade and remark from score_criteria
                $criteria = \DB::table('score_criteria')
                    ->where('min_score', '<=', $finalAvgScore)
                    ->where('max_score', '>=', $finalAvgScore)
                    ->first();

                if ($criteria) {
                    $assignment->grade = $criteria->title;
                    $assignment->remark = $criteria->description;
                }

                $assignment->save();
            }
        }


        return redirect()->route('kpi.assignments.index')->with('success', 'KPI scores submitted successfully!');
    }
}
