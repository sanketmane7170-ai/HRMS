<?php

namespace Modules\AgenticAI\Tools;

use Modules\Resignation\Entities\Resignation;
use Exception;

/**
 * WithdrawResignationTool - Withdraw resignation request
 * Author: Sanket
 */
class WithdrawResignationTool extends BaseTool
{
    public function name(): string
    {
        return 'withdraw_resignation';
    }

    public function description(): string
    {
        return 'Withdraw a pending resignation request. Use when employee wants to cancel their resignation.';
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
                    'description' => 'Resignation ID to withdraw (optional, uses latest if not provided)'
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for withdrawal (optional)'
                ],
            ],
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

            // Get resignation
            $resignation = !empty($args['resignation_id'])
                ? Resignation::where('user_id', $user->id)->find($args['resignation_id'])
                : Resignation::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->orderBy('created_at', 'desc')
                    ->first();

            if (!$resignation) {
                return [
                    'error' => 'No active resignation found to withdraw',
                    'has_resignation' => false
                ];
            }

            if (!in_array($resignation->status, ['pending', 'approved'])) {
                return [
                    'error' => "Cannot withdraw resignation with status '{$resignation->status}'",
                    'current_status' => $resignation->status
                ];
            }

            $resignation->update([
                'status' => 'withdrawn',
                'withdrawal_reason' => $args['reason'] ?? 'Employee decided to stay',
                'withdrawn_at' => now()
            ]);

            return [
                'success' => true,
                'resignation_id' => $resignation->id,
                'previous_status' => $resignation->status,
                'message' => "Resignation withdrawn successfully. You are no longer in the resignation process."
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
