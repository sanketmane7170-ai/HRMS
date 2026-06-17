<?php

namespace Modules\Onboarding\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Onboarding\Database\factories\OnboardingRecordFactory;

class OnboardingRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'user_id',
        'full_name',
        'email',
        'department_id',
        'joining_date',
        'status',
        'progress_percent',
        'division_id', // Added by Sanket
    ];
    
    protected static function newFactory(): OnboardingRecordFactory
    {
        //return OnboardingRecordFactory::new();
    }

    /**
     * Relationship with Department model.
     * Added by Sanket.
     */
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    /**
     * Relationship with Division model.
     * Added by Sanket (Maps to 'Department' in UI).
     */
    public function division()
    {
        return $this->belongsTo(\App\Models\Division::class, 'division_id');
    }

    /**
     * Relationship with User model.
     * Added by Sanket (ONB-SEC-008) for authorization checks.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
