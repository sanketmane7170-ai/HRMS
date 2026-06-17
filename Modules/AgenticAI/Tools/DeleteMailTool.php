<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteMailTool extends BaseTool
{
    public function name(): string
    {
        return 'delete_mail';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Delete a mail. Use when user wants to remove a mail from their inbox or sent items.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'mail_id' => ['type' => 'integer', 'description' => 'ID of mail to delete']
            ],
            'required' => ['mail_id']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        $id = $args['mail_id'] ?? null;
        
        if (!$id) return ['error' => 'Missing ID', 'message' => 'Provide mail ID.'];
        
        try {
            $deleted = DB::table('mails')
                ->where('id', $id)
                ->where(function($q) use ($user) {
                    $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })
                ->delete();
            
            if (!$deleted) return ['error' => 'Not found', 'message' => 'Mail not found or no permission.'];
            
            return ['success' => true, 'message' => 'Mail deleted.'];
        } catch (\Exception $e) {
            \Log::error('DeleteMailTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
