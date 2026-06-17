<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMIDeduction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class)->where('status', User::STATUS_ACTIVE);
    }

    public function emiData()
    {
        return $this->hasMany(EMIDeductionData::class,'emi_id');
    }
}
