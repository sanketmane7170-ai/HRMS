<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * AssignTrainingTool - Assign training to employees
 * Author: Sanket
 */
class AssignTrainingTool extends BaseTool
{
    public function name(): string
    {
        return 'assign_training';
    }

    public function description(): string
    {
        return 'Assign training program to employees. Use when HR wants to assign training.';
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
                'program_id' => [
                    'type' => 'integer',
                    'description' => 'Training program ID'
                ],
                'user_ids' => [
                    'type' => 'array',
                    'description' => 'Employee IDs to assign',
                    'items' => ['type' => 'integer']
                ],
                'deadline' => [
                    'type' => 'string',
                    'description' => 'Completion deadline in YYYY-MM-DD format (optional)'
                ],
            ],
            'required' => ['program_id', 'user_ids']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can assign training
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to assign training'];
            }

            $program = \DB::table('training_programs')->find($args['program_id']);
            
            if (!$program) {
                return ['error' => "Training program with ID {$args['program_id']} not found"];
            }

            $assignedCount = 0;
            foreach ($args['user_ids'] as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                \DB::table('training_enrollments')->insert([
                    'program_id' => $args['program_id'],
                    'user_id' => $userId,
                    'status' => 'assigned',
                    'deadline' => $args['deadline'] ?? null,
                    'assigned_by' => $currentUser->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $assignedCount++;
            }

            return [
                'success' => true,
                'program_title' => $program->title,
                'assigned_count' => $assignedCount,
                'message' => "Training '{$program->title}' assigned to {$assignedCount} employee(s)"
            ];

        } catch (Exception $e) {
            \Log::error('AssignTrainingTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to assign training. Please try again.', 'success' => false];
        }
    }
}
