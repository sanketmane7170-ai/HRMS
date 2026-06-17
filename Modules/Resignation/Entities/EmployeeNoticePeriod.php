<?php

namespace Modules\Resignation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class EmployeeNoticePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'resignation_id',
        'start_date',
        'end_date',
        'status',
        'remarks'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function resignation()
    {
        return $this->belongsTo(Resignation::class);
    }
}
