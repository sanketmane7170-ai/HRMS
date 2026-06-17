<?php

namespace App\Services\Recruitment;

use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Job;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CandidatePortalService
{
    /**
     * Generate candidate portal access token
     */
    public function generatePortalToken(Application $application): string
    {
        $token = hash('sha256', $application->id . $application->candidate_email . now()->timestamp . config('app.key'));
        
        // Store token with expiry (30 days)
        $tokenData = [
            'application_id' => $application->id,
            'candidate_email' => $application->candidate_email,
            'expires_at' => now()->addDays(30)->toISOString(),
            'created_at' => now()->toISOString()
        ];
        
        Storage::disk('local')->put("candidate_tokens/{$token}.json", json_encode($tokenData));
        
        return $token;
    }

    /**
     * Validate portal token
     */
    public function validatePortalToken(string $token): ?Application
    {
        $tokenPath = "candidate_tokens/{$token}.json";
        
        if (!Storage::disk('local')->exists($tokenPath)) {
            return null;
        }
        
        $tokenData = json_decode(Storage::disk('local')->get($tokenPath), true);
        
        if (!$tokenData || Carbon::parse($tokenData['expires_at'])->isPast()) {
            // Clean up expired token
            Storage::disk('local')->delete($tokenPath);
            return null;
        }
        
        return Application::find($tokenData['application_id']);
    }

    /**
     * Get candidate dashboard data
     */
    public function getCandidateDashboard(Application $application): array
    {
        return [
            'application' => [
                'id' => $application->id,
                'job_title' => $application->job?->title,
                'department' => $application->job?->department?->name,
                'applied_date' => $application->applied_on->format('M d, Y'),
                'current_stage' => $application->formatted_stage,
                'score' => $application->score,
                'status' => $this->getApplicationStatus($application)
            ],
            'timeline' => $this->getApplicationTimeline($application),
            'interviews' => $this->getUpcomingInterviews($application),
            'documents' => $this->getApplicationDocuments($application),
            'feedback' => $this->getInterviewFeedback($application),
            'next_steps' => $this->getNextSteps($application)
        ];
    }

    /**
     * Get application timeline/history
     */
    public function getApplicationTimeline(Application $application): array
    {
        $timeline = [
            [
                'stage' => 'applied',
                'title' => 'Application Submitted',
                'date' => $application->applied_on,
                'status' => 'completed',
                'description' => 'Your application has been received and is under review.'
            ]
        ];

        // Add stage transitions based on current stage
        $stages = ['screening', 'interview', 'offer', 'hired'];
        $currentStageIndex = array_search($application->stage, $stages);

        foreach ($stages as $index => $stage) {
            $status = $index <= $currentStageIndex ? 'completed' : 
                     ($index === $currentStageIndex + 1 ? 'current' : 'pending');
            
            $timeline[] = [
                'stage' => $stage,
                'title' => $this->getStageTitle($stage),
                'date' => $status === 'completed' ? $application->updated_at : null,
                'status' => $status,
                'description' => $this->getStageDescription($stage, $status)
            ];
        }

        return $timeline;
    }

    /**
     * Get upcoming interviews for candidate
     */
    public function getUpcomingInterviews(Application $application): array
    {
        return $application->interviews()
            ->where('scheduled_at', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->get()
            ->map(function($interview) {
                return [
                    'id' => $interview->id,
                    'scheduled_at' => $interview->scheduled_at,
                    'duration' => $interview->duration_minutes,
                    'type' => $interview->type,
                    'location' => $interview->location,
                    'meeting_link' => $interview->meeting_link,
                    'interviewer' => $interview->interviewer?->name,
                    'agenda' => $interview->agenda,
                    'preparation_notes' => $interview->preparation_notes
                ];
            })
            ->toArray();
    }

    /**
     * Get application documents
     */
    public function getApplicationDocuments(Application $application): array
    {
        $documents = [];
        
        if ($application->resume_path) {
            $documents[] = [
                'type' => 'resume',
                'name' => 'Resume/CV',
                'path' => $application->resume_path,
                'upload_date' => $application->applied_on,
                'size' => $this->getFileSize($application->resume_path)
            ];
        }

        if ($application->cover_letter_path) {
            $documents[] = [
                'type' => 'cover_letter',
                'name' => 'Cover Letter',
                'path' => $application->cover_letter_path,
                'upload_date' => $application->applied_on,
                'size' => $this->getFileSize($application->cover_letter_path)
            ];
        }

        // Additional documents from storage
        $additionalDocs = $this->getAdditionalDocuments($application);
        $documents = array_merge($documents, $additionalDocs);

        return $documents;
    }

    /**
     * Upload additional document
     */
    public function uploadDocument(Application $application, $file, string $documentType): array
    {
        try {
            // Sanitize filename (Sanket - REC-SEC-014)
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeName = \Illuminate\Support\Str::slug($originalName) . '.' . $extension;
            
            $filename = time() . '_' . $safeName;
            $path = $file->storeAs("recruitment/applications/{$application->id}/documents", $filename, 'public');
            
            // Log document upload
            $this->logDocumentActivity($application, [
                'action' => 'uploaded',
                'document_type' => $documentType,
                'filename' => $filename,
                'path' => $path,
                'size' => $file->getSize()
            ]);
            
            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'size' => $file->getSize()
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update candidate profile information
     */
    public function updateCandidateProfile(Application $application, array $profileData): bool
    {
        try {
            $allowedFields = [
                'candidate_name', 'candidate_phone', 'linkedin_url',
                'years_of_experience', 'expected_salary', 'current_location'
            ];
            
            $updateData = array_intersect_key($profileData, array_flip($allowedFields));
            $application->update($updateData);
            
            // Log profile update
            $this->logDocumentActivity($application, [
                'action' => 'profile_updated',
                'updated_fields' => array_keys($updateData)
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update candidate profile', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get interview feedback (if available to candidate)
     */
    public function getInterviewFeedback(Application $application): array
    {
        $interviews = $application->interviews()->where('status', 'completed')->get();
        $feedback = [];
        
        foreach ($interviews as $interview) {
            // Only show general feedback, not detailed internal notes
            if ($interview->candidate_feedback) {
                $feedback[] = [
                    'interview_date' => $interview->scheduled_at,
                    'interviewer' => $interview->interviewer?->name,
                    'feedback' => $interview->candidate_feedback,
                    'next_steps' => $interview->next_steps
                ];
            }
        }
        
        return $feedback;
    }

    /**
     * Get next steps for candidate
     */
    public function getNextSteps(Application $application): array
    {
        $nextSteps = [];
        
        switch ($application->stage) {
            case 'applied':
                $nextSteps[] = 'Your application is being reviewed by our recruitment team.';
                $nextSteps[] = 'You will be contacted within 5-7 business days if you match our requirements.';
                break;
                
            case 'screening':
                $nextSteps[] = 'Your application passed initial screening.';
                $nextSteps[] = 'We may contact you for a phone/video screening interview.';
                break;
                
            case 'interview':
                $upcomingInterviews = $this->getUpcomingInterviews($application);
                if (!empty($upcomingInterviews)) {
                    $nextInterview = $upcomingInterviews[0];
                    $nextSteps[] = "You have an upcoming interview on " . 
                                  Carbon::parse($nextInterview['scheduled_at'])->format('M d, Y \a\t g:i A');
                    $nextSteps[] = 'Please prepare by reviewing the job description and researching our company.';
                } else {
                    $nextSteps[] = 'Interview process in progress.';
                    $nextSteps[] = 'You will be contacted with feedback and next steps soon.';
                }
                break;
                
            case 'offer':
                $nextSteps[] = 'Congratulations! An offer is being prepared.';
                $nextSteps[] = 'You will receive the formal offer letter shortly.';
                break;
                
            case 'hired':
                $nextSteps[] = 'Welcome to the team!';
                $nextSteps[] = 'You will receive onboarding information from HR.';
                break;
                
            case 'rejected':
                $nextSteps[] = 'Thank you for your interest in our company.';
                $nextSteps[] = 'We encourage you to apply for future opportunities that match your profile.';
                break;
        }
        
        return $nextSteps;
    }

    /**
     * Send candidate portal invitation
     */
    public function sendPortalInvitation(Application $application): bool
    {
        try {
            $token = $this->generatePortalToken($application);
            $portalUrl = url("/candidate-portal?token={$token}");
            
            // Send email with portal access
            $candidate = $application->user ?: new \App\Services\Recruitment\ExternalCandidate(
                $application->candidate_email,
                $application->candidate_name
            );
            
            $candidate->notify(new \App\Notifications\Recruitment\CandidatePortalInvitation(
                $application,
                $portalUrl
            ));
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send portal invitation', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get application status with color coding
     */
    private function getApplicationStatus(Application $application): array
    {
        $statusMap = [
            'applied' => ['label' => 'Under Review', 'color' => 'blue'],
            'screening' => ['label' => 'In Screening', 'color' => 'yellow'],
            'interview' => ['label' => 'Interview Stage', 'color' => 'purple'],
            'offer' => ['label' => 'Offer Stage', 'color' => 'green'],
            'hired' => ['label' => 'Hired', 'color' => 'green'],
            'rejected' => ['label' => 'Not Selected', 'color' => 'red']
        ];
        
        return $statusMap[$application->stage] ?? ['label' => 'Unknown', 'color' => 'gray'];
    }

    /**
     * Get stage title for timeline
     */
    private function getStageTitle(string $stage): string
    {
        $titles = [
            'screening' => 'Initial Screening',
            'interview' => 'Interview Process',
            'offer' => 'Offer Extended',
            'hired' => 'Welcome Aboard!'
        ];
        
        return $titles[$stage] ?? ucfirst($stage);
    }

    /**
     * Get stage description for timeline
     */
    private function getStageDescription(string $stage, string $status): string
    {
        $descriptions = [
            'screening' => [
                'completed' => 'Your application successfully passed our initial screening.',
                'current' => 'Your application is currently being screened by our recruitment team.',
                'pending' => 'Your application will be screened after initial review.'
            ],
            'interview' => [
                'completed' => 'Interview process completed successfully.',
                'current' => 'You are in the interview stage. Good luck!',
                'pending' => 'Interview will be scheduled if you pass screening.'
            ],
            'offer' => [
                'completed' => 'Offer has been extended to you.',
                'current' => 'We are preparing an offer for you.',
                'pending' => 'An offer may be extended based on interview performance.'
            ],
            'hired' => [
                'completed' => 'Congratulations! You have been hired.',
                'current' => 'Final paperwork and onboarding in progress.',
                'pending' => 'Hiring decision pending.'
            ]
        ];
        
        return $descriptions[$stage][$status] ?? 'Stage in progress.';
    }

    /**
     * Get additional documents from storage
     */
    private function getAdditionalDocuments(Application $application): array
    {
        $documentsPath = "recruitment/applications/{$application->id}/documents";
        
        if (!Storage::disk('public')->exists($documentsPath)) {
            return [];
        }
        
        $files = Storage::disk('public')->files($documentsPath);
        $documents = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            $documents[] = [
                'type' => 'additional',
                'name' => $filename,
                'path' => $file,
                'upload_date' => Carbon::createFromTimestamp(Storage::disk('public')->lastModified($file)),
                'size' => Storage::disk('public')->size($file)
            ];
        }
        
        return $documents;
    }

    /**
     * Get file size in human readable format
     */
    private function getFileSize(string $path): string
    {
        try {
            $bytes = Storage::disk('public')->size($path);
            
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } else {
                return $bytes . ' bytes';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Log document activity
     */
    private function logDocumentActivity(Application $application, array $data): void
    {
        $logPath = "recruitment/applications/{$application->id}/activity.json";
        
        $activity = [];
        if (Storage::disk('local')->exists($logPath)) {
            $activity = json_decode(Storage::disk('local')->get($logPath), true) ?? [];
        }
        
        $activity[] = [
            'timestamp' => now()->toISOString(),
            'data' => $data
        ];
        
        Storage::disk('local')->put($logPath, json_encode($activity, JSON_PRETTY_PRINT));
    }
}
