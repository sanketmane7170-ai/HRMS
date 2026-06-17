<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * ExtendProbationTool - Extend employee probation period
 * Author: Sanket
 */
class ExtendProbationTool extends BaseTool
{
    public function name(): string
    {
        return 'extend_probation';
    }

    public function description(): string
    {
        return 'Extend employee probation period. Use when HR wants to extend probation.';
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
                'extension_days' => [
                    'type' => 'integer',
                    'description' => 'Number of days to extend'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for extension'
                ],
            ],
            'required' => ['user_id', 'extension_days', 'reason']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can extend probation
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to extend probation'];
            }

            if (empty($args['user_id']) || empty($args['extension_days']) || empty($args['reason'])) {
                return ['error' => 'user_id, extension_days, and reason are required'];
            }

            $user = User::find($args['user_id']);
            
            if (!$user) {
                return ['error' => "Employee with ID {$args['user_id']} not found"];
            }

            if (!$user->probation_end_date) {
                return [
                    'error' => "Employee {$user->name} does not have a probation period defined",
                    'employee' => $user->name
                ];
            }

            $oldEndDate = \Carbon\Carbon::parse($user->probation_end_date);
            $newEndDate = $oldEndDate->copy()->addDays($args['extension_days']);

            // Create extension record
            $extensionId = \DB::table('probation_extensions')->insertGetId([
                'user_id' => $user->id,
                'old_end_date' => $oldEndDate->format('Y-m-d'),
                'new_end_date' => $newEndDate->format('Y-m-d'),
                'extension_days' => $args['extension_days'],
                'reason' => $args['reason'],
                'extended_by' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update user probation end date
            $user->update([
                'probation_end_date' => $newEndDate->format('Y-m-d')
            ]);

            return [
                'success' => true,
                'extension' => [
                    'id' => $extensionId,
                    'employee' => $user->name,
                    'old_end_date' => $oldEndDate->format('Y-m-d'),
                    'new_end_date' => $newEndDate->format('Y-m-d'),
                    'extension_days' => $args['extension_days'],
                    'reason' => $args['reason']
                ],
                'message' => "Probation extended for {$user->name} by {$args['extension_days']} days. New end date: {$newEndDate->format('Y-m-d')}"
            ];

        } catch (Exception $e) {
            \Log::error('ExtendProbationTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to extend probation. Please try again.', 'success' => false];
        }
    }
}
