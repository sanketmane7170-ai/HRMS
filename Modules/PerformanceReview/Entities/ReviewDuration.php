<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;

class ReviewDuration extends Model
{
    protected $fillable = ['label', 'months'];

    public function reviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }
}
