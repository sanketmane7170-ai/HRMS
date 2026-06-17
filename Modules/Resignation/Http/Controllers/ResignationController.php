<?php

namespace Modules\Resignation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Resignation\Services\ResignationService;
use Exception;

class ResignationController extends Controller
{
    protected $service;

    public function __construct(ResignationService $service)
    {
        $this->service = $service;
    }

    // Employee: Apply
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
            'preferred_last_working_date' => 'nullable|date|after:today',
            'comments' => 'nullable|string',
            'signed_document' => 'nullable|file|mimes:pdf,jpg,png,docx|max:5120', // Max 5MB
        ]);

        try {
            // Author: Sanket - Handle signed document upload
            $signedDocumentPath = null;
            if ($request->hasFile('signed_document')) {
                $file = $request->file('signed_document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $signedDocumentPath = $file->storeAs('uploads/resignations', $filename, 'public');
            }

            // BUG-RES-001 Fix: Use only() to prevent mass assignment - Author: Sanket
            $resignationData = $request->only(['reason', 'preferred_last_working_date', 'comments', 'notice_period_days']);
            $resignationData['signed_document'] = $signedDocumentPath;

            $resignation = $this->service->applyResignation(
                $resignationData,
                $request->user()
            );
            
            // BUG-RES-012 Fix: Add logging - Author: Sanket
            \Log::info('Resignation submitted', [
                'resignation_id' => $resignation->id,
                'employee_id' => $request->user()->id,
                'preferred_date' => $request->preferred_last_working_date
            ]);
            
            return response()->json(['message' => 'Resignation submitted successfully.', 'data' => $resignation], 201);
        } catch (Exception $e) {
            \Log::error('Failed to submit resignation: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Employee: List My Resignations
    public function index(Request $request)
    {
        return response()->json(['data' => $this->service->getResignationsForUser($request->user())]);
    }

    // Employee: Withdraw
    public function destroy($id, Request $request)
    {
        try {
            $this->service->withdrawResignation($id, $request->user());
            return response()->json(['message' => 'Resignation withdrawn successfully.']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Manager/Admin: Take Action
    public function action($id, Request $request)
    {
        $request->validate([
            'action_type' => 'required|in:approve,reject,hold,waive,complete,update',
            'comments' => 'nullable|string',
            'approved_last_working_date' => 'nullable|date|after:today',
        ]);

        try {
            // Author: Sanket
            // Prevent changes to rejected resignations
            $resignation = \Modules\Resignation\Entities\Resignation::findOrFail($id);
            if ($resignation->status === 'rejected' && $request->action_type !== 'update') {
                return response()->json([
                    'message' => 'Cannot modify rejected resignation requests. Contact admin if changes are needed.'
                ], 403);
            }
            
            // BUG-RES-002 Fix: Use only() to prevent mass assignment - Author: Sanket
            $resignation = $this->service->takeAction(
                $id,
                $request->only(['action_type', 'comments', 'approved_last_working_date']),
                $request->user()
            );
            
            return response()->json(['message' => 'Action taken successfully.', 'data' => $resignation]);
        } catch (Exception $e) {
            \Log::error('Failed to take action on resignation: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    
    // Admin: List All
    public function adminIndex() {
        return response()->json(['data' => $this->service->getAllResignations()]);
    }

    // Manager: List Team
    public function teamIndex(Request $request) {
        return response()->json(['data' => $this->service->getResignationsForManager($request->user())]);
    }

    // Policy Management
    public function getPolicy() {
        $policy = \DB::table('settings')->where('key', 'resignation_policy')->value('value');
        $noticePeriod = \DB::table('settings')->where('key', 'resignation_notice_period')->value('value') ?? 30;
        return response()->json(['policy' => $policy, 'notice_period' => $noticePeriod]);
    }

    public function updatePolicy(Request $request) {
        // BUG-RES-007 Fix: Add authorization - Author: Sanket
        if (!$request->user()->hasAnyRole(['admin', 'hr'])) {
            return response()->json(['message' => 'Unauthorized. Only admins and HR can update resignation policy.'], 403);
        }
        
        $request->validate([
            'policy' => 'required|string',
            'notice_period' => 'required|integer|min:1'
        ]);
        
        \DB::table('settings')->updateOrInsert(
            ['key' => 'resignation_policy'],
            ['value' => $request->policy]
        );
        
        \DB::table('settings')->updateOrInsert(
            ['key' => 'resignation_notice_period'],
            ['value' => $request->notice_period]
        );
        
        // BUG-RES-012 Fix: Add logging - Author: Sanket
        \Log::info('Resignation policy updated', [
            'updated_by' => $request->user()->id,
            'notice_period' => $request->notice_period
        ]);

        return response()->json(['message' => 'Policy and Notice Period updated successfully']);
    }

    // Frontend Views
    public function employeeView() {
        // Author signature name: Sanket
        // Block Admin from accessing personal resignation view
        if (auth()->user()->hasRole(['admin', 'Admin', 'Super Admin', 'superadmin'])) {
            return redirect()->route('backend.dashboard')->with('error', 'Administrators cannot apply for resignation.');
        }

        $policy = \DB::table('settings')->where('key', 'resignation_policy')->value('value');
        $noticePeriod = \DB::table('settings')->where('key', 'resignation_notice_period')->value('value') ?? 30;
        return view('resignation::employee.index', ['activeLink' => 'resignation.employee', 'policy' => $policy, 'noticePeriod' => $noticePeriod]);
    }

    public function managerView() {
        return view('resignation::manager.index', ['activeLink' => 'resignation.manager']);
    }

    public function adminView() {
        return view('resignation::admin.index', ['activeLink' => 'resignation.admin']);
    }

    /**
     * Approve resignation
     * Author: Sanket
     */
    public function approve(Request $request, $id)
    {
        try {
            // BUG-RES-006 Fix: Use 'employee' relationship instead of 'user' - Author: Sanket
            $resignation = \Modules\Resignation\Entities\Resignation::with('employee')->findOrFail($id);
            
            if ($resignation->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending resignations can be approved'
                ], 422);
            }

            \DB::beginTransaction();

            $resignation->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Send notification to employee
            if ($resignation->employee) {
                dispatch(function() use ($resignation) {
                    $resignation->employee->notify(
                        new \Modules\Resignation\Notifications\ResignationApprovedNotification($resignation, auth()->user())
                    );
                })->afterResponse();
            }

            \Log::info('Resignation approved', [
                'resignation_id' => $id,
                'employee_id' => $resignation->employee_id,
                'approved_by' => auth()->id()
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resignation approved successfully'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to approve resignation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve resignation'
            ], 500);
        }
    }

    /**
     * Reject resignation
     * Author: Sanket
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:10'
        ]);

        try {
            // BUG-RES-006 Fix: Use 'employee' relationship instead of 'user' - Author: Sanket
            $resignation = \Modules\Resignation\Entities\Resignation::with('employee')->findOrFail($id);
            
            if ($resignation->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending resignations can be rejected'
                ], 422);
            }

            \DB::beginTransaction();

            $resignation->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason
            ]);

            // Send notification to employee
            if ($resignation->employee) {
                dispatch(function() use ($resignation, $request) {
                    $resignation->employee->notify(
                        new \Modules\Resignation\Notifications\ResignationRejectedNotification(
                            $resignation,
                            auth()->user(),
                            $request->reason
                        )
                    );
                })->afterResponse();
            }

            \Log::info('Resignation rejected', [
                'resignation_id' => $id,
                'employee_id' => $resignation->employee_id,
                'rejected_by' => auth()->id(),
                'reason' => $request->reason
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resignation rejected'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to reject resignation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject resignation'
            ], 500);
        }
    }

    // Author: Sanket
    // Admin: Delete Resignation Request
    public function adminDelete($id, Request $request) {
        try {
            // BUG-RES-014 Fix: Standardize role check - Author: Sanket
            if (!$request->user()->hasAnyRole(['admin', 'hr'])) {
                return response()->json(['message' => 'Unauthorized. Only admins and HR can delete resignation requests.'], 403);
            }

            $resignation = \Modules\Resignation\Entities\Resignation::findOrFail($id);
            
            // BUG-RES-010 Fix: Check if offboarding has started - Author: Sanket
            if (in_array($resignation->status, ['approved', 'completed'])) {
                $offboarding = \App\Models\offBoarding::where('user_id', $resignation->employee_id)->first();
                if ($offboarding) {
                    return response()->json([
                        'message' => 'Cannot delete: Offboarding process has started for this employee. Please cancel offboarding first.'
                    ], 400);
                }
            }
            
            // BUG-RES-003 Fix: Add transaction - Author: Sanket
            \DB::beginTransaction();
            
            // BUG-RES-012 Fix: Log the deletion - Author: Sanket
            \Log::info('Resignation request deleted', [
                'resignation_id' => $id,
                'employee_id' => $resignation->employee_id,
                'deleted_by' => $request->user()->id,
                'status' => $resignation->status
            ]);
            
            // Delete related records first
            \Modules\Resignation\Entities\ResignationAction::where('resignation_id', $id)->delete();
            \Modules\Resignation\Entities\EmployeeNoticePeriod::where('resignation_id', $id)->delete();
            
            // Delete the resignation
            $resignation->delete();
            
            \DB::commit();
            
            return response()->json(['message' => 'Resignation request deleted successfully.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to delete resignation: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete: ' . $e->getMessage()], 400);
        }
    }
}
