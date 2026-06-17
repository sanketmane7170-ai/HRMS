<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteNotificationTool extends BaseTool
{
    public function name(): string
    {
        return 'delete_notification';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Delete notification(s). Use when user wants to remove notifications.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'notification_id' => ['type' => 'string', 'description' => 'Notification ID to delete'],
                'delete_all_read' => ['type' => 'boolean', 'description' => 'Delete all read notifications', 'default' => false]
            ],
            'required' => []
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        try {
            if ($args['delete_all_read'] ?? false) {
                DB::table('notifications')
                    ->where('notifiable_id', $user->id)
                    ->whereNotNull('read_at')
                    ->delete();
                return ['success' => true, 'message' => 'All read notifications deleted.'];
            }
            
            $id = $args['notification_id'] ?? null;
            if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide notification ID or set delete_all_read to true.'];
            
            DB::table('notifications')
                ->where('id', $id)
                ->where('notifiable_id', $user->id)
                ->delete();
            
            return ['success' => true, 'message' => 'Notification deleted.'];
        } catch (\Exception $e) {
            \Log::error('DeleteNotificationTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
