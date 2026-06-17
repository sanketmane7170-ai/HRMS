<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reimbursement extends Model
{
    protected $table = 'ip_reimbursements';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAID = 'paid';

    public const TYPES = [
        'travel' => 'Travel Expense',
        'hotel' => 'Hotel Expense',
        'mobile' => 'Mobile Bill',
        'fuel' => 'Fuel Bill',
        'internet' => 'Internet Reimbursement',
        'medical' => 'Medical Reimbursement',
        'other' => 'Other',
    ];

    protected $fillable = [
        'user_id', 'reimbursement_type', 'claim_amount', 'taxable_amount',
        'claim_date', 'description', 'proof_path', 'status', 'run_id',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'claim_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'claim_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'run_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->reimbursement_type] ?? ucwords(str_replace('_', ' ', $this->reimbursement_type));
    }

    /** Tax-free portion of the claim. */
    public function nonTaxableAmount(): float
    {
        return round((float) $this->claim_amount - (float) $this->taxable_amount, 2);
    }
}
