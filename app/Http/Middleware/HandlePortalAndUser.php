<?php

namespace App\Http\Middleware;

use App\Models\PortalDetails;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandlePortalAndUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if route has unique_code and user_id
       
        $uniqueCode = $request->route('unique_code');
        $userId     = $request->route('user_id');

         app()->instance('portal', $uniqueCode);
        
        // if ($uniqueCode) {
        //     // Validate portal
        //     $portal = PortalDetails::where('unique_code', $uniqueCode)->first();
        //     if (!$portal) {
        //         return response()->json(['message' => 'Invalid unique code'], 400);
        //     }
        //     app()->instance('portal', $portal);
        // }

        if ($userId) {

            // Validate user
            if ($userId!=auth()->user()->id) {
                return response()->json(['message' => 'Invalid user ID'], 403);
            }
            app()->instance('portal_user', auth()->user());
        }

        // If not passed, fallback to authenticated user
        if (!app()->has('portal_user') && $request->user()) {
            app()->instance('portal_user', $request->user());
        }

        return $next($request);
    }
}
