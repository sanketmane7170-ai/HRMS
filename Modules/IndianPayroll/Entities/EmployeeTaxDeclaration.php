<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmployeeTaxDeclaration extends Model
{
    protected $table = 'ip_employee_tax_declarations';

    protected $fillable = [
        'user_id', 'financial_year', 'regime_choice', 'income_from_previous_employer',
        'tds_deducted_by_previous_employer', 'regime_locked_at',
    ];

    protected $casts = [
        'regime_locked_at' => 'datetime',
        'income_from_previous_employer' => 'decimal:2',
        'tds_deducted_by_previous_employer' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investmentDeclarations()
    {
        return $this->hasMany(InvestmentDeclaration::class, 'declaration_id');
    }

    public function hraExemptionInput()
    {
        return $this->hasOne(HraExemptionInput::class, 'declaration_id');
    }

    public function isRegimeLocked(): bool
    {
        return ! is_null($this->regime_locked_at);
    }
}
