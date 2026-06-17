<?php

namespace Modules\AirTicketSetting\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirTicketSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'policy_name',
        'allowance_currency',
        'allowance_amount',
        'request_after_months',
        'request_after_months_date',
        'policy_renewal_months',
        'request_limit_per_cycle',
        'allow_reimbursement',
        'allow_encashment',
        'allow_ticket_booking',
        'early_allow_ticket',
        'early_month',
        'encashment_amount',
        'request_after_from',
        'country',
    ];
}
