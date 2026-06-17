<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\IndianPayroll\Entities\Traits\LogsStatutoryChanges;

class ProfessionalTaxSlab extends Model
{
    use SoftDeletes, LogsStatutoryChanges;

    protected $table = 'ip_pt_slabs';

    protected $fillable = [
        'state_id', 'gender', 'salary_from', 'salary_to', 'monthly_tax', 'february_tax',
        'frequency', 'effective_from', 'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'is_active' => 'boolean',
    ];

    public function state()
    {
        return $this->belongsTo(IpState::class, 'state_id');
    }

    /**
     * Returns the PT amount due for the given run month.
     * When february_tax is set and the month is February (2), returns that
     * amount instead of the standard monthly_tax — handles the MH ₹300 Feb rule.
     */
    public function taxForMonth(int $month): float
    {
        if ($month === 2 && $this->february_tax !== null) {
            return (float) $this->february_tax;
        }

        return (float) $this->monthly_tax;
    }

    public static function findFor(int $stateId, float $monthlyGross, string $gender, \DateTimeInterface $date): ?self
    {
        return static::pickFrom(static::where('state_id', $stateId)->where('is_active', true)->get(), $monthlyGross, $gender, $date);
    }

    /**
     * Same matching logic as findFor(), but against an already-fetched collection —
     * lets a payroll run fetch each state's slabs ONCE and reuse them for every
     * employee in that state, instead of one query per employee per month.
     */
    public static function pickFrom(\Illuminate\Support\Collection $slabs, float $monthlyGross, string $gender, \DateTimeInterface $date): ?self
    {
        return $slabs
            ->filter(fn (self $slab) => in_array($slab->gender, [$gender, 'all'], true))
            ->filter(fn (self $slab) => (float) $slab->salary_from <= $monthlyGross)
            ->filter(fn (self $slab) => $slab->salary_to === null || (float) $slab->salary_to >= $monthlyGross)
            ->filter(fn (self $slab) => $slab->effective_from->lte($date))
            ->sortByDesc('effective_from')
            ->sortBy(fn (self $slab) => $slab->gender === 'all' ? 1 : 0) // prefer gender-specific over 'all'
            ->first();
    }
}
