<?php

namespace Modules\AgenticAI\Tools;

use Modules\PerformanceReview\Entities\EmployeeKpiAssignment;
use Modules\PerformanceReview\Entities\EmployeeKpiItem;
use App\Models\User;
use Exception;

class SubmitPerformanceRatingTool extends BaseTool
{
    public function name(): string
    {
        return 'submit_performance_rating';
    }

    public function description(): string
    {
        return 'Submit a final performance rating and remark for an employee KPI assignment. Required: user_id, grade, and remark.';
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
                'assignment_id' => ['type' => 'integer', 'description' => 'Specific assignment ID (optional, will find latest if not provided).'],
                'grade' => ['type' => 'string', 'description' => 'Performance grade (e.g., A, B, C, or numeric score).'],
                'remark' => ['type' => 'string', 'description' => 'Manager feedback/remark.'],
            ],
            'required' => ['user_id', 'grade', 'remark']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $query = EmployeeKpiAssignment::where('user_id', $args['user_id']);
            
            if (!empty($args['assignment_id'])) {
                $assignment = $query->findOrFail($args['assignment_id']);
            } else {
                $assignment = $query->orderBy('due_date', 'desc')->firstOrFail();
            }

            $assignment->update([
                'grade' => $args['grade'],
                'remark' => $args['remark'],
                'status' => 'completed',
            ]);

            return [
                'success' => true,
                'message' => "Performance rating '{$args['grade']}' submitted for '{$assignment->user->name}'.",
                'assignment_id' => $assignment->id
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
