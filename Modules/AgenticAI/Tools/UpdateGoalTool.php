<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateGoalTool extends BaseTool
{
    public function name(): string { return 'update_goal'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Update a performance goal/KPI. Use when user wants to modify their goal.'; }
    
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'goal_id' => ['type' => 'integer', 'description' => 'Goal ID'],
                'title' => ['type' => 'string', 'description' => 'New title'],
                'description' => ['type' => 'string', 'description' => 'New description']
            ],
            'required' => ['goal_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['goal_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide goal ID.'];
        
        try {
            $goal = DB::table('key_performance_indicators')
                ->where('id', $id)
                ->where('created_by', $user->id)
                ->first();
                
            if (!$goal) return ['error' => 'Not found', 'message' => 'Goal not found or not yours.'];
            
            $updates = ['updated_by' => $user->id, 'updated_at' => now()];
            if (isset($args['title'])) $updates['title'] = $args['title'];
            if (isset($args['description'])) $updates['description'] = $args['description'];
            
            if (count($updates) <= 2) return ['error' => 'No updates', 'message' => 'Provide at least one field to update.'];
            
            DB::table('key_performance_indicators')->where('id', $id)->update($updates);
            
            return ['success' => true, 'message' => 'Goal updated successfully.', 'goal' => ['id' => $id, 'title' => $args['title'] ?? $goal->title]];
        } catch (\Exception $e) {
            \Log::error('UpdateGoalTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
