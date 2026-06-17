<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'hire_date', 'departure_date', 'departure_reason_id', 'contract_type', 'total_service_duration', 'settlement_amount',
        'total_additions','total_deductions','leave_name','absent_days',
    ];
}
