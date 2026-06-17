<?php

namespace Modules\Training\Entities;

use Illuminate\Database\Eloquent\Model;

class TrainingQuestion extends Model
{
    protected $fillable = ['training_id', 'question','duration'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function answers()
    {
        return $this->hasMany(TrainingAnswer::class);
    }
}
