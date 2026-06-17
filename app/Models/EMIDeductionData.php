<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMIDeductionData extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function emiDeduction()
    {
        return $this->belongsTo(EMIDeduction::class, 'emi_id');
    }
}
