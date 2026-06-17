<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $table = 'ip_payroll_runs';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_COMPUTING = 'computing'; // set while the queued job is running
    public const STATUS_COMPUTED = 'computed';
    public const STATUS_FAILED = 'failed';       // job failed — inspect compute_error for details
    public const STATUS_APPROVED = 'approved';
    public const STATUS_LOCKED = 'locked';

    protected $fillable = [
        'month', 'year', 'period_start', 'period_end', 'status',
        'compute_error',
        'created_by', 'approved_by', 'approved_at', 'locked_by', 'locked_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'run_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_COMPUTED, self::STATUS_FAILED]);
    }

    public function isComputing(): bool
    {
        return $this->status === self::STATUS_COMPUTING;
    }
}
