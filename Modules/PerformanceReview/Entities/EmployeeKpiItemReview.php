<?php

namespace Modules\PerformanceReview\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeKpiItemReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_kpi_item_id',
        'step_number',
        'reviewer_id',
        'reviewer_score',
        'reviewer_remarks',
    ];

    public function kpiItem()
    {
        return $this->belongsTo(EmployeeKpiItem::class, 'employee_kpi_item_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }
}
