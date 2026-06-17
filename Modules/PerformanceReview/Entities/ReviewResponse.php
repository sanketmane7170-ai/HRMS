<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class ReviewResponse extends Model
{
    protected $fillable = [
        'performance_review_id',
        'question_id',
        'user_id',
        'answer',     
        'score',
        'comment'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'performance_review_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
