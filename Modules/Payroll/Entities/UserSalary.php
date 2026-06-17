<?php

namespace Modules\Payroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Payroll\Database\factories\UserSalaryFactory;

class UserSalary extends Model
{
    use HasFactory;

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    public static function boot()
    {

        static::creating(function ($model) {
            //$model->gross = $model->calculateGrossSalary();
            $model->gross = 0;
        });

        static::updating(function ($model) {
            //$model->gross = $model->calculateGrossSalary();
            $model->gross = 0;
        });

        parent::boot();
    }

    protected $fillable = [
        'user_id', 'basic', 'hra', 'food_allowance', 'travel_allowance', 'other_allowance', 'gross','total_working_days','fixed_allowances','fixed_deductions'
    ];

    protected static function newFactory()
    {
        return UserSalaryFactory::new();
    }


    public function calculateGrossSalary(): float
    {
        return ($this->basic + $this->hr + $this->food_allowance + $this->travel_allowance + $this->other_allowance);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
