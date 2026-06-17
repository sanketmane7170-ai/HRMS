<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use Exception;

/**
 * GetMyPromotionHistoryTool - View promotion history
 * Author: Sanket
 */
class GetMyPromotionHistoryTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_promotion_history';
    }

    public function description(): string
    {
        return 'Get employee promotion history. Use when employee asks about their promotions or career progression.';
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

            // Get promotion history
            $promotions = \DB::table('user_promotions')
                ->where('user_id', $userId)
                ->orderBy('effective_date', 'desc')
                ->get();

            if ($promotions->isEmpty()) {
                return [
                    'has_promotions' => false,
                    'employee' => $user->name,
                    'current_designation' => $user->designation->name ?? 'N/A',
                    'message' => 'No promotion history found'
                ];
            }

            $promotionList = $promotions->map(function($p) {
                $oldDesignation = \DB::table('designations')->find($p->old_designation_id);
                $newDesignation = \DB::table('designations')->find($p->new_designation_id);
                
                return [
                    'id' => $p->id,
                    'from' => $oldDesignation->name ?? 'Unknown',
                    'to' => $newDesignation->name ?? 'Unknown',
                    'effective_date' => $p->effective_date,
                    'reason' => $p->reason ?? 'N/A',
                    'salary_increase' => $p->salary_increase ?? 0
                ];
            });

            return [
                'has_promotions' => true,
                'employee' => $user->name,
                'current_designation' => $user->designation->name ?? 'N/A',
                'total_promotions' => $promotions->count(),
                'promotions' => $promotionList->toArray(),
                'message' => "Found {$promotions->count()} promotion(s) for {$user->name}"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
