<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * IssueIncrementLetterTool - Issue salary increment letter
 * Author: Sanket
 */
class IssueIncrementLetterTool extends BaseTool
{
    public function name(): string
    {
        return 'issue_increment_letter';
    }

    public function description(): string
    {
        return 'Issue a salary increment letter to an employee. Use when HR wants to give a salary raise.';
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
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Employee ID'
                ],
                'increment_amount' => [
                    'type' => 'number',
                    'description' => 'Increment amount'
                ],
                'effective_date' => [
                    'type' => 'string',
                    'description' => 'Increment effective date in YYYY-MM-DD format'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Increment reason (optional)'
                ],
            ],
            'required' => ['user_id', 'increment_amount', 'effective_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can issue salary increments
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required to issue increment letters'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to issue increment letters'];
            }

            if (empty($args['user_id']) || empty($args['increment_amount']) || empty($args['effective_date'])) {
                return ['error' => 'user_id, increment_amount, and effective_date are required'];
            }

            $user = User::find($args['user_id']);
            if (!$user) {
                return ['error' => "Employee with ID {$args['user_id']} not found"];
            }

            $previousSalary = $user->salary ?? 0;
            $newSalary = $previousSalary + $args['increment_amount'];
            $percentage = $previousSalary > 0 ? round(($args['increment_amount'] / $previousSalary) * 100, 2) : 0;

            // Create increment record
            $incrementId = \DB::table('user_increments')->insertGetId([
                'user_id' => $user->id,
                'previous_salary' => $previousSalary,
                'new_salary' => $newSalary,
                'increment_amount' => $args['increment_amount'],
                'effective_date' => $args['effective_date'],
                'reason' => $args['reason'] ?? 'Performance and contribution',
                'issued_by' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update user salary
            $user->update([
                'salary' => $newSalary
            ]);

            return [
                'success' => true,
                'increment' => [
                    'id' => $incrementId,
                    'employee' => $user->name,
                    'previous_salary' => $previousSalary,
                    'new_salary' => $newSalary,
                    'increment_amount' => $args['increment_amount'],
                    'increment_percentage' => $percentage,
                    'effective_date' => $args['effective_date'],
                ],
                'message' => "Increment letter issued to {$user->name}. Salary increased from {$previousSalary} to {$newSalary} ({$percentage}% increase)"
            ];

        } catch (Exception $e) {
            \Log::error('IssueIncrementLetterTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to issue increment letter. Please try again.', 'success' => false];
        }
    }
}
