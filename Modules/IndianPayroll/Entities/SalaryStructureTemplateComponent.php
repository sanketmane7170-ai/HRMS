<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;

class SalaryStructureTemplateComponent extends Model
{
    protected $table = 'ip_salary_structure_template_components';

    public const CALC_FLAT = 'flat';
    public const CALC_PERCENTAGE_OF_BASIC = 'percentage_of_basic';
    public const CALC_PERCENTAGE_OF_CTC = 'percentage_of_ctc';
    public const CALC_REMAINDER_OF_CTC = 'remainder_of_ctc';

    protected $fillable = ['template_id', 'salary_component_id', 'calculation_type', 'value'];

    public function template()
    {
        return $this->belongsTo(SalaryStructureTemplate::class, 'template_id');
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }
}
