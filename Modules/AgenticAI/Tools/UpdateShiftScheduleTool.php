<?php

namespace Modules\AgenticAI\Tools;

use App\Models\ShiftSchedule;
use Exception;

/**
 * UpdateShiftScheduleTool - Update existing shift schedule
 * Author: Sanket
 */
class UpdateShiftScheduleTool extends BaseTool
{
    public function name(): string
    {
        return 'update_shift_schedule';
    }

    public function description(): string
    {
        return 'Update an existing shift schedule. Use when user wants to modify shift timings or details.';
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
                    'description' => 'ID of the shift schedule to update'
                ],
                'title' => [
                    'type' => 'string',
                    'description' => 'New shift name (optional)'
                ],
                'shift_start' => [
                    'type' => 'string',
                    'description' => 'New start time in HH:MM:SS format (optional)'
                ],
                'shift_end' => [
                    'type' => 'string',
                    'description' => 'New end time in HH:MM:SS format (optional)'
                ],
                'shift_type' => [
                    'type' => 'integer',
                    'description' => 'New shift type: 1=Single, 2=Rotating, 3=Flexible (optional)'
                ],
                'break_duration' => [
                    'type' => 'integer',
                    'description' => 'New break duration in minutes (optional)'
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

            // Validate time format if provided
            if (!empty($args['shift_start']) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $args['shift_start'])) {
                return ['error' => 'shift_start must be in HH:MM:SS format'];
            }
            if (!empty($args['shift_end']) && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $args['shift_end'])) {
                return ['error' => 'shift_end must be in HH:MM:SS format'];
            }

            // Update only provided fields
            $updateData = [];
            $shiftUpdateData = [];
            
            if (!empty($args['title'])) {
                $updateData['title'] = $args['title'];
                $shiftUpdateData['title'] = $args['title'];
            }
            if (!empty($args['shift_start'])) {
                $updateData['shift_start'] = $args['shift_start'];
                $shiftUpdateData['shift_start'] = $args['shift_start'];
            }
            if (!empty($args['shift_end'])) {
                $updateData['shift_end'] = $args['shift_end'];
                $shiftUpdateData['shift_end'] = $args['shift_end'];
            }
            if (isset($args['shift_type'])) {
                $dbType = ($args['shift_type'] == 2) ? 'MS' : 'SS';
                $updateData['type'] = $dbType;
                $shiftUpdateData['type'] = $dbType;
            }

            if (empty($updateData)) {
                return ['error' => 'No fields to update provided'];
            }

            // 1. Update ShiftSchedule
            $schedule->update($updateData);

            // 2. Sync with base Shift model if exists
            if ($schedule->shift_id) {
                \App\Models\Shifts::where('id', $schedule->shift_id)->update($shiftUpdateData);
            }

            return [
                'success' => true,
                'schedule' => [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'start' => $schedule->shift_start,
                    'end' => $schedule->shift_end,
                    'type' => $schedule->type == 'SS' ? 'Single' : 'Rotating/Multiple'
                ],
                'message' => "Shift schedule '{$schedule->title}' updated successfully"
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage(), 'success' => false];
        }
    }
}
