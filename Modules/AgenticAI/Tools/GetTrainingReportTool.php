<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetTrainingReportTool - Generate training completion report
 * Author: Sanket
 */
class GetTrainingReportTool extends BaseTool
{
    public function name(): string
    {
        return 'get_training_report';
    }

    public function description(): string
    {
        return 'Generate training completion and enrollment report. Use when HR needs training analytics.';
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
                'program_id' => [
                    'type' => 'integer',
                    'description' => 'Training program ID (optional, all programs if not provided)'
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Department ID (optional)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $query = \DB::table('training_enrollments')
                ->join('users', 'training_enrollments.user_id', '=', 'users.id')
                ->join('training_programs', 'training_enrollments.program_id', '=', 'training_programs.id')
                ->select(
                    'training_programs.id as program_id',
                    'training_programs.title as program_title',
                    \DB::raw('COUNT(training_enrollments.id) as total_enrolled'),
                    \DB::raw('COUNT(CASE WHEN training_enrollments.status = "completed" THEN 1 END) as completed'),
                    \DB::raw('COUNT(CASE WHEN training_enrollments.status = "in_progress" THEN 1 END) as in_progress'),
                    \DB::raw('COUNT(CASE WHEN training_enrollments.status = "assigned" THEN 1 END) as not_started')
                )
                ->groupBy('training_programs.id', 'training_programs.title');

            if (!empty($args['program_id'])) {
                $query->where('training_programs.id', $args['program_id']);
            }

            if (!empty($args['department_id'])) {
                $query->where('users.department_id', $args['department_id']);
            }

            $trainingData = $query->get();

            if ($trainingData->isEmpty()) {
                return [
                    'has_data' => false,
                    'message' => 'No training data found'
                ];
            }

            return [
                'has_data' => true,
                'total_programs' => $trainingData->count(),
                'programs' => $trainingData->map(function($prog) {
                    $completionRate = $prog->total_enrolled > 0 
                        ? round(($prog->completed / $prog->total_enrolled) * 100, 1) 
                        : 0;
                    return [
                        'program_id' => $prog->program_id,
                        'program_title' => $prog->program_title,
                        'total_enrolled' => $prog->total_enrolled,
                        'completed' => $prog->completed,
                        'in_progress' => $prog->in_progress,
                        'not_started' => $prog->not_started,
                        'completion_rate' => $completionRate . '%'
                    ];
                })->toArray(),
                'message' => "Training report: {$trainingData->count()} program(s)"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
