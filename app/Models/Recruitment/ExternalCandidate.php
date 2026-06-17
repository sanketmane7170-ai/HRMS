<?php

namespace App\Models\Recruitment;

use Illuminate\Notifications\Notifiable;

class ExternalCandidate
{
    use Notifiable;
    
    public $email;
    public $name;
    
    public function __construct($email, $name = null)
    {
        $this->email = $email;
        $this->name = $name ?? 'Candidate';
    }
    
    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
    /**
     * Get the primary key for the model.
     * Used by NotificationFake and other Laravel components.
     */
    public function getKey()
    {
        return $this->email;
    }
}
