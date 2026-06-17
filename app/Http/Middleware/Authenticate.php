<?php
namespace App\Http\Middleware;

use Google\Service\ServiceControl\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
    protected function authenticate($request, array $guards)
    {
        parent::authenticate($request, $guards);
        // ✅ After login check, check user status
        if (auth()->check() && auth()->user()->status !== 'active') {

            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            redirect()->route('login')
                ->withErrors([
                    'account' => 'Your account has been deactivated.'
                ])->send();

            exit;
        }
    }
}
