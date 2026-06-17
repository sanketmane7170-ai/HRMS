<?php

namespace Modules\Recruitment\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Recruitment\Entities\Application;
use App\Models\User;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, Application $application): bool
    {
        return $user->hasAnyRole(['admin', 'hr', 'HR Manager']) || 
               ($user->hasRole('Manager') && $application->job->hiring_manager_id === $user->id);
    }

    /**
     * Determine whether the user can score the application.
     */
    public function score(User $user, Application $application): bool
    {
        return $user->hasAnyRole(['admin', 'hr', 'HR Manager']) || 
               ($user->hasRole('Manager') && $application->job->hiring_manager_id === $user->id);
    }

    /**
     * Determine whether the user can delete the application.
     */
    public function delete(User $user, Application $application): bool
    {
        return $user->hasAnyRole(['admin', 'hr', 'HR Manager']);
    }
}
