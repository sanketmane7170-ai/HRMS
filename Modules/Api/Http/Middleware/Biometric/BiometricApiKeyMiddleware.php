<?php

namespace Modules\Api\Http\Middleware\Biometric;

use Closure;
use Illuminate\Http\Request;

class BiometricApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     * Validates X-API-KEY header against BIOMETRIC_API_KEY env variable
     * 
     * Author: Sanket - API key authentication for biometric devices
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY');
        $expectedKey = config('biometric.api_key');

        if (!$apiKey || $apiKey !== $expectedKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing API Key.'
            ], 401);
        }

        return $next($request);
    }
}
