<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\CandidateScore;
use Modules\Recruitment\Entities\ScoringCriterion;
use Modules\Recruitment\Entities\InterviewFeedback;
use Modules\Recruitment\Http\Requests\CandidateScoringRequest;
use App\Notifications\Recruitment\ScoringCompleteNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CandidateScoringController extends Controller
{
    use AuthorizesRequests;
    public function __construct()
    {
        view()->share('activeLink', 'recruitment-applications');
    }

    /**
     * Display candidate scores for an application.
     */
    public function index(Request $request, int $applicationId): Renderable
    {
        $application = Application::with([
            'job', 
            'user', 
            'candidateScores.scorer', 
            'candidateScores.criteria',
            'interviewFeedback.interviewer'
        ])->findOrFail($applicationId);

        $this->authorize('view', $application);

        $scores = $application->candidateScores()->with(['scorer', 'criteria'])->get();
        $feedback = $application->interviewFeedback()->with('interviewer')->get();
        
        // Get scoring criteria templates
        $jobTitle = $application->job->title ?? '';
        $commonCriteria = ScoringCriterion::getCriteriaByRole($jobTitle);

        return view('recruitment::scoring.index', compact(
            'application', 
            'scores', 
            'feedback', 
            'commonCriteria'
        ));
    }

    /**
     * Show form for creating a new candidate score.
     */
    public function create(int $applicationId): Renderable
    {
        $application = Application::with('job', 'user')->findOrFail($applicationId);
        $this->authorize('score', $application);

        // Get the next interview round
        $lastRound = $application->candidateScores()->max('interview_round') ?? 0;
        $nextRound = $lastRound + 1;

        // Get scoring criteria based on job role
        $jobTitle = $application->job->title ?? '';
        $criteria = ScoringCriterion::getCriteriaByRole($jobTitle);

        return view('recruitment::scoring.create', compact(
            'application', 
            'nextRound', 
            'criteria'
        ));
    }

    /**
     * Store a new candidate score.
     */
    public function store(CandidateScoringRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validatedWithMetrics();
            
            // Create the candidate score
            $candidateScore = CandidateScore::create($validatedData);

            // Create scoring criteria
            if (isset($validatedData['scoring_criteria'])) {
                foreach ($validatedData['scoring_criteria'] as $criterionData) {
                    $candidateScore->criteria()->create([
                        'criterion_id' => $criterionData['criterion_id'],
                        'criterion_name' => $criterionData['criterion_name'],
                        'score' => $criterionData['score'],
                        'weight' => $criterionData['weight'],
                        'notes' => $criterionData['notes'] ?? null,
                    ]);
                }
            }

            // Update application stage if this is a final score
            $application = $candidateScore->application;
            if ($candidateScore->is_final_score) {
                $newStage = match($candidateScore->recommendation) {
                    'strongly_recommend', 'recommend' => 'offer',
                    'strongly_not_recommend', 'not_recommend' => 'rejected',
                    default => $application->stage
                };
                
                if ($newStage !== $application->stage) {
                    $application->update(['stage' => $newStage]);
                }
            }

            // Send notifications to relevant stakeholders
            $this->sendScoringNotifications($candidateScore);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Candidate scoring completed successfully.',
                'data' => [
                    'score_id' => $candidateScore->id,
                    'overall_score' => $candidateScore->overall_score,
                    'recommendation' => $candidateScore->recommendation_text,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save candidate score: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific candidate score.
     */
    public function show(int $scoreId): Renderable
    {
        $candidateScore = CandidateScore::with([
            'application.job',
            'application.user',
            'scorer',
            'criteria'
        ])->findOrFail($scoreId);

        $this->authorize('view', $candidateScore->application);

        return view('recruitment::scoring.show', compact('candidateScore'));
    }

    /**
     * Show form for editing a candidate score.
     */
    public function edit(int $scoreId): Renderable
    {
        $candidateScore = CandidateScore::with([
            'application.job',
            'criteria'
        ])->findOrFail($scoreId);

        $this->authorize('score', $candidateScore->application);

        // Get scoring criteria based on job role
        $jobTitle = $candidateScore->application->job->title ?? '';
        $criteriaTemplates = ScoringCriterion::getCriteriaByRole($jobTitle);

        return view('recruitment::scoring.edit', compact(
            'candidateScore', 
            'criteriaTemplates'
        ));
    }

    /**
     * Update a candidate score.
     */
    public function update(CandidateScoringRequest $request, int $scoreId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $candidateScore = CandidateScore::findOrFail($scoreId);
            $this->authorize('score', $candidateScore->application);

            $validatedData = $request->validatedWithMetrics();
            $candidateScore->update($validatedData);

            // Update scoring criteria
            if (isset($validatedData['scoring_criteria'])) {
                // Delete existing criteria
                $candidateScore->criteria()->delete();
                
                // Create new criteria
                foreach ($validatedData['scoring_criteria'] as $criterionData) {
                    $candidateScore->criteria()->create([
                        'criterion_id' => $criterionData['criterion_id'],
                        'criterion_name' => $criterionData['criterion_name'],
                        'score' => $criterionData['score'],
                        'weight' => $criterionData['weight'],
                        'notes' => $criterionData['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Candidate score updated successfully.',
                'data' => [
                    'score_id' => $candidateScore->id,
                    'overall_score' => $candidateScore->overall_score,
                    'recommendation' => $candidateScore->recommendation_text,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update candidate score: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a candidate score.
     */
    public function destroy(int $scoreId): JsonResponse
    {
        try {
            $candidateScore = CandidateScore::findOrFail($scoreId);
            $this->authorize('delete', $candidateScore->application);

            $candidateScore->delete();

            return response()->json([
                'success' => true,
                'message' => 'Candidate score deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete candidate score: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scoring analytics for an application.
     */
    public function analytics(int $applicationId): JsonResponse
    {
        $application = Application::with('candidateScores')->findOrFail($applicationId);
        $this->authorize('view', $application);

        $scores = $application->candidateScores;
        
        if ($scores->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No scores available for this application.'
            ]);
        }

        $analytics = [
            'total_rounds' => $scores->count(),
            'average_score' => round($scores->avg('overall_score'), 2),
            'highest_score' => $scores->max('overall_score'),
            'lowest_score' => $scores->min('overall_score'),
            'score_trend' => $scores->pluck('overall_score', 'interview_round'),
            'recommendation_distribution' => $scores->groupBy('recommendation')
                ->map->count()
                ->toArray(),
            'scorer_breakdown' => $scores->load('scorer')
                ->groupBy('scorer.name')
                ->map(function ($scores) {
                    return [
                        'count' => $scores->count(),
                        'average_score' => round($scores->avg('overall_score'), 2)
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get scoring criteria templates.
     */
    public function getCriteriaTemplates(Request $request): JsonResponse
    {
        $role = $request->get('role', '');
        $criteria = ScoringCriterion::getCriteriaByRole($role);

        return response()->json([
            'success' => true,
            'data' => $criteria
        ]);
    }

    /**
     * Compare scores between candidates.
     */
    public function compare(Request $request): JsonResponse
    {
        $applicationIds = $request->input('application_ids', []);
        
        if (empty($applicationIds) || count($applicationIds) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least 2 applications to compare.'
            ]);
        }

        $applications = Application::with(['candidateScores', 'user', 'job'])
            ->whereIn('id', $applicationIds)
            ->get();

        $comparison = [];
        
        foreach ($applications as $application) {
            $finalScore = $application->finalScore ?? $application->latestScore;
            
            $comparison[] = [
                'application_id' => $application->id,
                'candidate_name' => $application->candidate_name ?? $application->user->name,
                'job_title' => $application->job->title,
                'overall_score' => $finalScore?->overall_score,
                'recommendation' => $finalScore?->recommendation_text,
                'total_rounds' => $application->candidateScores->count(),
                'average_score' => $application->average_score,
            ];
        }

        // Sort by overall score (descending)
        usort($comparison, function ($a, $b) {
            return ($b['overall_score'] ?? 0) <=> ($a['overall_score'] ?? 0);
        });

        return response()->json([
            'success' => true,
            'data' => $comparison
        ]);
    }

    /**
     * Send notifications when scoring is completed.
     */
    private function sendScoringNotifications(CandidateScore $candidateScore): void
    {
        $application = $candidateScore->application;
        
        // Notify HR team
        $hrUsers = \App\Models\User::role(['admin', 'hr'])->get();
        Notification::send($hrUsers, new ScoringCompleteNotification($candidateScore));
        
        // Notify hiring manager if different from scorer
        if ($application->job && $application->job->hiring_manager_id !== $candidateScore->scored_by) {
            $hiringManager = \App\Models\User::find($application->job->hiring_manager_id);
            if ($hiringManager) {
                $hiringManager->notify(new ScoringCompleteNotification($candidateScore));
            }
        }
    }
}