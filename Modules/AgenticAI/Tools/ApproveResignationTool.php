<?php

namespace Modules\AgenticAI\Tools;

use Modules\Resignation\Entities\Resignation;
use Exception;

/**
 * ApproveResignationTool - Approve or reject resignation
 * Author: Sanket
 */
class ApproveResignationTool extends BaseTool
{
    public function name(): string
    {
        return 'approve_resignation';
    }

    public function description(): string
    {
        return 'Approve or reject an employee resignation request. Use when manager needs to process resignation.';
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
                'resignation_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the resignation to approve/reject'
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['approve', 'reject'],
                    'description' => 'Action to take: approve or reject'
                ],
                'approved_last_working_day' => [
                    'type' => 'string',
                    'description' => 'Approved last working day in YYYY-MM-DD format (required for approve)'
                ],
                'comments' => [
                    'type' => 'string',
                    'description' => 'Manager comments (optional)'
                ],
            ],
            'required' => ['resignation_id', 'action']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can approve resignations
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required to process resignations'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to process resignations'];
            }

            if (empty($args['resignation_id']) || empty($args['action'])) {
                return ['error' => 'resignation_id and action are required'];
            }

            $resignation = Resignation::find($args['resignation_id']);
            
            if (!$resignation) {
                return ['error' => "Resignation with ID {$args['resignation_id']} not found"];
            }

            if ($resignation->status !== 'pending') {
                return [
                    'error' => "Cannot process resignation - current status is '{$resignation->status}'",
                    'current_status' => $resignation->status
                ];
            }

            if ($args['action'] === 'approve') {
                if (empty($args['approved_last_working_day'])) {
                    return ['error' => 'approved_last_working_day is required for approval'];
                }

                $resignation->update([
                    'status' => 'approved',
                    'approved_last_working_day' => $args['approved_last_working_day'],
                    'manager_comments' => $args['comments'] ?? null,
                    'approved_by' => $currentUser->id,
                    'approved_at' => now()
                ]);

                return [
                    'success' => true,
                    'action' => 'approved',
                    'employee' => $resignation->user->name ?? 'Unknown',
                    'last_working_day' => $args['approved_last_working_day'],
                    'message' => "Resignation approved. Last working day: {$args['approved_last_working_day']}"
                ];

            } else { // reject
                $resignation->update([
                    'status' => 'rejected',
                    'manager_comments' => $args['comments'] ?? 'Resignation rejected',
                    'approved_by' => $currentUser->id,
                    'approved_at' => now()
                ]);

                return [
                    'success' => true,
                    'action' => 'rejected',
                    'employee' => $resignation->user->name ?? 'Unknown',
                    'message' => "Resignation rejected"
                ];
            }

        } catch (Exception $e) {
            \Log::error('ApproveResignationTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to process resignation. Please try again.', 'success' => false];
        }
    }
}
