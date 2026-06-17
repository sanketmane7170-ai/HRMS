<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * GetMyIncrementHistoryTool - View salary increment history
 * Author: Sanket
 */
class GetMyIncrementHistoryTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_increment_history';
    }

    public function description(): string
    {
        return 'Get employee salary increment history. Use when employee asks about their salary increments or raises.';
    }

    public function isSensitive(): bool
    {
        return false;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'Employee ID (optional, defaults to current user)'
                ],
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $userId = $args['user_id'] ?? auth()->id();
            
            if (!$userId) {
                return ['error' => 'User not authenticated'];
            }

            $user = User::find($userId);
            if (!$user) {
                return ['error' => "Employee with ID {$userId} not found"];
            }

            // Get increment history
            $increments = \DB::table('user_increments')
                ->where('user_id', $userId)
                ->orderBy('effective_date', 'desc')
                ->get();

            if ($increments->isEmpty()) {
                return [
                    'has_increments' => false,
                    'employee' => $user->name,
                    'current_salary' => $user->salary ?? 0,
                    'message' => 'No increment history found'
                ];
            }

            $incrementList = $increments->map(function($inc) {
                return [
                    'id' => $inc->id,
                    'previous_salary' => $inc->previous_salary,
                    'new_salary' => $inc->new_salary,
                    'increment_amount' => $inc->increment_amount,
                    'increment_percentage' => round(($inc->increment_amount / $inc->previous_salary) * 100, 2),
                    'effective_date' => $inc->effective_date,
                    'reason' => $inc->reason ?? 'N/A'
                ];
            });

            $totalIncrease = $increments->sum('increment_amount');

            return [
                'has_increments' => true,
                'employee' => $user->name,
                'current_salary' => $user->salary ?? 0,
                'total_increments' => $increments->count(),
                'total_increase' => $totalIncrease,
                'increments' => $incrementList->toArray(),
                'message' => "Found {$increments->count()} increment(s) for {$user->name}. Total increase: {$totalIncrease}"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
