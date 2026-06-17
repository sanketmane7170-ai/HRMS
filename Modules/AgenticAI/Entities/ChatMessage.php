<?php

namespace Modules\AgenticAI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'a_i_chat_messages';

    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
