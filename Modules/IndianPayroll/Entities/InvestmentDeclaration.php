<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InvestmentDeclaration extends Model
{
    protected $table = 'ip_investment_declarations';

    protected $fillable = [
        'declaration_id', 'section_code', 'declared_amount', 'proof_path',
        'verified_amount', 'verified_by', 'verified_at', 'status',
    ];

    protected $casts = [
        'declared_amount' => 'decimal:2',
        'verified_amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function declaration()
    {
        return $this->belongsTo(EmployeeTaxDeclaration::class, 'declaration_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * The amount the tax engine should actually apply — verified amount once HR has
     * checked proofs, otherwise the lower of (declared, section cap) as a conservative
     * default so an employee's monthly TDS isn't under-deducted before verification.
     */
    public function effectiveAmount(): float
    {
        $amount = ($this->status === 'verified' && ! is_null($this->verified_amount))
            ? (float) $this->verified_amount
            : (float) $this->declared_amount;

        $cap = config('indianpayroll.investment_sections.'.$this->section_code.'.cap');

        return $cap ? min($amount, (float) $cap) : $amount;
    }
}
