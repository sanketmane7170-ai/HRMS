<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\ApplicationLog;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\Recruitment\ApplicationSubmittedNotification;
use Carbon\Carbon;
use Exception;

class CareerController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'career');
    }

    /**
     * Display career portal homepage
     */
    public function index(Request $request)
    {
        $jobs = Job::with(['department', 'role'])
            ->where('status', 'active')
            ->external() // Show only external or internal_external jobs
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('job_type', $type);
            })
            ->when($request->location, function ($query, $location) {
                return $query->where('location', 'like', '%' . $location . '%');
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('requirements', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $departments = Department::whereHas('recruitmentJobs', function ($query) {
            $query->where('status', 'active')->external();
        })->get();

        $jobTypes = Job::where('status', 'active')
            ->external()
            ->select('job_type')->distinct()->pluck('job_type');
            
        $locations = Job::where('status', 'active')
            ->external()
            ->select('location')->distinct()->whereNotNull('location')->pluck('location');

        return view('recruitment::career.index', compact('jobs', 'departments', 'jobTypes', 'locations'));
    }

    /**
     * Display job details
     */
    public function jobDetail($id)
    {
        $job = Job::with(['department', 'role'])
            ->external()
            ->findOrFail($id);

        // Redirect if not open (Sanket)
        if (!$job->is_open) {
            return redirect()->route('career.index')->with('error', 'This job posting has closed.');
        }

        // Get related jobs from same department
        $relatedJobs = Job::with(['department', 'role'])
            ->where('status', 'active')
            ->external()
            ->where('department_id', $job->department_id)
            ->where('id', '!=', $job->id)
            ->limit(3)
            ->get();

        return view('recruitment::career.job-detail', compact('job', 'relatedJobs'));
    }

    /**
     * Show job application form
     */
    public function apply($id)
    {
        $job = Job::with(['department', 'role'])
            ->external() // Only allow applications for external and internal_external jobs
            ->findOrFail($id);

        // Block if closed (Sanket)
        if (!$job->is_open) {
            return redirect()->route('career.index')->with('error', 'Applications for this position are no longer being accepted.');
        }

        return view('recruitment::career.apply', compact('job'));
    }

    /**
     * Submit job application
     */
    public function submitApplication(Request $request, $id)
    {
        $job = Job::external()->findOrFail($id);

        // Security Check: Block submission if closed (Sanket)
        if (!$job->is_open) {
            return redirect()->route('career.index')->with('error', 'Sorry, the application deadline for this position has passed.');
        }

        $validator = Validator::make($request->all(), [
            'candidate_name' => 'required|string|max:255',
            'candidate_email' => 'required|email|max:255|unique:recruitment_applications,candidate_email,NULL,id,job_id,' . $id,
            'candidate_phone' => 'nullable|string|max:20',
            'cover_letter' => 'required|string|max:2000',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
            'expected_salary' => 'nullable|numeric|min:0',
            'availability_date' => 'nullable|date|after_or_equal:today', // Changed by Sanket to allow today's date
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'years_experience' => 'nullable|integer|min:0|max:80',
            'current_company' => 'nullable|string|max:255',
            'current_position' => 'nullable|string|max:255',
            'notice_period' => 'nullable|integer|min:0|max:365', // days
            'willing_to_relocate' => 'nullable', // Loosened for checkbox handling - Sanket
            'authorization_to_work' => 'required', // Loosened for checkbox handling - Sanket
            'terms_accepted' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if application already exists
        $existingApplication = Application::where('job_id', $id)
            ->where('candidate_email', $request->candidate_email)
            ->first();

        if ($existingApplication) {
            return redirect()->back()
                ->with('error', 'You have already applied for this position.')
                ->withInput();
        }

        try {
            DB::beginTransaction();
            
            $resumePath = null;
            // Handle resume upload
            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('recruitment/resumes', 'public');
            }

            $application = Application::create([
                'job_id' => $id,
                'candidate_name' => $request->candidate_name,
                'candidate_email' => $request->candidate_email,
                'candidate_phone' => $request->candidate_phone,
                'cover_letter' => $request->cover_letter,
                'resume_path' => $resumePath,
                'expected_salary' => $request->expected_salary,
                'availability_date' => $request->availability_date,
                'linkedin_url' => $request->linkedin_url,
                'portfolio_url' => $request->portfolio_url,
                'years_experience' => $request->years_experience,
                'current_company' => $request->current_company,
                'current_position' => $request->current_position,
                'notice_period' => $request->notice_period,
                'willing_to_relocate' => $request->willing_to_relocate ?? false,
                'authorization_to_work' => $request->authorization_to_work,
                'stage' => 'applied',
                'applied_on' => now()
            ]);

            // Log the application
            ApplicationLog::create([
                'application_id' => $application->id,
                'action' => 'application_submitted',
                'description' => 'Application submitted via career portal',
                'changed_by' => null, // No authenticated user for public applications
                'metadata' => json_encode([
                    'source' => 'career_portal',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
            ]);

            DB::commit();

            // Notify HR/Admin about new application (Wrapped in try-catch to prevent blocking candidate email - Sanket)
            try {
                $this->notifyHRAboutNewApplication($application);
            } catch (\Exception $e) {
                \Log::error('HR Notification failed', ['error' => $e->getMessage()]);
            }

            // Send confirmation email to candidate (Wrapped in try-catch for reliability - Sanket)
            try {
                $candidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name);
                $candidate->notify(new \App\Notifications\Recruitment\ApplicationReceivedNotification($application));
            } catch (\Exception $e) {
                \Log::error('Candidate Confirmation Email failed', ['error' => $e->getMessage(), 'email' => $application->candidate_email]);
            }

            return redirect()->route('career.application-success', $application->id)
                ->with('success', 'Your application has been submitted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            
            \Log::error('Application submission failed', [
                'job_id' => $id,
                'candidate_email' => $request->candidate_email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Delete uploaded file if exists
            if (isset($resumePath) && Storage::disk('public')->exists($resumePath)) {
                Storage::disk('public')->delete($resumePath);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to submit application. Please check your information and try again. If the problem persists, please contact our support team.')
                ->withInput();
        }
    }

    /**
     * Application success page
     */
    public function applicationSuccess($id)
    {
        $application = Application::with(['job'])->findOrFail($id);
        
        return view('recruitment::career.success', compact('application'));
    }

    /**
     * Track application status
     */
    public function trackApplication(Request $request)
    {
        if ($request->isMethod('post')) {
            // Clean application ID if user included '#' prefix (Added by Sanket)
            $appId = ltrim(trim($request->application_id), '#');
            
            // Re-merge cleaned value into request for validation
            $request->merge(['application_id' => $appId]);

            $validator = Validator::make($request->all(), [
                'application_id' => 'required|numeric|exists:recruitment_applications,id',
                'email' => 'required|email'
            ], [
                'application_id.exists' => 'No application found with this ID.',
                'application_id.numeric' => 'The Application ID must be a number.'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $email = strtolower(trim($request->email));

            // Expanded lookup: Check candidate_email OR linked user email (Sanket)
            // Added TRIM and LOWER to both ends to be resilient against whitespace/casing issues
            $application = Application::with(['job', 'logs', 'user'])
                ->where('id', $appId)
                ->where(function($query) use ($email) {
                    $query->whereRaw('TRIM(LOWER(candidate_email)) = ?', [$email])
                          ->orWhereHas('user', function($q) use ($email) {
                              $q->whereRaw('TRIM(LOWER(email)) = ?', [$email]);
                          });
                })
                ->first();

            if (!$application) {
                // Diagnostic lookup: See if ID exists at all (Sanket)
                $idExists = Application::where('id', $appId)->exists();
                
                // Log the attempt for debugging (Sanket)
                \Log::warning('Tracking Auth Failed', [
                    'id' => $appId,
                    'id_exists' => $idExists,
                    'input_email' => $email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                $errorMessage = $idExists 
                    ? 'Authentication failed. Please check if the email address matches the one used in your application.'
                    : 'No application found with this ID.';

                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }

            return view('recruitment::career.track-status', compact('application'));
        }

        return view('recruitment::career.track-application');
    }

    /**
     * Company information page
     */
    public function about()
    {
        return view('recruitment::career.about');
    }

    /**
     * Benefits and culture page
     */
    public function benefits()
    {
        return view('recruitment::career.benefits');
    }

    /**
     * FAQ page
     */
    public function faq()
    {
        // Remove dummy data - FAQs should be managed through admin interface
        $faqs = []; // Empty array - no dummy data
        
        return view('recruitment::career.faq', compact('faqs'));
    }

    /**
     * Contact page
     */
    public function contact()
    {
        return view('recruitment::career.contact');
    }

    // API Methods for mobile app or AJAX requests
    public function apiJobs(Request $request): JsonResponse
    {
        try {
            $jobs = Job::with(['department', 'role'])
                ->where('status', 'active')
                ->external()
                ->when($request->department_id, function ($query, $departmentId) {
                    return $query->where('department_id', $departmentId);
                })
                ->when($request->type, function ($query, $type) {
                    return $query->where('job_type', $type);
                })
                ->when($request->location, function ($query, $location) {
                    return $query->where('location', 'like', '%' . $location . '%');
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%')
                          ->orWhere('description', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 12));

            return response()->json(['success' => true, 'data' => $jobs]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch jobs'], 500);
        }
    }

    public function apiJobDetail($id): JsonResponse
    {
        try {
            $job = Job::with(['department', 'role'])
                ->where('status', 'active')
                ->external()
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => $job]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Job not found'], 404);
        }
    }

    public function apiSubmitApplication(Request $request, $id): JsonResponse
    {
        // Same validation and logic as submitApplication method
        // Return JSON response for API usage
        $job = Job::where('status', 'active')->external()->findOrFail($id);

        // Security Check: Block submission if closed (Sanket - REC-SEC-006)
        if (!$job->is_open) {
            return response()->json(['success' => false, 'message' => 'Job closed'], 400); 
        }

        $validator = Validator::make($request->all(), [
            'candidate_name' => 'required|string|max:255',
            'candidate_email' => 'required|email|max:255',
            'candidate_phone' => 'nullable|string|max:20',
            'cover_letter' => 'required|string|max:2000',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability_date' => 'nullable|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $resumePath = null;
            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('recruitment/resumes', 'public');
            }

            $application = Application::create([
                'job_id' => $id,
                'candidate_name' => $request->candidate_name,
                'candidate_email' => $request->candidate_email,
                'candidate_phone' => $request->candidate_phone,
                'cover_letter' => $request->cover_letter,
                'resume_path' => $resumePath,
                'expected_salary' => $request->expected_salary,
                'availability_date' => $request->availability_date,
                'stage' => 'applied',
                'applied_on' => now()
            ]);

            ApplicationLog::create([
                'application_id' => $application->id,
                'action' => 'application_submitted',
                'description' => 'Application submitted via API',
                'changed_by' => null,
                'metadata' => json_encode([
                    'source' => 'api',
                    'ip_address' => $request->ip()
                ])
            ]);

            DB::commit();

            // Notify HR/Admin about new application (Wrapped - Sanket)
            try {
                $this->notifyHRAboutNewApplication($application);
            } catch (\Exception $e) {
                \Log::error('API: HR Notification failed', ['error' => $e->getMessage()]);
            }

            // Send confirmation email to candidate (Wrapped - Sanket)
            try {
                $candidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name);
                $candidate->notify(new \App\Notifications\Recruitment\ApplicationReceivedNotification($application));
            } catch (\Exception $e) {
                \Log::error('API: Candidate Confirmation Email failed', ['error' => $e->getMessage(), 'email' => $application->candidate_email]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => ['application_id' => $application->id]
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            
            if ($resumePath && Storage::disk('public')->exists($resumePath)) {
                Storage::disk('public')->delete($resumePath);
            }
            
            return response()->json(['success' => false, 'message' => 'Failed to submit application'], 500);
        }
    }

    /**
     * Notify HR/Admin users about new application
     */
    private function notifyHRAboutNewApplication($application)
    {
        $hrUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'hr']);
        })->get();

        // Send notification to each HR/Admin user
        Notification::send($hrUsers, new ApplicationSubmittedNotification($application));
    }
}
