<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Query
{
    /**
     * Scope a query to only include popular users.
     */
    public function scopeMy(Builder $query): void
    {
        $query->where('user_id', auth()->id());
    }
}
