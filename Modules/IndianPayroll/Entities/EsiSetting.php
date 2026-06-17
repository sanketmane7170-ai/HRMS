<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\IndianPayroll\Entities\Traits\LogsStatutoryChanges;

class EsiSetting extends Model
{
    use LogsStatutoryChanges;

    protected $table = 'ip_esi_settings';

    protected $fillable = [
        'effective_from', 'employee_rate', 'employer_rate',
        'wage_threshold', 'wage_threshold_disabled', 'is_active',
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
