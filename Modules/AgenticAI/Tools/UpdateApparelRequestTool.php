<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateApparelRequestTool extends BaseTool
{
    public function name(): string { return 'update_apparel_request'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Update apparel request. Use when user wants to modify uniform request.'; }
    
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'request_id' => ['type' => 'integer', 'description' => 'Request ID'],
                'quantity' => ['type' => 'integer', 'description' => 'New quantity']
            ],
            'required' => ['request_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['request_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide request ID.'];
        
        try {
            $updates = ['updated_by' => $user->id, 'updated_at' => now()];
            if (isset($args['quantity'])) $updates['number_of_apparel'] = $args['quantity'];
            
            if (count($updates) <= 2) return ['error' => 'No updates', 'message' => 'Provide at least one field.'];
            
            $updated = DB::table('apparel_requests')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', 0) // Pending only
                ->update($updates);
                
            if (!$updated) return ['error' => 'Not found', 'message' => 'Request not found or already processed.'];
            
            return ['success' => true, 'message' => 'Apparel request updated.'];
        } catch (\Exception $e) {
            \Log::error('UpdateApparelRequestTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
