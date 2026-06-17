<?php

namespace Modules\Recruitment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringCriterion extends Model
{
    use HasFactory;

    protected $table = 'scoring_criteria';

    protected $fillable = [
        'candidate_score_id',
        'criterion_id',
        'criterion_name',
        'score',
        'weight',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Get the candidate score this criterion belongs to.
     */
    public function candidateScore(): BelongsTo
    {
        return $this->belongsTo(CandidateScore::class);
    }

    /**
     * Get the weighted score for this criterion.
     */
    public function getWeightedScoreAttribute(): float
    {
        return round($this->score * $this->weight, 2);
    }

    /**
     * Scope to get criteria by criterion ID.
     */
    public function scopeByCriterionId($query, string $criterionId)
    {
        return $query->where('criterion_id', $criterionId);
    }

    /**
     * Scope to get criteria with minimum score.
     */
    public function scopeMinimumScore($query, int $minScore)
    {
        return $query->where('score', '>=', $minScore);
    }

    /**
     * Common scoring criteria templates.
     */
    public static function getCommonCriteria(): array
    {
        return [
            [
                'criterion_id' => 'technical_skills',
                'criterion_name' => 'Technical Skills',
                'weight' => 0.3,
                'description' => 'Candidate\'s technical competency and expertise'
            ],
            [
                'criterion_id' => 'problem_solving',
                'criterion_name' => 'Problem Solving',
                'weight' => 0.25,
                'description' => 'Ability to analyze and solve complex problems'
            ],
            [
                'criterion_id' => 'communication',
                'criterion_name' => 'Communication Skills',
                'weight' => 0.2,
                'description' => 'Verbal and written communication effectiveness'
            ],
            [
                'criterion_id' => 'cultural_fit',
                'criterion_name' => 'Cultural Fit',
                'weight' => 0.15,
                'description' => 'Alignment with company values and culture'
            ],
            [
                'criterion_id' => 'leadership',
                'criterion_name' => 'Leadership Potential',
                'weight' => 0.1,
                'description' => 'Demonstrated or potential leadership abilities'
            ]
        ];
    }

    /**
     * Get criteria for specific job roles.
     */
    public static function getCriteriaByRole(string $role): array
    {
        $baseCriteria = self::getCommonCriteria();

        return match(strtolower($role)) {
            'developer', 'engineer' => array_merge($baseCriteria, [
                [
                    'criterion_id' => 'code_quality',
                    'criterion_name' => 'Code Quality',
                    'weight' => 0.2,
                    'description' => 'Quality and maintainability of code'
                ]
            ]),
            'manager', 'lead' => array_merge($baseCriteria, [
                [
                    'criterion_id' => 'team_management',
                    'criterion_name' => 'Team Management',
                    'weight' => 0.25,
                    'description' => 'Ability to lead and manage teams effectively'
                ]
            ]),
            'sales' => array_merge($baseCriteria, [
                [
                    'criterion_id' => 'sales_skills',
                    'criterion_name' => 'Sales Ability',
                    'weight' => 0.3,
                    'description' => 'Sales techniques and customer relationship skills'
                ]
            ]),
            default => $baseCriteria
        };
    }
}