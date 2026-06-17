<?php

namespace Modules\Attendance\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use HasFactory;

    const RECURRING = 1;
    const NOT_RECURRING = 0;
    protected $fillable = [
        'created_by_id', 'start_date', 'end_date', 'detail', 'is_recurring'
    ];

    public static function boot()
    {
        static::creating(function ($attendance) {
            if (php_sapi_name() != 'cli') {
                $attendance->created_by_id = auth()->id();
            }
        });
        parent::boot();
    }
}
