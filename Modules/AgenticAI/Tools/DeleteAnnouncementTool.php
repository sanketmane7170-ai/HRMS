<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Modules\Announcement\Entities\Announcement;

class DeleteAnnouncementTool extends BaseTool
{
    public function name(): string
    {
        return 'delete_announcement';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        //Sanket v2.0 - admin/hr can delete any; others only their own
        return 'Delete an announcement. Admin and HR can delete any announcement. Other roles can only delete announcements they created. '
            . 'Always call get_announcements first to confirm the correct ID before deleting.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'announcement_id' => [
                    'type'        => 'integer',
                    'description' => 'ID of the announcement to delete. Call get_announcements first if unsure.'
                ]
            ],
            'required' => ['announcement_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id   = $args['announcement_id'] ?? null;

        if (!$user) {
            return ['error' => 'Authentication required'];
        }
        if (!$id) {
            return ['error' => 'Missing ID', 'message' => 'Provide the announcement_id to delete.'];
        }

        try {
            //Sanket v2.0 - admin/hr bypass ownership check
            $isPrivileged = $user->hasAnyRole(['admin', 'hr', 'super-admin']);
            $announcement = $isPrivileged
                ? Announcement::find($id)
                : Announcement::where('id', $id)->where('user_id', $user->id)->first();

            if (!$announcement) {
                return ['error' => 'Not found', 'message' => $isPrivileged
                    ? "Announcement #{$id} does not exist."
                    : "Announcement #{$id} not found or you don't have permission to delete it."];
            }

            $snapshot = strip_tags(mb_substr($announcement->body, 0, 80)) . '...';
            $announcement->delete();

            return [
                'success' => true,
                'message' => "Announcement #{$id} deleted successfully.",
                'deleted_preview' => $snapshot,
            ];
        } catch (\Exception $e) {
            \Log::error('DeleteAnnouncementTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
