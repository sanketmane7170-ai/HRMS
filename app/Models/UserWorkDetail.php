<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserWorkDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'designation_id',
        'joining_date',
        'probation_end_date',
        'company_name',
        'location',
        'work_week',
        'weekend',
        'shift_start',
        'shift_end',
        'report_to_ids',
        'medical_insurance_provided',
        'annual_premium',
        'is_rider',
        'air_ticket_setting_id',
        'salary_mode',
        'attendance_base',
        'company_accommodation',
        'accommodation_location',
        'grade',
        'air_ticket_count',
        'renewal_air_ticket',
        'approved_first_level',
        'free_document_request',
        'document_request_charge',
        'probation_month',
        'mol_number',
        'insurance_number',
        'insurance_expiry',
        'last_working_day',
        'remarks',
        'entity',

    ];

    protected $casts = [
        // 'joining_date' => 'date',
        'joining_date' => 'datetime:Y-m-d',
        'report_to_ids' => 'array',
        'insurance_expiry' => 'date',
        'last_working_day' => 'date',
        'probation_end_date' => 'date',

    ];
    protected $attributes = [
        'approved_first_level' => false,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    // protected function probationEndDate(): Attribute
    // {
    //     return Attribute::make(
    //         set: fn(mixed $value, array $attributes) =>  now()->parse($attributes['joining_date'])->addMonths(6)->toDateString(),
    //     );
    // }

    protected function probationEndDate(): Attribute
    {
        return Attribute::make(
            set: function ($value, array $attributes) {
                // If a value is explicitly provided, use it
                if (!empty($value)) {
                    return $value;
                }

                // Ensure joining_date is available
                if (!empty($attributes['joining_date'])) {
                    $joiningDate = Carbon::parse($attributes['joining_date']);

                    // Get probation period from settings
                    $probationSetting = getSetting('probation_period_month');

                    if ($probationSetting == '1_month') {
                        $months = 1;
                    } elseif ($probationSetting == '3_month') {
                        $months = 3;
                    } else {
                        $months = 6;
                    }

                    return $joiningDate->addMonths($months)->toDateString();
                }

                return null; // fallback if no joining_date
            }
        );
    }

    /**
     * Scope a query to only include user having birthday this month.
     */
    public function scopeAnniversaryThisMonth(Builder $query): void
    {
        $query->whereMonth('joining_date', date('m'))
            ->whereDay('joining_date', '>=', date('d'));
    }


    /**
     * Scope a query to only include user having birthday this month.
     */
    public function scopeProbationEndsIn(Builder $query, $days = null): void
    {
        $days = $days ?? 40;
        $query->where('probation_end_date', '<=', now()->addDays($days)->toDateString())
            ->where('probation_end_date', '>=', now()->toDateString());
    }
    public function reportmanager()
    {
        return $this->belongsTo(User::class, 'report_to_ids');
    }

    public function subordinates()
    {
        return $this->hasMany(UserWorkDetail::class, 'report_to_ids')->with('user.role', 'subordinates');
    }
}
