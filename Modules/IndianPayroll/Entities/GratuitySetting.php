<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\IndianPayroll\Entities\Traits\LogsStatutoryChanges;

class GratuitySetting extends Model
{
    use LogsStatutoryChanges;

    protected $table = 'ip_gratuity_settings';

    protected $fillable = [
        'effective_from', 'exemption_ceiling', 'days_per_year_first_slab',
        'divisor_days_per_month', 'minimum_vesting_years', 'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'is_active' => 'boolean',
    ];

    public static function effectiveAsOf(\DateTimeInterface $date): ?self
    {
        return static::where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->first();
    }
}
