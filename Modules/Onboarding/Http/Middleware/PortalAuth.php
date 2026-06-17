<?php

namespace Modules\Onboarding\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PortalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('portal.login');
        }

        $user = Auth::user();

        // Strictly allow ONLY New Hires
        if (!$user->hasRole('new-hire')) {
            Auth::logout();
            return redirect()->route('portal.login')->with('error', 'Access denied. This portal is for new hires only.');
        }

        return $next($request);
    }
}
