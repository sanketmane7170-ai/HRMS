<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $application_id
 * @property string $status
 * @property string|null $offer_letter_url
 * @property \Illuminate\Support\Carbon|null $response_deadline
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $offer_date
 * @property \Illuminate\Support\Carbon|null $joining_date
 * @property bool $is_sent
 * @property string|null $sent_at
 * @property int $days_remaining
 * @property string $deadline_color
 * @property bool $is_expired
 */
class Offer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'recruitment_offers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'position',
        'department',
        'salary',
        'salary_currency',
        'salary_period',
        'salary_type',
        'currency',
        'payment_period',
        'pay_frequency',
        'start_date',
        'joining_date',
        'status',
        'offer_letter_url',
        'terms_conditions',
        'benefits',
        'notes',
        'additional_notes',
        'offer_date',
        'response_deadline',
        'responded_at',
        'created_by',
        'updated_by',
        'pre_hire_id',
        'pre_hire_created',
        'is_foreign_national',
        'nationality',
        'visa_required',
        'probation_period_days',
        'content'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'salary' => 'decimal:2',
        'start_date' => 'date',
        'joining_date' => 'date',
        'offer_date' => 'datetime',
        'response_deadline' => 'datetime',
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($offer) {
            if (!$offer->offer_date) {
                $offer->offer_date = now();
            }
            if (!$offer->response_deadline) {
                $offer->response_deadline = now()->addDays(7); // 7 days to respond
            }
        });

        static::updating(function ($offer) {
            if ($offer->isDirty('status') && in_array($offer->status, ['accepted', 'declined'])) {
                $offer->responded_at = now();
            }
        });
    }

    // Relationships
    
    /**
     * Get the application this offer belongs to.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // Scopes
    
    /**
     * Scope for pending offers.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for accepted offers.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for declined offers.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    /**
     * Scope for expired offers.
     */
    public function scopeExpired($query)
    {
        return $query->where('response_deadline', '<', now())
                    ->where('status', 'pending');
    }

    // Accessors
    
    /**
     * Get formatted salary.
     */
    public function getFormattedSalaryAttribute(): string
    {
        return $this->salary ? number_format((float) $this->salary, 2) : 'Not specified';
    }

    /**
     * Get formatted joining date.
     */
    public function getFormattedJoiningDateAttribute(): string
    {
        return $this->joining_date ? $this->joining_date->format('d/m/Y') : 'Not specified';
    }

    /**
     * Get formatted offer date.
     */
    public function getFormattedOfferDateAttribute(): string
    {
        return $this->offer_date->format('d/m/Y H:i');
    }

    /**
     * Get formatted deadline.
     */
    public function getFormattedDeadlineAttribute(): string
    {
        return $this->response_deadline ? $this->response_deadline->format('d/m/Y H:i') : 'No deadline';
    }

    /**
     * Get formatted response date.
     */
    public function getFormattedResponseDateAttribute(): ?string
    {
        return $this->responded_at ? $this->responded_at->format('d/m/Y H:i') : null;
    }

    /**
     * Get formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucwords($this->status);
    }

    /**
     * Check if offer is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->response_deadline && 
               $this->response_deadline < now() && 
               $this->status === 'pending';
    }

    /**
     * Check if offer is pending.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending' && !$this->is_expired;
    }

    /**
     * Get days left to respond.
     */
    public function getDaysLeftAttribute(): int
    {
        if (!$this->response_deadline || $this->status !== 'pending') {
            return 0;
        }
        
        return max(0, $this->response_deadline->diffInDays(now(), false));
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => $this->is_expired ? 'warning' : 'primary',
            'accepted' => 'success',
            'declined' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }
    
}
