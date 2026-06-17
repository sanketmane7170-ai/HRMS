<?php

namespace Modules\AgenticAI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $table = 'ai_messages';

    protected $fillable = [
        'conversation_id',
        'sender',
        'content',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Sender types
     */
    const SENDER_USER = 'user';
    const SENDER_ASSISTANT = 'assistant';
    const SENDER_SYSTEM = 'system';
    const SENDER_TOOL = 'tool';

    /**
     * Relationships
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * Scopes
     */
    public function scopeByUser($query)
    {
        return $query->where('sender', self::SENDER_USER);
    }

    public function scopeByAssistant($query)
    {
        return $query->where('sender', self::SENDER_ASSISTANT);
    }

    public function scopeBySystem($query)
    {
        return $query->where('sender', self::SENDER_SYSTEM);
    }

    /**
     * Accessors
     */
    public function getIsUserMessageAttribute(): bool
    {
        return $this->sender === self::SENDER_USER;
    }

    public function getIsAssistantMessageAttribute(): bool
    {
        return $this->sender === self::SENDER_ASSISTANT;
    }

    public function getToolCallsAttribute(): ?array
    {
        return $this->metadata['tool_calls'] ?? null;
    }

    public function getModuleAttribute(): ?string
    {
        return $this->metadata['module'] ?? null;
    }

    /**
     * Helper Methods
     */
    public function addToolCall(string $tool, array $arguments, $result): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['tool_calls'] = $metadata['tool_calls'] ?? [];
        
        $metadata['tool_calls'][] = [
            'tool' => $tool,
            'arguments' => $arguments,
            'result' => $result,
            'timestamp' => now()->toIso8601String()
        ];

        $this->update(['metadata' => $metadata]);
    }

    public function setModule(string $module): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['module'] = $module;
        $this->update(['metadata' => $metadata]);
    }
}
