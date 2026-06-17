<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReplyToMailTool extends BaseTool
{
    public function name(): string
    {
        return 'reply_to_mail';
    }

    public function description(): string
    {
        return 'Reply to a mail. Use when user wants to respond to a received mail.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'mail_id' => ['type' => 'integer', 'description' => 'ID of mail to reply to'],
                'message' => ['type' => 'string', 'description' => 'Reply message']
            ],
            'required' => ['mail_id', 'message']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        if (empty($args['mail_id']) || empty($args['message'])) {
            return ['error' => 'Missing fields', 'message' => 'Provide mail_id and message.'];
        }
        
        try {
            $originalMail = DB::table('mails')->where('id', $args['mail_id'])->first();
            if (!$originalMail) return ['error' => 'Not found', 'message' => 'Original mail not found.'];
            
            $replyId = DB::table('mails')->insertGetId([
                'sender_id' => $user->id,
                'recipient_id' => $originalMail->sender_id,
                'subject' => 'Re: ' . $originalMail->subject,
                'message' => $args['message'],
                'read_status' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => 'Reply sent successfully.',
                'mail' => ['id' => $replyId, 'subject' => 'Re: ' . $originalMail->subject]
            ];
        } catch (\Exception $e) {
            \Log::error('ReplyToMailTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => $e->getMessage()];
        }
    }
}
