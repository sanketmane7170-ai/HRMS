<?php

namespace App\Models;

use App\Enums\MartialStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'personal_email', 'personal_phone','visa_category', 'visa_designation', 'visa_type',
        'date_of_birth', 'martial_status', 'country_id', 'gender',
        'linkedin_url', 'skills', 'hobbies', 'address',
    ];

    protected $casts = [
        'martial_status' => MartialStatus::class,
        //'date_of_birth' => 'date'
        'date_of_birth' => 'datetime:Y-m-d'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Scope a query to only include user having birthday this month.
     */
    public function scopeBirthdayThisMonth(Builder $query): void
    {
        $query->whereMonth('date_of_birth', date('m'));
            // ->whereDay('date_of_birth', '>=', date('d'));
    }
}
