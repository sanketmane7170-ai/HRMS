<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * GetProbationStatusTool - Check probation period status
 * Author: Sanket
 * 
 * Allows employees to check their probation period details
 */
class GetProbationStatusTool extends BaseTool
{
    public function name(): string
    {
        return 'get_probation_status';
    }

    public function description(): string
    {
        return 'Get probation period status and details. Use when employee asks about probation, confirmation, or probation period.';
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

            // Check if user has probation period defined
            if (!$user->probation_end_date) {
                return [
                    'is_on_probation' => false,
                    'message' => 'You are not on probation or probation period is not defined.'
                ];
            }

            $probationStart = \Carbon\Carbon::parse($user->probation_start_date ?? $user->joining_date);
            $probationEnd = \Carbon\Carbon::parse($user->probation_end_date);
            $today = now();

            $isOnProbation = $today->lte($probationEnd);
            $daysRemaining = $isOnProbation ? $today->diffInDays($probationEnd) : 0;
            $totalDays = $probationStart->diffInDays($probationEnd);
            $daysCompleted = $probationStart->diffInDays($today);

            // Check if confirmation letter exists
            $confirmationLetterSent = \DB::table('probation_letters')
                ->where('user_id', $user->id)
                ->where('type', 'confirmation')
                ->exists();

            return [
                'is_on_probation' => $isOnProbation,
                'probation_details' => [
                    'start_date' => $probationStart->format('Y-m-d'),
                    'end_date' => $probationEnd->format('Y-m-d'),
                    'total_days' => $totalDays,
                    'days_completed' => $daysCompleted,
                    'days_remaining' => $daysRemaining,
                    'percentage_completed' => round(($daysCompleted / $totalDays) * 100, 1),
                    'status' => $isOnProbation ? 'active' : 'completed',
                    'confirmation_letter_sent' => $confirmationLetterSent
                ],
                'message' => $this->getStatusMessage($isOnProbation, $daysRemaining, $probationEnd, $confirmationLetterSent)
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
    private function getStatusMessage(bool $isOnProbation, int $daysRemaining, $probationEnd, bool $letterSent): string
    {
        if ($isOnProbation) {
            return "You are currently on probation. {$daysRemaining} days remaining until {$probationEnd->format('M d, Y')}.";
        } else {
            if ($letterSent) {
                return "Your probation period ended on {$probationEnd->format('M d, Y')}. Confirmation letter has been issued.";
            } else {
                return "Your probation period ended on {$probationEnd->format('M d, Y')}. Awaiting confirmation letter from HR.";
            }
        }
    }
}
