<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\PerformanceReview;
use Modules\PerformanceReview\Entities\ReviewResponse;
use Modules\PerformanceReview\Entities\QuestionOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ReviewResponseController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'Review Response');
    }

    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = PerformanceReview::with(['duration', 'questionSet', 'employees'])
    //             ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()));

    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('duration', fn($row) => $row->duration ? $row->duration->months . ' ' . $row->duration->label : '-')
    //             ->addColumn('question_set', fn($row) => $row->questionSet->name ?? '-')
    //             ->addColumn('status', function ($row) {
    //                 $employee = $row->employees->where('pivot.user_id', Auth::id())->first();
    //                 return ucfirst($employee?->pivot->status ?? 'Pending');
    //             })

    //             // ->addColumn('action', function ($row) {
    //             //     $employee = $row->employees->where('pivot.user_id', Auth::id())->first();
    //             //     $status = strtolower($employee?->pivot->status ?? 'pending');

    //             //     // if ($status === 'completed') {
    //             //     //     return '-';
    //             //     // }
    //             //     return createActionButton(route('backend.employee.reviewresponse.view', $row->id), 'View', 'btn-success edit-button', 'fa fa-check');
    //             // })
    //             ->addColumn('action', function ($row) {
    //                 $employee = $row->employees->where('pivot.user_id', Auth::id())->first();
    //                 $status = strtolower($employee?->pivot->status ?? 'pending');

    //                 $reviewerScore = ReviewResponse::where('performance_review_id', $row->id)
    //                     ->where('user_id', Auth::id())
    //                     ->sum('score');

    //                 $buttons= "";
    //                 if ($status === 'completed' && $reviewerScore > 0) {
    //                     $buttons .= createActionButton(route('backend.employee.reviewresponse.view', $row->id) . '?mode=reviewed', 'Show Reviewed', 'btn-info edit-button', 'fa fa-eye');
    //                 }
    //                 else{
    //                 $buttons .= createActionButton(route('backend.employee.reviewresponse.view', $row->id), 'View', 'btn-success edit-button', 'fa fa-check');

    //                 }

    //                 return $buttons;
    //             })

    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('performancereview::reviewresponses.index');
    // }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // $data = PerformanceReview::with(['duration', 'questionSet', 'employees'])
            //     ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()));
            $data = PerformanceReview::with(['scoreCriteria', 'questionSet', 'employees'])
                ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()));


            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('grade', fn($row) => $row->scoreCriteria->title ?? '-')
                ->addColumn('question_set', fn($row) => $row->questionSet->name ?? '-')
                ->addColumn('status', function ($row) {
                    $employee = $row->employees->where('pivot.user_id', Auth::id())->first();
                    return ucfirst($employee?->pivot->status ?? 'Pending');
                })
                ->addColumn('hr_review', function ($row) {
                    $employee = $row->employees->where('pivot.user_id', Auth::id())->first();
                    return ucfirst($employee?->pivot->hr_review ?? 'Pending');
                })
                ->addColumn('employee_response', function ($row) {
                    $employee = $row->employees->where('pivot.user_id', Auth::id())->first();
                    return ucfirst($employee?->pivot->employee_response ?? 'Pending');
                })
                ->addColumn('action', function ($row) {
                    $employee = $row->employees->where('pivot.user_id', Auth::id())->first();
                    $pivot = $employee?->pivot;
                    $status = strtolower($pivot->status ?? 'pending');
                    $responseStatus = strtolower($pivot->employee_response ?? 'pending');

                    $reviewerScore = ReviewResponse::where('performance_review_id', $row->id)
                        ->where('user_id', Auth::id())
                        ->sum('score');

                    $buttons = '';

                    // Show Reviewed if completed and scored
                    if ($status === 'completed' && $reviewerScore > 0) {
                        $buttons .= createActionButton(
                            route('backend.employee.reviewresponse.view', $row->id) . '?mode=reviewed',
                            'Show Reviewed',
                            'btn-info edit-button',
                            'fa fa-eye'
                        );
                    } else {
                        // Show default View button
                        $buttons .= createActionButton(
                            route('backend.employee.reviewresponse.view', $row->id),
                            'View',
                            'btn-success edit-button',
                            'fa fa-check'
                        );
                    }

                    // Show Increment Letter if accepted
                    if ($responseStatus === 'accepted') {
                        $buttons .= createActionButton(
                            route('backend.employee.reviewresponse.incrementletter', [$row->id, Auth::id()]),
                            'View Increment',
                            'btn-warning',
                            'fa fa-file-alt',
                            '_blank'
                        );
                    }

                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::reviewresponses.index');
    }


    // public function respondView($id)
    // {
    //     $review = PerformanceReview::with(['questionSet.questions.options'])
    //         ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()))
    //         ->findOrFail($id);

    //     return response()->json([
    //         'success' => true,
    //         'html' => view('performancereview::reviewresponses.respond', compact('review'))->render()
    //     ]);
    // }

    // public function respondView($id)
    // {
    //     $review = PerformanceReview::with(['questionSet.questions.options'])
    //         ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()))
    //         ->findOrFail($id);

    //     // Check if already submitted
    //     $employeePivot = $review->employees->where('pivot.user_id', Auth::id())->first()?->pivot;
    //     $isCompleted = strtolower($employeePivot->status ?? '') === 'completed';

    //     // Load responses if completed
    //     $responses = [];
    //     $totalScore = 0;

    //     if ($isCompleted) {
    //         $responses = ReviewResponse::where('performance_review_id', $review->id)
    //             ->where('user_id', Auth::id())
    //             ->get()
    //             ->keyBy('question_id');

    //         foreach ($review->questionSet->questions as $q) {
    //             $selected = $responses[$q->id]->answer ?? null;
    //             $correctOption = $q->options->firstWhere('is_correct', 1)?->id;
    //             if ($selected && $selected == $correctOption) {
    //                 $totalScore += $q->max_score ?? 0;
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'html' => view('performancereview::reviewresponses.respond', compact('review', 'responses', 'isCompleted', 'totalScore'))->render()
    //     ]);
    // }
    // public function respondView($id)
    // {
    //     $review = PerformanceReview::with(['questionSet.questions.options'])
    //         ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()))
    //         ->findOrFail($id);

    //     $employeePivot = $review->employees->where('pivot.user_id', Auth::id())->first()?->pivot;
    //     $isCompleted = strtolower($employeePivot->status ?? '') === 'completed';
    //     $status = ucfirst($employeePivot->status ?? 'Pending');  // ✅ define $status

    //     $responses = [];
    //     $totalScore = 0;

    //     if ($isCompleted) {
    //         $responses = ReviewResponse::where('performance_review_id', $review->id)
    //             ->where('user_id', Auth::id())
    //             ->get()
    //             ->keyBy('question_id');

    //         foreach ($review->questionSet->questions as $q) {
    //             $selected = $responses[$q->id]->answer ?? null;
    //             $correctOption = $q->options->firstWhere('is_correct', 1)?->id;
    //             if ($selected && $selected == $correctOption) {
    //                 $totalScore += $q->max_score ?? 0;
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'html' => view('performancereview::reviewresponses.respond', compact(
    //             'review',
    //             'responses',
    //             'isCompleted',
    //             'totalScore',
    //             'status',
    //             'employeePivot'
    //         ))->render()
    //     ]);
    // }
    // public function respondView($id)
    // {
    //     $review = PerformanceReview::with(['questionSet.questions.options'])
    //         ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()))
    //         ->findOrFail($id);

    //     $employeePivot = $review->employees->where('pivot.user_id', Auth::id())->first()?->pivot;
    //     $isCompleted = strtolower($employeePivot->status ?? '') === 'completed';
    //     $status = ucfirst($employeePivot->status ?? 'Pending');

    //     $responses = [];
    //     $totalScore = 0;

    //     if ($isCompleted) {
    //         $responses = ReviewResponse::where('performance_review_id', $review->id)
    //             ->where('user_id', Auth::id())
    //             ->get()
    //             ->keyBy('question_id');

    //         foreach ($review->questionSet->questions as $q) {
    //             $selected = $responses[$q->id]->answer ?? null;
    //             $correctOption = $q->options->firstWhere('is_correct', 1)?->id;
    //             if ($selected && $selected == $correctOption) {
    //                 $totalScore += $q->max_score ?? 0;
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'html' => view('performancereview::reviewresponses.respond', compact(
    //             'review',
    //             'responses',
    //             'isCompleted',
    //             'totalScore',
    //             'status',
    //             'employeePivot'
    //         ))->render()
    //     ]);
    // }
    public function respondView($id)
    {
        $self_total=0;
        $review = PerformanceReview::with(['questionSet.questions.options'])
            ->whereHas('employees', fn($q) => $q->where('users.id', Auth::id()))
            ->findOrFail($id);

        $employeePivot = $review->employees->where('pivot.user_id', Auth::id())->first()?->pivot;
        $isCompleted = strtolower($employeePivot->status ?? '') === 'completed';
        $status = ucfirst($employeePivot->status ?? 'Pending');

        $responses = [];
        $totalScore = 0;
        $reviewerScore = 0;

        if ($isCompleted) {
            $responses = ReviewResponse::where('performance_review_id', $review->id)
                ->where('user_id', Auth::id())
                ->get()
                ->keyBy('question_id');

            foreach ($responses as $r) {
                $reviewerScore += $r->score ?? 0;
            }
            $self_total=0;
            foreach ($review->questionSet->questions as $q) {
                $selected = $responses[$q->id]->answer ?? null;
                $correctOption = $q->options->firstWhere('is_correct', 1)?->id;
                $self_total+= $q->max_score ?? 0;
                if ($selected && $selected == $correctOption) {
                    $totalScore += $q->max_score ?? 0;
                }
            }
        }
        $questionCount = $review->questionSet->questions->count();
        $selfScore = $questionCount > 0 ? round($totalScore / $questionCount, 2) : 0;
        $reviewerAvgScore = $employeePivot->reviewer_avg_score ?? 0;
        $hrAvgScore = $employeePivot->hr_avg_score ?? 0;
        return response()->json([
            'success' => true,
            'html' => view('performancereview::reviewresponses.respond', compact(
                'review',
                'responses',
                'isCompleted',
                'totalScore',
                'reviewerScore',
                'employeePivot',
                'status',
                'selfScore',
                'self_total',
                'reviewerAvgScore',
                'hrAvgScore'
            ))->render()
        ]);
    }




    public function respondSubmit(Request $request, $id)
    {
        $response = getErrorResponse();
        $review = PerformanceReview::whereHas('employees', fn($q) => $q->where('users.id', Auth::id()))
            ->findOrFail($id);

        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => ['required', 'exists:question_options,id'],
        ]);

        DB::beginTransaction();
        try {
            foreach ($data['answers'] as $question_id => $option_id) {
                ReviewResponse::updateOrCreate(
                    [
                        'performance_review_id' => $review->id,
                        'question_id' => $question_id,
                        'user_id' => Auth::id(),
                    ],
                    [
                        'answer' => $option_id,
                    ]
                );
            }

            DB::table('performance_review_user')
                ->where('performance_review_id', $review->id)
                ->where('user_id', Auth::id())
                ->update(['status' => 'Completed']);

            DB::commit();
            // return redirect()->route('reviewresponses.index')->with('success', 'Review submitted successfully.');
            $response = getSuccessResponse(createFlashMessage('Review', 'submitted'));

            // } catch (Exception $e) {
            //     DB::rollBack();
            //     return redirect()->back()->withErrors(['error' => $e->getMessage()]);
            // }
        } catch (Exception $e) {
            dd($e->getMessage());
            $response['error'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function accept($review_id, $user_id)
    {
        DB::table('performance_review_user')
            ->where('performance_review_id', $review_id)
            ->where('user_id', $user_id)
            ->update([
                'employee_response' => 'Accepted',
                'responded_at' => now()
            ]);

        return redirect()->route('backend.employee.reviewresponse.incrementletter', [$review_id, $user_id]);
    }

    public function decline($review_id, $user_id)
    {
        DB::table('performance_review_user')
            ->where('performance_review_id', $review_id)
            ->where('user_id', $user_id)
            ->update([
                'employee_response' => 'Declined',
                'responded_at' => now()
            ]);

        return redirect()->back()->with('success', 'You have declined the review.');
    }

    // public function incrementLetter($review_id, $user_id)
    // {
    //     $responses = ReviewResponse::where('performance_review_id', $review_id)
    //         ->where('user_id', $user_id)
    //         ->get();

    //     $totalReviewerScore = $responses->sum('score');

    //     $criteria = \Modules\PerformanceReview\Entities\IncrementCriteria::where('min_score', '<=', $totalReviewerScore)
    //         ->where('max_score', '>=', $totalReviewerScore)
    //         ->first();

    //     if (!$criteria) {
    //         return view('performancereview::reviewresponses.increment_letter')->with('error', 'No increment criteria found for this score.');
    //     }

    //     return view('performancereview::reviewresponses.increment_letter', compact('criteria', 'totalReviewerScore'));
    // }

    // public function incrementLetter($review_id, $user_id)
    // {
    //     $review = PerformanceReview::with('employees')->findOrFail($review_id);
    //     $pivot = $review->employees->where('id', $user_id)->first()?->pivot;

    //     if (!$pivot || $pivot->employee_response !== 'Accepted') {
    //         return back()->with('error', 'Employee has not accepted the review yet.');
    //     }

    //     $reviewerAvgScore = $pivot->reviewer_avg_score;
    //     $hrAvgScore = $pivot->hr_avg_score;

    //     $reviewerCriteria = null;
    //     if ($reviewerAvgScore) {
    //         $reviewerCriteria = \Modules\PerformanceReview\Entities\IncrementCriteria::where('min_score', '<=', $reviewerAvgScore)
    //             ->where('max_score', '>=', $reviewerAvgScore)
    //             ->first();
    //     }

    //     $hrCriteria = (object)[
    //         'label' => 'HR Evaluation',
    //         'basic_percent' => $pivot->hr_basic_percent ?? 0,
    //         'housing_percent' => $pivot->hr_housing_percent ?? 0,
    //         'transport_percent' => $pivot->hr_transport_percent ?? 0,
    //         'other_percent' => $pivot->hr_other_percent ?? 0,
    //         'incentive_percent' => $pivot->hr_incentive_percent ?? 0,
    //         'increment_percent' => $pivot->hr_increment_percent ?? 0,
    //     ];

    //     return view('performancereview::reviewresponses.increment_letter', compact(
    //         'reviewerAvgScore',
    //         'hrAvgScore',
    //         'reviewerCriteria',
    //         'hrCriteria'
    //     ));
    // }
    // public function incrementLetter($review_id, $user_id)
    // {
    //     $review = PerformanceReview::with('employees')->findOrFail($review_id);
    //     $user = \App\Models\User::with('user_salary')->findOrFail($user_id);
    //     $pivot = $review->employees->where('id', $user_id)->first()?->pivot;

    //     if (!$pivot || !$user->user_salary) {
    //         return back()->with('error', 'Salary data not found for user.');
    //     }

    //     $salary = $user->user_salary;

    //     $data = [
    //         'basic' => $salary->basic,
    //         'housing' => $salary->housing_allowance,
    //         'transport' => $salary->transport,
    //         'other' => $salary->other,
    //         'incentive' => $salary->incentive
    //     ];

    //     $percentages = [
    //         'basic' => $pivot->hr_basic_percent,
    //         'housing' => $pivot->hr_housing_percent,
    //         'transport' => $pivot->hr_transport_percent,
    //         'other' => $pivot->hr_other_percent,
    //         'incentive' => $pivot->hr_incentive_percent,
    //         'total' => $pivot->hr_increment_percent
    //     ];

    //     $increments = [];
    //     $newAmounts = [];

    //     foreach ($data as $key => $amount) {
    //         $inc = ($amount * ($percentages[$key] ?? 0)) / 100;
    //         $increments[$key] = round($inc, 2);
    //         $newAmounts[$key] = round($amount + $inc, 2);
    //     }

    //     return view('performancereview::reviewresponses.increment_letter', [
    //         'user' => $user,
    //         'review' => $review,
    //         'percentages' => $percentages,
    //         'salary' => $data,
    //         'increments' => $increments,
    //         'newAmounts' => $newAmounts,
    //         'totalReviewerScore' => $pivot->reviewer_avg_score,
    //         'totalHrScore' => $pivot->hr_avg_score,
    //     ]);
    // }
    // public function incrementLetter($review_id, $user_id)
    // {
    //     $responses = ReviewResponse::where('performance_review_id', $review_id)
    //         ->where('user_id', $user_id)
    //         ->get();

    //     $totalReviewerScore = $responses->sum('score');

    //     // ✅ Fetch the employee's review pivot row
    //     $pivot = DB::table('performance_review_user')
    //         ->where('performance_review_id', $review_id)
    //         ->where('user_id', $user_id)
    //         ->first();

    //     // ✅ Get avg scores from pivot
    //     $reviewerAvgScore = $pivot->reviewer_avg_score ?? null;
    //     $hrAvgScore = $pivot->hr_avg_score ?? null;

    //     // ✅ Find both criteria
    //     $reviewerCriteria = \Modules\PerformanceReview\Entities\IncrementCriteria::where('min_score', '<=', $reviewerAvgScore)
    //         ->where('max_score', '>=', $reviewerAvgScore)
    //         ->first();

    //     $hrCriteria = \Modules\PerformanceReview\Entities\IncrementCriteria::where('min_score', '<=', $hrAvgScore)
    //         ->where('max_score', '>=', $hrAvgScore)
    //         ->first();

    //     // ✅ Load salary
    //     $user = \App\Models\User::with('user_salary')->findOrFail($user_id);
    //     $salary = $user->user_salary;

    //     // ✅ Calculate increment breakdown from HR criteria
    //     $percentages = [
    //         'basic' => $hrCriteria->basic_percent ?? 0,
    //         'housing' => $hrCriteria->housing_percent ?? 0,
    //         'transport' => $hrCriteria->transport_percent ?? 0,
    //         'other' => $hrCriteria->other_percent ?? 0,
    //         'incentive' => $hrCriteria->incentive_percent ?? 0,
    //     ];
    //     $percentages['total'] = $hrCriteria->increment_percent ?? 0;

    //     $salaryValues = [
    //         'basic' => $salary->basic ?? 0,
    //         'housing' => $salary->housing_allowance ?? 0,
    //         'transport' => $salary->transport ?? 0,
    //         'other' => $salary->other ?? 0,
    //         'incentive' => $salary->incentive ?? 0,
    //     ];

    //     $increments = [];
    //     $newAmounts = [];
    //     foreach ($salaryValues as $key => $value) {
    //         $inc = ($value * $percentages[$key]) / 100;
    //         $increments[$key] = $inc;
    //         $newAmounts[$key] = $value + $inc;
    //     }

    //     return view('performancereview::reviewresponses.increment_letter', compact(
    //         'reviewerAvgScore',
    //         'reviewerCriteria',
    //         'hrAvgScore',
    //         'hrCriteria',
    //         'salaryValues',
    //         'increments',
    //         'newAmounts',
    //         'percentages'
    //     ));
    // }
    // public function incrementLetter($review_id, $user_id)
    // {
    //     $responses = ReviewResponse::where('performance_review_id', $review_id)
    //         ->where('user_id', $user_id)
    //         ->get();

    //     $totalReviewerScore = $responses->sum('score');

    //     $pivot = DB::table('performance_review_user')
    //         ->where('performance_review_id', $review_id)
    //         ->where('user_id', $user_id)
    //         ->first();

    //     $reviewerAvgScore = $pivot->reviewer_avg_score ?? null;
    //     $hrAvgScore = $pivot->hr_avg_score ?? null;

    //     $reviewerCriteria = \Modules\PerformanceReview\Entities\IncrementCriteria::where('min_score', '<=', $reviewerAvgScore)
    //         ->where('max_score', '>=', $reviewerAvgScore)
    //         ->first();

    //     $hrCriteria = \Modules\PerformanceReview\Entities\IncrementCriteria::where('min_score', '<=', $hrAvgScore)
    //         ->where('max_score', '>=', $hrAvgScore)
    //         ->first();

    //     $user = \App\Models\User::with('user_salary')->findOrFail($user_id);
    //     $salary = $user->user_salary;

    //     $salaryValues = [
    //         'basic' => $salary->basic ?? 0,
    //         'housing' => $salary->housing_allowance ?? 0,
    //         'transport' => $salary->transport ?? 0,
    //         'other' => $salary->other ?? 0,
    //         'incentive' => $salary->incentive ?? 0,
    //     ];

    //     $percentages = [
    //         'basic' => $hrCriteria->basic_percent ?? 0,
    //         'housing' => $hrCriteria->housing_percent ?? 0,
    //         'transport' => $hrCriteria->transport_percent ?? 0,
    //         'other' => $hrCriteria->other_percent ?? 0,
    //         'incentive' => $hrCriteria->incentive_percent ?? 0,
    //         'total' => $hrCriteria->increment_percent ?? 0,
    //     ];

    //     $increments = [];
    //     $newAmounts = [];
    //     foreach ($salaryValues as $key => $value) {
    //         $inc = ($value * ($percentages[$key] ?? 0)) / 100;
    //         $increments[$key] = round($inc, 2);
    //         $newAmounts[$key] = round($value + $inc, 2);
    //     }

    //     return view('performancereview::reviewresponses.increment_letter', compact(
    //         'reviewerAvgScore',
    //         'hrAvgScore',
    //         'reviewerCriteria',
    //         'hrCriteria',
    //         'salaryValues',
    //         'percentages',
    //         'increments',
    //         'newAmounts'
    //     ));
    // }
    public function incrementLetter($review_id, $user_id)
    {
        $pivot = DB::table('performance_review_user')
            ->where('performance_review_id', $review_id)
            ->where('user_id', $user_id)
            ->first();

        if (!$pivot) {
            return back()->with('error', 'No performance review record found.');
        }

        // HR scores & percent values
        $hrAvgScore = $pivot->hr_avg_score ?? null;

        $hrCriteria = (object)[
            'label' => 'Based on HR Review',
            'basic_percent' => $pivot->hr_basic_percent ?? 0,
            'housing_percent' => $pivot->hr_housing_percent ?? 0,
            'transport_percent' => $pivot->hr_transport_percent ?? 0,
            'other_percent' => $pivot->hr_other_percent ?? 0,
            'incentive_percent' => $pivot->hr_incentive_percent ?? 0,
            'increment_percent' => $pivot->hr_increment_percent ?? 0,
        ];

        // Load user and salary
        $user = \App\Models\User::with('user_salary')->findOrFail($user_id);
        $salary = $user->user_salary;

        $salaryValues = [
            'basic' => $salary->basic ?? 0,
            'housing' => $salary->housing_allowance ?? 0,
            'transport' => $salary->transport ?? 0,
            'other' => $salary->other ?? 0,
            'incentive' => $salary->incentive ?? 0,
        ];

        // Apply percentages
        $percentages = [
            'basic' => $hrCriteria->basic_percent,
            'housing' => $hrCriteria->housing_percent,
            'transport' => $hrCriteria->transport_percent,
            'other' => $hrCriteria->other_percent,
            'incentive' => $hrCriteria->incentive_percent,
            'total' => $hrCriteria->increment_percent,
        ];

        $increments = [];
        $newAmounts = [];

        foreach ($salaryValues as $key => $value) {
            $inc = ($value * ($percentages[$key] ?? 0)) / 100;
            $increments[$key] = round($inc, 2);
            $newAmounts[$key] = round($value + $inc, 2);
        }

        // Format for Blade
        $salaryComponents = [];
        foreach ($salaryValues as $key => $value) {
            $salaryComponents[$key] = [
                'existing' => $value,
                'revised' => $newAmounts[$key] ?? 0,
            ];
        }

        // Optional: if you want to display designations like in the PDF format
        $designationBefore = $user->user_salary->designation_before ?? null;
        $designationAfter = $user->user_salary->designation_after ?? null;

        $effectiveDate = now();

        return view('performancereview::reviewresponses.increment_letter', compact(
            'user',
            'hrAvgScore',
            'hrCriteria',
            'salaryValues',
            'percentages',
            'increments',
            'newAmounts',
            'salaryComponents',
            'designationBefore',
            'designationAfter',
            'effectiveDate'
        ));
    }
}
