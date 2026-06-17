<?php

namespace Modules\AgenticAI\Tools;

use Modules\Resignation\Entities\Resignation;
use Exception;

/**
 * SubmitResignationTool - Submit resignation request
 * Author: Sanket
 * 
 * Allows employees to submit resignation requests via AI
 */
class SubmitResignationTool extends BaseTool
{
    public function name(): string
    {
        return 'submit_resignation';
    }

    public function description(): string
    {
        return 'Submit a resignation request. Use when employee wants to resign or give notice.';
    }

    public function isSensitive(): bool
    {
        return true; // Important action, requires confirmation
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for resignation'
                ],
                'last_working_day' => [
                    'type' => 'string',
                    'description' => 'Proposed last working day (YYYY-MM-DD format, optional)'
                ],
                'comments' => [
                    'type' => 'string',
                    'description' => 'Additional comments (optional)'
                ],
            ],
            'required' => ['reason']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return ['error' => 'User not authenticated'];
            }

            // Check if user already has pending resignation
            $existing = Resignation::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return [
                    'error' => 'You already have a ' . $existing->status . ' resignation',
                    'resignation_id' => $existing->id,
                    'status' => $existing->status,
                    'submitted_on' => $existing->created_at->format('Y-m-d')
                ];
            }

            // Get notice period from user's contract or default
            $noticePeriodDays = $user->notice_period ?? 30;
            
            // Calculate expected last working day if not provided
            $lastWorkingDay = $args['last_working_day'] ?? 
                now()->addDays($noticePeriodDays)->format('Y-m-d');

            // Create resignation
            $resignation = Resignation::create([
                'user_id' => $user->id,
                'reason' => $args['reason'],
                'proposed_last_working_day' => $lastWorkingDay,
                'comments' => $args['comments'] ?? null,
                'status' => 'pending',
                'notice_period_days' => $noticePeriodDays,
                'submitted_at' => now()
            ]);

            return [
                'success' => true,
                'resignation_id' => $resignation->id,
                'notice_period_days' => $noticePeriodDays,
                'proposed_last_working_day' => $lastWorkingDay,
                'status' => 'pending',
                'message' => "Resignation submitted successfully. Notice period: {$noticePeriodDays} days. Proposed last working day: {$lastWorkingDay}"
            ];

        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'success' => false
            ];
        }
    }
}
