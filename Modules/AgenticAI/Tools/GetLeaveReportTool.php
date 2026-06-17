<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetLeaveReportTool - Generate leave utilization report
 * Author: Sanket
 */
class GetLeaveReportTool extends BaseTool
{
    public function name(): string
    {
        return 'get_leave_report';
    }

    public function description(): string
    {
        return 'Generate leave utilization report. Use when HR needs leave analytics.';
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
                'year' => [
                    'type' => 'integer',
                    'description' => 'Year (optional, defaults to current year)'
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
            $year = $args['year'] ?? now()->year;

            $query = \DB::table('leaves')
                ->join('users', 'leaves.user_id', '=', 'users.id')
                ->whereYear('leaves.start_date', $year)
                ->where('leaves.status', 'approved')
                ->select(
                    'users.id',
                    'users.name',
                    \DB::raw('SUM(leaves.total_days) as total_leave_days'),
                    \DB::raw('COUNT(leaves.id) as leave_count')
                )
                ->groupBy('users.id', 'users.name');

            if (!empty($args['department_id'])) {
                $query->where('users.department_id', $args['department_id']);
            }

            $leaveData = $query->get();

            if ($leaveData->isEmpty()) {
                return [
                    'has_data' => false,
                    'year' => $year,
                    'message' => 'No leave data found'
                ];
            }

            $totalLeaveDays = $leaveData->sum('total_leave_days');

            return [
                'has_data' => true,
                'year' => $year,
                'summary' => [
                    'total_employees' => $leaveData->count(),
                    'total_leave_days' => $totalLeaveDays,
                    'average_leave_per_employee' => round($totalLeaveDays / $leaveData->count(), 1)
                ],
                'employees' => $leaveData->map(function($emp) {
                    return [
                        'id' => $emp->id,
                        'name' => $emp->name,
                        'total_leave_days' => $emp->total_leave_days,
                        'leave_count' => $emp->leave_count
                    ];
                })->toArray(),
                'message' => "Leave report for {$year}: {$leaveData->count()} employees, Total: {$totalLeaveDays} days"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
