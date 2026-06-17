<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Leave\Entities\Leave;

class CancelLeaveTool extends BaseTool
{
    public function name(): string
    {
        return 'cancel_leave';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Cancel a pending or approved leave request. Use when user wants to cancel their leave.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'leave_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the leave request to cancel'
                ]
            ],
            'required' => ['leave_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $leaveId = $args['leave_id'] ?? null;
        
        if (!$leaveId) {
            return [
                'error' => 'Missing leave ID',
                'message' => 'Please provide the leave request ID to cancel.'
            ];
        }
        
        try {
            $leave = Leave::query()
                ->where('id', $leaveId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if (!$leave) {
                return [
                    'error' => 'Leave not found',
                    'message' => "Leave request ID {$leaveId} not found, already cancelled, or you don't have permission to cancel it."
                ];
            }
            
            $leave->update(['status' => 'cancelled']);
            
            return [
                'success' => true,
                'message' => "Leave request ID {$leaveId} has been cancelled successfully.",
                'leave' => [
                    'id' => $leave->id,
                    'from' => $leave->from_date,
                    'to' => $leave->to_date,
                    'status' => 'cancelled'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CancelLeaveTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'leave_id' => $leaveId
            ]);
            
            return [
                'error' => 'Failed to cancel leave',
                'message' => 'Unable to cancel leave request. Error: ' . $e->getMessage()
            ];
        }
    }
}
