<?php

namespace Modules\Onboarding\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ComplianceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ohc_status', // pending, applied, issued
        'ohc_expiry_date',
        'ohc_file',
        'food_safety_training_status', // pending, assigned, passed
        'training_completion_date',
        'certificate_file',
    ];

    protected $casts = [
        'ohc_expiry_date' => 'date',
        'training_completion_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
