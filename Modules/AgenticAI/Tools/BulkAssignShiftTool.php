<?php

namespace Modules\AgenticAI\Tools;

use Modules\Shift\Entities\UsersShift;
use App\Models\User;
use Exception;

/**
 * BulkAssignShiftTool - Assign shifts to multiple employees
 * Author: Sanket
 */
class BulkAssignShiftTool extends BaseTool
{
    public function name(): string
    {
        return 'bulk_assign_shift';
    }

    public function description(): string
    {
        return 'Assign a shift schedule to multiple employees for a date range. Use when assigning shifts to teams or departments.';
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
                'user_ids' => [
                    'type' => 'array',
                    'description' => 'Array of employee IDs to assign shift to',
                    'items' => ['type' => 'integer']
                ],
                'schedule_id' => [
                    'type' => 'integer',
                    'description' => 'Shift schedule ID to assign'
                ],
                'start_date' => [
                    'type' => 'string',
                    'description' => 'Start date in YYYY-MM-DD format'
                ],
                'end_date' => [
                    'type' => 'string',
                    'description' => 'End date in YYYY-MM-DD format (optional, defaults to start_date)'
                ],
                'days_of_week' => [
                    'type' => 'array',
                    'description' => 'Specific days of week (optional, e.g., ["monday", "friday"])',
                    'items' => ['type' => 'string']
                ],
            ],
            'required' => ['user_ids', 'schedule_id', 'start_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            //Sanket v2.0 - auth check: only admin/HR/manager can bulk assign shifts
            $currentUser = auth()->user();
            if (!$currentUser) {
                return ['error' => 'Authentication required'];
            }
            if (!$currentUser->hasAnyRole(['admin', 'hr', 'manager', 'super-admin'])) {
                return ['error' => 'You do not have permission to assign shifts'];
            }

            // Validation
            if (empty($args['user_ids']) || !is_array($args['user_ids'])) {
                return ['error' => 'user_ids must be a non-empty array'];
            }
            if (empty($args['schedule_id'])) {
                return ['error' => 'schedule_id is required'];
            }
            if (empty($args['start_date'])) {
                return ['error' => 'start_date is required'];
            }

            $startDate = \Carbon\Carbon::parse($args['start_date']);
            $endDate = !empty($args['end_date']) 
                ? \Carbon\Carbon::parse($args['end_date']) 
                : $startDate->copy();

            $daysOfWeek = $args['days_of_week'] ?? null;
            $assignedCount = 0;
            $skippedCount = 0;

            // Loop through each employee
            foreach ($args['user_ids'] as $userId) {
                // Verify employee exists
                if (!User::find($userId)) {
                    $skippedCount++;
                    continue;
                }

                // Loop through date range
                $currentDate = $startDate->copy();
                while ($currentDate->lte($endDate)) {
                    // Check if we should assign for this day of week
                    if ($daysOfWeek && !in_array(strtolower($currentDate->format('l')), $daysOfWeek)) {
                        $currentDate->addDay();
                        continue;
                    }

                    // Check if already assigned
                    $existing = UsersShift::where('user_id', $userId)
                        ->where('assigned_for_date', $currentDate->format('Y-m-d'))
                        ->first();

                    if ($existing) {
                        // Update existing
                        $existing->update(['schedule_id' => $args['schedule_id']]);
                    } else {
                        // Create new
                        UsersShift::create([
                            'user_id' => $userId,
                            'schedule_id' => $args['schedule_id'],
                            'assigned_for_date' => $currentDate->format('Y-m-d'),
                            'assigned_by_id' => auth()->id()
                        ]);
                    }

                    $assignedCount++;
                    $currentDate->addDay();
                }
            }

            $employeeCount = count($args['user_ids']) - $skippedCount;
            $days = $startDate->diffInDays($endDate) + 1;

            return [
                'success' => true,
                'summary' => [
                    'employees_processed' => $employeeCount,
                    'employees_skipped' => $skippedCount,
                    'total_assignments' => $assignedCount,
                    'date_range' => "{$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}",
                    'days' => $days
                ],
                'message' => "Shift assigned to {$employeeCount} employees for {$days} days ({$assignedCount} total assignments)"
            ];

        } catch (Exception $e) {
            \Log::error('BulkAssignShiftTool error', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to assign shifts. Please try again.', 'success' => false];
        }
    }
}
