<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;

class IpState extends Model
{
    protected $table = 'ip_states';

    protected $fillable = [
        'name', 'code', 'region_type', 'pt_applicable', 'lwf_applicable', 'is_active',
    ];

    protected $casts = [
        'pt_applicable' => 'boolean',
        'lwf_applicable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employeeProfiles()
    {
        return $this->hasMany(EmployeeProfile::class, 'state_id');
    }

    public function ptSlabs()
    {
        return $this->hasMany(ProfessionalTaxSlab::class, 'state_id');
    }

    public function lwfRules()
    {
        return $this->hasMany(LwfRule::class, 'state_id');
    }
}
