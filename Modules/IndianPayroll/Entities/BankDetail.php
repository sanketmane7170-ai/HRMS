<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $table = 'ip_bank_details';

    protected $fillable = [
        'user_id', 'bank_name', 'account_number', 'ifsc', 'account_type', 'account_holder_name', 'is_verified',
    ];

    protected $casts = [
        'account_number' => 'encrypted',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
