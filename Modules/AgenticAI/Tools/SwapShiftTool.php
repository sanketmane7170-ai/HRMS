<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SwapShiftTool extends BaseTool
{
    public function name(): string { return 'swap_shift'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Request to swap shift with another employee. Use when user wants to exchange shifts.'; }
    
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'with_user_id' => ['type' => 'integer', 'description' => 'User ID to swap with'],
                'reason' => ['type' => 'string', 'description' => 'Reason for swap']
            ],
            'required' => ['with_user_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (empty($args['with_user_id'])) return ['error' => 'Missing user', 'message' => 'Provide user ID to swap with.'];
        
        try {
            // Find "Shift Swap" request type or use first available
            $requestType = DB::table('general_request_types')
                ->where('name', 'LIKE', '%Shift%')
                ->orWhere('name', 'LIKE', '%Swap%')
                ->first();
            
            if (!$requestType) {
                $requestType = DB::table('general_request_types')->first();
            }
            
            if (!$requestType) {
                return ['error' => 'No request type', 'message' => 'Request types not configured.'];
            }
            
            $note = "Shift swap request with user ID {$args['with_user_id']}";
            if (isset($args['reason'])) $note .= ". Reason: {$args['reason']}";
            
            $id = DB::table('general_requests')->insertGetId([
                'user_id' => $user->id,
                'type_id' => $requestType->id,
                'note' => $note,
                'status' => 'pending',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return ['success' => true, 'message' => "Shift swap request created. ID: {$id}", 'request_id' => $id];
        } catch (\Exception $e) {
            \Log::error('SwapShiftTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
