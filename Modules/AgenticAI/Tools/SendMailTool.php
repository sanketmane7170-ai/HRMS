<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SendMailTool extends BaseTool
{
    public function name(): string
    {
        return 'send_mail';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function description(): string
    {
        return 'Send internal mail to another employee. Use when user wants to send a message to a colleague.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'recipient' => ['type' => 'string', 'description' => 'Recipient name or user ID'],
                'subject' => ['type' => 'string', 'description' => 'Mail subject'],
                'message' => ['type' => 'string', 'description' => 'Mail content']
            ],
            'required' => ['recipient', 'subject', 'message']
        ];
    }

    public function execute(array $args): mixed
    {
        $user = Auth::user();
        
        if (empty($args['recipient']) || empty($args['subject']) || empty($args['message'])) {
            return ['error' => 'Missing fields', 'message' => 'Provide recipient, subject, and message.'];
        }
        
        try {
            // Lookup user by name or ID
            $recipientId = $args['recipient'];
            if (!is_numeric($recipientId)) {
                //Sanket v2.0 - use parameterized query to prevent SQL injection via LIKE wildcards
                $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $recipientId);
                $recipient = DB::table('users')
                    ->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('first_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                    ->first();
                
                if (!$recipient) {
                    return ['error' => 'User not found', 'message' => "No user found with name: {$recipientId}"];
                }
                
                $recipientId = $recipient->id;
            }
            
            $mailId = DB::table('messages')->insertGetId([
                'from' => $user->id,
                'to' => $recipientId,
                'subject' => $args['subject'],
                'message' => $args['message'],
                'message_type' => 'email',
                'read_status' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => 'Mail sent successfully.',
                'mail' => [
                    'id' => $mailId,
                    'subject' => $args['subject'],
                    'sent_at' => now()->format('M d, Y H:i')
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('SendMailTool failed', ['error' => $e->getMessage()]);
            return ['error' => 'Failed', 'message' => 'Unable to send mail. Please try again.'];
        }
    }
}
