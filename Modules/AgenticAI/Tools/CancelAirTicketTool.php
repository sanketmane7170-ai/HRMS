<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancelAirTicketTool extends BaseTool
{
    public function name(): string { return 'cancel_air_ticket'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Cancel air ticket request. Use when user wants to cancel flight request.'; }
    
    public function schema(): array
    {
        return ['type' => 'object', 'properties' => ['ticket_id' => ['type' => 'integer', 'description' => 'Ticket request ID']], 'required' => ['ticket_id']];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['ticket_id'] ?? null;
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide ticket ID.'];
        
        try {
            $updated = DB::table('e_m_p_air_tickets')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', 'Pending')
                ->update(['status' => 'Rejected', 'updated_at' => now(), 'updated_by' => $user->id]);
                
            if (!$updated) return ['error' => 'Not found', 'message' => 'Ticket not found or already processed.'];
            
            return ['success' => true, 'message' => 'Air ticket request cancelled.'];
        } catch (\Exception $e) {
            \Log::error('CancelAirTicketTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
