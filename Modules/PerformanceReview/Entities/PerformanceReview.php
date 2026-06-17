<?php


namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    protected $fillable = [
        'review_duration_id',
        'question_set_id',
        'status',
        'start_date',
        'score',
        'submitted_at',
        'score_criteria_id',

    ];
    protected $casts = [
        'start_date' => 'date',
    ];

    public function duration()
    {
        return $this->belongsTo(ReviewDuration::class, 'review_duration_id');
    }

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function responses()
    {
        return $this->hasMany(ReviewResponse::class);
    }

    public function incrementLetter()
    {
        return $this->hasOne(IncrementLetter::class);
    }


    public function employees()
    {
        return $this->belongsToMany(\App\Models\User::class, 'performance_review_user')
            ->withPivot([
                'status',
                'employee_response',
                'responded_at',
                'hr_review',
                'hr_review_date',
                'reviewer_avg_score',
                'hr_avg_score',
                'hr_increment_percent',
                'hr_basic_percent',
                'hr_housing_percent',
                'hr_transport_percent',
                'hr_other_percent',
                'hr_incentive_percent'
            ])
            ->withTimestamps();
    }

    public function scoreCriteria()
    {
        return $this->belongsTo(ScoreCriterion::class);
    }
}
