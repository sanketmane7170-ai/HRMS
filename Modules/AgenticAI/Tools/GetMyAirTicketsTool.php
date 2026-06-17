<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetMyAirTicketsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_air_tickets';
    }

    public function description(): string
    {
        return 'Get user\'s air ticket requests and travel bookings. Use when user asks about their flight tickets, travel requests, or air bookings.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of tickets (default 10)',
                    'default' => 10
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $limit = min($args['limit'] ?? 10, 50);
        
        try {
            $tickets = DB::table('air_ticket_requests')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit($limit)
                ->get();
                
            if ($tickets->isEmpty()) {
                return [
                    'message' => 'You have no air ticket requests.',
                    'tickets' => []
                ];
            }
            
            return [
                'tickets' => $tickets->map(function($ticket) {
                    return [
                        'from' => $ticket->from_location ?? 'N/A',
                        'to' => $ticket->to_location ?? 'N/A',
                        'departure_date' => $ticket->departure_date ? date('M d, Y', strtotime($ticket->departure_date)) : 'N/A',
                        'return_date' => $ticket->return_date ? date('M d, Y', strtotime($ticket->return_date)) : 'N/A',
                        'status' => $ticket->status ?? 'pending',
                        'requested' => date('M d, Y', strtotime($ticket->created_at))
                    ];
                })->toArray(),
                'count' => $tickets->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyAirTicketsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch tickets',
                'message' => 'Unable to retrieve air tickets. Error: ' . $e->getMessage()
            ];
        }
    }
}
