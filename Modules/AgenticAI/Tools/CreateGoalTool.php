<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateGoalTool extends BaseTool
{
    public function name(): string
    {
        return 'create_goal';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Create a performance goal/KPI. Use when user wants to set a new goal or objective.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'description' => 'Goal title/objective'],
                'description' => ['type' => 'string', 'description' => 'Goal description and details']
            ],
            'required' => ['title']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (empty($args['title'])) return ['error' => 'Missing title', 'message' => 'Provide goal title.'];
        
        try {
            // Get default duration (quarterly, annual, etc.)
            $duration = DB::table('review_durations')->first();
            if (!$duration) {
                return ['error' => 'No duration', 'message' => 'Review durations not configured. Contact admin.'];
            }
            
            $goalId = DB::table('key_performance_indicators')->insertGetId([
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'duration_id' => $duration->id,
                'title' => $args['title'],
                'description' => $args['description'] ?? '',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => "Goal created successfully. ID: {$goalId}",
                'goal' => [
                    'id' => $goalId,
                    'title' => $args['title'],
                    'duration' => $duration->title ?? 'Default'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CreateGoalTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
