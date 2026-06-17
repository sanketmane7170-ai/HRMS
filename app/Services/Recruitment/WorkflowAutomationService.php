<?php

namespace App\Services\Recruitment;

use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\Interview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use App\Jobs\ProcessWorkflowAction;

class WorkflowAutomationService
{
    /**
     * Workflow trigger types
     */
    const TRIGGER_TYPES = [
        'application_received',
        'stage_changed',
        'interview_completed',
        'score_assigned',
        'time_based',
        'manual_trigger'
    ];

    /**
     * Available workflow actions
     */
    const ACTION_TYPES = [
        'send_email',
        'send_sms',
        'move_to_stage',
        'schedule_interview',
        'assign_score',
        'add_note',
        'notify_team',
        'create_task',
        'update_field'
    ];

    /**
     * Create custom workflow
     */
    public function createWorkflow(array $workflowData): array
    {
        try {
            $workflow = [
                'id' => uniqid('wf_'),
                'name' => $workflowData['name'],
                'description' => $workflowData['description'] ?? '',
                'trigger' => $workflowData['trigger'],
                'conditions' => $workflowData['conditions'] ?? [],
                'actions' => $workflowData['actions'],
                'active' => $workflowData['active'] ?? true,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $this->saveWorkflow($workflow);

            return [
                'success' => true,
                'workflow_id' => $workflow['id'],
                'message' => 'Workflow created successfully'
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to create workflow', [
                'error' => $e->getMessage(),
                'workflow_data' => $workflowData
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create workflow: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Execute workflows for given trigger
     */
    public function executeWorkflows(string $triggerType, array $context): void
    {
        try {
            $workflows = $this->getActiveWorkflows($triggerType);

            foreach ($workflows as $workflow) {
                if ($this->evaluateConditions($workflow['conditions'], $context)) {
                    $this->executeWorkflowActions($workflow, $context);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to execute workflows', [
                'trigger_type' => $triggerType,
                'context' => $context,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process automatic stage transitions
     */
    public function processStageTransitions(Application $application): bool
    {
        try {
            $rules = $this->getStageTransitionRules($application->job_id);

            foreach ($rules as $rule) {
                if ($this->shouldTransitionStage($application, $rule)) {
                    $this->transitionToStage($application, $rule['target_stage'], $rule['reason']);
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Failed to process stage transitions', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Smart candidate matching using AI/ML algorithms
     */
    public function matchCandidates(Job $job, array $filters = []): array
    {
        try {
            $applications = Application::where('job_id', $job->id)
                                    ->where('stage', 'applied')
                                    ->get();

            $matchedCandidates = [];

            foreach ($applications as $application) {
                $matchScore = $this->calculateMatchScore($application, $job);
                
                if ($matchScore >= ($filters['min_score'] ?? 70)) {
                    $matchedCandidates[] = [
                        'application' => $application,
                        'match_score' => $matchScore,
                        'match_reasons' => $this->getMatchReasons($application, $job),
                        'recommendations' => $this->getRecommendations($application, $job)
                    ];
                }
            }

            // Sort by match score
            usort($matchedCandidates, function($a, $b) {
                return $b['match_score'] <=> $a['match_score'];
            });

            return $matchedCandidates;
        } catch (\Exception $e) {
            \Log::error('Failed to match candidates', [
                'job_id' => $job->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Automated resume screening
     */
    public function screenResume(Application $application): array
    {
        try {
            $resumeContent = $this->extractResumeContent($application->resume_path);
            $jobRequirements = $this->getJobRequirements($application->job);

            $screeningResults = [
                'overall_score' => 0,
                'skill_matches' => [],
                'experience_score' => 0,
                'education_score' => 0,
                'keyword_matches' => [],
                'red_flags' => [],
                'recommendations' => []
            ];

            // Skill matching
            $screeningResults['skill_matches'] = $this->matchSkills($resumeContent, $jobRequirements['skills']);
            
            // Experience evaluation
            $screeningResults['experience_score'] = $this->evaluateExperience($resumeContent, $jobRequirements['experience']);
            
            // Education evaluation
            $screeningResults['education_score'] = $this->evaluateEducation($resumeContent, $jobRequirements['education']);
            
            // Keyword matching
            $screeningResults['keyword_matches'] = $this->matchKeywords($resumeContent, $jobRequirements['keywords']);
            
            // Calculate overall score
            $screeningResults['overall_score'] = $this->calculateOverallScreeningScore($screeningResults);
            
            // Auto-assign score to application
            $application->update(['score' => $screeningResults['overall_score']]);
            
            return $screeningResults;
        } catch (\Exception $e) {
            \Log::error('Failed to screen resume', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'overall_score' => 0,
                'error' => 'Failed to process resume'
            ];
        }
    }

    /**
     * Schedule automated interview reminders
     */
    public function scheduleAutomatedReminders(): int
    {
        try {
            $interviews = Interview::where('status', 'scheduled')
                                 ->where('scheduled_at', '>', now())
                                 ->whereNull('reminder_24h_sent')
                                 ->whereNull('reminder_2h_sent')
                                 ->whereNull('reminder_30m_sent')
                                 ->get();

            $scheduled = 0;

            foreach ($interviews as $interview) {
                $this->scheduleInterviewReminders($interview);
                $scheduled++;
            }

            return $scheduled;
        } catch (\Exception $e) {
            \Log::error('Failed to schedule automated reminders', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get workflow recommendations for application
     */
    public function getWorkflowRecommendations(Application $application): array
    {
        $recommendations = [];

        // Check application age
        $daysOld = $application->applied_on->diffInDays(now());
        if ($daysOld > 7 && $application->stage === 'applied') {
            $recommendations[] = [
                'type' => 'follow_up',
                'title' => 'Application Requires Follow-up',
                'message' => "This application is {$daysOld} days old and still in 'Applied' stage.",
                'suggested_action' => 'Move to screening or send update email',
                'priority' => 'medium'
            ];
        }

        // Check for high scores without interviews
        if ($application->score >= 80 && !$application->interviews()->exists()) {
            $recommendations[] = [
                'type' => 'schedule_interview',
                'title' => 'High-Scoring Candidate',
                'message' => "Candidate scored {$application->score}% but no interview scheduled.",
                'suggested_action' => 'Schedule interview immediately',
                'priority' => 'high'
            ];
        }

        // Check for completed interviews without feedback
        $completedInterviews = $application->interviews()
                                          ->where('status', 'completed')
                                          ->whereNull('feedback')
                                          ->count();
        
        if ($completedInterviews > 0) {
            $recommendations[] = [
                'type' => 'missing_feedback',
                'title' => 'Missing Interview Feedback',
                'message' => "{$completedInterviews} completed interview(s) missing feedback.",
                'suggested_action' => 'Request feedback from interviewers',
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    /**
     * Get active workflows for trigger type
     */
    private function getActiveWorkflows(string $triggerType): array
    {
        $workflowsPath = 'recruitment/workflows/active.json';
        
        if (!Storage::disk('local')->exists($workflowsPath)) {
            return [];
        }
        
        $allWorkflows = json_decode(Storage::disk('local')->get($workflowsPath), true) ?? [];
        
        return array_filter($allWorkflows, function($workflow) use ($triggerType) {
            return $workflow['active'] && $workflow['trigger']['type'] === $triggerType;
        });
    }

    /**
     * Evaluate workflow conditions
     */
    private function evaluateConditions(array $conditions, array $context): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate single condition
     */
    private function evaluateCondition(array $condition, array $context): bool
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];
        $contextValue = data_get($context, $field);

        return match($operator) {
            'equals' => $contextValue == $value,
            'not_equals' => $contextValue != $value,
            'greater_than' => $contextValue > $value,
            'less_than' => $contextValue < $value,
            'contains' => str_contains(strtolower($contextValue), strtolower($value)),
            'in' => in_array($contextValue, $value),
            'not_in' => !in_array($contextValue, $value),
            default => false
        };
    }

    /**
     * Execute workflow actions
     */
    private function executeWorkflowActions(array $workflow, array $context): void
    {
        foreach ($workflow['actions'] as $action) {
            ProcessWorkflowAction::dispatch($action, $context, $workflow['id']);
        }
    }

    /**
     * Calculate match score for candidate
     */
    private function calculateMatchScore(Application $application, Job $job): int
    {
        $score = 0;
        $factors = 0;

        // Experience matching (30%)
        if ($application->years_of_experience && $job->min_experience) {
            $experienceScore = min(100, ($application->years_of_experience / $job->min_experience) * 100);
            $score += $experienceScore * 0.3;
            $factors += 0.3;
        }

        // Salary expectations (20%)
        if ($application->expected_salary && $job->max_salary) {
            $salaryScore = $application->expected_salary <= $job->max_salary ? 100 : 50;
            $score += $salaryScore * 0.2;
            $factors += 0.2;
        }

        // Location matching (10%)
        if ($application->current_location && $job->location) {
            $locationScore = strtolower($application->current_location) === strtolower($job->location) ? 100 : 70;
            $score += $locationScore * 0.1;
            $factors += 0.1;
        }

        // Skills matching (40%) - would need skills extraction from resume
        $skillsScore = $this->calculateSkillsMatchScore($application, $job);
        $score += $skillsScore * 0.4;
        $factors += 0.4;

        return $factors > 0 ? (int)($score / $factors) : 50;
    }

    /**
     * Calculate skills match score
     */
    private function calculateSkillsMatchScore(Application $application, Job $job): int
    {
        // Mock implementation - in real scenario, extract skills from resume and job description
        return rand(60, 90);
    }

    /**
     * Get match reasons
     */
    private function getMatchReasons(Application $application, Job $job): array
    {
        $reasons = [];

        if ($application->years_of_experience >= $job->min_experience) {
            $reasons[] = "Meets experience requirements ({$application->years_of_experience} years)";
        }

        if ($application->expected_salary <= $job->max_salary) {
            $reasons[] = "Salary expectations within budget";
        }

        return $reasons;
    }

    /**
     * Get recommendations for candidate
     */
    private function getRecommendations(Application $application, Job $job): array
    {
        $recommendations = [];

        if ($application->score >= 80) {
            $recommendations[] = "High-scoring candidate - schedule interview immediately";
        }

        if (!$application->linkedin_url) {
            $recommendations[] = "Consider requesting LinkedIn profile for additional verification";
        }

        return $recommendations;
    }

    /**
     * Save workflow to storage
     */
    private function saveWorkflow(array $workflow): void
    {
        $workflowsPath = 'recruitment/workflows/active.json';
        
        $workflows = [];
        if (Storage::disk('local')->exists($workflowsPath)) {
            $workflows = json_decode(Storage::disk('local')->get($workflowsPath), true) ?? [];
        }
        
        $workflows[] = $workflow;
        
        Storage::disk('local')->put($workflowsPath, json_encode($workflows, JSON_PRETTY_PRINT));
    }

    /**
     * Get stage transition rules
     */
    private function getStageTransitionRules(int $jobId): array
    {
        // Mock rules - in real implementation, these would be configurable
        return [
            [
                'condition' => 'score >= 80',
                'target_stage' => 'interview',
                'reason' => 'High score automatic promotion'
            ],
            [
                'condition' => 'score < 50',
                'target_stage' => 'rejected',
                'reason' => 'Low score automatic rejection'
            ]
        ];
    }

    /**
     * Check if stage should transition
     */
    private function shouldTransitionStage(Application $application, array $rule): bool
    {
        // Simple condition evaluation - expand as needed
        if (str_contains($rule['condition'], 'score >=')) {
            $threshold = (int)str_replace('score >= ', '', $rule['condition']);
            return $application->score >= $threshold;
        }
        
        if (str_contains($rule['condition'], 'score <')) {
            $threshold = (int)str_replace('score < ', '', $rule['condition']);
            return $application->score < $threshold;
        }
        
        return false;
    }

    /**
     * Transition application to new stage
     */
    private function transitionToStage(Application $application, string $newStage, string $reason): void
    {
        $oldStage = $application->stage;
        
        $application->update([
            'stage' => $newStage,
            'notes' => ($application->notes ? $application->notes . "\n" : '') . 
                      "Auto-transition from {$oldStage} to {$newStage}: {$reason}"
        ]);
        
        // Trigger workflow for stage change
        $this->executeWorkflows('stage_changed', [
            'application' => $application,
            'old_stage' => $oldStage,
            'new_stage' => $newStage
        ]);
    }

    /**
     * Schedule interview reminders for specific interview
     */
    private function scheduleInterviewReminders(Interview $interview): void
    {
        $scheduledAt = $interview->scheduled_at;
        
        // Schedule 24-hour reminder
        $reminder24h = $scheduledAt->copy()->subDay();
        if ($reminder24h->isFuture()) {
            ProcessWorkflowAction::dispatch([
                'type' => 'send_interview_reminder',
                'reminder_type' => '24_hours'
            ], ['interview' => $interview], 'interview_reminders')->delay($reminder24h);
        }
        
        // Schedule 2-hour reminder
        $reminder2h = $scheduledAt->copy()->subHours(2);
        if ($reminder2h->isFuture()) {
            ProcessWorkflowAction::dispatch([
                'type' => 'send_interview_reminder', 
                'reminder_type' => '2_hours'
            ], ['interview' => $interview], 'interview_reminders')->delay($reminder2h);
        }
        
        // Schedule 30-minute reminder
        $reminder30m = $scheduledAt->copy()->subMinutes(30);
        if ($reminder30m->isFuture()) {
            ProcessWorkflowAction::dispatch([
                'type' => 'send_interview_reminder',
                'reminder_type' => '30_minutes'
            ], ['interview' => $interview], 'interview_reminders')->delay($reminder30m);
        }
    }

    /**
     * Extract resume content (mock implementation)
     */
    private function extractResumeContent(string $resumePath): string
    {
        // Mock implementation - in real scenario, use PDF parser or OCR
        return "Sample resume content with skills: PHP, Laravel, JavaScript, React, Project Management";
    }

    /**
     * Get job requirements
     */
    private function getJobRequirements(Job $job): array
    {
        // Mock implementation - parse from job description
        return [
            'skills' => ['PHP', 'Laravel', 'JavaScript'],
            'experience' => $job->min_experience ?? 2,
            'education' => 'Bachelor\'s degree',
            'keywords' => ['development', 'programming', 'web']
        ];
    }

    /**
     * Match skills from resume with job requirements
     */
    private function matchSkills(string $resumeContent, array $requiredSkills): array
    {
        $matches = [];
        
        foreach ($requiredSkills as $skill) {
            $matches[] = [
                'skill' => $skill,
                'found' => str_contains(strtolower($resumeContent), strtolower($skill)),
                'confidence' => rand(70, 95)
            ];
        }
        
        return $matches;
    }

    /**
     * Evaluate experience from resume
     */
    private function evaluateExperience(string $resumeContent, int $requiredYears): int
    {
        // Mock implementation - in real scenario, parse years from resume
        return rand(60, 90);
    }

    /**
     * Evaluate education from resume
     */
    private function evaluateEducation(string $resumeContent, string $requiredEducation): int
    {
        // Mock implementation
        return rand(70, 95);
    }

    /**
     * Match keywords in resume
     */
    private function matchKeywords(string $resumeContent, array $keywords): array
    {
        $matches = [];
        
        foreach ($keywords as $keyword) {
            $matches[] = [
                'keyword' => $keyword,
                'found' => str_contains(strtolower($resumeContent), strtolower($keyword)),
                'frequency' => substr_count(strtolower($resumeContent), strtolower($keyword))
            ];
        }
        
        return $matches;
    }

    /**
     * Calculate overall screening score
     */
    private function calculateOverallScreeningScore(array $results): int
    {
        $skillsWeight = 0.4;
        $experienceWeight = 0.3;
        $educationWeight = 0.2;
        $keywordsWeight = 0.1;
        
        $skillsScore = count(array_filter($results['skill_matches'], fn($match) => $match['found'])) / 
                      count($results['skill_matches']) * 100;
        
        $keywordsScore = count(array_filter($results['keyword_matches'], fn($match) => $match['found'])) /
                        count($results['keyword_matches']) * 100;
        
        $overallScore = ($skillsScore * $skillsWeight) + 
                       ($results['experience_score'] * $experienceWeight) +
                       ($results['education_score'] * $educationWeight) +
                       ($keywordsScore * $keywordsWeight);
        
        return (int)$overallScore;
    }
}
