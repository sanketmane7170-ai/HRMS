<?php

namespace Modules\Resignation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Resignation\Emails\ResignationMail;

class ResignationNotification extends Notification
{
    protected $resignation;
    protected $type;
    protected $data;

    /**
     * Create a new notification instance.
     *
     * @param $resignation
     * @param $type (submitted, approved, rejected, waived, completed)
     * @param array $data
     */
    public function __construct($resignation, $type, $data = [])
    {
        $this->resignation = $resignation;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = $this->getSubject();
        
        return (new MailMessage)
            ->subject($subject)
            ->view('resignation::emails.resignation_notification', [
                'resignation' => $this->resignation,
                'type' => $this->type,
                'data' => $this->data,
                'user' => $notifiable
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $subject = $this->getSubject();
        $actionUrl = $this->getActionUrl($notifiable);
        
        return [
            'resignation_id' => $this->resignation->id,
            'type' => $this->type,
            'title' => 'Resignation Module',
            'message' => $subject,
            'url' => $actionUrl,
            'route' => $actionUrl,
            'action_url' => $actionUrl,
        ];
    }

    protected function getSubject()
    {
        switch ($this->type) {
            case 'submitted':
                return "New Resignation Submitted: " . $this->resignation->employee->name;
            case 'withdrawn':
                return "Resignation Application Withdrawn: " . $this->resignation->employee->name;
            case 'approved':
                return "Resignation Approved - Notice Period Started";
            case 'rejected':
                return "Resignation Application Rejected";
            case 'waived':
                return "Notice Period Waived - Important Update";
            case 'completed':
                return "Resignation Process Completed - Final Settlement Initiated";
            case 'hold':
                return "Resignation Process Put On Hold";
            default:
                return "Resignation Module Update";
        }
    }

    protected function getActionUrl($notifiable)
    {
        // Using named routes for robustness
        if ($notifiable->hasRole('admin') || $notifiable->hasRole('hr') || $notifiable->hasRole('CEO')) {
            return route('resignation.admin');
        } elseif ($notifiable->hasRole('Manager') || $notifiable->hasRole('Director')) {
            return route('resignation.manager');
        } else {
            return route('resignation.employee');
        }
    }
}
