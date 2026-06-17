<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $table = 'ip_employee_profiles';

    protected $fillable = [
        'user_id', 'pan', 'aadhaar', 'uan', 'pf_number', 'esi_number', 'pt_enrollment_number',
        'state_id', 'pf_applicable', 'pf_voluntary_above_ceiling', 'esi_applicable',
        'pt_applicable', 'lwf_applicable', 'date_of_joining', 'date_of_exit', 'exit_reason', 'gender',
        'employment_type',
    ];

    protected $casts = [
        'aadhaar' => 'encrypted',
        'pf_applicable' => 'boolean',
        'pf_voluntary_above_ceiling' => 'boolean',
        'esi_applicable' => 'boolean',
        'pt_applicable' => 'boolean',
        'lwf_applicable' => 'boolean',
        'date_of_joining' => 'date',
        'date_of_exit' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function state()
    {
        return $this->belongsTo(IpState::class, 'state_id');
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class, 'user_id', 'user_id');
    }

    /**
     * Completed years of continuous service as of a given date — the basis for gratuity vesting.
     */
    public function completedYearsOfService(\DateTimeInterface $asOf = null): int
    {
        $asOf = $asOf ? \Carbon\Carbon::parse($asOf) : now();

        return (int) $this->date_of_joining->diffInYears($asOf);
    }

    public function isGratuityEligible(\DateTimeInterface $asOf = null): bool
    {
        if (in_array($this->exit_reason, ['death', 'disablement'])) {
            return true;
        }

        return $this->completedYearsOfService($asOf) >= 5;
    }
}
