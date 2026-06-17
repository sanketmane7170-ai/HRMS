<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;

class PayslipComponent extends Model
{
    protected $table = 'ip_payslip_components';

    protected $fillable = ['payslip_id', 'salary_component_id', 'label', 'is_manual', 'type', 'amount'];

    protected $casts = ['amount' => 'decimal:2', 'is_manual' => 'boolean'];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class, 'payslip_id');
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }
}
