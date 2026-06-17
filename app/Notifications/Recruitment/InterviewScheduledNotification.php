<?php

namespace App\Notifications\Recruitment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Recruitment\Entities\Interview;

class InterviewScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $interview;

    /**
     * Create a new notification instance.
     */
    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];
        if ($notifiable instanceof \Illuminate\Database\Eloquent\Model) {
            $channels[] = 'database';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->interview->application;
        $candidateName = $application?->candidate_name ?? $application?->user?->name ?? 'Candidate';
        $jobTitle = $application?->job?->title ?? 'Position';
        
        $message = (new MailMessage)
            ->subject("Interview Scheduled: {$candidateName} - {$jobTitle}")
            ->greeting('Interview Scheduled!')
            ->line("An interview has been scheduled for {$candidateName} applying for the {$jobTitle} position.")
            ->line('**Interview Details:**')
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

        if ($this->interview->preparation_notes) {
            $message->line('**Preparation Notes:**')
                   ->line($this->interview->preparation_notes);
        }

        $message->line('Please ensure you are available at the scheduled time.')
               ->action('View Interview Details', route('recruitment.interviews.show', $this->interview->id))
               ->line('Thank you for your participation in our recruitment process!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Interview Scheduled',
            'message' => 'An interview has been scheduled for ' . ($this->interview->application->candidate_name ?? $this->interview->application->user?->name) . ' on ' . $this->interview->scheduled_at->format('M d, Y H:i'),
            'url' => route('recruitment.interviews.show', $this->interview->id),
            'route' => route('recruitment.interviews.show', $this->interview->id),
            'action_url' => route('recruitment.interviews.show', $this->interview->id),
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->application_id,
            'scheduled_at' => $this->interview->scheduled_at->toDateTimeString(),
            'type' => 'interview_scheduled'
        ];
    }
}
