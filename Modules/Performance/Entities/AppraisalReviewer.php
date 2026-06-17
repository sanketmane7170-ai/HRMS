<?php

// Modules/Performance/Entities/AppraisalReviewer.php
namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;

class AppraisalReviewer extends Model
{
    protected $fillable = ['appraisal_id', 'reviewer_id', 'level', 'status'];

    public function appraisal()
    {
        return $this->belongsTo(PerformanceAppraisal::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }
}
