<?php

namespace Modules\AgenticAI\Tools;

use Modules\Recruitment\Entities\Application;
use Exception;

/**
 * RejectCandidateTool - Reject candidate application
 * Author: Sanket
 */
class RejectCandidateTool extends BaseTool
{
    public function name(): string
    {
        return 'reject_candidate';
    }

    public function description(): string
    {
        return 'Reject a candidate application. Use when HR wants to reject a candidate.';
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
                'application_id' => [
                    'type' => 'integer',
                    'description' => 'Application ID to reject'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Rejection reason'
                ],
            ],
            'required' => ['application_id', 'reason']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can reject candidates
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to reject candidates'];
            }

            if (empty($args['application_id']) || empty($args['reason'])) {
                return ['error' => 'application_id and reason are required'];
            }

            $application = Application::find($args['application_id']);
            
            if (!$application) {
                return ['error' => "Application with ID {$args['application_id']} not found"];
            }

            $application->update([
                'status' => 'rejected',
                'rejection_reason' => $args['reason'],
                'rejected_by' => $currentUser->id,
                'rejected_at' => now()
            ]);

            return [
                'success' => true,
                'application' => [
                    'id' => $application->id,
                    'candidate' => $application->name,
                    'status' => 'rejected'
                ],
                'message' => "Candidate {$application->name} rejected. Reason: {$args['reason']}"
            ];

        } catch (Exception $e) {
            \Log::error('RejectCandidateTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to reject candidate. Please try again.', 'success' => false];
        }
    }
}
