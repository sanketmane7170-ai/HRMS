<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','shift_start','shift_end','type','shift_id'
    ];

    public function getFormattedShiftTypeAttribute()
    {
        return $this->type == 'SS' ? 'Single' : 'Multiple';
    }

    public function shift()
    {
        return $this->belongsTo(Shifts::class, 'shift_id');
    }
}
