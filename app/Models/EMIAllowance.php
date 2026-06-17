<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMIAllowance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class)->where('status', User::STATUS_ACTIVE);
    }

    public function emiData()
    {
        return $this->hasMany(EMIAllowanceData::class,'emi_id');
    }
}
