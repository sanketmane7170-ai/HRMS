<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetReviewCycleStatusTool - Track review cycle progress
 * Author: Sanket
 */
class GetReviewCycleStatusTool extends BaseTool
{
    public function name(): string
    {
        return 'get_review_cycle_status';
    }

    public function description(): string
    {
        return 'Get performance review cycle status and completion progress. Use when HR wants to track review progress.';
    }

    public function isSensitive(): bool
    {
        return false;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'cycle_id' => [
                    'type' => 'integer',
                    'description' => 'Review cycle ID (optional, gets latest if not provided)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $cycle = !empty($args['cycle_id'])
                ? \DB::table('review_cycles')->find($args['cycle_id'])
                : \DB::table('review_cycles')->orderBy('created_at', 'desc')->first();

            if (!$cycle) {
                return [
                    'has_cycle' => false,
                    'message' => 'No review cycle found'
                ];
            }

            // Get review statistics
            $reviews = \DB::table('employee_reviews')
                ->where('cycle_id', $cycle->id)
                ->get();

            $statusBreakdown = [
                'pending' => $reviews->where('status', 'pending')->count(),
                'in_progress' => $reviews->where('status', 'in_progress')->count(),
                'completed' => $reviews->where('status', 'completed')->count(),
            ];

            $completionPercentage = $reviews->count() > 0 
                ? round(($statusBreakdown['completed'] / $reviews->count()) * 100, 1)
                : 0;

            return [
                'has_cycle' => true,
                'cycle' => [
                    'id' => $cycle->id,
                    'title' => $cycle->title,
                    'start_date' => $cycle->start_date,
                    'end_date' => $cycle->end_date,
                    'status' => $cycle->status
                ],
                'progress' => [
                    'total_employees' => $reviews->count(),
                    'completed' => $statusBreakdown['completed'],
                    'in_progress' => $statusBreakdown['in_progress'],
                    'pending' => $statusBreakdown['pending'],
                    'completion_percentage' => $completionPercentage
                ],
                'message' => "Review cycle '{$cycle->title}': {$completionPercentage}% complete ({$statusBreakdown['completed']}/{$reviews->count()})"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
