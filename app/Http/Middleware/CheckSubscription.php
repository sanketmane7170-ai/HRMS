<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckSubscription
{
    // public function handle($request, Closure $next)
    // {
    //     // Skip for login/logout routes
    //     if ($request->is('login') || $request->is('logout')) {
    //         return $next($request);
    //     }

    //     // Avoid multiple API calls
    //     if (! Session::has('subscription')) {

    //         $subdomain = explode('.', $request->getHost())[0];

    //         try {
    //             $response = Http::timeout(3)
    //                 ->get("https://superadmin.yourdomain.com/api/subscription/" . $subdomain);

    //             if ($response->successful()) {
    //                 Session::put('subscription', $response->json());
    //             }

    //         } catch (\Exception $e) {
    //             // optional: log error
    //         }
    //     }

    //     $sub = Session::get('subscription');

    //     // 🚫 Block if overdue
    //     if ($sub && $sub['status'] == 'overdue') {
    //         auth()->logout();

    //         return redirect()->route('login')->withErrors([
    //             'error' => 'Subscription expired. Please renew.',
    //         ]);
    //     }

    //     return $next($request);
    // }
    // public function handle($request, Closure $next)
    // {
    //     // Skip login/logout routes
    //     if ($request->is('login') || $request->is('logout')) {
    //         return $next($request);
    //     }

    //     // Refresh every 10 minutes
    //     $lastCheck = session('subscription_time');

    //     if (! $lastCheck || now()->diffInMinutes($lastCheck) > 10) {

    //         $subdomain = explode('.', $request->getHost())[0];
    //         // $subdomain = $subdomain === '127' ? 'demo3.WorkPilot.io' : $subdomain;

    //         try {
    //             if ($request->getHost() == '127.0.0.1') {
    //                 $subdomain = 'demo3.WorkPilot.io';
    //                 $response  = \Http::timeout(3)
    //                     ->get("http://127.0.0.1:8082/api/v1/subscription/" . $subdomain);
    //                 dd($lastCheck, $subdomain, $response->status(), $response->json());
    //             } else {
    //                 $response = \Http::timeout(3)
    //                     ->get("https://superadmin.WorkPilot.io/api/v1/subscription/" . $subdomain);
    //             }

    //             if ($response->successful()) {
    //                 session([
    //                     'subscription'      => $response->json(),
    //                     'subscription_time' => now(),
    //                 ]);
    //             }

    //         } catch (\Exception $e) {
    //             // optional log
    //         }
    //     }

    //     $sub = session('subscription');

    //     if ($sub && $sub['status'] === 'suspended') {
    //         auth()->logout();

    //         return redirect()->route('login')->withErrors([
    //             'error' => 'Subscription expired. Please renew.',
    //         ]);
    //     }

    //     return $next($request);
    // }
    // public function handle($request, Closure $next)
    // {
    //     // Skip login/logout routes
    //     if ($request->is('login') || $request->is('logout')) {
    //         return $next($request);
    //     }

    //     // Refresh every 10 minutes
    //     $lastCheck = session('subscription_time');

    //     if (! $lastCheck || now()->diffInMinutes($lastCheck) > 1) {

    //         $host = $request->getHost();

    //         if ($host == '127.0.0.1' || $host == 'localhost') {
    //             $subdomain = 'demo3.WorkPilot.io';

    //             $baseUrl = "http://127.0.0.1:8082";
    //         } else {
    //             // $subdomain = explode('.', $host)[0];
    //             $subdomain = $host;
    //             $baseUrl   = "https://superadmin.WorkPilot.io";
    //         }

    //         try {
    //             $response = \Http::timeout(3)
    //                 ->get($baseUrl . "/api/v1/subscription/" . $subdomain);

    //             if ($response->successful()) {
    //                 session([
    //                     'subscription'      => $response->json(),
    //                     'subscription_time' => now(),
    //                 ]);
    //             }

    //         } catch (\Exception $e) {
    //             // optional log
    //         }
    //     }

    //     $sub = session('subscription');

    //     if ($sub) {

    //         // 🚫 HARD BLOCK → STATUS
    //         if ($sub['status'] === 'suspended') {

    //             if ($request->expectsJson()) {

    //                 // Revoke token instead of logout
    //                 if ($request->user()) {
    //                     $request->user()->currentAccessToken()?->delete();
    //                 }

    //                 return response()->success(__trans('Your account has been deactivated. Please contact the MOM team.'), []);
    //             }

    //             auth()->logout();

    //             return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the MOM team.'])->withInput($request->only('email'));
    //         }

    //         // ⚠️ SOFT WARNING → PAYMENT
    //         if (in_array($sub['payment_status'], ['pending', 'overdue'])) {

    //             session()->flash('subscription_warning', [
    //                 'message'  => $sub['payment_status_message'] ?? 'Payment pending',
    //                 'type'     => $sub['payment_status'] === 'overdue' ? 'danger' : 'warning',
    //                 'due_date' => $sub['end_date'] ?? null,
    //             ]);
    //         }
    //     }

    //     return $next($request);
    // }

    public function handle($request, Closure $next)
    {
        Log::info('Subscription Middleware Start', [
            'url'     => $request->fullUrl(),
            'user_id' => optional($request->user())->id,
        ]);

        if (! $request->user()) {
            return $next($request);
        }

        // // Skip login/logout routes
        // if ($request->is('login') || $request->is('logout')) {
        //     Log::info('Skipping middleware for auth routes');
        //     return $next($request);
        // }

        // Refresh every 1 minute
        $lastCheck = session('subscription_time');

        Log::info('Last subscription check', [
            'last_check' => $lastCheck,
        ]);

        if (! $lastCheck || now()->diffInMinutes($lastCheck) > 1) {

            Log::info('Refreshing subscription from API');

            $host = $request->getHost();

            if ($host == '127.0.0.1' || $host == 'localhost') {
                $subdomain = 'demo3.WorkPilot.io';
                $baseUrl   = "http://127.0.0.1:8082";

                Log::info('Local environment detected', [
                    'subdomain' => $subdomain,
                    'baseUrl'   => $baseUrl,
                ]);
            } else {
                $subdomain = $host;
                $baseUrl   = "https://superadmin.WorkPilot.io";

                Log::info('Production environment detected', [
                    'subdomain' => $subdomain,
                    'baseUrl'   => $baseUrl,
                ]);
            }

            try {
                Log::info('Calling subscription API', [
                    'url' => $baseUrl . "/api/v1/subscription/" . $subdomain,
                ]);

                $response = Http::timeout(3)
                    ->get($baseUrl . "/api/v1/subscription/" . $subdomain);

                Log::info('API response received', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);

                if ($response->successful()) {
                    session([
                        'subscription'      => $response->json(),
                        'subscription_time' => now(),
                    ]);

                    Log::info('Subscription stored in session');
                } else {
                    Log::warning('Subscription API failed', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Subscription API exception', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info('Using cached subscription data');
        }

        $sub = session('subscription');
        $sub = is_array($sub) ? $sub : [];

// Normalize keys (important)
        $sub = array_merge([
            'status'                 => null,
            'payment_status'         => null,
            'payment_status_message' => null,
            'end_date'               => null,
        ], $sub);

        Log::info('Subscription session data', [
            'subscription' => $sub,
        ]);

        if ($sub) {

            // 🚫 HARD BLOCK → STATUS
            if ($sub['status'] === 'suspended') {

                Log::warning('User suspended - blocking access', [
                    'user_id' => optional($request->user())->id,
                ]);

                if ($request->is('api/*') || $request->expectsJson()) {

                    if ($request->user()) {
                        $request->user()->currentAccessToken()?->delete();

                        Log::info('API token revoked for suspended user');
                    }

                    return response()->json([
                        'success' => false,
                        'message' => __trans('Your account has been deactivated. Please contact the MOM team.'),
                        'data'    => ['base_url' => "https://superadmin.WorkPilot.io"],
                    ], 401);
                }

                auth()->logout();

                Log::info('User logged out due to suspension');

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the MOM team.',
                ])->withInput($request->only('email'));
            }

            // ⚠️ SOFT WARNING → PAYMENT
            if ($sub && in_array($sub['payment_status'], ['pending', 'overdue'])) {

                Log::warning('Payment warning triggered', [
                    'status'   => $sub['payment_status'],
                    'due_date' => $sub['end_date'] ?? null,
                ]);

                session()->flash('subscription_warning', [
                    'message'  => $sub['payment_status_message'] ?? 'Payment pending',
                    'type'     => $sub['payment_status'] === 'overdue' ? 'danger' : 'warning',
                    'due_date' => $sub['end_date'] ?? null,
                ]);
            }
        } else {
            Log::warning('No subscription data found in session');
        }

        Log::info('Subscription Middleware End');

        return $next($request);
    }
}
