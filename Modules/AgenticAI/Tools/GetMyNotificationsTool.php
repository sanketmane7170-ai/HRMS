<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetMyNotificationsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_notifications';
    }

    public function description(): string
    {
        return 'Get user notifications. Use when user asks about notifications, alerts, or messages.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'unread_only' => [
                    'type' => 'boolean',
                    'description' => 'If true, only show unread notifications. Default false.',
                    'default' => false
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of notifications (default 10)',
                    'default' => 10
                ]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $unreadOnly = $args['unread_only'] ?? false;
        $limit = min($args['limit'] ?? 10, 50);
        
        try {
            $query = DB::table('notifications')
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', 'App\\Models\\User');
                
            if ($unreadOnly) {
                $query->whereNull('read_at');
            }
            
            $notifications = $query
                ->latest('created_at')
                ->limit($limit)
                ->get();
                
            if ($notifications->isEmpty()) {
                return [
                    'message' => $unreadOnly ? 'You have no unread notifications.' : 'You have no notifications.',
                    'notifications' => [],
                    'count' => 0,
                    'unread_count' => DB::table('notifications')
                        ->where('notifiable_id', $user->id)
                        ->whereNull('read_at')
                        ->count()
                ];
            }
            
            return [
                'notifications' => $notifications->map(function($notification) {
                    $data = json_decode($notification->data, true);
                    
                    return [
                        'id' => $notification->id,
                        'type' => $data['type'] ?? 'Notification',
                        'message' => $data['message'] ?? 'No message',
                        'read' => $notification->read_at ? true : false,
                        'created' => date('M d, Y H:i', strtotime($notification->created_at)),
                        'time_ago' => \Carbon\Carbon::parse($notification->created_at)->diffForHumans()
                    ];
                })->toArray(),
                'count' => $notifications->count(),
                'unread_count' => DB::table('notifications')
                    ->where('notifiable_id', $user->id)
                    ->whereNull('read_at')
                    ->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyNotificationsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch notifications',
                'message' => 'Unable to retrieve notifications at this time.'
            ];
        }
    }
}
