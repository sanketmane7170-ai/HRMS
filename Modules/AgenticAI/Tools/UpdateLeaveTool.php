<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Leave\Entities\Leave;

class UpdateLeaveTool extends BaseTool
{
    public function name(): string
    {
        return 'update_leave';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Update a pending leave request. Use when user wants to modify their leave dates, reason, or type before it\'s approved.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'leave_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the leave request to update'
                ],
                'from_date' => [
                    'type' => 'string',
                    'description' => 'New start date (YYYY-MM-DD format)'
                ],
                'to_date' => [
                    'type' => 'string',
                    'description' => 'New end date (YYYY-MM-DD format)'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Updated reason for leave'
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
                'message' => 'Please provide the leave request ID to update.'
            ];
        }
        
        try {
            $leave = Leave::query()
                ->where('id', $leaveId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$leave) {
                return [
                    'error' => 'Leave not found',
                    'message' => "Leave request ID {$leaveId} not found, already processed, or you don't have permission to update it."
                ];
            }
            
            $updates = [];
            if (isset($args['from_date'])) $updates['from_date'] = $args['from_date'];
            if (isset($args['to_date'])) $updates['to_date'] = $args['to_date'];
            if (isset($args['reason'])) $updates['reason'] = $args['reason'];
            
            if (empty($updates)) {
                return [
                    'error' => 'No updates provided',
                    'message' => 'Please provide at least one field to update.'
                ];
            }
            
            $leave->update($updates);
            
            return [
                'success' => true,
                'message' => "Leave request ID {$leaveId} updated successfully.",
                'leave' => [
                    'id' => $leave->id,
                    'from' => $leave->from_date,
                    'to' => $leave->to_date,
                    'reason' => $leave->reason,
                    'status' => 'pending'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('UpdateLeaveTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'leave_id' => $leaveId
            ]);
            
            return [
                'error' => 'Failed to update leave',
                'message' => 'Unable to update leave request. Error: ' . $e->getMessage()
            ];
        }
    }
}
