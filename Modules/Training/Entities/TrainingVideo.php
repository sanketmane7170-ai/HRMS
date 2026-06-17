<?php

namespace Modules\Training\Entities;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Training\Database\factories\TrainingFactory;

class TrainingVideo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['training_id', 'video_path'];



    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
