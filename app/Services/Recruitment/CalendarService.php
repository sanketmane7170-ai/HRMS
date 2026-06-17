<?php

namespace App\Services\Recruitment;

use Modules\Recruitment\Entities\Interview;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Microsoft\Graph\GraphServiceClient;

class CalendarService
{
    /**
     * Create calendar events for an interview.
     */
    public function createInterviewEvent(Interview $interview): ?array
    {
        $eventIds = [];
        
        try {
            // Create Google Calendar event if configured
            if ($this->isGoogleCalendarEnabled()) {
                $googleEventId = $this->createGoogleCalendarEvent($interview);
                if ($googleEventId) {
                    $eventIds['google'] = $googleEventId;
                }
            }

            // Create Outlook/Microsoft Graph event if configured
            if ($this->isMicrosoftCalendarEnabled()) {
                $outlookEventId = $this->createMicrosoftCalendarEvent($interview);
                if ($outlookEventId) {
                    $eventIds['outlook'] = $outlookEventId;
                }
            }

            return !empty($eventIds) ? $eventIds : null;

        } catch (\Exception $e) {
            \Log::error('Failed to create calendar event', [
                'interview_id' => $interview->id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Update calendar events for an interview.
     */
    public function updateInterviewEvent(Interview $interview): bool
    {
        try {
            $eventIds = $interview->calendar_event_ids ?? [];
            
            if (isset($eventIds['google']) && $this->isGoogleCalendarEnabled()) {
                $this->updateGoogleCalendarEvent($interview, $eventIds['google']);
            }

            if (isset($eventIds['outlook']) && $this->isMicrosoftCalendarEnabled()) {
                $this->updateMicrosoftCalendarEvent($interview, $eventIds['outlook']);
            }

            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to update calendar event', [
                'interview_id' => $interview->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Cancel calendar events for an interview.
     */
    public function cancelInterviewEvent(Interview $interview): bool
    {
        try {
            $eventIds = $interview->calendar_event_ids ?? [];
            
            if (isset($eventIds['google']) && $this->isGoogleCalendarEnabled()) {
                $this->cancelGoogleCalendarEvent($eventIds['google']);
            }

            if (isset($eventIds['outlook']) && $this->isMicrosoftCalendarEnabled()) {
                $this->cancelMicrosoftCalendarEvent($eventIds['outlook']);
            }

            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to cancel calendar event', [
                'interview_id' => $interview->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Create Google Calendar event.
     */
    private function createGoogleCalendarEvent(Interview $interview): ?string
    {
        try {
            $client = $this->getGoogleClient();
            $service = new GoogleCalendar($client);
            
            $event = new \Google\Service\Calendar\Event([
                'summary' => $this->getEventTitle($interview),
                'description' => $this->getEventDescription($interview),
                'start' => [
                    'dateTime' => $interview->scheduled_at->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'end' => [
                    'dateTime' => $interview->end_time->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ],
                'attendees' => $this->getEventAttendees($interview),
                'location' => $interview->location,
                'conferenceData' => $this->getConferenceData($interview),
                'reminders' => [
                    'useDefault' => false,
                    'overrides' => [
                        ['method' => 'email', 'minutes' => $interview->reminder_minutes],
                        ['method' => 'popup', 'minutes' => 15],
                    ],
                ],
            ]);

            $calendarId = config('services.google.calendar_id', 'primary');
            $event = $service->events->insert($calendarId, $event);
            
            return $event->getId();

        } catch (\Exception $e) {
            \Log::error('Failed to create Google Calendar event', [
                'interview_id' => $interview->id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Create Microsoft Calendar event.
     */
    private function createMicrosoftCalendarEvent(Interview $interview): ?string
    {
        try {
            $graphServiceClient = $this->getMicrosoftGraphClient();
            
            $event = [
                'subject' => $this->getEventTitle($interview),
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $this->getEventDescription($interview)
                ],
                'start' => [
                    'dateTime' => $interview->scheduled_at->toISOString(),
                    'timeZone' => config('app.timezone')
                ],
                'end' => [
                    'dateTime' => $interview->end_time->toISOString(),
                    'timeZone' => config('app.timezone')
                ],
                'attendees' => $this->getMicrosoftEventAttendees($interview),
                'location' => [
                    'displayName' => $interview->location
                ],
                'isReminderOn' => true,
                'reminderMinutesBeforeStart' => $interview->reminder_minutes
            ];

            $response = $graphServiceClient->me()->events()->post($event);
            
            return $response->getId();

        } catch (\Exception $e) {
            \Log::error('Failed to create Microsoft Calendar event', [
                'interview_id' => $interview->id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Update Google Calendar event.
     */
    private function updateGoogleCalendarEvent(Interview $interview, string $eventId): void
    {
        try {
            $client = $this->getGoogleClient();
            $service = new GoogleCalendar($client);
            $calendarId = config('services.google.calendar_id', 'primary');
            
            $event = $service->events->get($calendarId, $eventId);
            
            $event->setSummary($this->getEventTitle($interview));
            $event->setDescription($this->getEventDescription($interview));
            $event->setStart(new \Google\Service\Calendar\EventDateTime([
                'dateTime' => $interview->scheduled_at->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]));
            $event->setEnd(new \Google\Service\Calendar\EventDateTime([
                'dateTime' => $interview->end_time->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]));
            $event->setLocation($interview->location);
            
            $service->events->update($calendarId, $eventId, $event);

        } catch (\Exception $e) {
            \Log::error('Failed to update Google Calendar event', [
                'interview_id' => $interview->id,
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update Microsoft Calendar event.
     */
    private function updateMicrosoftCalendarEvent(Interview $interview, string $eventId): void
    {
        try {
            $graphServiceClient = $this->getMicrosoftGraphClient();
            
            $event = [
                'subject' => $this->getEventTitle($interview),
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $this->getEventDescription($interview)
                ],
                'start' => [
                    'dateTime' => $interview->scheduled_at->toISOString(),
                    'timeZone' => config('app.timezone')
                ],
                'end' => [
                    'dateTime' => $interview->end_time->toISOString(),
                    'timeZone' => config('app.timezone')
                ],
                'location' => [
                    'displayName' => $interview->location
                ]
            ];

            $graphServiceClient->me()->events()->byEventId($eventId)->patch($event);

        } catch (\Exception $e) {
            \Log::error('Failed to update Microsoft Calendar event', [
                'interview_id' => $interview->id,
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel Google Calendar event.
     */
    private function cancelGoogleCalendarEvent(string $eventId): void
    {
        try {
            $client = $this->getGoogleClient();
            $service = new GoogleCalendar($client);
            $calendarId = config('services.google.calendar_id', 'primary');
            
            $service->events->delete($calendarId, $eventId);

        } catch (\Exception $e) {
            \Log::error('Failed to cancel Google Calendar event', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel Microsoft Calendar event.
     */
    private function cancelMicrosoftCalendarEvent(string $eventId): void
    {
        try {
            $graphServiceClient = $this->getMicrosoftGraphClient();
            $graphServiceClient->me()->events()->byEventId($eventId)->delete();

        } catch (\Exception $e) {
            \Log::error('Failed to cancel Microsoft Calendar event', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Google Client instance.
     */
    private function getGoogleClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setApplicationName('Recruitment System');
        $client->setScopes(GoogleCalendar::CALENDAR);
        $client->setAuthConfig(config('services.google.credentials_path'));
        $client->setAccessType('offline');
        
        return $client;
    }

    /**
     * Get Microsoft Graph Client instance.
     */
    private function getMicrosoftGraphClient(): GraphServiceClient
    {
        // Implementation depends on Microsoft Graph SDK setup
        // This is a placeholder for the actual implementation
        throw new \Exception('Microsoft Graph client not implemented yet');
    }

    /**
     * Check if Google Calendar is enabled.
     */
    private function isGoogleCalendarEnabled(): bool
    {
        return config('services.google.calendar_enabled', false) && 
               config('services.google.credentials_path');
    }

    /**
     * Check if Microsoft Calendar is enabled.
     */
    private function isMicrosoftCalendarEnabled(): bool
    {
        return config('services.microsoft.calendar_enabled', false);
    }

    /**
     * Get event title for calendar.
     */
    private function getEventTitle(Interview $interview): string
    {
        $candidateName = $interview->application->candidate_name ?? 
                        $interview->application->user->name ?? 
                        'Candidate';
                        
        $jobTitle = $interview->application->job->title ?? 'Position';
        
        return "Interview: {$candidateName} - {$jobTitle}";
    }

    /**
     * Get event description for calendar.
     */
    private function getEventDescription(Interview $interview): string
    {
        $application = $interview->application;
        $candidateName = $application->candidate_name ?? $application->user->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        
        $description = "Interview Details:\n\n";
        $description .= "Candidate: {$candidateName}\n";
        $description .= "Position: {$jobTitle}\n";
        $description .= "Type: {$interview->type_text}\n";
        $description .= "Duration: {$interview->duration_text}\n";
        
        if ($interview->meeting_link) {
            $description .= "Meeting Link: {$interview->meeting_link}\n";
        }
        
        if ($interview->agenda) {
            $description .= "\nAgenda:\n{$interview->agenda}\n";
        }
        
        if ($interview->preparation_notes) {
            $description .= "\nPreparation Notes:\n{$interview->preparation_notes}\n";
        }
        
        return $description;
    }

    /**
     * Get event attendees for Google Calendar.
     */
    private function getEventAttendees(Interview $interview): array
    {
        $attendees = [];
        
        // Add interviewer
        $attendees[] = ['email' => $interview->interviewer->email];
        
        // Add candidate if they have an email
        $candidateEmail = $interview->application->candidate_email ?? 
                         $interview->application->user->email ?? null;
                         
        if ($candidateEmail) {
            $attendees[] = ['email' => $candidateEmail];
        }
        
        // Add additional interviewers for panel interviews
        if ($interview->additional_interviewers) {
            foreach ($interview->additional_interviewers as $interviewerId) {
                $user = \App\Models\User::find($interviewerId);
                if ($user) {
                    $attendees[] = ['email' => $user->email];
                }
            }
        }
        
        return $attendees;
    }

    /**
     * Get event attendees for Microsoft Calendar.
     */
    private function getMicrosoftEventAttendees(Interview $interview): array
    {
        $attendees = [];
        
        // Add interviewer
        $attendees[] = [
            'emailAddress' => [
                'address' => $interview->interviewer->email,
                'name' => $interview->interviewer->name
            ]
        ];
        
        // Add candidate if they have an email
        $candidateEmail = $interview->application->candidate_email ?? 
                         $interview->application->user->email ?? null;
        $candidateName = $interview->application->candidate_name ?? 
                        $interview->application->user->name ?? 'Candidate';
                         
        if ($candidateEmail) {
            $attendees[] = [
                'emailAddress' => [
                    'address' => $candidateEmail,
                    'name' => $candidateName
                ]
            ];
        }
        
        return $attendees;
    }

    /**
     * Get conference data for video interviews.
     */
    private function getConferenceData(Interview $interview): ?array
    {
        if ($interview->type === 'video' && $interview->meeting_link) {
            return [
                'createRequest' => [
                    'requestId' => 'interview-' . $interview->id,
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                ]
            ];
        }
        
        return null;
    }
}
