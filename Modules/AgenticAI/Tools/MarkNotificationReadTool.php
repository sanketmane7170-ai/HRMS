<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarkNotificationReadTool extends BaseTool
{
    public function name(): string
    {
        return 'mark_notification_read';
    }

    public function description(): string
    {
        return 'Mark notification(s) as read. Use when user wants to mark notifications as read.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'notification_id' => ['type' => 'string', 'description' => 'Notification ID (optional, marks all if not provided)'],
                'mark_all' => ['type' => 'boolean', 'description' => 'Mark all as read', 'default' => false]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        try {
            if ($args['mark_all'] ?? false) {
                DB::table('notifications')
                    ->where('notifiable_id', $user->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
                return ['success' => true, 'message' => 'All notifications marked as read.'];
            }
            
            $id = $args['notification_id'] ?? null;
            if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide notification ID or set mark_all to true.'];
            
            DB::table('notifications')
                ->where('id', $id)
                ->where('notifiable_id', $user->id)
                ->update(['read_at' => now()]);
            
            return ['success' => true, 'message' => 'Notification marked as read.'];
        } catch (\Exception $e) {
            \Log::error('MarkNotificationReadTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
