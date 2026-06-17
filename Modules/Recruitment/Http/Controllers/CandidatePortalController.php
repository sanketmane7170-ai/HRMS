<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\Recruitment\CandidatePortalService;
use Modules\Recruitment\Entities\Application;

class CandidatePortalController extends Controller
{
    protected CandidatePortalService $portalService;

    public function __construct(CandidatePortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    /**
     * Display candidate portal login page
     */
    public function index(Request $request)
    {
        $token = $request->query('token');
        
        if ($token) {
            $application = $this->portalService->validatePortalToken($token);
            
            if ($application) {
                // Store token in session for subsequent requests
                session(['candidate_portal_token' => $token]);
                return redirect()->route('candidate.dashboard');
            }
        }
        
        return view('recruitment::candidate-portal.login');
    }

    /**
     * Authenticate with email and token
     */
    public function authenticate(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string'
        ]);

        $application = $this->portalService->validatePortalToken($request->input('token'));
        
        if (!$application || $application->candidate_email !== $request->input('email')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or expired token'
            ], 401);
        }

        session(['candidate_portal_token' => $request->input('token')]);
        
        return response()->json([
            'success' => true,
            'redirect_url' => route('candidate.dashboard')
        ]);
    }

    /**
     * Display candidate dashboard
     */
    public function dashboard(Request $request)
    {
        $application = $this->getCurrentApplication();
        
        if (!$application) {
            return redirect()->route('candidate.portal')->with('error', 'Session expired. Please log in again.');
        }
        
        $dashboardData = $this->portalService->getCandidateDashboard($application);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);
        }
        
        return view('recruitment::candidate-portal.dashboard', compact('dashboardData'));
    }

    /**
     * Get application timeline
     */
    public function getTimeline(Request $request): JsonResponse
    {
        $application = $this->getCurrentApplication();
        
        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $timeline = $this->portalService->getApplicationTimeline($application);
        
        return response()->json([
            'success' => true,
            'data' => $timeline
        ]);
    }

    /**
     * Get upcoming interviews
     */
    public function getInterviews(Request $request): JsonResponse
    {
        $application = $this->getCurrentApplication();
        
        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $interviews = $this->portalService->getUpcomingInterviews($application);
        
        return response()->json([
            'success' => true,
            'data' => $interviews
        ]);
    }

    /**
     * Get application documents
     */
    public function getDocuments(Request $request): JsonResponse
    {
        $application = $this->getCurrentApplication();
        
        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $documents = $this->portalService->getApplicationDocuments($application);
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Upload additional document
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
            'document_type' => 'required|string|max:50'
        ]);

        $application = $this->getCurrentApplication();
        
        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $result = $this->portalService->uploadDocument(
            $application,
            $request->file('document'),
            $request->input('document_type')
        );
        
        return response()->json($result);
    }

    /**
     * Update candidate profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'candidate_name' => 'nullable|string|max:100',
            'candidate_phone' => 'nullable|string|max:20',
            'linkedin_url' => 'nullable|url|max:255',
            'years_of_experience' => 'nullable|integer|min:0|max:50',
            'expected_salary' => 'nullable|numeric|min:0',
            'current_location' => 'nullable|string|max:100'
        ]);

        $application = $this->getCurrentApplication();
        
        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $success = $this->portalService->updateCandidateProfile(
            $application,
            $request->only([
                'candidate_name', 'candidate_phone', 'linkedin_url',
                'years_of_experience', 'expected_salary', 'current_location'
            ])
        );
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Profile updated successfully' : 'Failed to update profile'
        ]);
    }

    /**
     * Get interview feedback
     */
    public function getFeedback(Request $request): JsonResponse
    {
        $application = $this->getCurrentApplication();
        
        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $feedback = $this->portalService->getInterviewFeedback($application);
        
        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    /**
     * Send portal invitation (for admin use)
     */
    public function sendInvitation(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:recruitment_applications,id'
        ]);

        $this->authorize('manage_applications');
        
        $application = Application::findOrFail($request->input('application_id'));
        $success = $this->portalService->sendPortalInvitation($application);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Portal invitation sent successfully' : 'Failed to send invitation'
        ]);
    }

    /**
     * Download document
     */
    public function downloadDocument(Request $request, string $documentPath)
    {
        $application = $this->getCurrentApplication();
        
        if (!$application) {
            abort(401, 'Unauthorized');
        }
        
        // Verify document belongs to this application
        $applicationPath = "recruitment/applications/{$application->id}";
        if (!str_contains($documentPath, $applicationPath)) {
            abort(403, 'Access denied');
        }
        
        if (!Storage::disk('public')->exists($documentPath)) {
            abort(404, 'Document not found');
        }
        
        return Storage::disk('public')->download($documentPath);
    }

    /**
     * Logout from portal
     */
    public function logout(Request $request)
    {
        session()->forget('candidate_portal_token');
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('candidate.portal')->with('message', 'Logged out successfully');
    }

    /**
     * Get current authenticated application
     */
    private function getCurrentApplication(): ?Application
    {
        $token = session('candidate_portal_token');
        
        if (!$token) {
            return null;
        }
        
        return $this->portalService->validatePortalToken($token);
    }
}