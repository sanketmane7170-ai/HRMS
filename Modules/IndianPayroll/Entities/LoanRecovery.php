<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRecovery extends Model
{
    protected $table = 'ip_loan_recoveries';

    protected $fillable = ['loan_id', 'run_id', 'user_id', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'loan_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'run_id');
    }
}
