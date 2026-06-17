<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\PerformanceReview;
use Modules\PerformanceReview\Entities\ReviewDuration;
use Modules\PerformanceReview\Entities\QuestionSet;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Exception;

class PerformanceReviewController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'performance-reviews');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PerformanceReview::with(['employees', 'duration', 'questionSet', 'scoreCriteria']);


            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('employees', fn($row) => $row->employees->pluck('name')->join(', '))
                // ->addColumn('duration', fn($row) => $row->duration ? "{$row->duration->months} {$row->duration->label}" : '-')
                ->addColumn('grade', fn($row) => $row->scoreCriteria->title ?? '-')

                ->addColumn('question_set', fn($row) => $row->questionSet->name ?? '-')
                ->addColumn('status', fn($row) => ucfirst($row->status))
                ->addColumn('action', function ($row) {
                    return createActionButton(route('performancereview.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit') .
                        createActionButton(route('performancereview.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable') .
                        createActionButton(route('performancereview.show', $row->id), 'View', 'btn-success', 'fa fa-eye');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::reviews.index');
    }

    public function create()
    {
        $employees = User::pluck('name', 'id');
        $durations = ReviewDuration::all()->mapWithKeys(fn($d) => [$d->id => "{$d->months} {$d->label} "]);
        $questionSets = QuestionSet::pluck('name', 'id');

        return response()->json([
            'success' => true,
            'html' => view('performancereview::reviews.create', compact('employees', 'durations', 'questionSets'))->render()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employees' => 'required|array',
            'employees.*' => 'exists:users,id',
            'review_duration_id' => 'nullable|exists:review_durations,id', // Make nullable
            'question_set_id' => 'nullable|exists:question_sets,id',
            'score_criteria_id' => 'nullable|exists:score_criteria,id',
            'status' => 'required|string|in:Pending,In Progress,Completed,Declined',
            'start_date' => 'required|date',
        ]);


        DB::beginTransaction();
        try {
            $data['review_duration_id'] = null;

            $review = PerformanceReview::create(collect($data)->except('employees')->toArray());
            $review->employees()->sync($data['employees']);
            DB::commit();

            return response()->json(getSuccessResponse("Performance review created."));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        $review = PerformanceReview::with('employees')->findOrFail($id);
        $employees = User::pluck('name', 'id');
        // $durations = ReviewDuration::all()->mapWithKeys(fn($d) => [$d->id => "{$d->months} {$d->label} "]);
        // In controller
        $durations = ReviewDuration::all();
        $employees = User::pluck('name', 'id'); // id => name
        $selectedEmployeeIds = $review->employees->pluck('id')->toArray();

        $questionSets = QuestionSet::pluck('name', 'id');

        return response()->json([
            'success' => true,
            'html' => view('performancereview::reviews.edit', compact('review', 'employees', 'durations', 'questionSets', 'selectedEmployeeIds'))->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $review = PerformanceReview::findOrFail($id);

        $data = $request->validate([
            'employees' => 'required|array',
            'employees.*' => 'exists:users,id',
            'review_duration_id' => 'required|exists:review_durations,id',
            'question_set_id' => 'nullable|exists:question_sets,id',
            'status' => 'required|string|in:Pending,In Progress,Completed,Declined',
            'start_date' => 'required|date',
        ]);

        $review->update(collect($data)->except('employees')->toArray());
        $review->employees()->sync($data['employees']);

        return response()->json(getSuccessResponse("Performance review updated."));
    }

    public function destroy($id)
    {
        $review = PerformanceReview::findOrFail($id);
        $review->delete();

        return response()->json(getSuccessResponse("Performance review deleted."));
    }

    public function show($id)
    {
        $review = PerformanceReview::with(['employees', 'duration', 'questionSet', 'responses'])->findOrFail($id);
        return view('performancereview::reviews.show', compact('review'));
    }
}
