<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;

class HraExemptionInput extends Model
{
    protected $table = 'ip_hra_exemption_inputs';

    protected $fillable = [
        'declaration_id', 'monthly_rent', 'is_metro', 'landlord_pan', 'landlord_name',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'is_metro' => 'boolean',
    ];

    public function declaration()
    {
        return $this->belongsTo(EmployeeTaxDeclaration::class, 'declaration_id');
    }
}
