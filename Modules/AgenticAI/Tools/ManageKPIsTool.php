<?php

namespace Modules\AgenticAI\Tools;

use Modules\PerformanceReview\Entities\KeyPerformanceIndicator;
use Modules\PerformanceReview\Entities\EmployeeKpiAssignment;
use Modules\PerformanceReview\Entities\ReviewDuration;
use App\Models\User;
use Exception;

class ManageKPIsTool extends BaseTool
{
    public function name(): string
    {
        return 'manage_kpis';
    }

    public function description(): string
    {
        return 'List available KPIs or assign them to an employee for a specific duration (Quarterly, Bi-Annual, Annual).';
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
                'action' => [
                    'type' => 'string',
                    'enum' => ['list_kpis', 'assign_kpi', 'list_durations'],
                    'description' => 'Action to perform.'
                ],
                'user_id' => ['type' => 'integer', 'description' => 'Required for assign_kpi.'],
                'duration_id' => ['type' => 'integer', 'description' => 'Required for assign_kpi.'],
                'due_date' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD. Required for assign_kpi.'],
                'kpi_ids' => [
                    'type' => 'array', 
                    'items' => ['type' => 'integer'],
                    'description' => 'List of KPI IDs to assign.'
                ]
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $action = $args['action'];

        try {
            switch ($action) {
                case 'list_durations':
                    $durations = ReviewDuration::all();
                    return ['durations' => $durations->toArray()];

                case 'list_kpis':
                    $kpis = KeyPerformanceIndicator::all();
                    return ['kpis' => $kpis->toArray()];

                case 'assign_kpi':
                    if (empty($args['user_id']) || empty($args['duration_id']) || empty($args['due_date'])) {
                        throw new Exception('user_id, duration_id, and due_date are required for assign_kpi.');
                    }

                    $assignment = EmployeeKpiAssignment::create([
                        'user_id' => $args['user_id'],
                        'duration_id' => $args['duration_id'],
                        'due_date' => $args['due_date'],
                        'status' => 'pending',
                    ]);

                    if (!empty($args['kpi_ids'])) {
                        foreach ($args['kpi_ids'] as $kpiId) {
                            $assignment->items()->create(['key_performance_indicator_id' => $kpiId]);
                        }
                    }

                    return [
                        'success' => true,
                        'message' => "KPIs assigned successfully to user ID {$args['user_id']}.",
                        'assignment_id' => $assignment->id
                    ];

                default:
                    return ['error' => 'Invalid action.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
