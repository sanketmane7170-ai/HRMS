<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Shift\Entities\Shift;
use Modules\Shift\Entities\ShiftAssignment;

class GetMyShiftsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_shifts';
    }

    public function description(): string
    {
        return 'Get my work shift schedule. Use when user asks about their shift, schedule, roster, or work hours.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'days' => [
                    'type' => 'integer',
                    'description' => 'Number of days to show (default 7)',
                    'default' => 7
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $days = min($args['days'] ?? 7, 30);
        
        try {
            // Use user_shifts table directly
            $shifts = \DB::table('user_shifts')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
                
            if ($shifts->isEmpty()) {
                return [
                    'message' => 'No shifts assigned. Contact your manager for shift assignment.',
                    'shifts' => []
                ];
            }
            
            return [
                'shifts' => $shifts->map(function($shift) {
                    return [
                        'shift_start' => $shift->shift_start ?? 'N/A',
                        'shift_end' => $shift->shift_end ?? 'N/A',
                        'assigned_on' => $shift->created_at ? date('M d, Y', strtotime($shift->created_at)) : 'N/A'
                    ];
                })->toArray(),
                'count' => $shifts->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyShiftsTool failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return [
                'error' => 'Failed to fetch shifts',
                'message' => 'Unable to retrieve your shift schedule. Please contact your manager.'
            ];
        }
    }
}
