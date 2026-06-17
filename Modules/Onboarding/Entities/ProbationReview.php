<?php

namespace Modules\Onboarding\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProbationReview extends Model
{
    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'cycle_number',
        'scheduled_date',
        'completed_at',
        'status', // pending, submitted, completed
        'performance_score',
        'recommendation', // confirmed, extended, terminated
        'manager_comments',
        'employee_comments',
        'hr_comments',
        'option_to_extend_duration_months',
        'metadata'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
