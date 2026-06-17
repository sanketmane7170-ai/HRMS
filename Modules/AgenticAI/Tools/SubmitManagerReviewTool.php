<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * SubmitManagerReviewTool - Submit manager's review for employee
 * Author: Sanket
 */
class SubmitManagerReviewTool extends BaseTool
{
    public function name(): string
    {
        return 'submit_manager_review';
    }

    public function description(): string
    {
        return 'Submit manager review for an employee. Use when manager wants to review subordinate performance.';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'review_id' => [
                    'type' => 'integer',
                    'description' => 'Employee review ID'
                ],
                'rating' => [
                    'type' => 'integer',
                    'description' => 'Overall rating (1-5)'
                ],
                'comments' => [
                    'type' => 'string',
                    'description' => 'Manager comments'
                ],
                'strengths' => [
                    'type' => 'string',
                    'description' => 'Employee strengths (optional)'
                ],
                'areas_for_improvement' => [
                    'type' => 'string',
                    'description' => 'Areas for improvement (optional)'
                ],
            ],
            'required' => ['review_id', 'rating', 'comments']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can submit reviews
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to submit reviews'];
            }

            if (empty($args['review_id']) || empty($args['rating']) || empty($args['comments'])) {
                return ['error' => 'review_id, rating, and comments are required'];
            }

            $review = \DB::table('employee_reviews')->find($args['review_id']);
            
            if (!$review) {
                return ['error' => "Review with ID {$args['review_id']} not found"];
            }

            // Update review
            \DB::table('employee_reviews')
                ->where('id', $args['review_id'])
                ->update([
                    'manager_rating' => $args['rating'],
                    'manager_comments' => $args['comments'],
                    'strengths' => $args['strengths'] ?? null,
                    'areas_for_improvement' => $args['areas_for_improvement'] ?? null,
                    'status' => 'completed',
                    'reviewed_by' => $currentUser->id,
                    'reviewed_at' => now(),
                    'updated_at' => now()
                ]);

            $employee = \App\Models\User::find($review->employee_id);

            return [
                'success' => true,
                'review' => [
                    'id' => $review->id,
                    'employee' => $employee->name ?? 'Unknown',
                    'rating' => $args['rating'],
                    'status' => 'completed'
                ],
                'message' => "Manager review submitted for {$employee->name}. Rating: {$args['rating']}/5"
            ];

        } catch (Exception $e) {
            \Log::error('SubmitManagerReviewTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to submit review. Please try again.', 'success' => false];
        }
    }
}
