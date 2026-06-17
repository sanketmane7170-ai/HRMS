<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * ConfirmProbationTool - Confirm employee probation
 * Author: Sanket
 */
class ConfirmProbationTool extends BaseTool
{
    public function name(): string
    {
        return 'confirm_probation';
    }

    public function description(): string
    {
        return 'Confirm employee probation and generate confirmation letter. Use when HR wants to confirm an employee after probation.';
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
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Employee ID to confirm'
                ],
                'confirmation_date' => [
                    'type' => 'string',
                    'description' => 'Confirmation date in YYYY-MM-DD format (optional, defaults to today)'
                ],
                'comments' => [
                    'type' => 'string',
                    'description' => 'Confirmation comments (optional)'
                ],
            ],
            'required' => ['user_id']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can confirm probation
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to confirm probation'];
            }

            if (empty($args['user_id'])) {
                return ['error' => 'user_id is required'];
            }

            $user = User::find($args['user_id']);
            
            if (!$user) {
                return ['error' => "Employee with ID {$args['user_id']} not found"];
            }

            // Check if probation period exists
            if (!$user->probation_end_date) {
                return [
                    'error' => "Employee {$user->name} does not have a probation period defined",
                    'employee' => $user->name
                ];
            }

            $confirmationDate = $args['confirmation_date'] ?? now()->format('Y-m-d');

            // Create probation confirmation letter
            $letterId = \DB::table('probation_letters')->insertGetId([
                'user_id' => $user->id,
                'type' => 'confirmation',
                'confirmation_date' => $confirmationDate,
                'comments' => $args['comments'] ?? 'Probation successfully completed',
                'issued_by' => $currentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update user status if needed
            $user->update([
                'probation_confirmed' => true,
                'probation_confirmation_date' => $confirmationDate
            ]);

            return [
                'success' => true,
                'employee' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'confirmation' => [
                    'letter_id' => $letterId,
                    'confirmation_date' => $confirmationDate,
                    'probation_start' => $user->probation_start_date ?? $user->joining_date,
                    'probation_end' => $user->probation_end_date
                ],
                'message' => "Probation confirmed for {$user->name}. Confirmation letter generated (ID: {$letterId})"
            ];

        } catch (Exception $e) {
            \Log::error('ConfirmProbationTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to confirm probation. Please try again.', 'success' => false];
        }
    }
}
