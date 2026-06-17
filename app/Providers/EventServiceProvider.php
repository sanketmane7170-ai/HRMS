<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use App\Observers\DepartmentObserver;
use App\Observers\SettingObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Attendance\Entities\Attendance;
use App\Observers\AttendanceObserver;
use App\Events\OnlineStatusChanged;
use App\Listeners\UpdateOnlineStatus;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OnlineStatusChanged::class => [
            UpdateOnlineStatus::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Department::observe(DepartmentObserver::class);
        Setting::observe(SettingObserver::class);
        Attendance::observe(AttendanceObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
