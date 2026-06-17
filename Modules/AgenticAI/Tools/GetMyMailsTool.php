<?php

namespace Modules\AgenticAI\Tools;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetMyMailsTool extends BaseTool
{
    public function name(): string
    {
        return 'get_my_mails';
    }

    public function description(): string
    {
        return 'Get internal company mails/messages. Use when user asks about their internal mails, messages, or company communications.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'unread_only' => [
                    'type' => 'boolean',
                    'description' => 'If true, only show unread mails',
                    'default' => false
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of mails (default 10)',
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
            $query = DB::table('mails')
                ->where('recipient_id', $user->id)
                ->orWhere('sender_id', $user->id);
                
            if ($unreadOnly) {
                $query->where('read_status', 0);
            }
            
            $mails = $query
                ->latest('created_at')
                ->limit($limit)
                ->get();
                
            if ($mails->isEmpty()) {
                return [
                    'message' => $unreadOnly ? 'You have no unread mails.' : 'You have no mails.',
                    'mails' => []
                ];
            }
            
            return [
                'mails' => $mails->map(function($mail) use ($user) {
                    $isSent = $mail->sender_id == $user->id;
                    
                    return [
                        'subject' => $mail->subject ?? 'No subject',
                        'from' => $isSent ? 'You' : ($mail->sender_name ?? 'Unknown'),
                        'to' => $isSent ? ($mail->recipient_name ?? 'Unknown') : 'You',
                        'preview' => substr($mail->message ?? '', 0, 100) . '...',
                        'read' => $mail->read_status ? true : false,
                        'date' => date('M d, Y H:i', strtotime($mail->created_at)),
                        'type' => $isSent ? 'sent' : 'received'
                    ];
                })->toArray(),
                'count' => $mails->count()
            ];
        } catch (\Exception $e) {
            \Log::error('GetMyMailsTool failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                'error' => 'Failed to fetch mails',
                'message' => 'Unable to retrieve mails. Error: ' . $e->getMessage()
            ];
        }
    }
}
