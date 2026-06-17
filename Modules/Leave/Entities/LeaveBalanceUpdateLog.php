<?php

namespace Modules\Leave\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Leave\Database\factories\LeaveBalanceUpdateLogFactory;

class LeaveBalanceUpdateLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'previous_balance',
        'new_balance',
        'updated_by',
        'updated_at',
        'diff_value',
        'is_less',
        'description'
    ];
    
    protected static function newFactory(): LeaveBalanceUpdateLogFactory
    {
        //return LeaveBalanceUpdateLogFactory::new();
    }
}
