<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Attendance\Entities\Attendance;

class CheckOutTool extends BaseTool
{
    public function name(): string
    {
        return 'check_out';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Check out from work. Use when user wants to mark their end of workday.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        try {
            $attendance = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('date', now()->toDateString())
                ->whereNull('check_out')
                ->first();
                
            if (!$attendance) {
                return ['error' => 'No check-in found', 'message' => 'You need to check in first before checking out.'];
            }
            
            $attendance->update([
                'check_out' => now()->format('H:i:s'),
                'status' => 'present'
            ]);
            
            return [
                'success' => true,
                'message' => "Checked out successfully at " . now()->format('H:i:s'),
                'attendance' => [
                    'date' => $attendance->date,
                    'check_in' => $attendance->check_in,
                    'check_out' => $attendance->check_out,
                    'status' => 'present'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CheckOutTool failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return ['error' => 'Failed to check out', 'message' => $e->getMessage()];
        }
    }
}
