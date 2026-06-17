<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Modules\Asset\Entities\Asset;

class ReturnAssetTool extends BaseTool
{
    public function name(): string { return 'return_asset'; }
    public function description(): string { return 'Return an asset. Use when user returns equipment.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['asset_id' => ['type' => 'integer', 'description' => 'Asset ID']], 'required' => ['asset_id']]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['asset_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide asset ID.'];
        
        try {
            $asset = Asset::where('id', $id)->where('user_id', $user->id)->first();
            if (!$asset) return ['error' => 'Not found', 'message' => 'Asset not found.'];
            $asset->update(['user_id' => null, 'status' => 'available', 'returned_at' => now()]);
            return ['success' => true, 'message' => "Asset {$id} returned."];
        } catch (\Exception $e) {
            \Log::error('ReturnAssetTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
