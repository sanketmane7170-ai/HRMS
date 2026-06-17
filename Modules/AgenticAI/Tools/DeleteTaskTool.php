<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Task\Entities\Task;

class DeleteTaskTool extends BaseTool
{
    public function name(): string
    {
        return 'delete_task';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Delete a task. Use when user wants to remove a task they created.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the task to delete'
                ]
            ],
            'required' => ['task_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $taskId = $args['task_id'] ?? null;
        
        if (!$taskId) {
            return ['error' => 'Missing task ID', 'message' => 'Please provide the task ID to delete.'];
        }
        
        try {
            $task = Task::query()
                ->where('id', $taskId)
                ->where(function($q) use ($user) {
                    $q->where('assigned_by', $user->id)
                      ->orWhere('assigned_to', $user->id);
                })
                ->first();
                
            if (!$task) {
                return ['error' => 'Task not found', 'message' => "Task ID {$taskId} not found or you don't have permission to delete it."];
            }
            
            $task->delete();
            
            return [
                'success' => true,
                'message' => "Task ID {$taskId} deleted successfully."
            ];
        } catch (\Exception $e) {
            \Log::error('DeleteTaskTool failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return ['error' => 'Failed to delete task', 'message' => $e->getMessage()];
        }
    }
}
