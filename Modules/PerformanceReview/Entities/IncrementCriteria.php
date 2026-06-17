<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class IncrementCriteria extends Model
{
    protected $fillable = [
        'label',
        'min_score',
        'max_score',
        'increment_percent',
        'basic_percent',
        'housing_percent',
        'transport_percent',
        'other_percent',
        // 'incentive_percent',
    ];
    protected $table = 'increment_criteria';
}
