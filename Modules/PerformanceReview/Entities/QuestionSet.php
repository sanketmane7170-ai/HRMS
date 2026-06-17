<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class QuestionSet extends Model
{
    protected $fillable = ['name'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function reviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }
}
