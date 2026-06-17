<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAppreciation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'detail', 'type', 'acknowledgement', 'document',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFileName(): string
    {
        return str()->slug($this->type->name) . "_" . $this->id . "_" . str()->slug($this->user->name) . ".pdf";
    }
}
