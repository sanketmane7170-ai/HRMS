<?php

namespace Modules\Payroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOvertime extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','overtime_type','rate_per_hour','hours','salary_id','user_id','calculated_amount','date','month_code','year','is_system_add'];
    
    protected static function newFactory()
    {
        return \Modules\Payroll\Database\factories\UserOvertimeFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
