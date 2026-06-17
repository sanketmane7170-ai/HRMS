<?php

namespace Modules\AgenticAI\Tools;

use Exception;

/**
 * ApproveOvertimeTool - Approve overtime requests
 * Author: Sanket
 */
class ApproveOvertimeTool extends BaseTool
{
    public function name(): string
    {
        return 'approve_overtime';
    }

    public function description(): string
    {
        return 'Approve or reject overtime requests. Use when manager needs to process overtime.';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'overtime_id' => [
                    'type' => 'integer',
                    'description' => 'Overtime request ID'
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['approve', 'reject'],
                    'description' => 'Action to take'
                ],
                'comments' => [
                    'type' => 'string',
                    'description' => 'Comments (optional)'
                ],
            ],
            'required' => ['overtime_id', 'action']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can approve overtime
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required to process overtime requests'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to process overtime requests'];
            }

            $overtime = \DB::table('overtime_requests')->find($args['overtime_id']);
            
            if (!$overtime) {
                return ['error' => "Overtime request with ID {$args['overtime_id']} not found"];
            }

            $status = $args['action'] === 'approve' ? 'approved' : 'rejected';
            
            \DB::table('overtime_requests')
                ->where('id', $args['overtime_id'])
                ->update([
                    'status' => $status,
                    'manager_comments' => $args['comments'] ?? null,
                    'approved_by' => $currentUser->id,
                    'approved_at' => now(),
                    'updated_at' => now()
                ]);

            $user = \App\Models\User::find($overtime->user_id);

            return [
                'success' => true,
                'overtime_id' => $overtime->id,
                'employee' => $user->name ?? 'Unknown',
                'hours' => $overtime->hours,
                'status' => $status,
                'message' => "Overtime request {$status} for {$user->name}: {$overtime->hours} hours"
            ];

        } catch (Exception $e) {
            \Log::error('ApproveOvertimeTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to process overtime request. Please try again.', 'success' => false];
        }
    }
}
