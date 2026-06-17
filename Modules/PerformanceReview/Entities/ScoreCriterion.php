<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class ScoreCriterion extends Model
{
    protected $fillable = [
        'title', 'min_score', 'max_score', 'description'
    ];
}
