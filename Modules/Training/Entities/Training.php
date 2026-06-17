<?php

namespace Modules\Training\Entities;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Training\Database\factories\TrainingFactory;
use Modules\Training\Entities\TrainingQuestion;

class Training extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'department_id',
        'title',
        'description',
        'video_path',
    ];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function chats()
    {
        return $this->hasMany(TrainingChat::class)->whereNull('parent_id')->with('replies.user');
    }
    public function videos()
    {
        return $this->hasMany(TrainingVideo::class);
    }
    public function questions()
    {
        return $this->hasMany(TrainingQuestion::class);
    }
}
