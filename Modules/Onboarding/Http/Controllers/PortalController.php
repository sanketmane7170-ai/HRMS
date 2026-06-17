<?php

namespace Modules\Onboarding\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Country;
use App\Models\UserBankDetail;
use App\Models\UserEmergencyContact;
use App\Models\UserProfile;
use App\Enums\Gender;
use App\Enums\MartialStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Onboarding\Emails\PasswordResetOtp;

class PortalController extends Controller
{
    /**
     * Display the portal landing page.
     */
    public function index()
    {
        return view('onboarding::portal.index');
    }

    /**
     * Display the portal login page.
     */
    public function showLogin()
    {
        if (Auth::guard('portal')->check() && Auth::guard('portal')->user()->hasRole('new-hire')) {
            return redirect()->route('portal.dashboard');
        }
        return view('onboarding::portal.auth.login');
    }


    /**
     * Handle portal authentication.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('portal')->attempt($request->only('email', 'password'))) {
            $user = Auth::guard('portal')->user();

            if ($user->hasRole('new-hire')) {
                return redirect()->intended(route('portal.dashboard'));
            }

            // If not a new hire, logout and reject
            Auth::guard('portal')->logout();
            return back()->withErrors(['email' => 'Access denied. This portal is for new hires only.']);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    /**
     * Display the portal dashboard.
     */
    public function dashboard()
    {
        $user = Auth::guard('portal')->user()->load(['onboardingRecord', 'visaProcess', 'complianceRecord', 'profile']);
        return view('onboarding::portal.dashboard', compact('user'));
    }


    /**
     * Display the personal info form.
     */
    public function showPersonalInfo()
    {
        $user = Auth::guard('portal')->user()->load(['profile', 'bankDetail', 'emergencyContacts']);
        $countries = Country::orderBy('name')->get();
        $genders = Gender::cases();
        $maritalStatuses = MartialStatus::cases();

        return view('onboarding::portal.forms.personal_info', compact('user', 'countries', 'genders', 'maritalStatuses'));
    }

