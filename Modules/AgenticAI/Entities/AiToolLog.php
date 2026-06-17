<?php

namespace Modules\AgenticAI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class AiToolLog extends Model
{
    use HasFactory;

    protected $table = 'ai_tool_logs';

    /**
     * Disable updated_at as we only need created_at for logging.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'tool_name',
        'payload',
        'response',
        'status',
        'duration_ms',
        'created_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
