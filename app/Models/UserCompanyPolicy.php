<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCompanyPolicy extends Model
{
    protected $table = 'user_company_policies';

    protected $fillable = [
        'company_policy_id',
        'user_id',
        'ack_status',
        'ack_datetime',
        'ack_document'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function policy()
    {
        return $this->belongsTo(CompanyPolicy::class, 'company_policy_id');
    }
}
