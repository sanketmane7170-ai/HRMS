<?php

namespace Modules\Onboarding\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Onboarding\Entities\ProbationReview;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProbationReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Manage Onboarding'); // Added by Sanket - Master access
        $this->middleware('permission:manage-probation-reviews');
    }

    /**
     * Show list of pending reviews for the current manager.
     */
    public function index()
    {
        view()->share('activeLink', 'probation-reviews');
        $user = Auth::user();
        
        $query = ProbationReview::where('status', 'pending')
                    ->with('employee.department', 'employee.workDetail');

        // If not super admin/HR admin, only show reviews where the user is the manager
        if (!$user->hasRole(['Super Admin', 'HR Manager'])) {
            $query->whereHas('employee.workDetail', function($q) use ($user) {
                $q->whereJsonContains('report_to_ids', (string)$user->id)
                  ->orWhereJsonContains('report_to_ids', $user->id);
            });
        }
        
        $reviews = $query->get();

        return view('onboarding::probation.index', compact('reviews'));
    }

    /**
     * Show the review form.
     */
    public function edit($id)
    {
        $review = ProbationReview::with('employee.workDetail')->findOrFail($id);
        $user = Auth::user();
        
        // Authorization: Only HR or Direct Manager (Sanket - ONB-SEC-006)
        if (!$user->hasRole(['Super Admin', 'HR Manager'])) {
            $employee = $review->employee;
            $reportToIds = $employee->workDetail->report_to_ids ?? [];
            
            // Check if current user is the direct manager
            if (!in_array($user->id, $reportToIds) && !in_array((string)$user->id, $reportToIds)) {
                abort(403, 'Unauthorized access to this probation review.');
            }
        }

        return view('onboarding::probation.edit', compact('review'));
    }

    /**
     * Submit the review.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'performance_score' => 'required|integer|min:1|max:5',
            'recommendation' => 'required|in:confirmed,extended,terminated',
            'manager_comments' => 'required|string|min:10',
            'option_to_extend_duration_months' => 'nullable|integer|required_if:recommendation,extended'
        ]);

        $review = ProbationReview::with('employee.workDetail')->findOrFail($id);
        $user = Auth::user();
        
        // Authorization: Only HR or Direct Manager (Sanket - ONB-SEC-006)
        if (!$user->hasRole(['Super Admin', 'HR Manager'])) {
            $employee = $review->employee;
            $reportToIds = $employee->workDetail->report_to_ids ?? [];
            
            if (!in_array($user->id, $reportToIds) && !in_array((string)$user->id, $reportToIds)) {
                abort(403, 'Unauthorized to update this probation review.');
            }
        }
        
        $review->update([
            'reviewer_id' => Auth::id(),
            'performance_score' => $request->performance_score,
            'recommendation' => $request->recommendation,
            'manager_comments' => $request->manager_comments,
            'status' => 'submitted', // Or 'completed' if HR approval not needed
            'completed_at' => now(),
            'option_to_extend_duration_months' => $request->option_to_extend_duration_months
        ]);

        // Auto-Action based on recommendation?
        // Usually HR needs to valid finally. We'll mark as 'submitted' for now.
        
        return redirect()->route('onboarding.probation.index')->with('success', 'Review submitted successfully.');
    }
}
