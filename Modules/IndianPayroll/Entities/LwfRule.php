<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\IndianPayroll\Entities\Traits\LogsStatutoryChanges;

class LwfRule extends Model
{
    use SoftDeletes, LogsStatutoryChanges;

    protected $table = 'ip_lwf_rules';

    protected $fillable = [
        'state_id', 'frequency', 'due_months', 'employee_contribution', 'employer_contribution',
        'wage_ceiling', 'effective_from', 'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'is_active' => 'boolean',
        'due_months' => 'array',
    ];

    /**
     * Returns the calendar months in which this LWF rule's payment falls due.
     * For monthly-frequency rules every month is a due month.
     * For half-yearly/annual rules, falls back to the due_months column;
     * if that is also null (legacy rows), defaults to [6, 12] (Jun/Dec) as the
     * most common convention so old data doesn't silently deduct every month.
     *
     * @return int[]
     */
    public function dueMonths(): array
    {
        if ($this->frequency === 'monthly') {
            return range(1, 12);
        }

        return $this->due_months ?? [6, 12];
    }

    public function state()
    {
        return $this->belongsTo(IpState::class, 'state_id');
    }

    public static function findFor(int $stateId, \DateTimeInterface $date): ?self
    {
        return static::where('state_id', $stateId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->first();
    }
}
