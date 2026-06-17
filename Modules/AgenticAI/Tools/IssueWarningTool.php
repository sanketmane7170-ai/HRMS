<?php

namespace Modules\AgenticAI\Tools;

use Modules\Warning\Entities\UserWarning;
use App\Models\User;
use Exception;
use Modules\Warning\Enums\WarningType;

class IssueWarningTool extends BaseTool
{
    public function name(): string
    {
        return 'issue_warning';
    }

    public function description(): string
    {
        return 'Issue a disciplinary warning to an employee. Use this for record-keeping or performance management.';
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
                'user_id' => ['type' => 'integer', 'description' => 'ID of the employee.'],
                'detail' => ['type' => 'string', 'description' => 'Reason for the warning.'],
                'type' => [
                    'type' => 'string', 
                    'enum' => ['verbal', 'first', 'second', 'third', 'performance', 'attendance_issue', 'notice_of_termination', 'termination'],
                    'description' => 'Level of warning.'
                ],
                'date' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD. Default: today']
            ],
            'required' => ['user_id', 'detail', 'type']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - only HR/managers can issue warnings
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required', 'message' => 'You must be logged in to issue warnings.'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'Permission denied', 'message' => 'Only HR or Managers can issue warnings.'];
            }

            $user = User::findOrFail($args['user_id']);
            
            $warning = UserWarning::create([
                'user_id' => $args['user_id'],
                'date' => $args['date'] ?? now()->format('Y-m-d'),
                'detail' => $args['detail'],
                'type' => $args['type'], 
                'acknowledgement' => 0,
                'created_by' => $currentUser->id, //Sanket v2.0 - removed unsafe fallback to user 1
            ]);

            return [
                'success' => true,
                'message' => "Warning issued successfully to '{$user->name}'.",
                'warning_id' => $warning->id
            ];

        } catch (Exception $e) {
            \Log::error('IssueWarningTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to issue warning. Please try again.'];
        }
    }
}
