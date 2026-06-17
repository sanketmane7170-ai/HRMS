<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeEntry extends Model
{
    protected $table = 'ip_overtime_entries';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PAID = 'paid';

    public const TYPES = [
        'overtime' => 'Overtime',
        'comp_off' => 'Comp-off Payout',
    ];

    protected $fillable = [
        'user_id', 'month', 'year', 'entry_type', 'hours', 'rate_per_unit',
        'amount', 'remarks', 'status', 'run_id', 'created_by',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'hours' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'amount' => 'decimal:2',
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
        return self::TYPES[$this->entry_type] ?? ucwords(str_replace('_', ' ', $this->entry_type));
    }
}
