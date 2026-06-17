<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetOrgChartTool - Get organization chart
 * Author: Sanket
 */
class GetOrgChartTool extends BaseTool
{
    public function name(): string
    {
        return 'get_org_chart';
    }

    public function description(): string
    {
        return 'Get organization hierarchy chart. Use when user wants to see company structure.';
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
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Department ID (optional, shows all if not provided)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $query = \DB::table('users')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'designations.name as designation',
                    'departments.name as department',
                    'users.manager_id',
                    'managers.name as manager_name'
                )
                ->leftJoin('designations', 'users.designation_id', '=', 'designations.id')
                ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                ->leftJoin('users as managers', 'users.manager_id', '=', 'managers.id')
                ->where('users.status', 'active');

            if (!empty($args['department_id'])) {
                $query->where('users.department_id', $args['department_id']);
            }

            $employees = $query->get();

            if ($employees->isEmpty()) {
                return [
                    'has_data' => false,
                    'message' => 'No employees found'
                ];
            }

            // Build hierarchy
            $hierarchy = [];
            foreach ($employees as $emp) {
                $hierarchy[] = [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'designation' => $emp->designation ?? 'N/A',
                    'department' => $emp->department ?? 'N/A',
                    'manager_id' => $emp->manager_id,
                    'manager_name' => $emp->manager_name ?? 'None'
                ];
            }

            return [
                'has_data' => true,
                'total_employees' => $employees->count(),
                'organization_chart' => $hierarchy,
                'message' => "Organization chart: {$employees->count()} employees"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
