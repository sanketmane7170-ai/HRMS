<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shifts extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','shift_start','shift_end','type','created_by','department_id','is_weekend'
    ];

    public function getFormattedShiftTypeAttribute()
    {
        return $this->type == 'SS' ? 'Single' : 'Multiple';
    }
}
