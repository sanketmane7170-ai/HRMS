<?php

namespace Modules\Payroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSalaryIncrement extends Model
{
    use HasFactory;

   

    public static function boot()
    {

       
        parent::boot();
    }

    protected $fillable = [
       'id', 'user_id', 'before_increment','increment','after_increment', 'increment_date'
    ];

  
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
