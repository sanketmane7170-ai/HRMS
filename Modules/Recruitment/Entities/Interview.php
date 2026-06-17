<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Carbon\Carbon;

class Interview extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'recruitment_interviews';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'round',
        'interviewer_id',
        'scheduled_by',
        'scheduled_at',
        'duration_minutes',
        'type',
        'status',
        'location',
        'meeting_link',
        'agenda',
        'preparation_notes',
        'additional_interviewers',
        'send_reminder',
        'reminder_minutes',
        'reminder_sent_at',
        'cancellation_reason',
        'completed_at',
        'overall_rating',
        'score',
        'calendar_event_ids',
        'notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'additional_interviewers' => 'array',
        'calendar_event_ids' => 'array',
        'send_reminder' => 'boolean',
    ];

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($interview) {
            if (auth()->check() && !$interview->scheduled_by) {
                $interview->scheduled_by = auth()->id();
            }
        });
    }

    // Relationships
    
    /**
     * Get the application this interview belongs to.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the interviewer.
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    /**
     * Get the user who scheduled this interview.
     */
    public function scheduler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    /**
     * Get the interview feedback.
     */
    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InterviewFeedback::class, 'interview_id');
    }

    // Accessors & Methods
    
    /**
     * Get the interview type as human-readable text.
     */
    public function getTypeTextAttribute(): string
    {
        return match($this->type) {
            'phone' => 'Phone Interview',
            'video' => 'Video Interview',
            'in-person' => 'In-Person Interview',
            'panel' => 'Panel Interview',
            default => 'Interview'
        };
    }

    /**
     * Get the status as human-readable text.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rescheduled' => 'Rescheduled',
            'no_show' => 'No Show',
            default => 'Unknown'
        };
    }

    /**
     * Get the duration in human-readable format.
     */
    public function getDurationTextAttribute(): string
    {
        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }

    /**
     * Get the end time of the interview.
     */
    public function getEndTimeAttribute(): Carbon
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }

    /**
     * Check if the interview is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at->isFuture();
    }

    /**
     * Check if the interview is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress' || 
               ($this->status === 'scheduled' && 
                $this->scheduled_at->isPast() && 
                $this->end_time->isFuture());
    }

    /**
     * Check if the interview is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->completed_at !== null;
    }

    /**
     * Check if reminder should be sent.
     */
    public function shouldSendReminder(): bool
    {
        if (!$this->send_reminder || $this->reminder_sent_at || $this->status !== 'scheduled') {
            return false;
        }

        $reminderTime = $this->scheduled_at->subMinutes($this->reminder_minutes);
        return now()->gte($reminderTime);
    }

    /**
     * Mark reminder as sent.
     */
    public function markReminderSent(): void
    {
        $this->update(['reminder_sent_at' => now()]);
    }

    /**
     * Get all interviewers (primary + additional).
     */
    public function getAllInterviewersAttribute(): array
    {
        $interviewers = [$this->interviewer_id];
        
        if ($this->additional_interviewers) {
            $interviewers = array_merge($interviewers, $this->additional_interviewers);
        }
        
        return array_unique($interviewers);
    }

    /**
     * Check if interview conflicts with another interview.
     */
    public function hasConflict(): bool
    {
        $conflictQuery = self::where('interviewer_id', $this->interviewer_id)
            ->where('status', 'scheduled')
            ->where('id', '!=', $this->id ?? 0);

        // Check for time overlap
        $startTime = $this->scheduled_at;
        $endTime = $this->end_time;

        $conflicts = $conflictQuery->where(function ($query) use ($startTime, $endTime) {
            $query->whereBetween('scheduled_at', [$startTime, $endTime])
                  ->orWhere(function ($q) use ($startTime) {
                      $q->where('scheduled_at', '<=', $startTime)
                        ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$startTime]);
                  });
        })->exists();

        return $conflicts;
    }

    // Scopes
    
    /**
     * Scope for upcoming interviews.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '>', now());
    }

    /**
     * Scope for today's interviews.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    /**
     * Scope for interviews by interviewer.
     */
    public function scopeByInterviewer($query, int $interviewerId)
    {
        return $query->where('interviewer_id', $interviewerId);
    }

    /**
     * Scope for interviews by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for interviews by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for interviews needing reminders.
     */
    public function scopeNeedingReminders($query)
    {
        return $query->where('send_reminder', true)
                    ->whereNull('reminder_sent_at')
                    ->where('status', 'scheduled')
                    ->whereRaw('scheduled_at <= DATE_ADD(NOW(), INTERVAL reminder_minutes MINUTE)');
    }

    /**
     * Get all interviews for the same application ordered by round.
     */
    public function getAllRounds()
    {
        return self::where('application_id', $this->application_id)
                  ->orderBy('round')
                  ->get();
    }

    /**
     * Get the next round number for this application.
     */
    public static function getNextRoundNumber($applicationId)
    {
        return self::where('application_id', $applicationId)->max('round') + 1;
    }

    /**
     * Check if the previous round is completed.
     */
    public static function canScheduleNextRound($applicationId, $round = null)
    {
        if (!$round) {
            $round = self::getNextRoundNumber($applicationId);
        }
        
        if ($round <= 1) {
            return true; // First round can always be scheduled
        }
        
        // Check if previous round is completed
        $previousRound = self::where('application_id', $applicationId)
                             ->where('round', $round - 1)
                             ->where('status', 'completed')
                             ->exists();
                             
        return $previousRound;
    }

    /**
     * Get the latest completed round for this application.
     */
    public static function getLatestCompletedRound($applicationId)
    {
        return self::where('application_id', $applicationId)
                  ->where('status', 'completed')
                  ->orderBy('round', 'desc')
                  ->first();
    }

    /**
     * Get round name/label.
     */
    public function getRoundLabelAttribute()
    {
        $labels = [
            1 => 'First Round',
            2 => 'Second Round', 
            3 => 'Third Round',
            4 => 'Final Round'
        ];
        
        return $labels[$this->round] ?? "Round {$this->round}";
    }
    
}
