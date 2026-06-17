<?php

namespace Modules\Payroll\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTax extends Model
{
    use HasFactory;

    protected $fillable = ['taxtype', 'taxunit', 'taxamount'];
    public function employeeTaxUsers()
    {
        return $this->hasMany(EmployeeTaxUser::class);
    }

}
