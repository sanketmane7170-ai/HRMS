<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryStructureComponent extends Model
{
    protected $table = 'ip_employee_salary_structure_components';

    protected $fillable = ['structure_id', 'salary_component_id', 'monthly_amount', 'annual_amount'];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'annual_amount' => 'decimal:2',
    ];

    public function structure()
    {
        return $this->belongsTo(EmployeeSalaryStructure::class, 'structure_id');
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }
}
