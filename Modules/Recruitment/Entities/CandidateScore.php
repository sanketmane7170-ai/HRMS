<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'scored_by',
        'overall_score',
        'scoring_method',
        'interviewer_notes',
        'strengths',
        'weaknesses',
        'recommendation',
        'recommendation_notes',
        'cultural_fit_score',
        'technical_skills_score',
        'communication_score',
        'leadership_potential_score',
        'problem_solving_score',
        'average_component_score',
        'recommendation_weight',
        'is_final_score',
        'next_steps',
        'interview_round',
        'interview_type',
        'scored_at',
    ];

    protected $casts = [
        'strengths' => 'array',
        'weaknesses' => 'array',
        'overall_score' => 'decimal:2',
        'average_component_score' => 'decimal:2',
        'is_final_score' => 'boolean',
        'scored_at' => 'datetime',
    ];

    /**
     * Get the application that this score belongs to.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the user who scored the candidate.
     */
    public function scorer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'scored_by');
    }

    /**
     * Get the scoring criteria for this score.
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(ScoringCriterion::class);
    }

    /**
     * Calculate the weighted average score from criteria.
     */
    public function calculateWeightedScore(): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($this->criteria as $criterion) {
            $totalScore += $criterion->score * $criterion->weight;
            $totalWeight += $criterion->weight;
        }

        return $totalWeight > 0 ? round(($totalScore / $totalWeight) * 10, 2) : 0;
    }

    /**
     * Get the recommendation as a human-readable string.
     */
    public function getRecommendationTextAttribute(): string
    {
        return match($this->recommendation) {
            'strongly_recommend' => 'Strongly Recommend',
            'recommend' => 'Recommend',
            'neutral' => 'Neutral',
            'not_recommend' => 'Not Recommend',
            'strongly_not_recommend' => 'Strongly Not Recommend',
            default => 'Unknown'
        };
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
     * Scope to get scores for a specific application.
     */
    public function scopeForApplication($query, int $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    /**
     * Scope to get final scores only.
     */
    public function scopeFinalScores($query)
    {
        return $query->where('is_final_score', true);
    }

    /**
     * Scope to get scores by interview round.
     */
    public function scopeByRound($query, int $round)
    {
        return $query->where('interview_round', $round);
    }

    /**
     * Scope to get scores by recommendation.
     */
    public function scopeByRecommendation($query, string $recommendation)
    {
        return $query->where('recommendation', $recommendation);
    }
}