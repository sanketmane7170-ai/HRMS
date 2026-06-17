<?php

namespace Modules\Payroll\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EmployeeTaxUser extends Model 
{
    protected $fillable = ['user_id', 'employee_tax_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeeTax() 
    {
        return $this->belongsTo(EmployeeTax::class);
    }
}
