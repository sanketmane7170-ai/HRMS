<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;
use Modules\Asset\Entities\AssetAssignment;
use Illuminate\Support\Facades\Auth;
use Modules\AgenticAI\Tools\BaseTool;

class GetMyAssetsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_assets';
    }

    public function description(): string
    {
        return 'Get a list of company assets (laptops, phones, etc.) currently assigned to me.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
            'required' => [],
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not logged in.'];
        }

        try {
            $assignments = AssetAssignment::with(['asset', 'asset.type'])
                ->where('user_id', $user->id)
                ->get();

            return [
                'assets' => $assignments->map(function($a) {
                    return [
                        'id' => $a->asset->id,
                        'name' => $a->asset->name,
                        'type' => $a->asset->type->name ?? 'N/A',
                        'serial_number' => $a->asset->serial_number,
                        'issue_date' => $a->issue_date,
                    ];
                }),
                'count' => $assignments->count()
            ];
        } catch (\Exception $e) {
            return ['error' => 'Failed to fetch assets', 'message' => $e->getMessage()];
        }
    }
}
