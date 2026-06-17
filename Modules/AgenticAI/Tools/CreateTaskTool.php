<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Task\Entities\Task;

class CreateTaskTool extends BaseTool
{
    public function name(): string
    {
        return 'create_task';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Create a new task. Use when user wants to create a task for themselves or assign to someone.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Task title/name'
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Task description'
                ],
                'due_date' => [
                    'type' => 'string',
                    'description' => 'Due date (YYYY-MM-DD format)'
                ],
                'assign_to' => [
                    'type' => 'integer',
                    'description' => 'User ID to assign task to (optional, defaults to self)'
                ]
            ],
            'required' => ['title']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $title = $args['title'] ?? '';
        
        if (empty($title)) {
            return ['error' => 'Missing title', 'message' => 'Please provide a task title.'];
        }
        
        try {
            $task = Task::create([
                'title' => $title,
                'description' => $args['description'] ?? '',
                'end_date' => $args['due_date'] ?? null, // Model uses end_date
                'assigned_to' => $args['assign_to'] ?? $user->id,
                'assigned_by' => $user->id,
                'status' => 'pending'
            ]);
            
            return [
                'success' => true,
                'message' => "Task created successfully. Task ID: {$task->id}",
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'due_date' => $task->end_date,
                    'status' => 'pending'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('CreateTaskTool failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return ['error' => 'Failed to create task', 'message' => $e->getMessage()];
        }
    }
}
