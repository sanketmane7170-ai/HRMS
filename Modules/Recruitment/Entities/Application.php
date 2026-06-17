<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use Carbon\Carbon;

class Application extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'recruitment_applications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'job_id',
        'user_id',
        'candidate_email',
        'candidate_name',
        'candidate_phone',
        'linkedin_url',
        'portfolio_url',
        'resume_path',
        'resume_url',
        'cover_letter',
        'expected_salary',
        'availability_date',
        'years_experience',
        'current_company',
        'current_position',
        'current_salary',
        'notice_period',
        'willing_to_relocate',
        'authorization_to_work',
        'stage',
        'current_stage', // Author: Sanket - Added to allow mass assignment
        'score',
        'applied_on',
        'source',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'score' => 'decimal:2',
        'applied_on' => 'datetime',
        'applied_at' => 'datetime',
        'availability_date' => 'date',
        'expected_salary' => 'decimal:2',
        'years_experience' => 'integer',
        'notice_period' => 'integer',
        'willing_to_relocate' => 'boolean',
        'authorization_to_work' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($application) {
            if (!$application->applied_on) {
                $application->applied_on = now();
            }
        });

        // Log stage changes
        static::updating(function ($application) {
            if ($application->isDirty('stage')) {
                $original = $application->getOriginal('stage');
                
                // Get a valid user ID for logging
                $changedBy = auth()->id();
                if (!$changedBy) {
                    // Get the first available admin/hr user as fallback
                    $fallbackUser = \App\Models\User::whereHas('roles', function($query) {
                        $query->whereIn('name', ['admin', 'hr']);
                    })->first();
                    $changedBy = $fallbackUser ? $fallbackUser->id : null;
                }
                
                if ($changedBy) {
                    ApplicationLog::create([
                        'application_id' => $application->id,
                        'previous_stage' => $original,
                        'new_stage' => $application->stage,
                        'action' => 'stage_changed',
                        'changed_by' => $changedBy,
                        'description' => "Stage changed from {$original} to {$application->stage}",
                        'created_at' => now() // Fixed by Sanket
                    ]);
                }
            }
        });

        // Cascading Delete to prevent orphaned data
        static::deleting(function ($application) {
            // 1. Delete Resume File
            if ($application->resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($application->resume_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($application->resume_path);
            }

            // 2. Delete Related Records
            $application->logs()->delete();
            $application->interviews()->delete();
            $application->offers()->delete();
            $application->candidateScores()->delete();
            $application->interviewFeedback()->delete();
        });
    }

    // Relationships
    
    /**
     * Get the job this application belongs to.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the user who applied.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all logs for this application.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ApplicationLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Accessor for current_stage to map to stage column
     */
    public function getCurrentStageAttribute()
    {
        return $this->stage;
    }

    /**
     * Get all interviews for this application.
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class)->orderBy('scheduled_at', 'desc');
    }

    /**
     * Get the latest interview.
     */
    public function latestInterview(): HasOne
    {
        return $this->hasOne(Interview::class)->latestOfMany('scheduled_at');
    }


    /**
     * Get the offer for this application.
     */
    public function offer(): HasOne
    {
        return $this->hasOne(Offer::class);
    }

    /**
     * Get all offers for this application.
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all candidate scores for this application.
     */
    public function candidateScores(): HasMany
    {
        return $this->hasMany(CandidateScore::class)->orderBy('interview_round', 'desc');
    }

    /**
     * Get the final candidate score for this application.
     */
    public function finalScore(): HasOne
    {
        return $this->hasOne(CandidateScore::class)->where('is_final_score', true);
    }

    /**
     * Get the latest candidate score.
     */
    public function latestScore(): HasOne
    {
        return $this->hasOne(CandidateScore::class)->latestOfMany('scored_at');
    }

    /**
     * Get all interview feedback for this application.
     */
    public function interviewFeedback(): HasMany
    {
        return $this->hasMany(InterviewFeedback::class)->orderBy('interview_round', 'desc');
    }

    /**
     * Get completed interview feedback only.
     */
    public function completedInterviewFeedback(): HasMany
    {
        return $this->hasMany(InterviewFeedback::class)
            ->where('status', 'completed')
            ->orderBy('interview_round', 'desc');
    }

    // Scopes
    
    /**
     * Scope by stage.
     */
    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Scope for recent applications.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('applied_on', '>=', now()->subDays($days));
    }

    // Accessors
    
    /**
     * Get formatted stage.
     */
    public function getFormattedStageAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->stage));
    }

    /**
     * Get formatted applied date.
     */
    public function getFormattedAppliedDateAttribute(): string
    {
        return $this->applied_on->format('d/m/Y');
    }

    /**
     * Get days since applied.
     */
    public function getDaysSinceAppliedAttribute(): int
    {
        return $this->applied_on->diffInDays(now());
    }

 /**
     * Get the publicly accessible URL for the resume.
     * Checks both resume_url and resume_path fields.
     */
    public function getResumeUrlAttribute(): ?string
    {
        // FIX: Access raw attributes to avoid infinite recursion
        $resumeUrl = $this->attributes['resume_url'] ?? null;
        $resumePath = $this->attributes['resume_path'] ?? null;
        // If resume_url is an external link, return it
        if ($resumeUrl && (str_starts_with($resumeUrl, 'http://') || str_starts_with($resumeUrl, 'https://'))) {
            return $resumeUrl;
        }
        // If resume_path exists, generate storage URL
        if ($resumePath) {
            return \Illuminate\Support\Facades\Storage::url($resumePath);
        }
        // Fallback: if resume_url has a path-like string (backward compatibility)
        if ($resumeUrl) {
            return \Illuminate\Support\Facades\Storage::url($resumeUrl);
        }
        return null;
    }

    /**
     * Check if application is in progress.
     */
    public function getInProgressAttribute(): bool
    {
        return !in_array($this->stage, ['hired', 'rejected']);
    }

    /**
     * Get stage color for UI.
     */
    public function getStageColorAttribute(): string
    {
        $colors = [
            'applied' => 'primary',
            'screening' => 'info',
            'shortlisted' => 'warning',
            'interview' => 'secondary',
            'offer' => 'success',
            'hired' => 'success',
            'rejected' => 'danger',
            'offer_declined' => 'danger', // Author: Sanket - Added color for offer_declined
            'withdrawn' => 'secondary' // Author: Sanket - Added color for withdrawn
        ];

        return $colors[$this->stage] ?? 'secondary';
    }
    
    /**
     * Get stage attribute (maps to stage column).
     */
    public function getStageAttribute(): ?string
    {
        return $this->attributes['stage'] ?? null;
    }
    
    /**
     * Set stage attribute (maps to stage column).
     */
    public function setStageAttribute($value): void
    {
        $this->attributes['stage'] = $value;
    }
    
    /**
     * Get applied_at attribute (maps to applied_on).
     */
    public function getAppliedAtAttribute()
    {
        return $this->applied_on;
    }
    
    /**
     * Set applied_at attribute (maps to applied_on).
     */
    public function setAppliedAtAttribute($value): void
    {
        $this->attributes['applied_on'] = $value;
    }
    
    /**
     * Get score attribute from the latest candidate score.
     */
    public function getScoreAttribute(): ?float
    {
        // Return the overall score from the latest scoring or final score
        $finalScore = $this->finalScore;
        if ($finalScore) {
            return (float) $finalScore->overall_score;
        }

        $latestScore = $this->latestScore;
        return $latestScore ? (float) $latestScore->overall_score : null;
    }

    /**
     * Get the average score across all scoring rounds.
     */
    public function getAverageScoreAttribute(): ?float
    {
        $scores = $this->candidateScores()->pluck('overall_score');
        
        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 2);
    }

    /**
     * Get the recommendation from the latest scoring.
     */
    public function getRecommendationAttribute(): ?string
    {
        $finalScore = $this->finalScore;
        if ($finalScore) {
            return $finalScore->recommendation;
        }

        $latestScore = $this->latestScore;
        return $latestScore ? $latestScore->recommendation : null;
    }
    
    /**
     * Get all available application stages.
     */
    public static function getStages()
    {
        return [
            'applied' => 'Applied',
            'screening' => 'Screening',
            'shortlisted' => 'Shortlisted',
            'interview' => 'Interview',
            'offer' => 'Offer',
            'hired' => 'Hired',
            'rejected' => 'Rejected',
            'offer_declined' => 'Offer Declined', // Author: Sanket - Added missing stage
            'withdrawn' => 'Withdrawn' // Author: Sanket - Added missing stage
        ];
    }
    
}
