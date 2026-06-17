<?php

namespace Modules\AgenticAI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_id',
        'title',
        'is_archived'
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'conversation_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }

    /**
     * Accessors
     */
    public function getMessageCountAttribute()
    {
        return $this->messages()->count();
    }

    public function getLastMessageAttribute()
    {
        //Sanket v2.0 - explicit column for latest() to avoid ambiguity
        return $this->messages()->latest('created_at')->first();
    }

    /**
     * Helper Methods
     */
    public function generateTitle(): string
    {
        $firstMessage = $this->messages()
            ->where('sender', 'user')
            ->oldest()
            ->first();

        if (!$firstMessage) {
            return 'New Conversation';
        }

        // Take first 50 characters or first sentence
        $content = $firstMessage->content;
        $title = mb_substr($content, 0, 50);
        
        // Try to end at a word boundary
        if (mb_strlen($content) > 50) {
            $lastSpace = mb_strrpos($title, ' ');
            if ($lastSpace !== false) {
                $title = mb_substr($title, 0, $lastSpace);
            }
            $title .= '...';
        }

        return $title;
    }

    public function updateTitle(): void
    {
        if ($this->title === 'New Conversation' || empty($this->title)) {
            // Reload the relationship to ensure we have the latest messages
            $this->load('messages');
            $generatedTitle = $this->generateTitle();
            if ($generatedTitle !== 'New Conversation') {
                $this->update(['title' => $generatedTitle]);
            }
        }
    }
}
