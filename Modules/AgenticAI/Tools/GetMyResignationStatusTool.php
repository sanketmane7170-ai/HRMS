<?php

namespace Modules\AgenticAI\Tools;

use Modules\Resignation\Entities\Resignation;
use Exception;

/**
 * GetMyResignationStatusTool - Check resignation status
 * Author: Sanket
 * 
 * Allows employees to check their resignation status and details
 */
class GetMyResignationStatusTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_resignation_status';
    }

    public function description(): string
    {
        return 'Get resignation status and details. Use when employee asks about their resignation, notice period, or last working day.';
    }

    public function isSensitive(): bool
    {
        return false;
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
        try {
            $user = auth()->user();
            
            if (!$user) {
                return ['error' => 'User not authenticated'];
            }

            // Get latest resignation
            $resignation = Resignation::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$resignation) {
                return [
                    'has_resignation' => false,
                    'message' => 'No resignation found. You have not submitted any resignation request.'
                ];
            }

            // Calculate days remaining if approved
            $daysRemaining = null;
            if ($resignation->status === 'approved' && $resignation->approved_last_working_day) {
                $lastDay = \Carbon\Carbon::parse($resignation->approved_last_working_day);
                $daysRemaining = now()->diffInDays($lastDay, false);
            }

            return [
                'has_resignation' => true,
                'resignation' => [
                    'id' => $resignation->id,
                    'status' => $resignation->status,
                    'reason' => $resignation->reason,
                    'submitted_on' => $resignation->created_at->format('Y-m-d'),
                    'notice_period_days' => $resignation->notice_period_days,
                    'proposed_last_working_day' => $resignation->proposed_last_working_day,
                    'approved_last_working_day' => $resignation->approved_last_working_day,
                    'days_remaining' => $daysRemaining,
                    'comments' => $resignation->comments,
                    'manager_comments' => $resignation->manager_comments ?? null
                ],
                'message' => $this->getStatusMessage($resignation, $daysRemaining)
            ];

        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'success' => false
            ];
        }
    }

    /**
     * Get human-readable status message
     * Author: Sanket
     */
    private function getStatusMessage($resignation, $daysRemaining): string
    {
        switch ($resignation->status) {
            case 'pending':
                return "Your resignation is pending approval. Proposed last working day: {$resignation->proposed_last_working_day}";
            case 'approved':
                $lastDay = $resignation->approved_last_working_day;
                $days = $daysRemaining > 0 ? "{$daysRemaining} days remaining" : "last working day reached";
                return "Your resignation is approved. Last working day: {$lastDay} ({$days})";
            case 'rejected':
                return "Your resignation was rejected. Reason: " . ($resignation->manager_comments ?? 'No reason provided');
            case 'withdrawn':
                return "Your resignation was withdrawn on " . $resignation->updated_at->format('Y-m-d');
            default:
                return "Resignation status: {$resignation->status}";
        }
    }
}
