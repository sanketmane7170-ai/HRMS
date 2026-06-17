<?php

namespace Modules\Performance\Entities;

use Illuminate\Database\Eloquent\Model;

class AppraisalTemplate extends Model
{
    protected $fillable = [
        'name',
        'branch_id',
        'department_id',
        'designation_id',
        'role_id',
        'company_id',
        'period_type',
        'is_active'
    ];

    public function criteria()
    {
        return $this->hasMany(
            AppraisalTemplateCriteria::class,
            'template_id'
        );
    }
    public function branch()
    {
        return $this->belongsTo(
            \App\Models\Department::class,
            'branch_id'
        );
    }
    public function department()
    {
        return $this->belongsTo(
            \App\Models\Division::class,
            'department_id'
        );
    }
}
