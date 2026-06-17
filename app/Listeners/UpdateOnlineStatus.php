<?php

namespace App\Listeners;

use App\Events\OnlineStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateOnlineStatus
{
    public function handle(OnlineStatusChanged $event)
    {
        // Log or do something when the status changes
        \Log::info("User {$event->user->name} status changed to " . ($event->user->online ? 'online' : 'offline'));
    }
}
