<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestAssetTool extends BaseTool
{
    public function name(): string { return 'request_asset'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Request a new asset/equipment. Use when user needs equipment.'; }
    
    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['asset_type' => ['type' => 'string', 'description' => 'Type of asset (laptop, mouse, etc)'], 'reason' => ['type' => 'string', 'description' => 'Reason for request']], 'required' => ['asset_type']];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (empty($args['asset_type'])) return ['error' => 'Missing type', 'message' => 'Provide asset type.'];
        
        try {
            // Find or create "Equipment Request" type
            $requestType = DB::table('general_request_types')
                ->where('name', 'LIKE', '%Equipment%')
                ->orWhere('name', 'LIKE', '%Asset%')
                ->first();
            
            if (!$requestType) {
                // Fallback to first available type
                $requestType = DB::table('general_request_types')->first();
            }
            
            if (!$requestType) {
                return ['error' => 'No request type', 'message' => 'Equipment request type not configured.'];
            }
            
            $id = DB::table('general_requests')->insertGetId([
                'user_id' => $user->id,
                'type_id' => $requestType->id,
                'note' => "Request for {$args['asset_type']}" . (isset($args['reason']) ? ": {$args['reason']}" : ''),
                'status' => 0,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return ['success' => true, 'message' => "Asset request created. ID: {$id}", 'request_id' => $id];
        } catch (\Exception $e) {
            \Log::error('RequestAssetTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
