<?php

namespace Modules\AgenticAI\Tools;

use App\Models\User;
use App\Models\UserWorkDetail;
use Exception;

class UpdateHierarchyTool extends BaseTool
{
    public function name(): string
    {
        return 'update_hierarchy';
    }

    public function description(): string
    {
        return 'Update the reporting hierarchy for an employee. Assign or change their reporting manager.';
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
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the employee whose hierarchy is being updated.'
                ],
                'manager_id' => [
                    'type' => 'integer',
                    'description' => 'The user ID of the new reporting manager.'
                ],
                'action' => [
                    'type' => 'string',
                    'enum' => ['set', 'add', 'remove'],
                    'description' => 'Action: set (replace all), add (add to existing), or remove (remove from list).'
                ]
            ],
            'required' => ['user_id', 'manager_id', 'action']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $user = User::findOrFail($args['user_id']);
            $workDetail = $user->workDetail;

            if (!$workDetail) {
                // Return descriptive error if work detail missing (shouldn't happen for valid employees)
                return [
                    'success' => false,
                    'message' => "User ID #{$user->id} does not have profile work details setup. Please enroll them correctly first."
                ];
            }

            $currentManagers = $workDetail->report_to_ids ?? [];
            if (!is_array($currentManagers)) {
                $currentManagers = [];
            }

            $newManagerId = (string) $args['manager_id'];

            switch ($args['action']) {
                case 'set':
                    $currentManagers = [$newManagerId];
                    break;
                case 'add':
                    if (!in_array($newManagerId, $currentManagers)) {
                        $currentManagers[] = $newManagerId;
                    }
                    break;
                case 'remove':
                    $currentManagers = array_values(array_diff($currentManagers, [$newManagerId]));
                    break;
            }

            $workDetail->report_to_ids = $currentManagers;
            $workDetail->save();

            return [
                'success' => true,
                'message' => "Successfully updated hierarchy for '{$user->name}'. New reporting managers IDs: " . implode(', ', $currentManagers)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Hierarchy update failed',
                'message' => $e->getMessage()
            ];
        }
    }
}
