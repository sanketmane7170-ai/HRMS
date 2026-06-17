<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * GetAttendanceReportTool - Generate attendance report
 * Author: Sanket
 */
class GetAttendanceReportTool extends BaseTool
{
    public function name(): string
    {
        return 'get_attendance_report';
    }

    public function description(): string
    {
        return 'Generate attendance report for employees or teams. Use when manager needs attendance analytics.';
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
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date in YYYY-MM-DD format'
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date in YYYY-MM-DD format'
                ],
                'user_ids' => [
                    'type' => 'array',
                    'description' => 'Employee IDs (optional, all if not provided)',
                    'items' => ['type' => 'integer']
                ],
                'department_id' => [
                    'type' => 'integer',
                    'description' => 'Department ID (optional)'
                ],
            ],
            'required' => ['start_date', 'end_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            if (empty($args['start_date']) || empty($args['end_date'])) {
                return ['error' => 'start_date and end_date are required'];
            }

            $startDate = \Carbon\Carbon::parse($args['start_date']);
            $endDate = \Carbon\Carbon::parse($args['end_date']);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            $query = \DB::table('attendances')
                ->join('users', 'attendances.user_id', '=', 'users.id')
                ->whereBetween('attendances.date', [$startDate, $endDate])
                ->select(
                    'users.id',
                    'users.name',
                    \DB::raw('COUNT(CASE WHEN attendances.status = "present" THEN 1 END) as present_days'),
                    \DB::raw('COUNT(CASE WHEN attendances.status = "absent" THEN 1 END) as absent_days'),
                    \DB::raw('COUNT(CASE WHEN attendances.is_late = 1 THEN 1 END) as late_days')
                )
                ->groupBy('users.id', 'users.name');

            if (!empty($args['user_ids'])) {
                $query->whereIn('users.id', $args['user_ids']);
            }

            if (!empty($args['department_id'])) {
                $query->where('users.department_id', $args['department_id']);
            }

            $attendanceData = $query->get();

            if ($attendanceData->isEmpty()) {
                return [
                    'has_data' => false,
                    'period' => "{$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}",
                    'message' => 'No attendance data found'
                ];
            }

            return [
                'has_data' => true,
                'period' => "{$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}",
                'total_days' => $totalDays,
                'summary' => [
                    'total_employees' => $attendanceData->count(),
                    'average_attendance' => round($attendanceData->avg('present_days') / $totalDays * 100, 1) . '%'
                ],
                'employees' => $attendanceData->map(function($emp) use ($totalDays) {
                    $attendancePercentage = $totalDays > 0 ? round(($emp->present_days / $totalDays) * 100, 1) : 0;
                    return [
                        'id' => $emp->id,
                        'name' => $emp->name,
                        'present_days' => $emp->present_days,
                        'absent_days' => $emp->absent_days,
                        'late_days' => $emp->late_days,
                        'attendance_percentage' => $attendancePercentage
                    ];
                })->toArray(),
                'message' => "Attendance report: {$attendanceData->count()} employees for {$totalDays} days"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
