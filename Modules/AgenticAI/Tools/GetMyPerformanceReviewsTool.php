<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\PerformanceReview\Entities\PerformanceReview;

class GetMyPerformanceReviewsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_performance_reviews';
    }

    public function description(): string
    {
        return 'Get user\'s performance reviews and ratings. Use when user asks about their performance reviews, ratings, or evaluations.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of reviews (default 5)',
                    'default' => 5
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $limit = min($args['limit'] ?? 5, 20);
        
        try {
            // Use pivot table to get user's reviews
            $reviews = \DB::table('performance_review_user')
                ->join('performance_reviews', 'performance_review_user.performance_review_id', '=', 'performance_reviews.id')
                ->where('performance_review_user.user_id', $user->id)
                ->select(
                    'performance_reviews.id',
                    'performance_reviews.status',
                    'performance_reviews.start_date',
                    'performance_reviews.score',
                    'performance_review_user.status as user_status',
                    'performance_review_user.reviewer_avg_score',
                    'performance_review_user.hr_avg_score'
                )
                ->orderBy('performance_reviews.start_date', 'desc')
                ->limit($limit)
                ->get();
                
            if ($reviews->isEmpty()) {
                return [
                    'message' => 'You have no performance reviews yet.',
                    'reviews' => []
                ];
            }
            
            return [
                'reviews' => $reviews->map(function($review) {
                    return [
                        'start_date' => $review->start_date ? date('M d, Y', strtotime($review->start_date)) : 'N/A',
                        'status' => $review->status,
                        'score' => $review->score ?? 'Not scored',
                        'reviewer_score' => $review->reviewer_avg_score ?? 'N/A',
                        'hr_score' => $review->hr_avg_score ?? 'N/A',
                        'user_status' => $review->user_status
                    ];
                })->toArray(),
                'count' => $reviews->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyPerformanceReviewsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch reviews',
                'message' => 'Unable to retrieve performance reviews. Please contact HR.'
            ];
        }
    }
}
