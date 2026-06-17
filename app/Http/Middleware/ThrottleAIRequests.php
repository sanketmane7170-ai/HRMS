<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class ThrottleAIRequests
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
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        $key = 'ai-chat:' . $request->user()->id;

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('AI rate limit exceeded', [
                'user_id' => $request->user()->id,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Too many AI requests. Please wait ' . $seconds . ' seconds.',
                'retry_after' => $seconds
            ], 429);
        }

        // Increment attempts (10 requests per 60 seconds)
        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
