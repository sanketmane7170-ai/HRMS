<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Leave\Entities\LeaveRequest;

class ApproveLeaveRequestTool extends BaseTool
{
    public function name(): string
    {
        return 'approve_leave_request';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Approve a pending leave request. Use when user wants to approve a leave request they are authorized to approve.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'request_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the leave request to approve'
                ],
                'comment' => [
                    'type' => 'string',
                    'description' => 'Optional comment or note for the approval'
                ]
            ],
            'required' => ['request_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $requestId = $args['request_id'] ?? null;
        $comment = $args['comment'] ?? '';
        
        if (!$requestId) {
            return [
                'error' => 'Missing request ID',
                'message' => 'Please provide the leave request ID to approve.'
            ];
        }
        
        try {
            $roles = $user->getRoleNames()->map(fn($r) => strtolower($r))->toArray();
            $isAdmin = in_array('admin', $roles);

            $query = LeaveRequest::query()->where('id', $requestId);

            // Admin override: Admins can approve any leave
            if (!$isAdmin) {
                $query->where('current_approver_id', $user->id);
            }

            $leaveRequest = $query->where('status', 'pending')->first();
                
            if (!$leaveRequest) {
                return [
                    'error' => 'Request not found',
                    'message' => "Leave request ID {$requestId} not found, already processed, or you don't have permission to approve it."
                ];
            }
            
            $leaveRequest->update([
                'status' => 'approved',
                'approver_comment' => $comment,
                'updated_by' => $user->id
            ]);
            
            return [
                'success' => true,
                'message' => "Leave request ID {$requestId} has been approved successfully.",
                'request' => [
                    'id' => $leaveRequest->id,
                    'employee' => $leaveRequest->user->name ?? 'Unknown',
                    'from' => $leaveRequest->from_date,
                    'to' => $leaveRequest->to_date,
                    'days' => $leaveRequest->days,
                    'status' => 'approved'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('ApproveLeaveRequestTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'request_id' => $requestId
            ]);
            
            return [
                'error' => 'Failed to approve request',
                'message' => 'Unable to approve the leave request. Error: ' . $e->getMessage()
            ];
        }
    }
}
