<?php

namespace Modules\PerformanceReview\Entities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\PerformanceReview\Entities\ReviewDuration;

class KeyPerformanceIndicator extends Model
{
    use HasFactory;

    protected $fillable = ['duration_id', 'title', 'description'];

    public function duration()
    {
        return $this->belongsTo(ReviewDuration::class);
    }
}
