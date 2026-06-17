<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * CreateReviewCycleTool - Create performance review cycle
 * Author: Sanket
 */
class CreateReviewCycleTool extends BaseTool
{
    public function name(): string
    {
        return 'create_review_cycle';
    }

    public function description(): string
    {
        return 'Create a performance review cycle for employees. Use when HR wants to initiate performance reviews.';
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
                'title' => [
                    'type' => 'string',
                    'description' => 'Review cycle title (e.g., "Q1 2026 Review")'
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date in YYYY-MM-DD format'
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date in YYYY-MM-DD format'
                ],
                'employee_ids' => [
                    'type' => 'array',
                    'description' => 'Employee IDs to include (optional, all if empty)',
                    'items' => ['type' => 'integer']
                ],
            ],
            'required' => ['title', 'start_date', 'end_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can create review cycles
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to create review cycles'];
            }

            if (empty($args['title']) || empty($args['start_date']) || empty($args['end_date'])) {
                return ['error' => 'title, start_date, and end_date are required'];
            }

            // Create review cycle
            $cycleId = \DB::table('review_cycles')->insertGetId([
                'title' => $args['title'],
                'start_date' => $args['start_date'],
                'end_date' => $args['end_date'],
                'status' => 'active',
                'created_by' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Get employees
            $employees = !empty($args['employee_ids']) 
                ? \App\Models\User::whereIn('id', $args['employee_ids'])->where('status', 'active')->get()
                : \App\Models\User::where('status', 'active')->get();

            // Assign review to employees
            $assignedCount = 0;
            foreach ($employees as $employee) {
                \DB::table('employee_reviews')->insert([
                    'cycle_id' => $cycleId,
                    'employee_id' => $employee->id,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $assignedCount++;
            }

            return [
                'success' => true,
                'cycle' => [
                    'id' => $cycleId,
                    'title' => $args['title'],
                    'start_date' => $args['start_date'],
                    'end_date' => $args['end_date'],
                    'employees_count' => $assignedCount
                ],
                'message' => "Review cycle '{$args['title']}' created for {$assignedCount} employees"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
