<?php

namespace Modules\Payroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UserBankDetail;
use Modules\Payroll\Entities\UserSalary;

class UserPaySlip extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','slip_generation_date','month_code','year','basic','total_net_salary','status','is_close','start_date','end_date'];
    
    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function bank_details(): BelongsTo
    {
        return $this->belongsTo(UserBankDetail::class,'user_id','user_id');
    }

    public function user_salary(): BelongsTo
    {
        return $this->belongsTo(UserSalary::class,'user_id','user_id');
    }

    public static function exists($userId, $month, $year)
    {
        $result = UserPaySlip::where([
            'user_id' => $userId,
            'month_code' => $month,
            'year' => $year,
        ])->exists();
        
        if($result){
            return 'true';
        } else {
            return 'false';
        }
        //print_r($result); die();
    }
}
