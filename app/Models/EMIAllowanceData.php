<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMIAllowanceData extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function emiAllowance()
    {
        return $this->belongsTo(EMIAllowance::class, 'emi_id');
    }
}
