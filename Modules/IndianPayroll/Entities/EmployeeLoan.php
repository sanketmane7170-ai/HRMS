<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    protected $table = 'ip_employee_loans';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPES = [
        'salary_advance' => 'Salary Advance',
        'personal_loan' => 'Employee Loan',
        'emergency_loan' => 'Emergency Loan',
    ];

    protected $fillable = [
        'user_id', 'loan_type', 'principal_amount', 'emi_amount',
        'start_month', 'start_year', 'disbursed_on', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'emi_amount' => 'decimal:2',
        'start_month' => 'integer',
        'start_year' => 'integer',
        'disbursed_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recoveries(): HasMany
    {
        return $this->hasMany(LoanRecovery::class, 'loan_id');
    }

    /** Total recovered so far across every payroll run. */
    public function recoveredAmount(): float
    {
        return (float) $this->recoveries()->sum('amount');
    }

    /** Principal still owed. */
    public function outstandingBalance(): float
    {
        return round((float) $this->principal_amount - $this->recoveredAmount(), 2);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->loan_type] ?? ucwords(str_replace('_', ' ', $this->loan_type));
    }
}
