<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Task\Entities\Task;
use Illuminate\Support\Facades\Auth;
use Modules\AgenticAI\Tools\BaseTool;

class UpdateTaskTool extends BaseTool
{
    public function name(): string
    {
        return 'update_task';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Update the status of a task (e.g., mark as completed, in_progress).';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'task_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the task.'
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['pending', 'in_progress', 'completed', 'hold'],
                    'description' => 'The new status.'
                ],
                'comment' => [
                    'type' => 'string',
                    'description' => 'Optional comment/note to add with the update.'
                ]
            ],
            'required' => ['task_id', 'status'],
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $task = Task::findOrFail($args['task_id']);
            
            $task->update([
                'status' => $args['status'],
                'description' => $args['comment'] ? ($task->description . "\nUpdate: " . $args['comment']) : $task->description
            ]);

            return [
                'success' => true,
                'message' => "Task #{$args['task_id']} updated to {$args['status']}.",
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status
                ]
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to update task', 'message' => $e->getMessage()];
        }
    }
}
