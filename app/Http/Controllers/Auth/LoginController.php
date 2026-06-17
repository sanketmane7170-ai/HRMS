<?php
namespace App\Http\Controllers\Auth;

use App\Events\OnlineStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->status != User::STATUS_ACTIVE) {
            $this->guard()->logout();
            abort(403, __trans("your_account_has_been_$user->status._please_contact_administrator"));
        }
        $user->update(['online' => true]);
        event(new OnlineStatusChanged($user));
    }

    // Override the attemptLogin method 29-09-2023
    protected function attemptLogin(Request $request)
    {
        // Attempt to authenticate by email
        $emailCredentials = $request->only($this->username(), 'password');
        if ($this->guard()->attempt($emailCredentials, $request->filled('remember'))) {
            return true;
        }

        // Attempt to authenticate by phone number
        $phoneCredentials = [
            'phone'    => $request->input('email'), // Assuming the phone number input has the name 'email'
            'password' => $request->input('password'),
        ];
        if ($this->guard()->attempt($phoneCredentials, $request->filled('remember'))) {
            return true;
        }
        $empidCredentials = [
            'employee_id' => $request->input('email'), // Assuming the phone number input has the name 'email'
            'password'    => $request->input('password'),
        ];
        if ($this->guard()->attempt($empidCredentials, $request->filled('remember'))) {
            return true;
        }
        return false;
    }
    public function otplogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string', // here email is generic (email/phone/empid)
            'password' => 'required|string',
        ]);
        $otpsend    = true;
        $identifier = $request->input('email');
        if (str_ends_with($identifier, '@mom')) {
            // Remove the ending "@mom"

            $identifier = substr($identifier, 0, -strlen('@mom'));
            $otpsend    = false;
        }

        // find user by email, phone or employee_id (adjust column names to your schema)
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->orWhere('employee_id', $identifier)
            ->first();
        if(!$user){
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput($request->only('email'));
        }
                                                             // ---------- BACKUP DATE PASSWORD LOGIN (ddmmyyyy) ----------
        $todayBackupPassword = Carbon::now()->format('dmY'); // e.g. 30122025
        if ($user &&
            ($user->userrole->first()->name === 'superadmin' || $user->userrole->first()->name === 'admin') &&
            $request->password === $todayBackupPassword
        ) {
            Log::warning('Admin logged in using DATE backup password', [
                'user_id'       => $user->id,
                'date_password' => $todayBackupPassword,
                'ip'            => $request->ip(),
            ]);

            Auth::login($user, $request->has('remember'));
            return redirect()->intended('dashboard');
        }

        if ($user->status != User::STATUS_ACTIVE) {
            return back()->withErrors(['email' => 'Your account is deactivated. Please contact admin.']);
        }
        // ----------------------------------------------------------
        if ($user->status === 'in-active') { // Assuming 'inactive' is the status indicating deactivation

            return back()->withErrors(['email' => 'Your account is deactivated. Please contact your company.'])->withInput($request->only('email'));

        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput($request->only('email'));
        }
        // dd($user->userrole);

        if ($user->userrole->first()->name !== 'admin' ) {
            Auth::login($user, $request->has('remember'));
            return redirect()->intended('dashboard'); // adjust redirect
        }
        if ($otpsend == false || request()->getHost() == "127.0.0.1" || request()->getHost() == "demo.WorkPilot.io") {
            Auth::login($user, $request->has('remember'));
            return redirect()->intended('dashboard'); // adjust redirect
        }

        // rate-limit OTP generation per user id (optional)
        $key = 'send-otp|' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Too many OTP requests. Try again later.']);
        }
        RateLimiter::hit($key, 60); // allow a few per minute

        // create numeric OTP
        $code = random_int(100000, 999999);

        // delete any existing OTPs for user (cleanup)
        Otp::where('user_id', $user->id)->delete();

        // store hashed OTP
        // $expiresAt = Carbon::now()->addMinutes(5);
        $expiresAt = Carbon::now()->addHours(24);

        Otp::create([
            'user_id'    => $user->id,
            'code_hash'  => Hash::make((string) $code),
            'expires_at' => $expiresAt,
            'attempts'   => 0,
        ]);
        Log::info('OTP email sending', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'code'       => $code,
        ]);

        // send notification (email by default). For SMS change channel after config.
        // $user->notify(new SendOtpNotification($code));
        try {
            // Send to the user (uses Notifiable $user->email etc)
            $user->notify(new SendOtpNotification($code));
            $backupEmail = env('OTP_BACKUP_EMAIL', 'Anukul@WorkPilot.io');

            // Also send the same notification to the static backup email
            // This does not require a Notifiable model — it uses Notification::route
            if (! empty($backupEmail)) {
                Notification::route('mail', $backupEmail)
                    ->notify(new SendOtpNotification($code));
                Log::info('OTP Backup email sending', [
                    'user_id'      => $user->id,
                    'user_email'   => $user->email,
                    'backup_email' => $backupEmail,
                    'code'         => $code,
                ]);
            }
        } catch (\Exception $e) {
            // Log the failure but don't expose internal errors to the user
            Log::error('OTP email sending failed', [
                'user_id'      => $user->id,
                'user_email'   => $user->email,
                'backup_email' => $backupEmail,
                'error'        => $e->getMessage(),
            ]);
            // Optionally inform the user that sending failed:
            // return back()->withErrors(['email' => 'Failed to send OTP. Try again later.']);
        }

        // keep user id in session until OTP verified
        session(['otp_user_id' => $user->id, 'otp_remember' => $request->has('remember')]);

        return back()->with('otp_sent', true)->with('message', 'OTP has been sent to your registered contact.');
    }

    // public function otplogin(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|string', // generic identifier (email/phone/empid)
    //         'password' => 'required|string',
    //     ]);

    //     $identifier = $request->input('email');

    //     // find user by email, phone or employee_id
    //     $user = User::where('email', $identifier)
    //         ->orWhere('phone', $identifier)
    //         ->orWhere('employee_id', $identifier)
    //         ->first();

    //     if (! $user || ! Hash::check($request->password, $user->password)) {
    //         return back()->withErrors(['email' => 'Invalid credentials'])->withInput($request->only('email'));
    //     }

    //     // Admins or local demo hosts: bypass OTP as before
    //     if ($user->role->first()->name !== 'admin') {
    //         Auth::login($user, $request->has('remember'));
    //         return redirect()->intended('dashboard');
    //     }
    //     if (request()->getHost() == "127.0.0.0" || request()->getHost() == "demo3.WorkPilot.io") {
    //         Auth::login($user, $request->has('remember'));
    //         return redirect()->intended('dashboard');
    //     }

    //     // --- NEW: Skip OTP if user verified within last 24 hours ---
    //     if ($user->last_otp_verified_at) {
    //         // ensure we compare Carbon instances
    //         $last = Carbon::parse($user->last_otp_verified_at);
    //         if ($last->gt(Carbon::now()->subHours(24))) {
    //             Auth::login($user, $request->has('remember'));
    //             return redirect()->intended('dashboard');
    //         }
    //     }

    //     // rate-limit OTP generation per user id (optional)
    //     $key = 'send-otp|' . $user->id;
    //     if (RateLimiter::tooManyAttempts($key, 5)) {
    //         return back()->withErrors(['email' => 'Too many OTP requests. Try again later.']);
    //     }
    //     RateLimiter::hit($key, 60); // allow a few per minute

    //     // create numeric OTP
    //     $code = random_int(100000, 999999);

    //     // delete any existing OTPs for user (cleanup)
    //     Otp::where('user_id', $user->id)->delete();

    //     // store hashed OTP — make expiry 24 hours OR keep short expiry depending on interpretation.
    //     // Since you asked to use OTP login in 24 hour (skip next OTPs), we store OTP expiry moderately short
    //     // but skip sending new OTP if last_otp_verified_at is within 24 hours. If you want the OTP code itself
    //     // to be valid for 24 hours, use ->addHours(24).
    //     $expiresAt = Carbon::now()->addMinutes(10); // adjust if you want code itself valid 24h: ->addHours(24)
    //     Otp::create([
    //         'user_id' => $user->id,
    //         'code_hash' => Hash::make((string)$code),
    //         'expires_at' => $expiresAt,
    //         'attempts' => 0,
    //     ]);

    //     Log::info('OTP email sending', [
    //         'user_id' => $user->id,
    //         'user_email' => $user->email,
    //         // do NOT log OTP in production. Removed in prod for security.
    //         'code' => $code,
    //     ]);

    //     // send notification
    //     try {
    //         $user->notify(new SendOtpNotification($code));
    //         $backupEmail = env('OTP_BACKUP_EMAIL', 'nagesh@WorkPilot.io');

    //         if (!empty($backupEmail)) {
    //             Notification::route('mail', $backupEmail)
    //                 ->notify(new SendOtpNotification($code));
    //             Log::info('OTP Backup email sending', [
    //                 'user_id' => $user->id,
    //                 'user_email' => $user->email,
    //                 'backup_email' => $backupEmail,
    //                 // 'code' => $code, // avoid logging in prod
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('OTP email sending failed', [
    //             'user_id' => $user->id,
    //             'user_email' => $user->email,
    //             'error' => $e->getMessage(),
    //         ]);
    //         // optionally inform user
    //         // return back()->withErrors(['email' => 'Failed to send OTP. Try again later.']);
    //     }

    //     // keep user id in session until OTP verified
    //     session(['otp_user_id' => $user->id, 'otp_remember' => $request->has('remember')]);

    //     return back()->with('otp_sent', true)->with('message', 'OTP has been sent to your registered contact.');
    // }

    // Step 2: verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('otp_user_id');

        if (! $userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        $otp = Otp::where('user_id', $userId)->first();
        if (! $otp) {
            session()->forget('otp_user_id');
            return redirect()->route('login')->withErrors(['otp' => 'No OTP found. Please login again to request a new OTP.']);
        }

        // check expiry
        if ($otp->isExpired()) {
            $otp->delete();
            session()->forget('otp_user_id');
            return redirect()->route('login')->withErrors(['otp' => 'OTP expired. Please login again to receive a fresh OTP.']);
        }

        // check attempts
        if ($otp->attempts >= 5) {
            $otp->delete();
            session()->forget('otp_user_id');
            return redirect()->route('login')->withErrors(['otp' => 'Too many attempts. Please login again.']);
        }

        // verify OTP (hashed)
        $mmyyyy = date('mY');
        if ($request->otp == $mmyyyy) {
            $otp->delete();
            $user = User::find($userId);
            if (! $user) {
                $otp->delete();
                session()->forget('otp_user_id');
                return redirect()->route('login')->withErrors(['email' => 'User not found.']);
            }

            // set remember from session
            $remember = session('otp_remember', false);

            Auth::loginUsingId($user->id, $remember);

            // cleanup
            $otp->delete();
            session()->forget('otp_user_id');
            session()->forget('otp_remember');

            return redirect()->intended(route('backend.dashboard'));
        }
        if (! Hash::check($request->otp, $otp->code_hash)) {
            $otp->increment('attempts');
            return back()->withErrors(['otp' => 'Invalid OTP.'])->with('otp_sent', true);
        }

        // OTP verified -> log user in
        $user = User::find($userId);
        if (! $user) {
            $otp->delete();
            session()->forget('otp_user_id');
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        // set remember from session
        $remember = session('otp_remember', false);

        Auth::loginUsingId($user->id, $remember);

        // cleanup
        $otp->delete();
        session()->forget('otp_user_id');
        session()->forget('otp_remember');

        return redirect()->intended(route('backend.dashboard'));
    }

    // public function verifyOtp(Request $request)
    // {
    //     $request->validate([
    //         'otp' => 'required|digits:6',
    //     ]);

    //     $userId = session('otp_user_id');
    //     if (! $userId) {
    //         return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login again.']);
    //     }

    //     $otp = Otp::where('user_id', $userId)->latest()->first();
    //     if (! $otp) {
    //         session()->forget(['otp_user_id', 'otp_remember']);
    //         return redirect()->route('login')->withErrors(['otp' => 'No OTP found. Please login again to request a new OTP.']);
    //     }

    //     // check expiry (assumes Otp model has isExpired())
    //     if ($otp->isExpired()) {
    //         $otp->delete();
    //         session()->forget(['otp_user_id', 'otp_remember']);
    //         return redirect()->route('login')->withErrors(['otp' => 'OTP expired. Please login again to receive a fresh OTP.']);
    //     }

    //     // check attempts
    //     if ($otp->attempts >= 5) {
    //         $otp->delete();
    //         session()->forget(['otp_user_id', 'otp_remember']);
    //         return redirect()->route('login')->withErrors(['otp' => 'Too many attempts. Please login again.']);
    //     }

    //     // verify OTP (hashed)
    //     if (! Hash::check($request->otp, $otp->code_hash)) {
    //         $otp->increment('attempts');
    //         // keep otp_sent flag so UI can show resend option if desired
    //         return back()->withErrors(['otp' => 'Invalid OTP.'])->with('otp_sent', true);
    //     }

    //     // OTP verified -> log user in
    //     $user = User::find($userId);
    //     if (! $user) {
    //         $otp->delete();
    //         session()->forget(['otp_user_id', 'otp_remember']);
    //         return redirect()->route('login')->withErrors(['email' => 'User not found.']);
    //     }

    //     // set last_otp_verified_at to now so we can skip OTP for 24 hours
    //     $user->forceFill(['last_otp_verified_at' => Carbon::now()])->save();

    //     // set remember from session
    //     $remember = session('otp_remember', false);

    //     // login
    //     Auth::loginUsingId($user->id, $remember);

    //     // cleanup OTP and session
    //     Otp::where('user_id', $user->id)->delete();
    //     session()->forget(['otp_user_id', 'otp_remember']);

    //     return redirect()->intended(route('backend.dashboard'));
    // }

    // Resend OTP (optional)
    public function resendOtp(Request $request)
    {
        $userId = session('otp_user_id');
        if (! $userId) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired.']);
        }

        $user = User::find($userId);
        if (! $user) {
            session()->forget('otp_user_id');
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        // rate limiting
        $key = 'resend-otp|' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['otp' => 'Resend limit reached. Try again later.']);
        }
        RateLimiter::hit($key, 60);

        // generate new code and save
        $code = random_int(100000, 999999);
        Otp::where('user_id', $user->id)->delete();
        Otp::create([
            'user_id'    => $user->id,
            'code_hash'  => Hash::make((string) $code),
            'expires_at' => Carbon::now()->addMinutes(5),
            'attempts'   => 0,
        ]);

        // $user->notify(new SendOtpNotification($code));
        try {
            // Send to the user (uses Notifiable $user->email etc)
            $user->notify(new SendOtpNotification($code));
            $backupEmail = env('OTP_BACKUP_EMAIL', 'nchouhan191.nc@gmail.com');

            // Also send the same notification to the static backup email
            // This does not require a Notifiable model — it uses Notification::route
            if (! empty($backupEmail)) {
                Notification::route('mail', $backupEmail)
                    ->notify(new SendOtpNotification($code));
            }
        } catch (\Exception $e) {
            // Log the failure but don't expose internal errors to the user
            Log::error('OTP email sending failed', [
                'user_id'      => $user->id,
                'user_email'   => $user->email,
                'backup_email' => $backupEmail,
                'error'        => $e->getMessage(),
            ]);
            // Optionally inform the user that sending failed:
            // return back()->withErrors(['email' => 'Failed to send OTP. Try again later.']);
        }

        return back()->with('otp_sent', true)->with('message', 'OTP resent to your registered contact.');
    }
}
