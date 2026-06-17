<?php

namespace Modules\AgenticAI\Tools;

use App\Models\ShiftSchedule;
use Modules\Shift\Entities\UsersShift;
use Exception;

/**
 * DeleteShiftScheduleTool - Delete shift schedule
 * Author: Sanket
 */
class DeleteShiftScheduleTool extends BaseTool
{
    public function name(): string
    {
        return 'delete_shift_schedule';
    }

    public function description(): string
    {
        return 'Delete a shift schedule. Use when user wants to remove an obsolete shift timing.';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'schedule_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the shift schedule to delete'
                ],
                'force' => [
                    'type' => 'boolean',
                    'description' => 'Force delete even if assigned to employees (optional, default false)'
                ],
            ],
            'required' => ['schedule_id']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            if (empty($args['schedule_id'])) {
                return ['error' => 'schedule_id is required'];
            }

            $schedule = ShiftSchedule::find($args['schedule_id']);
            
            if (!$schedule) {
                return ['error' => "Shift schedule with ID {$args['schedule_id']} not found"];
            }

            // Check if shift is assigned to any employees
            $assignedCount = UsersShift::where('schedule_id', $schedule->id)->count();
            
            if ($assignedCount > 0 && empty($args['force'])) {
                return [
                    'error' => "Cannot delete shift schedule '{$schedule->title}' - it is assigned to {$assignedCount} employee(s). Use force=true to delete anyway.",
                    'assigned_employees' => $assignedCount,
                    'schedule_title' => $schedule->title
                ];
            }

            $title = $schedule->title;
            
            // Delete shift schedule
            $schedule->delete();

            return [
                'success' => true,
                'message' => "Shift schedule '{$title}' deleted successfully" . 
                    ($assignedCount > 0 ? " (was assigned to {$assignedCount} employees)" : "")
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
