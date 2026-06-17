<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryStructure extends Model
{
    protected $table = 'ip_employee_salary_structures';

    protected $fillable = [
        'user_id', 'template_id', 'annual_ctc', 'monthly_ctc', 'effective_from', 'effective_to', 'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'annual_ctc' => 'decimal:2',
        'monthly_ctc' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(SalaryStructureTemplate::class, 'template_id');
    }

    public function components()
    {
        return $this->hasMany(EmployeeSalaryStructureComponent::class, 'structure_id');
    }

    public function componentAmount(string $code): float
    {
        return (float) $this->components->first(
            fn (EmployeeSalaryStructureComponent $c) => $c->component->code === $code
        )?->monthly_amount ?? 0.0;
    }
}
