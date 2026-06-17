<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Task\Entities\Task;
use Illuminate\Support\Facades\Auth;

class GetMyTasksTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_tasks';
    }

    public function description(): string
    {
        return 'Get a list of tasks assigned to me.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'description' => 'Filter by status (e.g., "pending", "in_progress", "completed"). Optional.'
                ]
            ],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        try {
            $query = Task::where('assigned_to', $user->id);
            
            if (isset($args['status'])) {
                $query->where('status', $args['status']);
            }

            $tasks = $query->orderBy('created_at', 'desc')->get();

            return [
                'tasks' => $tasks->map(function($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'start_date' => $task->start_date,
                        'end_date' => $task->end_date
                    ];
                }),
                'count' => $tasks->count()
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to fetch tasks',
                'message' => $e->getMessage()
            ];
        }
    }
}
