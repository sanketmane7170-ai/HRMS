<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class PerformanceReviewUser extends Model
{
    protected $table = 'performance_review_user'; // specify pivot table name

    protected $fillable = [
        'performance_review_id',
        'user_id',
        'status',
        'employee_response',
        'hr_review',
        'hr_review_date',
        'reviewer_avg_score',
        'hr_avg_score',
    ];

    protected $casts = [
        'hr_review_date' => 'datetime',
        'reviewer_avg_score' => 'float',
        'hr_avg_score' => 'float',
    ];

    public function performanceReview()
    {
        return $this->belongsTo(PerformanceReview::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
