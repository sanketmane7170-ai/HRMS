<?php

namespace Modules\AgenticAI\Tools;

use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetAssignment;
use App\Models\User;
use Exception;

class AssignAssetTool extends BaseTool
{
    public function name(): string
    {
        return 'assign_asset';
    }

    public function description(): string
    {
        return 'Assign a specific asset to an employee. Required: asset_id, user_id, issue_date.';
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
                'asset_id' => ['type' => 'integer', 'description' => 'ID of the asset to assign.'],
                'user_id' => ['type' => 'integer', 'description' => 'ID of the employee.'],
                'issue_date' => ['type' => 'string', 'description' => 'Date of assignment. Format: YYYY-MM-DD'],
                'notes' => ['type' => 'string', 'description' => 'Optional assignment notes.']
            ],
            'required' => ['asset_id', 'user_id', 'issue_date']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $asset = Asset::findOrFail($args['asset_id']);
            $user = User::findOrFail($args['user_id']);
            
            // Check if asset is already assigned
            if ($asset->activeAssignment) {
                return ['error' => "Asset '{$asset->unique_id}' is already assigned to another user."];
            }

            $assignment = AssetAssignment::create([
                'asset_id' => $args['asset_id'],
                'user_id' => $args['user_id'],
                'issue_date' => $args['issue_date'],
                // Notes column doesn't seem to exist in the model's fillable, but keeping for future or logging
            ]);

            return [
                'success' => true,
                'message' => "Asset '{$asset->unique_id}' ({$asset->model}) successfully assigned to '{$user->name}'.",
                'assignment_id' => $assignment->id
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
