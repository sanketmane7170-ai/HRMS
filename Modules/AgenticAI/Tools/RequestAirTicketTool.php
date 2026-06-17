<?php
namespace Modules\AgenticAI\Tools;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestAirTicketTool extends BaseTool
{
    public function name(): string { return 'request_air_ticket'; }

    public function isSensitive(): bool
    {
        return true;
    }
    public function description(): string { return 'Request air ticket for business travel. Use when user needs flight booking.'; }
    
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'from' => ['type' => 'string', 'description' => 'Departure city'],
                'to' => ['type' => 'string', 'description' => 'Destination city'],
                'date' => ['type' => 'string', 'description' => 'Travel date (YYYY-MM-DD)'],
                'details' => ['type' => 'string', 'description' => 'Additional details']
            ],
            'required' => ['from', 'to', 'date']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        if (empty($args['from']) || empty($args['to']) || empty($args['date'])) {
            return ['error' => 'Missing fields', 'message' => 'Provide from, to, and date.'];
        }
        
        try {
            $details = "Flight from {$args['from']} to {$args['to']}";
            if (isset($args['details'])) $details .= ". " . $args['details'];
            
            $id = DB::table('e_m_p_air_tickets')->insertGetId([
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'user_id' => $user->id,
                'date' => $args['date'],
                'details' => $details,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => "Air ticket request submitted. ID: {$id}",
                'request' => [
                    'id' => $id,
                    'from' => $args['from'],
                    'to' => $args['to'],
                    'date' => $args['date'],
                    'status' => 'Pending'
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('RequestAirTicketTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
