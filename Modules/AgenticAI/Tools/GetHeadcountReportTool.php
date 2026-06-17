<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetHeadcountReportTool - Get employee headcount analytics
 * Author: Sanket
 */
class GetHeadcountReportTool extends BaseTool
{
    public function name(): string
    {
        return 'get_headcount_report';
    }

    public function description(): string
    {
        return 'Get employee headcount by department, designation, or status. Use when management needs headcount analytics.';
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
                'group_by' => [
                    'type' => 'string',
                    'enum' => ['department', 'designation', 'status', 'branch'],
                    'description' => 'Group by field (optional, defaults to department)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $groupBy = $args['group_by'] ?? 'department';
            
            $query = \DB::table('users')
                ->select(
                    "{$groupBy}s.name as group_name",
                    \DB::raw('COUNT(users.id) as employee_count')
                )
                ->leftJoin("{$groupBy}s", "users.{$groupBy}_id", '=', "{$groupBy}s.id")
                ->groupBy("{$groupBy}s.name");

            $headcountData = $query->get();

            $totalEmployees = $headcountData->sum('employee_count');

            return [
                'has_data' => true,
                'grouped_by' => $groupBy,
                'total_employees' => $totalEmployees,
                'breakdown' => $headcountData->map(function($item) use ($totalEmployees) {
                    $percentage = $totalEmployees > 0 ? round(($item->employee_count / $totalEmployees) * 100, 1) : 0;
                    return [
                        'name' => $item->group_name ?? 'Unassigned',
                        'count' => $item->employee_count,
                        'percentage' => $percentage
                    ];
                })->toArray(),
                'message' => "Headcount report by {$groupBy}: {$totalEmployees} total employees"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