    /**
     * Save the personal info form.
     */
    public function savePersonalInfo(Request $request)
    {
        $user = Auth::guard('portal')->user();
        
        $request->validate([
            // Profile Info
            'personal_email' => 'nullable|email',
            'personal_phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'martial_status' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'bio' => 'nullable|string',
            'address' => 'nullable|string',
            
            // Bank Details
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'iba_number' => 'nullable|string',
            'swift_code' => 'nullable|string',
            'routing_number' => 'nullable|string',

            // Emergency Contact Details
            'emergency_name' => 'nullable|string',
            'emergency_relation' => 'nullable|string',
            'emergency_phone' => 'nullable|string',
            'emergency_email' => 'nullable|email',
            'emergency_home_country' => 'nullable|string',
            'emergency_home_address' => 'nullable|string',
            'emergency_local_address' => 'nullable|string',
        ]);

        // Update or Create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'personal_email' => $request->personal_email,
                'personal_phone' => $request->personal_phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'martial_status' => $request->martial_status,
                'country_id' => $request->country_id,
                'bio' => $request->bio,
                'address' => $request->address,
            ]
        );

        // Update or Create Bank Details
        $user->bankDetail()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'iba_number' => $request->iba_number,
                'swift_code' => $request->swift_code,
                'routing_number' => $request->routing_number,
            ]
        );

        // Update or Create Emergency Contact
        $user->emergencyContacts()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'emergency_name' => $request->emergency_name,
                'emergency_relation' => $request->emergency_relation,
                'emergency_phone' => $request->emergency_phone,
                'emergency_email' => $request->emergency_email,
                'emergency_home_country' => $request->emergency_home_country,
                'emergency_home_address' => $request->emergency_home_address,
                'emergency_local_address' => $request->emergency_local_address,
            ]
        );

        // Update onboarding record progress
        $record = \Modules\Onboarding\Entities\OnboardingRecord::where('user_id', $user->id)->first();
        if ($record) {
            $record->update([
                'progress_percent' => 33, // 1/3 phases complete
                'status' => 'in_progress'
            ]);
        }

        return redirect()->route('portal.dashboard')->with('success', 'Information saved successfully!');
    }

    /**
     * Display the photo upload form.
     */
    public function showPhotoUpload()
    {
        $user = Auth::guard('portal')->user();
        return view('onboarding::portal.forms.photo_upload', compact('user'));
    }


    /**
     * Save the profile photo.
     */
    public function savePhoto(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::guard('portal')->user();

        if ($request->hasFile('profile_image')) {
            // Store in private storage (Sanket - ONB-SEC-002)
            $image = $request->file('profile_image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profile-images', $name, 'local');
            
            $user->update(['profile_image' => $path]);
        }

        // Update onboarding record progress
        $record = \Modules\Onboarding\Entities\OnboardingRecord::where('user_id', $user->id)->first();
        if ($record) {
            $record->update([
                'progress_percent' => 66, // 2/3 phases complete
            ]);
        }

        return redirect()->route('portal.dashboard')->with('success', 'Profile photo uploaded successfully!');
    }

    /**
     * Display the documents upload form.
     */
    public function showDocuments()
    {
        $user = Auth::guard('portal')->user();
        $documents = \App\Models\UserDocument::where('user_id', $user->id)->get();
        return view('onboarding::portal.forms.document_upload', compact('user', 'documents'));
    }

    /**
     * Save a portal document.
     */
    public function saveDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240',
            'document_type' => 'required|string',
        ]);

        $user = Auth::guard('portal')->user();

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $name = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('user-documents', $name, 'local');
            
            \App\Models\UserDocument::create([
                'user_id' => $user->id,
                'type' => $request->document_type,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'issue_date' => now(), // Defaulting for now
                'expiry_date' => now()->addYear(), // Defaulting for now
                'status' => 'pending',
            ]);
        }

        // Update progress if all steps done (roughly)
        $docCount = \App\Models\UserDocument::where('user_id', $user->id)->count();
        if ($docCount >= 3) {
            $record = \Modules\Onboarding\Entities\OnboardingRecord::where('user_id', $user->id)->first();
            if ($record) {
                $record->update([
                    'progress_percent' => 100,
                    'status' => 'completed'
                ]);
            }
        }

        return redirect()->back()->with('success', 'Document uploaded successfully!');
    }


    /**
     * Delete a portal document (Bug 17).
     */
    public function deleteDocument($id)
    {
        $user = Auth::guard('portal')->user();
        $document = \App\Models\UserDocument::where('user_id', $user->id)->findOrFail($id);

        // Defer file deletion until after DB commit (Sanket - ONB-DATA-004)
        $filePath = $document->path;
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($document) {
            $document->delete();
        });
        
        // Delete file from storage AFTER successful DB commit
        if ($filePath && file_exists(storage_path('app/' . $filePath))) {
            unlink(storage_path('app/' . $filePath));
        }

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Handle portal logout.
     */
    public function logout()
    {
        Auth::guard('portal')->logout();
        return redirect()->route('portal.index');
    }

    /**
     * Show Forgot Password Form - Sanket
     */
    public function showForgotPassword()
    {
        return view('onboarding::portal.auth.forgot_password');
    }

    /**
     * Send OTP to user email - Sanket
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::role('new-hire')->where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'If this email exists in our records, you will receive an OTP.']);
        }

        $otp = rand(100000, 999999);
        Cache::put('portal_otp_' . $user->email, $otp, now()->addMinutes(10));

        try {
            Mail::to($user->email)->send(new PasswordResetOtp($user->name, $otp));
            session(['reset_email' => $user->email]);
            return redirect()->route('portal.password.otp')->with('success', 'OTP has been sent to your email.');
        } catch (\Exception $e) {
            \Log::error('OTP Email Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again later.']);
        }
    }

    /**
     * Show OTP Verify Form - Sanket
     */
    public function showVerifyOtp()
    {
        if (!session('reset_email')) {
            return redirect()->route('portal.password.request');
        }
        return view('onboarding::portal.auth.verify_otp');
    }

    /**
     * Verify OTP - Sanket
     */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('portal.password.request');
        }

        $cachedOtp = Cache::get('portal_otp_' . $email);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            session(['otp_verified' => true]);
            return redirect()->route('portal.password.reset')->with('success', 'OTP verified. Please set your new password.');
        }

        return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
    }

    /**
     * Show Reset Password Form - Sanket
     */
    public function showResetPassword()
    {
        if (!session('otp_verified') || !session('reset_email')) {
            return redirect()->route('portal.password.request');
        }
        return view('onboarding::portal.auth.reset_password');
    }

    /**
     * Reset Password - Sanket
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $email = session('reset_email');
        if (!$email || !session('otp_verified')) {
            return redirect()->route('portal.password.request');
        }

        $user = User::role('new-hire')->where('email', $email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Clear session and cache
            Cache::forget('portal_otp_' . $email);
            session()->forget(['reset_email', 'otp_verified']);

            return redirect()->route('portal.login')->with('success', 'Password reset successful. Please login with your new password.');
        }

        return redirect()->route('portal.login')->with('error', 'Something went wrong.');
    }
}
