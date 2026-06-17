<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestShiftChangeTool extends BaseTool
{
    public function name(): string { return 'request_shift_change'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Request a shift change. Use when user wants different shift timing.'; }
    
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'requested_shift' => ['type' => 'string', 'description' => 'Requested shift (e.g., "Morning", "Evening")'],
                'reason' => ['type' => 'string', 'description' => 'Reason for change']
            ],
            'required' => ['requested_shift']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (empty($args['requested_shift'])) return ['error' => 'Missing shift', 'message' => 'Provide requested shift.'];
        
        try {
            // Find "Shift Change" request type
            $requestType = DB::table('general_request_types')
                ->where('name', 'LIKE', '%Shift%')
                ->first();
            
            if (!$requestType) {
                $requestType = DB::table('general_request_types')->first();
            }
            
            if (!$requestType) {
                return ['error' => 'No request type', 'message' => 'Request types not configured.'];
            }
            
            $note = "Shift change request to {$args['requested_shift']}";
            if (isset($args['reason'])) $note .= ". Reason: {$args['reason']}";
            
            $id = DB::table('general_requests')->insertGetId([
                'user_id' => $user->id,
                'type_id' => $requestType->id,
                'note' => $note,
                'status' => 0,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return ['success' => true, 'message' => "Shift change request created. ID: {$id}", 'request_id' => $id];
        } catch (\Exception $e) {
            \Log::error('RequestShiftChangeTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
