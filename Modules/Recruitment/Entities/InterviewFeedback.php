<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewFeedback extends Model
{
    use HasFactory;

    protected $table = 'interview_feedback';

    protected $fillable = [
        'interview_id',
        'application_id',
        'interviewer_id',
        'interview_date',
        'interview_type',
        'interview_round',
        'duration_minutes',
        'status',
        'questions_asked',
        'candidate_responses',
        'interviewer_observations',
        'technical_assessment',
        'skills_demonstrated',
        'concerns_raised',
        'positive_highlights',
        'recommendation',
        'recommendation_reason',
        'overall_rating',
        'candidate_showed_up',
        'candidate_on_time',
        'follow_up_actions',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'interview_date' => 'datetime',
        'skills_demonstrated' => 'array',
        'candidate_showed_up' => 'boolean',
        'candidate_on_time' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the interview this feedback belongs to.
     */
    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    /**
     * Get the application this feedback belongs to.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the interviewer who provided this feedback.
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'interviewer_id');
    }

    /**
     * Get the interview type as a human-readable string.
     */
    public function getInterviewTypeTextAttribute(): string
    {
        return match($this->interview_type) {
            'phone_screening' => 'Phone Screening',
            'technical' => 'Technical Interview',
            'behavioral' => 'Behavioral Interview',
            'panel' => 'Panel Interview',
            'final' => 'Final Interview',
            'cultural_fit' => 'Cultural Fit Interview',
            default => 'Interview'
        };
    }

    /**
     * Get the status as a human-readable string.
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rescheduled' => 'Rescheduled',
            default => 'Unknown'
        };
    }

    /**
     * Get the recommendation as a human-readable string.
     */
    public function getRecommendationTextAttribute(): string
    {
        return match($this->recommendation) {
            'hire' => 'Hire',
            'reject' => 'Reject',
            'next_round' => 'Next Round',
            'hold' => 'Hold',
            default => 'Unknown'
        };
    }

    /**
     * Get duration in human-readable format.
     */
    public function getDurationTextAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'N/A';
        }

        $hours = intval($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }

    /**
     * Check if the interview was completed successfully.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' && $this->completed_at !== null;
    }

    /**
     * Check if the candidate was punctual and showed up.
     */
    public function candidateWasProfessional(): bool
    {
        return $this->candidate_showed_up && $this->candidate_on_time;
    }

    /**
     * Scope to get feedback for a specific application.
     */
    public function scopeForApplication($query, int $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    /**
     * Scope to get completed interviews only.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get feedback by interview type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('interview_type', $type);
    }

    /**
     * Scope to get feedback by round.
     */
    public function scopeByRound($query, int $round)
    {
        return $query->where('interview_round', $round);
    }

    /**
     * Scope to get feedback by recommendation.
     */
    public function scopeByRecommendation($query, string $recommendation)
    {
        return $query->where('recommendation', $recommendation);
    }

    /**
     * Scope to get feedback by interviewer.
     */
    public function scopeByInterviewer($query, int $interviewerId)
    {
        return $query->where('interviewer_id', $interviewerId);
    }
}