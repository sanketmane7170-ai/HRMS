<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['question_set_id', 'question_text', 'max_score'];

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function responses()
    {
        return $this->hasMany(ReviewResponse::class);
    }
    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }
}
