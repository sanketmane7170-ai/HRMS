<?php

namespace App\Services\Recruitment;

use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\ApplicationLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class ApplicationStageService
{
    /**
     * Define the standard recruitment stage progression sequence
     */
    private const STAGE_PROGRESSION = [
        'applied',
        'screening', 
        'shortlisted',
        'interview',
        'offer',
        'hired'
    ];

    /**
     * Move application to a specific stage (allows backward movement for manual overrides)
     * 
     * @param Application $application
     * @param string $targetStage
     * @param string $reason
     * @param int|null $userId
     * @return bool
     */
    public function moveToStage(Application $application, string $targetStage, string $reason = 'Manual update', ?int $userId = null): bool
    {
        try {
            $allStages = Application::getStages();
            if (!array_key_exists($targetStage, $allStages)) {
                Log::warning("Invalid stage: {$targetStage}");
                return false;
            }

            // Security check for 'hired' stage
            if ($targetStage === 'hired') {
                if (!auth()->check() || (!auth()->user()->hasRole('admin') && !auth()->user()->can('Hiring Access'))) {
                    Log::warning("Unauthorized attempt to move application {$application->id} to hired stage by user " . (auth()->id() ?? 'guest'));
                    return false;
                }
            }

            $oldStage = $application->stage;
            
            // Skip if already at target
            if ($oldStage === $targetStage) {
                return true;
            }

            // Update application
            $updateData = $this->buildUpdateData($targetStage);
            $application->update($updateData);

            // Log the change
            $logData = [
                'application_id' => $application->id,
                'previous_stage' => $oldStage,
                'new_stage' => $targetStage,
                'action' => 'stage_changed',
                'description' => $reason,
                'created_at' => now()
            ];

            if ($userId || (auth()->check() && auth()->id())) {
                $logData['changed_by'] = $userId ?? auth()->id();
            }

            ApplicationLog::create($logData);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to move application {$application->id} to stage {$targetStage}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Auto-progress application through stages to target stage
     * 
     * @param Application $application
     * @param string $targetStage
     * @param string $reason
     * @return bool Success status
     */
    public function progressToStage(Application $application, string $targetStage, string $reason = 'Auto-progression'): bool
    {
        try {
            // Validate target stage
            if (!$this->isValidStage($targetStage)) {
                Log::warning("Invalid target stage: {$targetStage}");
                return false;
            }

            $currentStage = $application->stage;
            $currentIndex = array_search($currentStage, self::STAGE_PROGRESSION);
            $targetIndex = array_search($targetStage, self::STAGE_PROGRESSION);

            // Only progress if current stage is before target stage
            if ($currentIndex === false || $targetIndex === false || $currentIndex >= $targetIndex) {
                return true; // Already at or past target stage
            }

            // Update application stage
            $updateData = $this->buildUpdateData($targetStage);
            $application->update($updateData);

            // Log stage progressions
            $this->logStageProgressions($application, $currentStage, $targetStage, $reason, $currentIndex, $targetIndex);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to progress application {$application->id} to stage {$targetStage}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a stage is valid
     */
    private function isValidStage(string $stage): bool
    {
        return in_array($stage, self::STAGE_PROGRESSION);
    }

    /**
     * Build update data array for application
     */
    private function buildUpdateData(string $targetStage): array
    {
        $updateData = ['stage' => $targetStage];

        // Add current_stage field if it exists in database
        if ($this->hasCurrentStageColumn()) {
            $updateData['current_stage'] = $targetStage;
        }

        return $updateData;
    }

    /**
     * Check if current_stage column exists in recruitment_applications table
     */
    private function hasCurrentStageColumn(): bool
    {
        try {
            return Schema::hasColumn('recruitment_applications', 'current_stage');
        } catch (\Exception $e) {
            Log::debug("Could not check current_stage column existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log all stage progressions that occurred
     */
    private function logStageProgressions(
        Application $application, 
        string $currentStage, 
        string $targetStage, 
        string $reason, 
        int $currentIndex, 
        int $targetIndex
    ): void {
        // Get all stages that were skipped/progressed through
        $stagesProgressed = array_slice(self::STAGE_PROGRESSION, $currentIndex + 1, $targetIndex - $currentIndex);
        
        foreach ($stagesProgressed as $index => $progressedStage) {
            $prevStage = $index === 0 ? $currentStage : $stagesProgressed[$index - 1];
            
            try {
                $logData = [
                    'application_id' => $application->id,
                    'previous_stage' => $prevStage,
                    'new_stage' => $progressedStage,
                    'action' => 'stage_changed',
                    'description' => "{$reason}: Auto-progressed to {$progressedStage} stage",
                    'created_at' => now()
                ];
                
                // Only add changed_by if user is authenticated
                if (auth()->check() && auth()->id()) {
                    $logData['changed_by'] = auth()->id();
                }
                
                ApplicationLog::create($logData);
            } catch (\Exception $e) {
                Log::error("Failed to log stage progression for application {$application->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get all available stages
     */
    public function getAvailableStages(): array
    {
        return self::STAGE_PROGRESSION;
    }

    /**
     * Get next stage in progression
     */
    public function getNextStage(string $currentStage): ?string
    {
        $currentIndex = array_search($currentStage, self::STAGE_PROGRESSION);
        
        if ($currentIndex === false || $currentIndex >= count(self::STAGE_PROGRESSION) - 1) {
            return null;
        }

        return self::STAGE_PROGRESSION[$currentIndex + 1];
    }

    /**
     * Check if stage progression is possible
     */
    public function canProgressTo(string $currentStage, string $targetStage): bool
    {
        $currentIndex = array_search($currentStage, self::STAGE_PROGRESSION);
        $targetIndex = array_search($targetStage, self::STAGE_PROGRESSION);

        return $currentIndex !== false && 
               $targetIndex !== false && 
               $currentIndex < $targetIndex;
    }
}
