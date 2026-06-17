<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * IssuePromotionLetterTool - Issue promotion letter
 * Author: Sanket
 */
class IssuePromotionLetterTool extends BaseTool
{
    public function name(): string
    {
        return 'issue_promotion_letter';
    }

    public function description(): string
    {
        return 'Issue a promotion letter to an employee. Use when HR wants to promote an employee.';
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
                    'description' => 'Employee ID to promote'
                ],
                'new_designation_id' => [
                    'type' => 'integer',
                    'description' => 'New designation ID'
                ],
                'effective_date' => [
                    'type' => 'string',
                    'description' => 'Promotion effective date in YYYY-MM-DD format'
                ],
                'salary_increase' => [
                    'type' => 'number',
                    'description' => 'Salary increase amount (optional)'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Promotion reason (optional)'
                ],
            ],
            'required' => ['user_id', 'new_designation_id', 'effective_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can issue promotions
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required to issue promotion letters'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to issue promotion letters'];
            }

            if (empty($args['user_id']) || empty($args['new_designation_id']) || empty($args['effective_date'])) {
                return ['error' => 'user_id, new_designation_id, and effective_date are required'];
            }

            $user = User::find($args['user_id']);
            if (!$user) {
                return ['error' => "Employee with ID {$args['user_id']} not found"];
            }

            $newDesignation = \DB::table('designations')->find($args['new_designation_id']);
            if (!$newDesignation) {
                return ['error' => "Designation with ID {$args['new_designation_id']} not found"];
            }

            $oldDesignationId = $user->designation_id;
            $oldDesignation = $user->designation;

            // Create promotion record
            $promotionId = \DB::table('user_promotions')->insertGetId([
                'user_id' => $user->id,
                'old_designation_id' => $oldDesignationId,
                'new_designation_id' => $args['new_designation_id'],
                'effective_date' => $args['effective_date'],
                'salary_increase' => $args['salary_increase'] ?? 0,
                'reason' => $args['reason'] ?? 'Performance and contribution',
                'issued_by' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update user designation
            $user->update([
                'designation_id' => $args['new_designation_id']
            ]);

            // Update salary if provided
            if (!empty($args['salary_increase'])) {
                $user->update([
                    'salary' => ($user->salary ?? 0) + $args['salary_increase']
                ]);
            }

            return [
                'success' => true,
                'promotion' => [
                    'id' => $promotionId,
                    'employee' => $user->name,
                    'from_designation' => $oldDesignation->name ?? 'N/A',
                    'to_designation' => $newDesignation->name,
                    'effective_date' => $args['effective_date'],
                    'salary_increase' => $args['salary_increase'] ?? 0,
                ],
                'message' => "Promotion letter issued to {$user->name}. Promoted from {$oldDesignation->name} to {$newDesignation->name}"
            ];

        } catch (Exception $e) {
            \Log::error('IssuePromotionLetterTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to issue promotion letter. Please try again.', 'success' => false];
        }
    }
}
