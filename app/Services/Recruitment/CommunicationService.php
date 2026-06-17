<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Interview;
use App\Models\User;
use App\Notifications\Recruitment\CustomRecruitmentNotification;

class CommunicationService
{
    /**
     * Email template types
     */
    const TEMPLATE_TYPES = [
        'application_received',
        'application_rejected',
        'interview_invitation',
        'interview_reminder',
        'interview_rescheduled', 
        'offer_extended',
        'offer_accepted',
        'welcome_onboarding',
        'custom_message'
    ];

    /**
     * Send custom email to candidate
     */
    public function sendCustomEmail(Application $application, array $emailData): bool
    {
        try {
            $template = $this->getEmailTemplate($emailData['template_type']);
            $processedContent = $this->processTemplate($template, $application, $emailData);
            
            $candidate = $application->user ?: new \App\Services\Recruitment\ExternalCandidate(
                $application->candidate_email,
                $application->candidate_name
            );

            $candidate->notify(new CustomRecruitmentNotification(
                $emailData['subject'],
                $processedContent,
                $emailData['template_type']
            ));

            // Log communication
            $this->logCommunication($application, 'email', $emailData);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send custom email', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send SMS notification
     */
    public function sendSMS(Application $application, string $message): bool
    {
        try {
            // Integration with SMS service (Twilio, Nexmo, etc.)
            $phone = $application->candidate_phone ?? $application->user?->phone;
            
            if (!$phone) {
                throw new \Exception('No phone number available for candidate');
            }

            // Mock SMS sending - integrate with actual SMS service
            $smsResponse = $this->sendSMSViaProvider($phone, $message);
            
            // Log communication
            $this->logCommunication($application, 'sms', [
                'message' => $message,
                'phone' => $phone,
                'response' => $smsResponse
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Schedule automated follow-up sequence
     */
    public function scheduleFollowUpSequence(Application $application, string $sequenceType): bool
    {
        try {
            $sequence = $this->getFollowUpSequence($sequenceType);
            
            foreach ($sequence as $step) {
                $scheduledTime = now()->addDays($step['delay_days']);
                
                // Create scheduled job for follow-up
                \App\Jobs\SendFollowUpEmailJob::dispatch(
                    $application,
                    $step['template_type'],
                    $step['subject'],
                    $step['content']
                )->delay($scheduledTime);
            }
            
            $this->logCommunication($application, 'sequence_scheduled', [
                'sequence_type' => $sequenceType,
                'steps_count' => count($sequence)
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to schedule follow-up sequence', [
                'application_id' => $application->id,
                'sequence_type' => $sequenceType,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send bulk communication to multiple candidates
     */
    public function sendBulkCommunication(array $applicationIds, array $messageData): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($applicationIds as $applicationId) {
            try {
                $application = Application::findOrFail($applicationId);
                
                if ($messageData['type'] === 'email') {
                    $success = $this->sendCustomEmail($application, $messageData);
                } elseif ($messageData['type'] === 'sms') {
                    $success = $this->sendSMS($application, $messageData['message']);
                }
                
                if ($success) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'application_id' => $applicationId,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Get available email templates
     */
    public function getEmailTemplates(): array
    {
        $templates = [];
        
        foreach (self::TEMPLATE_TYPES as $type) {
            $template = $this->getEmailTemplate($type);
            $templates[$type] = [
                'name' => $template['name'],
                'subject' => $template['subject'],
                'description' => $template['description'],
                'variables' => $template['variables']
            ];
        }
        
        return $templates;
    }

    /**
     * Create or update email template
     */
    public function saveEmailTemplate(string $type, array $templateData): bool
    {
        try {
            $templatePath = $this->getTemplatePath($type);
            
            $template = [
                'name' => $templateData['name'],
                'subject' => $templateData['subject'],
                'content' => $templateData['content'],
                'description' => $templateData['description'] ?? '',
                'variables' => $templateData['variables'] ?? $this->getDefaultVariables(),
                'updated_at' => now()->toISOString()
            ];
            
            Storage::disk('local')->put($templatePath, json_encode($template, JSON_PRETTY_PRINT));
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to save email template', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get communication history for application
     */
    public function getCommunicationHistory(Application $application): array
    {
        $logPath = "recruitment/communications/{$application->id}.json";
        
        if (!Storage::disk('local')->exists($logPath)) {
            return [];
        }
        
        $history = json_decode(Storage::disk('local')->get($logPath), true);
        return $history ?? [];
    }

    /**
     * Schedule video interview (Zoom/Teams integration)
     */
    public function scheduleVideoInterview(Interview $interview, array $meetingData): array
    {
        try {
            // Integration with video conferencing platforms
            if ($meetingData['platform'] === 'zoom') {
                $meetingResponse = $this->createZoomMeeting($interview, $meetingData);
            } elseif ($meetingData['platform'] === 'teams') {
                $meetingResponse = $this->createTeamsMeeting($interview, $meetingData);
            } else {
                throw new \Exception('Unsupported video platform');
            }
            
            // Update interview with meeting details
            $interview->update([
                'meeting_link' => $meetingResponse['join_url'],
                'meeting_id' => $meetingResponse['meeting_id'],
                'meeting_password' => $meetingResponse['password'] ?? null
            ]);
            
            return [
                'success' => true,
                'meeting_url' => $meetingResponse['join_url'],
                'meeting_id' => $meetingResponse['meeting_id']
            ];
            
        } catch (\Exception $e) {
            \Log::error('Failed to schedule video interview', [
                'interview_id' => $interview->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get email template by type
     */
    private function getEmailTemplate(string $type): array
    {
        $templatePath = $this->getTemplatePath($type);
        
        if (Storage::disk('local')->exists($templatePath)) {
            $template = json_decode(Storage::disk('local')->get($templatePath), true);
            return $template;
        }
        
        // Return default template if custom doesn't exist
        return $this->getDefaultTemplate($type);
    }

    /**
     * Process template with application data
     */
    private function processTemplate(array $template, Application $application, array $emailData): string
    {
        $content = $emailData['content'] ?? $template['content'];
        $variables = $this->getTemplateVariables($application);
        
        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }

    /**
     * Get template variables for application
     */
    private function getTemplateVariables(Application $application): array
    {
        return [
            'candidate_name' => $application->candidate_name ?? $application->user?->name ?? 'Candidate',
            'job_title' => $application->job?->title ?? 'Position',
            'company_name' => config('app.name'),
            'application_date' => $application->applied_on->format('M d, Y'),
            'stage' => $application->formatted_stage,
            'score' => $application->score ?? 'Not scored',
            'recruiter_name' => $application->job?->recruiter?->name ?? 'Recruitment Team'
        ];
    }

    /**
     * Log communication activity
     */
    private function logCommunication(Application $application, string $type, array $data): void
    {
        $logPath = "recruitment/communications/{$application->id}.json";
        
        $history = [];
        if (Storage::disk('local')->exists($logPath)) {
            $history = json_decode(Storage::disk('local')->get($logPath), true) ?? [];
        }
        
        $history[] = [
            'type' => $type,
            'timestamp' => now()->toISOString(),
            'data' => $data,
            'user_id' => auth()->id()
        ];
        
        Storage::disk('local')->put($logPath, json_encode($history, JSON_PRETTY_PRINT));
    }

    /**
     * Get template file path
     */
    private function getTemplatePath(string $type): string
    {
        return "recruitment/templates/{$type}.json";
    }

    /**
     * Get default template for type
     */
    private function getDefaultTemplate(string $type): array
    {
        $defaults = [
            'application_received' => [
                'name' => 'Application Received',
                'subject' => 'Application Received - {{job_title}}',
                'content' => 'Dear {{candidate_name}}, Thank you for applying to {{job_title}} at {{company_name}}. We have received your application and will review it shortly.',
                'description' => 'Sent when a new application is received',
                'variables' => $this->getDefaultVariables()
            ],
            'interview_invitation' => [
                'name' => 'Interview Invitation',
                'subject' => 'Interview Invitation - {{job_title}}',
                'content' => 'Dear {{candidate_name}}, We would like to invite you for an interview for the {{job_title}} position. Please confirm your availability.',
                'description' => 'Sent when inviting candidate for interview',
                'variables' => $this->getDefaultVariables()
            ]
        ];
        
        return $defaults[$type] ?? [
            'name' => 'Custom Template',
            'subject' => 'Update on your application',
            'content' => 'Dear {{candidate_name}}, This is an update regarding your application.',
            'description' => 'Custom communication template',
            'variables' => $this->getDefaultVariables()
        ];
    }

    /**
     * Get default template variables
     */
    private function getDefaultVariables(): array
    {
        return [
            'candidate_name' => 'Candidate full name',
            'job_title' => 'Job position title', 
            'company_name' => 'Company name',
            'application_date' => 'Date application was submitted',
            'stage' => 'Current application stage',
            'score' => 'Candidate score',
            'recruiter_name' => 'Recruiter or HR person name'
        ];
    }

    /**
     * Get follow-up sequence by type
     */
    private function getFollowUpSequence(string $sequenceType): array
    {
        $sequences = [
            'post_application' => [
                [
                    'delay_days' => 1,
                    'template_type' => 'application_received',
                    'subject' => 'Application Received - {{job_title}}',
                    'content' => 'Thank you for your application. We are reviewing it and will get back to you soon.'
                ],
                [
                    'delay_days' => 7,
                    'template_type' => 'application_update',
                    'subject' => 'Application Update - {{job_title}}',
                    'content' => 'We are still reviewing your application. Thank you for your patience.'
                ]
            ],
            'post_interview' => [
                [
                    'delay_days' => 1,
                    'template_type' => 'interview_thanks',
                    'subject' => 'Thank you for interviewing with us',
                    'content' => 'Thank you for taking the time to interview with us. We will be in touch soon.'
                ],
                [
                    'delay_days' => 5,
                    'template_type' => 'interview_followup',
                    'subject' => 'Interview Follow-up - {{job_title}}',
                    'content' => 'We are finalizing our decision and will update you shortly.'
                ]
            ]
        ];
        
        return $sequences[$sequenceType] ?? [];
    }

    /**
     * Send SMS via provider (mock implementation)
     */
    private function sendSMSViaProvider(string $phone, string $message): array
    {
        // Mock SMS provider integration
        // In real implementation, integrate with Twilio, Nexmo, etc.
        return [
            'success' => true,
            'message_id' => 'sms_' . uniqid(),
            'status' => 'sent'
        ];
    }

    /**
     * Create Zoom meeting (mock implementation)
     */
    private function createZoomMeeting(Interview $interview, array $meetingData): array
    {
        // Mock Zoom API integration
        // In real implementation, use Zoom SDK/API
        return [
            'meeting_id' => 'zoom_' . uniqid(),
            'join_url' => 'https://zoom.us/j/123456789',
            'password' => 'pass123'
        ];
    }

    /**
     * Create Teams meeting (mock implementation)
     */
    private function createTeamsMeeting(Interview $interview, array $meetingData): array
    {
        // Mock Microsoft Teams integration
        // In real implementation, use Microsoft Graph API
        return [
            'meeting_id' => 'teams_' . uniqid(),
            'join_url' => 'https://teams.microsoft.com/l/meetup-join/...'
        ];
    }
}
