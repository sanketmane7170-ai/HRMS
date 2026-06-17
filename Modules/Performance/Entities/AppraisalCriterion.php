<?php

namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;

class AppraisalCriterion extends Model
{
    protected $fillable = ['appraisal_id', 'criteria_name', 'weight', 'score', 'self_score','template_criteria_id'];


    public function appraisal() {
        return $this->belongsTo(PerformanceAppraisal::class);
    }
}
