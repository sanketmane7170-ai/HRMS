<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;

class SalaryStructureTemplate extends Model
{
    protected $table = 'ip_salary_structure_templates';

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function components()
    {
        return $this->hasMany(SalaryStructureTemplateComponent::class, 'template_id');
    }
}
