<?php

namespace Modules\AgenticAI\Tools;

use App\Models\ShiftSchedule;
use Exception;

/**
 * CreateShiftScheduleTool - Create new shift schedule templates
 * Author: Sanket
 * 
 * Allows creating custom shift timings (e.g., 2AM-8PM) that can be assigned to employees
 */
class CreateShiftScheduleTool extends BaseTool
{
    public function name(): string
    {
        return 'create_shift_schedule';
    }

    public function description(): string
    {
        return 'Create a new shift schedule template with custom timings. Use when user wants to create a new shift timing that doesn\'t exist yet.';
    }

    public function isSensitive(): bool
    {
        return true; // Requires admin/HR approval
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Shift name (e.g., "Night Shift", "Custom 2AM-8PM")'
                ],
                'shift_start' => [
                    'type' => 'string',
                    'description' => 'Start time in HH:MM:SS format (e.g., "02:00:00")'
                ],
                'shift_end' => [
                    'type' => 'string',
                    'description' => 'End time in HH:MM:SS format (e.g., "20:00:00")'
                ],
                'shift_type' => [
                    'type' => 'integer',
                    'description' => 'Shift type: 1=Single, 2=Rotating, 3=Flexible',
                    'enum' => [1, 2, 3]
                ],
                'break_duration' => [
                    'type' => 'integer',
                    'description' => 'Break duration in minutes (optional, default 60)'
                ],
            ],
            'required' => ['title', 'shift_start', 'shift_end']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can create shift schedules
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to create shift schedules'];
            }

            // Validation
            if (empty($args['title']) || empty($args['shift_start']) || empty($args['shift_end'])) {
                return ['error' => 'title, shift_start, and shift_end are required'];
            }

            // Map shift_type (int) to DB enum 'type' ('SS', 'MS')
            $typeInt = $args['shift_type'] ?? 1;
            $dbType = ($typeInt == 2) ? 'MS' : 'SS';

            // Validate time format
            if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $args['shift_start']) || 
                !preg_match('/^\d{2}:\d{2}:\d{2}$/', $args['shift_end'])) {
                return ['error' => 'Time must be in HH:MM:SS format (e.g., "02:00:00")'];
            }

            // Check if shift with same name already exists
            $existing = \App\Models\ShiftSchedule::where('title', $args['title'])->first();
            if ($existing) {
                return [
                    'error' => "Shift schedule '{$args['title']}' already exists",
                    'existing_schedule' => [
                        'id' => $existing->id,
                        'title' => $existing->title,
                        'start' => $existing->shift_start,
                        'end' => $existing->shift_end
                    ]
                ];
            }

            // 1. Create entry in shifts table (required for shift_id)
            $shift = \App\Models\Shifts::create([
                'title' => $args['title'],
                'shift_start' => $args['shift_start'],
                'shift_end' => $args['shift_end'],
                'type' => $dbType,
                'created_by' => $currentUser->id,
                'department_id' => 0 // Default
            ]);

            // 2. Create shift schedule
            $schedule = \App\Models\ShiftSchedule::create([
                'title' => $args['title'],
                'shift_start' => $args['shift_start'],
                'shift_end' => $args['shift_end'],
                'type' => $dbType,
                'shift_id' => $shift->id,
                'created_by' => $currentUser->id
            ]);

            return [
                'success' => true,
                'schedule_id' => $schedule->id,
                'schedule' => [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'start' => $schedule->shift_start,
                    'end' => $schedule->shift_end,
                    'type' => $this->getShiftTypeName($typeInt),
                    'db_type' => $dbType
                ],
                'message' => "Shift schedule '{$schedule->title}' created successfully ({$schedule->shift_start} - {$schedule->shift_end})"
            ];

        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'success' => false
            ];
        }
    }

    /**
     * Get human-readable shift type name
     * Author: Sanket
     */
    private function getShiftTypeName(?int $type): string
    {
        if ($type === null) return 'Unknown';
        
        return match($type) {
            1 => 'Single',
            2 => 'Rotating',
            3 => 'Flexible',
            default => 'Unknown'
        };
    }
}
