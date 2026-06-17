<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetMyApparelTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_apparel';
    }

    public function description(): string
    {
        return 'Get user\'s uniform/apparel requests and assignments. Use when user asks about their uniform, apparel, or clothing requests.';
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
            $apparel = DB::table('apparel_requests')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit(20)
                ->get();
                
            if ($apparel->isEmpty()) {
                return [
                    'message' => 'You have no apparel/uniform requests.',
                    'apparel' => []
                ];
            }
            
            return [
                'apparel' => $apparel->map(function($item) {
                    return [
                        'item_type' => $item->item_type ?? $item->type ?? 'Uniform',
                        'size' => $item->size ?? 'N/A',
                        'quantity' => $item->quantity ?? 1,
                        'status' => $item->status ?? 'pending',
                        'requested' => date('M d, Y', strtotime($item->created_at))
                    ];
                })->toArray(),
                'count' => $apparel->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyApparelTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch apparel',
                'message' => 'Unable to retrieve apparel requests. Error: ' . $e->getMessage()
            ];
        }
    }
}
