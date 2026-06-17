<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEncashment extends Model
{
    protected $table = 'ip_leave_encashments';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'user_id', 'month', 'year', 'days', 'per_day_rate', 'amount',
        'taxable_amount', 'remarks', 'status', 'run_id', 'created_by',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'days' => 'decimal:2',
        'per_day_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'run_id');
    }
}
