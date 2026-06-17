<?php

namespace Modules\Training\Entities;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Training\Database\factories\TrainingFactory;

class TrainingChat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'training_id',
        'user_id',
        'message',
        'parent_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function replies()
    {
        return $this->hasMany(TrainingChat::class, 'parent_id')->with('user');
    }
}
