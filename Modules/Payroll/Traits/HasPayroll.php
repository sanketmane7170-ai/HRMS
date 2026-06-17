<?php

namespace Modules\Payroll\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Payroll\Entities\UserSalary;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Payroll\Entities\UserPaySlip;

trait HasPayroll
{
    public function salaries(): HasMany
    {
        return $this->hasMany(UserSalary::class);
    }

    public function salary(): HasOne
    {
        return $this->hasOne(UserSalary::class)->where('status', UserSalary::ACTIVE);
    }

    public function allowance(): HasOne
    {
        return $this->hasOne(UserSalaryAllowance::class);
    }

    public function overtime(): HasMany
    {
        return $this->hasMany(UserOvertime::class);
    }

    public function deduction(): HasMany
    {
        return $this->hasMany(UserDeduction::class);
    }

    public function payslip(): HasMany
    {
        return $this->hasMany(UserPaySlip::class);
    }
}
