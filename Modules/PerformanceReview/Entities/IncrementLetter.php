<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class IncrementLetter extends Model
{
    protected $fillable = [
        'performance_review_id',
        'basic',
        'housing',
        'letter_content',
        'status',
        'generated_at'
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class);
    }
}
