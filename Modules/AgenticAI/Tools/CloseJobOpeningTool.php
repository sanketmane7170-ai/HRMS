<?php

namespace Modules\AgenticAI\Tools;

use Modules\Recruitment\Entities\JobOpening;
use Exception;

/**
 * CloseJobOpeningTool - Close job opening
 * Author: Sanket
 */
class CloseJobOpeningTool extends BaseTool
{
    public function name(): string
    {
        return 'close_job_opening';
    }

    public function description(): string
    {
        return 'Close a job opening. Use when position is filled or no longer needed.';
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
                'job_id' => [
                    'type' => 'integer',
                    'description' => 'Job opening ID to close'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for closing (optional)'
                ],
            ],
            'required' => ['job_id']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can close job openings
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to close job openings'];
            }

            $job = JobOpening::find($args['job_id']);
            
            if (!$job) {
                return ['error' => "Job opening with ID {$args['job_id']} not found"];
            }

            if ($job->status === 'closed') {
                return [
                    'error' => "Job opening '{$job->title}' is already closed",
                    'job_title' => $job->title
                ];
            }

            $job->update([
                'status' => 'closed',
                'closed_reason' => $args['reason'] ?? 'Position filled',
                'closed_by' => $currentUser->id,
                'closed_at' => now()
            ]);

            return [
                'success' => true,
                'job_id' => $job->id,
                'title' => $job->title,
                'message' => "Job opening '{$job->title}' closed successfully"
            ];

        } catch (Exception $e) {
            \Log::error('CloseJobOpeningTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to close job opening. Please try again.', 'success' => false];
        }
    }
}
