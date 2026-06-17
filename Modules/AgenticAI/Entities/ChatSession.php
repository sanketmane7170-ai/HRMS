<?php

namespace Modules\AgenticAI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ChatSession extends Model
{
    use HasFactory;

    protected $table = 'a_i_chat_sessions';

    protected $fillable = [
        'user_id',
        'title'
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
