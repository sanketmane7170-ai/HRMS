<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FullFinalSettlement extends Model
{
    protected $table = 'ip_full_final_settlements';

    protected $fillable = [
        'user_id', 'last_working_day', 'pending_salary_amount', 'gratuity_amount',
        'gratuity_taxable_amount', 'leave_encashment_amount', 'leave_encashment_taxable_amount',
        'notice_pay_recovery', 'other_deductions', 'final_tds', 'net_payable',
        'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'last_working_day' => 'date',
        'approved_at' => 'datetime',
        'pending_salary_amount' => 'decimal:2',
        'gratuity_amount' => 'decimal:2',
        'gratuity_taxable_amount' => 'decimal:2',
        'leave_encashment_amount' => 'decimal:2',
        'leave_encashment_taxable_amount' => 'decimal:2',
        'notice_pay_recovery' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'final_tds' => 'decimal:2',
        'net_payable' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
