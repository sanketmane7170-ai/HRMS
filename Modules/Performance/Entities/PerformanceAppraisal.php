<?php
namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;

class PerformanceAppraisal extends Model
{
    protected $fillable = ['employee_id', 'reviewer_id', 'period', 'appraisal_date', 'status', 'employee_comments', 'reviewer_comments','template_id'];

    protected $casts = [
        'appraisal_date' => 'date',
    ];
    public function employee()
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }

    public function criteria()
    {
        return $this->hasMany(AppraisalCriterion::class, 'appraisal_id');
    }
    public function reviewers()
    {
        return $this->hasMany(\Modules\Performance\Entities\AppraisalReviewer::class, 'appraisal_id');
    }
}

