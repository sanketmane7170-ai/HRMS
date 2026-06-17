<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\PerformanceReview;
use Modules\PerformanceReview\Entities\ReviewResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReviewEvaluationController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'reviewevaluations');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PerformanceReview::with(['employees'])
                ->whereHas('employees', fn($q) => $q->where('performance_review_user.status', 'Completed'));

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('question_set', function ($row) {
                    return $row->questionSet->name ?? '-';
                })

                ->addColumn('employees', function ($row) {
                    return $row->employees
                        ->where('pivot.status', 'Completed')
                        ->pluck('name')
                        ->implode(', ');
                })
                ->addColumn('employee_responses', function ($row) {
                    return $row->employees
                        ->where('pivot.status', 'Completed')
                        ->map(fn($emp) => $emp->name . ' (' . ($emp->pivot->employee_response ?? 'Pending') . ')')
                        ->implode('<br>');
                })
                // ->addColumn('action', function ($row) {
                //     $buttons = '';
                //     foreach ($row->employees->where('pivot.status', 'Completed') as $emp) {
                //         $buttons .= '<a href="' . route('evaluate.view', [$row->id, $emp->id]) . '" class="btn btn-sm btn-primary m-1">Evaluate ' . $emp->name . '</a>';
                //     }
                //     return $buttons;
                // })
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    foreach ($row->employees->where('pivot.status', 'Completed') as $emp) {
                        $employeeResponse = $emp->pivot->employee_response ?? 'Pending';

                        // Only show evaluate button if response is not 'Accepted'
                        if (strtolower($employeeResponse) !== 'accepted') {
                            $buttons .= '<a href="' . route('evaluate.view', [$row->id, $emp->id]) . '" class="btn btn-sm btn-primary m-1">Evaluate ' . $emp->name . '</a>';
                        }
                    }

                    return $buttons ?: '-';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::reviewevaluations.index');
    }

    // public function view($review_id, $user_id)
    // {
    //     $responses = ReviewResponse::with(['question.options'])
    //         ->where('performance_review_id', $review_id)
    //         ->where('user_id', $user_id)
    //         ->get();

    //     $user = User::findOrFail($user_id);
    //     $review = PerformanceReview::findOrFail($review_id);

    //     return view('performancereview::reviewevaluations.evaluate', compact('responses', 'user', 'review'));
    // }
    // public function view($review_id, $user_id)
    // {
    //     $responses = ReviewResponse::with(['question.options'])
    //         ->where('performance_review_id', $review_id)
    //         ->where('user_id', $user_id)
    //         ->get();

    //     $user = User::findOrFail($user_id);
    //     $review = PerformanceReview::findOrFail($review_id);

    //     $totalScore = 0;

    //     foreach ($responses as $response) {
    //         $selected = $response->answer;
    //         $correctOption = $response->question->options->firstWhere('is_correct', 1)?->id;
    //         if ($selected && $selected == $correctOption) {
    //             $totalScore += $response->question->max_score ?? 0;
    //         }
    //     }

    //     return view('performancereview::reviewevaluations.evaluate', compact('responses', 'user', 'review', 'totalScore'));
    // }
    public function view($review_id, $user_id)
    {
        $responses = ReviewResponse::with(['question.options'])
            ->where('performance_review_id', $review_id)
            ->where('user_id', $user_id)
            ->get();

        $user = User::findOrFail($user_id);
        $review = PerformanceReview::findOrFail($review_id);

        $totalScore = 0;

        foreach ($responses as $response) {
            $selected = $response->answer;
            $correctOption = $response->question->options->firstWhere('is_correct', 1)?->id;
            $response->user_score = 0;

            if ($selected && $selected == $correctOption) {
                $score = $response->question->max_score ?? 0;
                $totalScore += $score;
                $response->user_score = $score;
            }
        }

        return view('performancereview::reviewevaluations.evaluate', compact('responses', 'user', 'review', 'totalScore'));
    }



    // public function storeScore(Request $request, $review_id, $user_id)
    // {
    //     $request->validate([
    //         'scores' => 'required|array',
    //         'scores.*' => 'nullable|numeric|min:0|max:10', // assuming 10 is max per question
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         foreach ($request->scores as $response_id => $score) {
    //             ReviewResponse::where('id', $response_id)
    //                 ->where('performance_review_id', $review_id)
    //                 ->where('user_id', $user_id)
    //                 ->update(['score' => $score]);
    //         }

    //         DB::commit();
    //         return redirect()->route('evaluate.index')->with('success', 'Scores saved successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->withErrors(['error' => $e->getMessage()]);
    //     }
    // }
    // public function storeScore(Request $request, $review_id, $user_id)
    // {
    //     $request->validate([
    //         'scores' => 'required|array',
    //         'scores.*' => 'nullable|numeric|min:0|max:10',
    //         'comments' => 'nullable|array',
    //         'comments.*' => 'nullable|string|max:1000',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         foreach ($request->scores as $response_id => $score) {
    //             $comment = $request->comments[$response_id] ?? null;

    //             ReviewResponse::where('id', $response_id)
    //                 ->where('performance_review_id', $review_id)
    //                 ->where('user_id', $user_id)
    //                 ->update([
    //                     'score' => $score,
    //                     'comment' => $comment,
    //                 ]);
    //         }

    //         DB::commit();
    //         return redirect()->route('evaluate.index')->with('success', 'Scores and comments saved successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->withErrors(['error' => $e->getMessage()]);
    //     }
    // }
    public function storeScore(Request $request, $review_id, $user_id)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:10',
            'comments' => 'nullable|array',
            'comments.*' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $totalScore = 0;
            $count = 0;

            foreach ($request->scores as $response_id => $score) {
                $comment = $request->comments[$response_id] ?? null;

                ReviewResponse::where('id', $response_id)
                    ->where('performance_review_id', $review_id)
                    ->where('user_id', $user_id)
                    ->update([
                        'score' => $score,
                        'comment' => $comment,
                    ]);

                if (is_numeric($score)) {
                    $totalScore += $score;
                    $count++;
                }
            }

            $avgScore = $count > 0 ? round($totalScore / $count, 2) : null;

            // ✅ Update reviewer_avg_score in pivot table
            DB::table('performance_review_user')
                ->where('performance_review_id', $review_id)
                ->where('user_id', $user_id)
                ->update([
                    'reviewer_avg_score' => $avgScore,
                    'updated_at' => now(),
                ]);

            DB::commit();
            return redirect()->route('evaluate.index')->with('success', 'Scores, comments and average saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function submitHrReview(Request $request, $review_id, $user_id)
    {
        $request->validate([
            'hr_increment_percent' => 'required|numeric|min:0|max:100',
            'hr_basic_percent' => 'required|numeric|min:0|max:100',
            'hr_housing_percent' => 'required|numeric|min:0|max:100',
            'hr_transport_percent' => 'required|numeric|min:0|max:100',
            'hr_other_percent' => 'required|numeric|min:0|max:100',
            'hr_incentive_percent' => 'required|numeric|min:0|max:100',
            'hr_avg_score' => 'required|numeric|min:0|max:100',
        ]);

        DB::table('performance_review_user')
            ->where('performance_review_id', $review_id)
            ->where('user_id', $user_id)
            ->update([
                'hr_increment_percent' => $request->hr_increment_percent,
                'hr_basic_percent' => $request->hr_basic_percent,
                'hr_housing_percent' => $request->hr_housing_percent,
                'hr_transport_percent' => $request->hr_transport_percent,
                'hr_other_percent' => $request->hr_other_percent,
                'hr_incentive_percent' => $request->hr_incentive_percent,
                'hr_review' => 'Completed',
                 'hr_avg_score' => $request->hr_avg_score,
                'hr_review_date' => now(),
            ]);

        return redirect()->route('evaluate.index')->with('success', 'HR Review submitted successfully.');
    }
}
