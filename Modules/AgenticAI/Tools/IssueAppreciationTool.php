<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * IssueAppreciationTool - Issue appreciation letter
 * Author: Sanket
 */
class IssueAppreciationTool extends BaseTool
{
    public function name(): string
    {
        return 'issue_appreciation';
    }

    public function description(): string
    {
        return 'Issue appreciation letter to an employee. Use when manager wants to appreciate an employee.';
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
                'title' => [
                    'type' => 'string',
                    'description' => 'Appreciation title'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for appreciation'
                ],
            ],
            'required' => ['user_id', 'title', 'reason']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can issue appreciations
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to issue appreciations'];
            }

            $user = User::find($args['user_id']);
            
            if (!$user) {
                return ['error' => "Employee with ID {$args['user_id']} not found"];
            }

            $appreciationId = \DB::table('appreciations')->insertGetId([
                'user_id' => $user->id,
                'title' => $args['title'],
                'reason' => $args['reason'],
                'issued_by' => $currentUser->id,
                'issued_by_name' => $currentUser->name,
                'issued_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return [
                'success' => true,
                'appreciation_id' => $appreciationId,
                'employee' => $user->name,
                'title' => $args['title'],
                'message' => "Appreciation letter issued to {$user->name}: {$args['title']}"
            ];

        } catch (Exception $e) {
            \Log::error('IssueAppreciationTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to issue appreciation. Please try again.', 'success' => false];
        }
    }
}
