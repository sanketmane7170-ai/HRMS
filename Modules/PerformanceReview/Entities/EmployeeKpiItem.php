<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeKpiItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_kpi_assignment_id',
        'key_performance_indicator_id',
        'self_score',
        'self_remarks',
        'avg_score',
    ];

    public function assignment()
    {
        return $this->belongsTo(EmployeeKpiAssignment::class, 'employee_kpi_assignment_id');
    }

    public function kpi()
    {
        return $this->belongsTo(KeyPerformanceIndicator::class, 'key_performance_indicator_id');
    }
    public function reviews()
    {
        return $this->hasMany(EmployeeKpiItemReview::class);
    }
}
