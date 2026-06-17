<?php

namespace Modules\AgenticAI\Tools;

use Modules\Shift\Entities\UsersShift;
use App\Models\ShiftSchedule;
use App\Models\User;
use Exception;

class ManageShiftRosterTool extends BaseTool
{
    public function name(): string
    {
        return 'manage_shift_roster';
    }

    public function description(): string
    {
        return 'List available shift schedules or assign shifts to employees for specific dates.';
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
                'action' => [
                    'type' => 'string',
                    'enum' => ['list_schedules', 'assign_shift', 'list_assignments'],
                    'description' => 'Action to perform.'
                ],
                'user_id' => ['type' => 'integer', 'description' => 'Required for assign_shift/list_assignments.'],
                'schedule_id' => ['type' => 'integer', 'description' => 'Required for assign_shift.'],
                'date' => ['type' => 'string', 'description' => 'Format: YYYY-MM-DD. Required for assign_shift/list_assignments.'],
                'end_date' => ['type' => 'string', 'description' => 'Optional for bulk assignment range.'],
            ],
            'required' => ['action']
        ];
    }

    public function execute(array $args): mixed
    {
        $action = $args['action'];

        try {
            switch ($action) {
                case 'list_schedules':
                    $schedules = ShiftSchedule::all();
                    return [
                        'schedules' => $schedules->map(function($s) {
                            return [
                                'id' => $s->id,
                                'title' => $s->title,
                                'start' => $s->shift_start,
                                'end' => $s->shift_end,
                                'type' => $s->formatted_shift_type
                            ];
                        })->toArray()
                    ];

                case 'assign_shift':
                    if (empty($args['user_id']) || empty($args['schedule_id']) || empty($args['date'])) {
                        throw new Exception('user_id, schedule_id, and date are required for assign_shift.');
                    }
                    
                    $userId = $args['user_id'];
                    $scheduleId = $args['schedule_id'];
                    $date = $args['date'];
                    
                    // Check if already assigned
                    $existing = UsersShift::where('user_id', $userId)->where('assigned_for_date', $date)->first();
                    if ($existing) {
                        $existing->update(['schedule_id' => $scheduleId]);
                        $message = "Shift updated for user ID {$userId} on {$date}.";
                    } else {
                        UsersShift::create([
                            'user_id' => $userId,
                            'schedule_id' => $scheduleId,
                            'assigned_for_date' => $date,
                            'assigned_by_id' => auth()->id()
                        ]);
                        $message = "Shift assigned to user ID {$userId} on {$date}.";
                    }
                    
                    return ['success' => true, 'message' => $message];

                case 'list_assignments':
                    if (empty($args['user_id'])) {
                         throw new Exception('user_id is required for list_assignments.');
                    }
                    $assignments = UsersShift::with('shift_schedule_information')
                        ->where('user_id', $args['user_id'])
                        ->when(!empty($args['date']), function($q) use ($args) {
                            $q->where('assigned_for_date', $args['date']);
                        })
                        ->orderBy('assigned_for_date', 'desc')
                        ->limit(10)
                        ->get();
                        
                    return [
                        'assignments' => $assignments->map(function($a) {
                            return [
                                'date' => $a->assigned_for_date,
                                'shift' => $a->shift_schedule_information->title ?? 'Unknown',
                                'time' => ($a->shift_schedule_information->shift_start ?? '') . ' - ' . ($a->shift_schedule_information->shift_end ?? '')
                            ];
                        })->toArray()
                    ];

                default:
                    return ['error' => 'Invalid action.'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
