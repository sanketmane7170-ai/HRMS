<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeKpiAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'duration_id',
        'due_date',
        'status',
        'grade',
        'remark',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function duration()
    {
        return $this->belongsTo(ReviewDuration::class);
    }

    public function items()
    {
        return $this->hasMany(EmployeeKpiItem::class);
    }
}
