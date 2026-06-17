<?php

namespace Modules\AgenticAI\Tools;

use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetType;
use Exception;

class ManageAssetInventoryTool extends BaseTool
{
    public function name(): string
    {
        return 'manage_asset_inventory';
    }

    public function description(): string
    {
        return 'List or search the company asset inventory. Can filter by asset type or search for a specific asset.';
    }

    public function isSensitive(): bool
    {
        return false;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'type_id' => ['type' => 'integer', 'description' => 'Filter by asset type ID.'],
                'search' => ['type' => 'string', 'description' => 'Search by model or unique ID.'],
                'status' => ['type' => 'string', 'enum' => ['Available', 'Assigned', 'UnderMaintenance', 'Disposed'], 'description' => 'Filter by asset status.'],
                'limit' => ['type' => 'integer', 'default' => 20]
            ]
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $query = Asset::with(['type', 'manufacturer', 'activeAssignment.user']);

            if (!empty($args['type_id'])) {
                $query->where('asset_type_id', $args['type_id']);
            }

            if (!empty($args['search'])) {
                $query->where(function($q) use ($args) {
                    $q->where('unique_id', 'like', "%{$args['search']}%")
                      ->orWhere('model', 'like', "%{$args['search']}%");
                });
            }

            if (!empty($args['status'])) {
                $query->where('status', $args['status']);
            }

            $assets = $query->limit($args['limit'] ?? 20)->get();

            return [
                'assets' => $assets->map(function($asset) {
                    return [
                        'id' => $asset->id,
                        'name' => $asset->name,
                        'type' => $asset->type->name ?? 'N/A',
                        'manufacturer' => $asset->manufacturer->name ?? 'N/A',
                        'model' => $asset->model,
                        'unique_id' => $asset->unique_id,
                        'status' => $asset->status->name ?? 'N/A',
                        'assigned_to' => $asset->activeAssignment->user->name ?? 'None'
                    ];
                })->toArray(),
                'count' => $assets->count()
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
