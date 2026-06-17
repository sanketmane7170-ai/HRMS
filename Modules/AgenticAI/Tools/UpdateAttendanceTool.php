<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Entities\Attendance;

class UpdateAttendanceTool extends BaseTool
{
    public function name(): string
    {
        return 'update_attendance';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Update attendance record. Use when user wants to correct their check-in/check-out time.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'date' => ['type' => 'string', 'description' => 'Date to update (YYYY-MM-DD)'],
                'check_in' => ['type' => 'string', 'description' => 'New check-in time (HH:MM:SS)'],
                'check_out' => ['type' => 'string', 'description' => 'New check-out time (HH:MM:SS)']
            ],
            'required' => ['date']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        if (empty($args['date'])) {
            return ['error' => 'Missing date', 'message' => 'Provide date.'];
        }
        
        try {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $args['date'])
                ->first();
            
            if (!$attendance) return ['error' => 'Not found', 'message' => 'No attendance record for this date.'];
            
            $updates = [];
            if (isset($args['check_in'])) $updates['check_in'] = $args['check_in'];
            if (isset($args['check_out'])) $updates['check_out'] = $args['check_out'];
            
            if (empty($updates)) return ['error' => 'No updates', 'message' => 'Provide check_in or check_out.'];
            
            $attendance->update($updates);
            
            return [
                'success' => true,
                'message' => 'Attendance updated successfully.',
                'attendance' => [
                    'date' => $attendance->date,
                    'check_in' => $attendance->check_in,
                    'check_out' => $attendance->check_out
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('UpdateAttendanceTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
