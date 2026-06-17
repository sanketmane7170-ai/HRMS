<?php

namespace Modules\AgenticAI\Tools;

use Modules\Training\Entities\TrainingProgram;
use Exception;

/**
 * CreateTrainingProgramTool - Create training program
 * Author: Sanket
 */
class CreateTrainingProgramTool extends BaseTool
{
    public function name(): string
    {
        return 'create_training_program';
    }

    public function description(): string
    {
        return 'Create a new training program. Use when HR wants to create a training program.';
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
                    'description' => 'Training program title'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Program description'
                ],
                'duration_hours' => [
                    'type' => 'integer',
                    'description' => 'Duration in hours'
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date in YYYY-MM-DD format (optional)'
                ],
            ],
            'required' => ['title', 'description', 'duration_hours']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can create training programs
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to create training programs'];
            }

            $program = TrainingProgram::create([
                'title' => $args['title'],
                'description' => $args['description'],
                'duration_hours' => $args['duration_hours'],
                'start_date' => $args['start_date'] ?? null,
                'status' => 'active',
                'created_by' => $currentUser->id
            ]);

            return [
                'success' => true,
                'program_id' => $program->id,
                'title' => $program->title,
                'duration_hours' => $program->duration_hours,
                'message' => "Training program '{$program->title}' created successfully ({$program->duration_hours} hours)"
            ];

        } catch (Exception $e) {
            \Log::error('CreateTrainingProgramTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to create training program. Please try again.', 'success' => false];
        }
    }
}
