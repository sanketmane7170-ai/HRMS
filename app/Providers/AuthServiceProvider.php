<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Only superadmin is full-access (god-mode) and bypasses every gate.
        // The 'admin' role is intentionally NOT here anymore: admins are now
        // permission-driven, so a superadmin can restrict an admin to only the
        // modules assigned to their role via the Roles screen.
        Gate::before(function ($user, $ability) {
            if ($user->hasRole(\App\Models\User::ROLE_SUPER_ADMIN)) {
                return true;
            }
        });
    }
}
