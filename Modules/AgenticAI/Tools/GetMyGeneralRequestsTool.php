<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\GeneralRequest\Entities\GeneralRequest;

class GetMyGeneralRequestsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_general_requests';
    }

    public function description(): string
    {
        return 'Get all general requests submitted by the user. Use when user asks about their requests, how many requests they have, or wants to see request status.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => (object)[],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        try {
            $requests = GeneralRequest::query()
                ->where('user_id', $user->id)
                ->with('type')
                ->latest('created_at')
                ->get();
                
            if ($requests->isEmpty()) {
                return [
                    'message' => 'You have no general requests.',
                    'requests' => []
                ];
            }
            
            return [
                'requests' => $requests->map(function($request) {
                    $statusMap = [
                        0 => 'Pending',
                        1 => 'Approved',
                        2 => 'Rejected'
                    ];
                    
                    return [
                        'id' => $request->id,
                        'type' => $request->type->name ?? 'General Request',
                        'note' => $request->note,
                        'status' => $statusMap[$request->status] ?? 'Unknown',
                        'submitted' => $request->created_at->format('M d, Y H:i'),
                        'submitted_ago' => $request->created_at->diffForHumans()
                    ];
                })->toArray(),
                'count' => $requests->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyGeneralRequestsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch requests',
                'message' => 'Unable to retrieve your requests at this time.'
            ];
        }
    }
}
