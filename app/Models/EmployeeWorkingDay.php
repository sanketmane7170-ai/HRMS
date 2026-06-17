<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWorkingDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month_code',
        'year',
        'total_working_days'
    ];
}
