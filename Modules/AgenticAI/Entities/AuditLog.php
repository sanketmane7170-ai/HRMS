<?php

namespace Modules\AgenticAI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'ai_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'action',
        'details',
        'created_at'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime'
    ];

    /**
     * Action types
     */
    const ACTION_MESSAGE_SENT = 'message_sent';
    const ACTION_TOOL_EXECUTED = 'tool_executed';
    const ACTION_APPROVAL_REQUESTED = 'approval_requested';
    const ACTION_APPROVAL_GRANTED = 'approval_granted';
    const ACTION_APPROVAL_DENIED = 'approval_denied';

    /**
     * Relationships
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper Methods
     */
    public static function logAction(
        int $conversationId,
        int $userId,
        string $action,
        array $details = []
    ): self {
        return self::create([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'created_at' => now()
        ]);
    }
}
