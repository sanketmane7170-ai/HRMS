<?php

namespace App\Notifications\Recruitment;

use Modules\Recruitment\Entities\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class InterviewReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Interview $interview;
    protected string $reminderType;

    /**
     * Create a new notification instance.
     *
     * @param Interview $interview
     * @param string $reminderType ('24_hours', '2_hours', '30_minutes')
     */
    public function __construct(Interview $interview, string $reminderType = '24_hours')
    {
        $this->interview = $interview;
        $this->reminderType = $reminderType;
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Send email for 24-hour and 2-hour reminders
        if (in_array($this->reminderType, ['24_hours', '2_hours'])) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->interview->application;
        $candidateName = $application->candidate_name ?? $application->user->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        $timeUntil = $this->getTimeUntilInterview();
        
        $subject = match($this->reminderType) {
            '24_hours' => "Interview Tomorrow: {$jobTitle}",
            '2_hours' => "Interview in 2 Hours: {$jobTitle}",
            '30_minutes' => "Interview Starting Soon: {$jobTitle}",
            default => "Upcoming Interview: {$jobTitle}"
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Interview Reminder!")
            ->line("This is a friendly reminder about your upcoming interview {$timeUntil}.")
            ->line('**Interview Details:**')
            ->line("👤 **Candidate:** {$candidateName}")
            ->line("💼 **Position:** {$jobTitle}")
            ->line("📅 **Date & Time:** {$this->interview->scheduled_at->format('l, F j, Y \a\t g:i A')}")
            ->line("⏱️ **Duration:** {$this->interview->duration_text}")
            ->line("📍 **Type:** {$this->interview->type_text}");

        if ($this->interview->location) {
            $message->line("🏢 **Location:** {$this->interview->location}");
        }

        if ($this->interview->meeting_link) {
            $message->line("🔗 **Meeting Link:** {$this->interview->meeting_link}");
        }

        if ($this->interview->agenda) {
            $message->line('**Agenda:**')
                   ->line($this->interview->agenda);
        }

        // Add preparation reminders
        $message->line('**Preparation Checklist:**')
               ->line('✅ Review your application and resume')
               ->line('✅ Research the company and position')
               ->line('✅ Prepare questions to ask the interviewer')
               ->line('✅ Test your technology (for virtual interviews)')
               ->line('✅ Plan to arrive 10-15 minutes early');

        if ($this->interview->preparation_notes) {
            $message->line('**Additional Preparation Notes:**')
                   ->line($this->interview->preparation_notes);
        }

        $message->action('View Interview Details', route('recruitment.interviews.show', $this->interview->id))
               ->line('Good luck with your interview!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->application_id,
            'reminder_type' => $this->reminderType,
            'title' => 'Interview Reminder',
            'message' => $this->getDatabaseMessage(),
            'url' => route('recruitment.interviews.show', $this->interview->id),
            'route' => route('recruitment.interviews.show', $this->interview->id),
            'scheduled_at' => $this->interview->scheduled_at->toISOString(),
            'type' => 'interview_reminder',
            'action_url' => route('recruitment.interviews.show', $this->interview->id)
        ];
    }

    /**
     * Get database message based on reminder type
     */
    private function getDatabaseMessage(): string
    {
        $application = $this->interview->application;
        $candidateName = $application->candidate_name ?? $application->user->name ?? 'Candidate';
        $jobTitle = $application->job->title ?? 'Position';
        $timeUntil = $this->getTimeUntilInterview();
        
        return "Interview reminder for {$candidateName} - {$jobTitle} {$timeUntil}";
    }

    /**
     * Get human-readable time until interview
     */
    private function getTimeUntilInterview(): string
    {
        return match($this->reminderType) {
            '24_hours' => 'tomorrow',
            '2_hours' => 'in 2 hours',
            '30_minutes' => 'in 30 minutes',
            default => 'soon'
        };
    }
}
