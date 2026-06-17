<?php

namespace Modules\Resignation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Resignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'manager_id',
        'applied_date',
        'preferred_last_working_date',
        'approved_last_working_date',
        'notice_period_days',
        'reason',
        'comments',
        'status',
        'signed_document'
    ];

    protected $casts = [
        'applied_date' => 'date',
        'preferred_last_working_date' => 'date',
        'approved_last_working_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function actions()
    {
        return $this->hasMany(ResignationAction::class);
    }

    public function noticePeriod()
    {
        return $this->hasOne(EmployeeNoticePeriod::class);
    }
}
