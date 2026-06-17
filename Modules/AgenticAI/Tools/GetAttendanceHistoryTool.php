<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Attendance\Entities\Attendance;
use Illuminate\Support\Facades\Auth;

class GetAttendanceHistoryTool extends BaseTool
{
    public function name(): string
    {
        return 'get_attendance_history';
    }

    public function description(): string
    {
        return 'Get attendance history for the authenticated user for the last N days.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'days' => [
                    'type' => 'integer',
                    'default' => 7,
                    'description' => 'Number of past days to retrieve. Default is 7.'
                ]
            ],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        $days = $args['days'] ?? 7;
        
        try {
            $attendance = Attendance::my()
                ->orderByDesc('date')
                ->limit($days)
                ->get();

            return [
                'attendance' => $attendance->map(function($record) {
                    return [
                        'date' => $record->date,
                        'clock_in' => $record->clock_in,
                        'clock_out' => $record->clock_out,
                        'total_worked' => $record->total_worked,
                        'status' => $record->status->name ?? $record->status,
                        'remarks' => $record->remark
                    ];
                }),
                'count' => $attendance->count(),
                'days_requested' => $days
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to fetch attendance history',
                'message' => $e->getMessage()
            ];
        }
    }
}
