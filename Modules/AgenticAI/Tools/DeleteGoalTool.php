<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteGoalTool extends BaseTool
{
    public function name(): string { return 'delete_goal'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Delete a performance goal/KPI. Use when user wants to remove a goal.'; }
    
    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['goal_id' => ['type' => 'integer', 'description' => 'Goal ID']], 'required' => ['goal_id']];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['goal_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide goal ID.'];
        
        try {
            $deleted = DB::table('key_performance_indicators')
                ->where('id', $id)
                ->where('created_by', $user->id)
                ->delete();
                
            if (!$deleted) return ['error' => 'Not found', 'message' => 'Goal not found or not yours.'];
            
            return ['success' => true, 'message' => 'Goal deleted successfully.'];
        } catch (\Exception $e) {
            \Log::error('DeleteGoalTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
