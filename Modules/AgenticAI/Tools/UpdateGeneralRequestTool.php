<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Modules\GeneralRequest\Entities\GeneralRequest;

class UpdateGeneralRequestTool extends BaseTool
{
    public function name(): string { return 'update_general_request'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Update general request. Use when user wants to modify their request.'; }
    public function schema(): array { return ['type' => 'object', 'properties' => ['request_id' => ['type' => 'integer'], 'note' => ['type' => 'string']], 'required' => ['request_id']]; }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['request_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide request ID.'];
        
        try {
            $request = GeneralRequest::where('id', $id)->where('user_id', $user->id)->where('status', 'pending')->first();
            if (!$request) return ['error' => 'Not found', 'message' => 'Request not found or already processed.'];
            if (isset($args['note'])) $request->update(['note' => $args['note']]);
            return ['success' => true, 'message' => 'General request updated.'];
        } catch (\Exception $e) {
            \Log::error('UpdateGeneralRequestTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
