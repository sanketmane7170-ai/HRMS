<?php

namespace App\Models;

use App\Enums\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_name',
        'path',
        'type',
        'serial_number',
        'expiry_date',
        'place_of_issue',
        'country_name',
        'issue_date',
        'ministry_of_labor_personal_no',
        'note',
        'status'
    ];

    protected $casts = [
        'type' => Document::class
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->where('status', User::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include user having birthday this month.
     */
    public function scopeExpired(Builder $query): void
    {
        $query->whereDate('expiry_date', '<', now()->toDateString());
    }
   
    // public function getTypeAttribute($value)
    // {
    //     return $value ? Document::from($value) : Document::Other;
    // }
}
