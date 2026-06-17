<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Leave\Entities\LeaveRequest;

class RejectLeaveRequestTool extends BaseTool
{
    public function name(): string
    {
        return 'reject_leave_request';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Reject a pending leave request. Use when user wants to reject or deny a leave request they are authorized to review.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'request_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the leave request to reject'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for rejection (required)'
                ]
            ],
            'required' => ['request_id', 'reason']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $requestId = $args['request_id'] ?? null;
        $reason = $args['reason'] ?? '';
        
        if (!$requestId || empty($reason)) {
            return [
                'error' => 'Missing required fields',
                'message' => 'Please provide both request ID and reason for rejection.'
            ];
        }
        
        try {
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);

            $query = LeaveRequest::query()->where('id', $requestId);

            // Admin override: Admins can reject any leave
            if (!$isAdmin) {
                $query->where('current_approver_id', $user->id);
            }

            $leaveRequest = $query->where('status', 'pending')->first();
                
            if (!$leaveRequest) {
                return [
                    'error' => 'Request not found',
                    'message' => "Leave request ID {$requestId} not found, already processed, or you don't have permission to reject it."
                ];
            }
            
            $leaveRequest->update([
                'status' => 'rejected',
                'approver_comment' => $reason,
                'updated_by' => $user->id
            ]);
            
            return [
                'success' => true,
                'message' => "Leave request ID {$requestId} has been rejected.",
                'request' => [
                    'id' => $leaveRequest->id,
                    'employee' => $leaveRequest->user->name ?? 'Unknown',
                    'from' => $leaveRequest->from_date,
                    'to' => $leaveRequest->to_date,
                    'days' => $leaveRequest->days,
                    'status' => 'rejected',
                    'reason' => $reason
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('RejectLeaveRequestTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'request_id' => $requestId
            ]);
            
            return [
                'error' => 'Failed to reject request',
                'message' => 'Unable to reject the leave request. Error: ' . $e->getMessage()
            ];
        }
    }
}
