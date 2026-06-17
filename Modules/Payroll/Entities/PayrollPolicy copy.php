<?php

namespace Modules\Payroll\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPolicy extends Model
{
    protected $fillable = [
        'name', 'type', 'hourly_charges', 'max_hours_per_day', 'max_hours_per_month',
        'formula', 'fixed_amount', 'min_hours_per_day', 'min_hours_per_month'
    ];
    use HasFactory;
}
