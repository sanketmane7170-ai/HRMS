<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Task\Entities\Task;

class AssignTaskTool extends BaseTool
{
    public function name(): string
    {
        return 'assign_task';
    }

    public function description(): string
    {
        return 'Assign or reassign a task to someone. Use when user wants to delegate a task.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => ['type' => 'integer', 'description' => 'ID of task to assign'],
                'assign_to' => ['type' => 'integer', 'description' => 'User ID to assign task to']
            ],
            'required' => ['task_id', 'assign_to']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        if (empty($args['task_id']) || empty($args['assign_to'])) {
            return ['error' => 'Missing fields', 'message' => 'Provide task_id and assign_to.'];
        }
        
        try {
            $task = Task::where('id', $args['task_id'])
                ->where('created_by', $user->id)
                ->first();
            
            if (!$task) return ['error' => 'Not found', 'message' => 'Task not found or no permission.'];
            
            $task->update(['user_id' => $args['assign_to']]);
            
            return [
                'success' => true,
                'message' => "Task {$args['task_id']} assigned successfully.",
                'task' => ['id' => $task->id, 'title' => $task->title, 'assigned_to' => $args['assign_to']]
            ];
        } catch (\Exception $e) {
            \Log::error('AssignTaskTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
