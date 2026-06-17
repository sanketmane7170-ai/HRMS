<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\Application;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Recruitment\ApplicationSubmittedNotification;

class RecruitmentController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'employee-jobs');
    }

    /**
     * Display available job listings for employees
     */
    public function index(Request $request)
    {
        view()->share('activeLink', 'employee-jobs');
        
        $jobs = Job::with(['department', 'role'])
            ->where('status', 'active')
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $departments = \App\Models\Department::whereHas('recruitmentJobs', function ($query) {
            $query->where('status', 'active');
        })->get();

        return view('employee.recruitment.jobs.index', compact('jobs', 'departments'));
    }

    /**
     * Display job details
     */
    public function show($id)
    {
        $job = Job::with(['department', 'role'])
            ->where('status', 'active')
            ->findOrFail($id);

        // Check if current user has already applied
        $hasApplied = Application::where('job_id', $job->id)
            ->where('user_id', Auth::id())
            ->exists();

        return view('employee.recruitment.jobs.show', compact('job', 'hasApplied'));
    }

    /**
     * Apply for a job
     */
    public function apply(Request $request, $id)
    {
        $job = Job::where('status', 'active')->findOrFail($id);
        $user = Auth::user();

        // Check if user has already applied
        $existingApplication = Application::where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingApplication) {
            return back()->with('error', 'You have already applied for this position.');
        }

        $validator = Validator::make($request->all(), [
            'cover_letter' => 'required|string|max:2000',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
        }

        // Create application
        $application = Application::create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'resume_url' => $resumePath,
            'current_stage' => 'applied',
            'applied_on' => now(),
            'notes' => $request->cover_letter,
        ]);

        // Notify HR/Admin about new application
        $this->notifyHRAboutNewApplication($application);

       return redirect()->route('backend.employee.jobs.index')
    ->with('success', 'Your application has been submitted successfully!');
    }

    /**
     * Display user's applications
     */
    public function applications()
    {
        view()->share('activeLink', 'employee-applications');
        
        $applications = Application::with(['job.department'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('employee.recruitment.applications.index', compact('applications'));
    }

    /**
     * Show application details
     */
    public function applicationDetails($id)
    {
        view()->share('activeLink', 'employee-applications');

        $application = Application::with(['job.department', 'job.role', 'offers'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('employee.recruitment.applications.show', compact('application'));
    }

    /**
     * Track application status
     */
    public function trackStatus($id)
    {
        // For now, tracking is same as showing details which includes timeline/status
        return $this->applicationDetails($id);
    }

    /**
     * Respond to a job offer
     */
    public function respondToOffer(Request $request, $id)
    {
        $offer = \Modules\Recruitment\Entities\Offer::whereHas('application', function($query) {
            $query->where('user_id', Auth::id());
        })->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,declined'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid response status.'
            ], 422);
        }

        $offer->update([
            'status' => $request->status,
            'responded_at' => now()
        ]);

        // Update application status if offer is accepted
        if ($request->status === 'accepted') {
            $offer->application->update([
                'current_stage' => 'hired'
            ]);
            $message = 'Congratulations! You have successfully accepted the job offer.';
        } else {
            $message = 'You have declined the job offer. Thank you for considering our opportunity.';
        }

        // Notify HR about the response
        $this->notifyHRAboutOfferResponse($offer, $request->status);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Notify HR/Admin users about new application
     */
    private function notifyHRAboutNewApplication($application)
    {
        // Get users with HR or Admin roles
        $hrUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'hr', 'HR Manager', 'Admin']);
        })->get();

        // Send notification to each HR/Admin user
        Notification::send($hrUsers, new ApplicationSubmittedNotification($application));
    }

    /**
     * Notify HR/Admin users about offer response
     */
    private function notifyHRAboutOfferResponse($offer, $status)
    {
        // Get users with HR or Admin roles
        $hrUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'hr', 'HR Manager', 'Admin']);
        })->get();

        // Send notification using the dedicated class
        Notification::send($hrUsers, new \App\Notifications\Recruitment\OfferResponseNotification($offer, $status));
    }
}
