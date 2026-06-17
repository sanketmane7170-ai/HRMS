<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentAllowance extends Model
{
    protected $fillable = [
        'department_id',
        'allowance_name',
        'type',           // monthly, yearly, one_time
        'allowance_type', // fixed, percentage
        'amount',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
