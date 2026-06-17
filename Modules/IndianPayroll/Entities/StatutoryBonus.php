<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatutoryBonus extends Model
{
    protected $table = 'ip_statutory_bonuses';

    // Payment of Bonus Act, 1965 thresholds (kept here as the single source).
    public const ELIGIBILITY_WAGE_CEILING = 21000;  // monthly basic+DA at or below which an employee is eligible
    public const CALCULATION_WAGE_CAP = 7000;        // % is applied to min(wage, 7000) (or state minimum wage if higher)
    public const MIN_PERCENTAGE = 8.33;
    public const MAX_PERCENTAGE = 20.0;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'user_id', 'financial_year', 'monthly_wage', 'bonus_wage_base',
        'percentage', 'months_eligible', 'bonus_amount', 'status',
    ];

    protected $casts = [
        'monthly_wage' => 'decimal:2',
        'bonus_wage_base' => 'decimal:2',
        'percentage' => 'decimal:2',
        'months_eligible' => 'integer',
        'bonus_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
