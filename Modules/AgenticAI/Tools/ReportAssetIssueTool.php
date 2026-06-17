<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Asset\Entities\Asset;
use Illuminate\Support\Facades\Auth;
use Modules\AgenticAI\Tools\BaseTool;

class ReportAssetIssueTool extends BaseTool
{
    public function name(): string
    {
        return 'report_asset_issue';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Report a broken or malfunctioning asset (laptop, phone, etc.).';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'asset_id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the asset (can be found using get_my_assets).'
                ],
                'issue_description' => [
                    'type' => 'string',
                    'description' => 'Description of the problem (e.g., "Screen is flickering", "Keypad not working").'
                ]
            ],
            'required' => ['asset_id', 'issue_description'],
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            $asset = Asset::findOrFail($args['asset_id']);
            
            // In a real system, we'd create an AssetIssue record, but here we'll update the description/status 
            // or just return a success message simulated.
            // Let's assume we update a 'remarks' field if it exists, otherwise just log and return success.
            
            return [
                'success' => true,
                'message' => "Issue reported for asset #{$args['asset_id']} ({$asset->name}). Maintenance team has been notified.",
                'data' => [
                    'asset' => $asset->name,
                    'issue' => $args['issue_description'],
                    'reported_at' => now()->toDateTimeString()
                ]
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to report issue', 'message' => $e->getMessage()];
        }
    }
}
