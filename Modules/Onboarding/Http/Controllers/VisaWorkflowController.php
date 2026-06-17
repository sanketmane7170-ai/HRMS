<?php

namespace Modules\Onboarding\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Onboarding\Entities\VisaProcess;
use Modules\Onboarding\Entities\ComplianceRecord;
use Modules\Onboarding\Entities\OperationalReadiness;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Modules\Onboarding\Emails\VisaIssued;
use Modules\Onboarding\Emails\MedicalAppointment;
use Modules\Onboarding\Emails\WelcomeEmployee;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class VisaWorkflowController extends Controller
{
    public function __construct()
    {
        // Simplified middleware (Sanket - ONB-LOGIC-009)
        // All methods require 'Manage Onboarding' permission
        $this->middleware('permission:Manage Onboarding');
    }

    /**
     * Display the Visa Workflow Tracker.
     */
    public function index()
    {
        view()->share('activeLink', 'onboarding-tracker'); // New sidebar link we will add later if needed

        // We fetch users who have a visa process OR are new hires
        // Ideally, every new hire should have a row here.
        // For query simplicity, we'll fetch users with role 'new-hire' or 'employee' who are in onboarding
        
        $trackers = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['new-hire', 'employee']);
            })
            ->with(['visaProcess', 'complianceRecord', 'onboardingRecord', 'documents'])
            ->whereHas('onboardingRecord') // Only those with onboarding records
            ->paginate(15);

        return view('onboarding::tracker.index', compact('trackers'));
    }

    /**
     * Initialise a visa process for a user if missing.
     */
    public function initProcess($userId)
    {
        VisaProcess::firstOrCreate(['user_id' => $userId]);
        ComplianceRecord::firstOrCreate(['user_id' => $userId]);
        
        return redirect()->back()->with('success', 'Workflow initialized for this user.');
    }

    /**
     * Show Detailed Visa Workflow for a candidate (Phase 7).
     */
    public function show($id)
    {
        $visa = VisaProcess::with(['user.onboardingRecord', 'user.complianceRecord', 'user.operationalReadiness'])->findOrFail($id);
        $user = $visa->user;
        $compliance = $user->complianceRecord ?? ComplianceRecord::firstOrCreate(['user_id' => $user->id]);
        $readiness = $user->operationalReadiness ?? OperationalReadiness::firstOrCreate(['user_id' => $user->id]);
        $offer = \Modules\Recruitment\Entities\Offer::whereHas('application', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->latest()->first();

        // Breadcrumb and Active Link
        view()->share('activeLink', 'onboarding-tracker');

        // Fetch assets and apparel for the tracker
        $availableAssets = \Modules\Asset\Entities\Asset::where('status', \Modules\Asset\Enums\AssetStatus::Available)->get();
        $availableApparel = \Modules\Apparel\Entities\Apparel::all();

        return view('onboarding::tracker.show', compact('visa', 'user', 'compliance', 'readiness', 'availableAssets', 'availableApparel', 'offer'));
    }

    /**
     * Update Visa Status (General) or Upload Contracts.
     */
    public function updateVisaStatus(Request $request, $id)
    {
        $request->validate([
            'mohre_offer_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'mohre_contract_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'labor_card_number' => 'nullable|string|max:50',
            'entry_permit_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'entry_permit_number' => 'nullable|string|max:50',
            'uid_number' => 'nullable|string|max:50',
            'medical_result_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'medical_center_name' => 'nullable|string|max:255',
            'eid_application_form' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'eid_card_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'residency_visa_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'residency_file_number' => 'nullable|string|max:50',
            'insurance_card_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'visa_expiry_date' => 'nullable|date',
            'eid_biometrics_date' => 'nullable|date',
        ]);

        $process = VisaProcess::findOrFail($id);
        
        
        // Handle File Uploads (Generic for granular files)
        $files = [
            'mohre_offer_file' => 'onboarding/mohre',
            'mohre_contract_file' => 'onboarding/mohre',
            'entry_permit_file' => 'onboarding/entry',
            'medical_result_file' => 'onboarding/medical',
            'eid_application_form' => 'onboarding/eid',
            'eid_card_file' => 'onboarding/eid',
            'residency_visa_file' => 'onboarding/residency',
            'insurance_card_file' => 'onboarding/insurance',
        ];

        foreach ($files as $field => $path) {
            if ($request->hasFile($field)) {
                // Store in 'local' disk (storage/app), NOT public
                $process->$field = $request->file($field)->store($path, 'local');
                
                // Auto-update status based on file presence if needed
                if ($field == 'mohre_contract_file') $process->mohre_contract_status = 'signed';
                if ($field == 'entry_permit_file') $process->entry_permit_status = 'issued';
                if ($field == 'residency_visa_file') $process->residency_visa_status = 'stamped';
                if ($field == 'insurance_card_file') $process->insurance_status = 'active';
                if ($field == 'eid_card_file') $process->eid_status = 'issued'; // Added for Bug 11
            }
        }

        // Generic Update for other fields
        $data = $request->except(array_merge(array_keys($files), ['user_id', 'id'])); // Exclude file fields & protected fields
        $process->fill($data);
        $process->save();

        // Handle specific boolean toggles or derived statuses
        if ($request->status_change_completed) {
            $process->update(['status_change_completed' => true]);
        }
        if ($request->medical_result_status) {
             $process->medical_status = $request->medical_result_status; // fit/unfit
             $process->save();
        }

        return redirect()->back()->with('success', 'Visa Workflow updated successfully.');
    }

    /**
     * Upload Entry Permit (Pink Visa).
     */
    public function uploadEntryPermit(Request $request, $id)
    {
        $request->validate(['entry_permit_file' => 'required|file|mimes:pdf,jpg,png']);
        
        $process = VisaProcess::findOrFail($id);
        $file = $request->file('entry_permit_file');
        $path = $file->store('onboarding/entry', 'local');
        
        $process->update([
            'entry_permit_status' => 'issued',
            'entry_permit_file' => $path
        ]);

        // Trigger Notification
        try {
            Mail::to($process->user->email)->send(new VisaIssued($process->user));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Email failed (Visa Issued) for User {$process->user_id}: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Entry Permit uploaded and candidate notified.');
    }

    /**
     * Schedule Medical.
     */
    public function scheduleMedical(Request $request, $id)
    {
        $request->validate(['medical_date' => 'required|date']);
        
        $process = VisaProcess::findOrFail($id);
        
        $process->update([
            'medical_status' => 'scheduled',
            'medical_appointment_date' => $request->medical_date
        ]);

        // Trigger Notification
        try {
            Mail::to($process->user->email)->send(new MedicalAppointment($process->user, $request->medical_date));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Email failed (Medical Appt) for User {$process->user_id}: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Medical scheduled and candidate notified.');
    }

    /**
     * Upload Medical Result.
     * Trigger Phase 3 (Compliance) if FIT.
     */
    public function uploadMedicalResult(Request $request, $id)
    {
        $request->validate(['medical_result_file' => 'required|file', 'medical_status' => 'required']);
        
        $process = VisaProcess::findOrFail($id);
        $path = $request->file('medical_result_file')->store('onboarding/medical', 'local');
        
        $process->update([
            'medical_status' => $request->medical_status, // fit / unfit
            'medical_result_file' => $path
        ]);

        // Phase 3 Trigger: If FIT, ensure Compliance Record exists & set OHC to pending
        if ($request->medical_status == 'fit') {
            $comp = ComplianceRecord::firstOrCreate(['user_id' => $process->user_id]);
            if ($comp->ohc_status == null) {
                $comp->update(['ohc_status' => 'pending']);
            }
        }

        return redirect()->back()->with('success', 'Medical result updated.');
    }

    /**
     * Upload Health Insurance.
     */
    public function uploadInsurance(Request $request, $id)
    {
        $request->validate(['insurance_card_file' => 'required|file']);
        
        $process = VisaProcess::findOrFail($id);
        $path = $request->file('insurance_card_file')->store('onboarding/insurance', 'local');
        
        $process->update([
            'insurance_status' => 'active',
            'insurance_card_file' => $path
        ]);

        return redirect()->back()->with('success', 'Insurance uploaded.');
    }

    /**
     * Finalize Residency.
     */
    public function stampVisa(Request $request, $id)
    {
        $request->validate([
            'visa_expiry_date' => 'required|date',
            'visa_file' => 'required|file|mimes:pdf,jpg,png'
        ]);
        
        $process = VisaProcess::findOrFail($id);
        $path = $request->file('visa_file')->store('onboarding/residency', 'local');
        
        $process->update([
            'residency_visa_status' => 'stamped',
            'residency_visa_file' => $path,
            'visa_expiry_date' => $request->visa_expiry_date
        ]);

        // Check Readiness
        $this->checkOperationalReadiness($process->user_id);

        return redirect()->back()->with('success', 'Residency Stamped! Visa Phase Complete.');
    }

    /**
     * Update Compliance Status (OHC / Food Safety).
     */
    public function updateCompliance(Request $request, $id)
    {
        $request->validate([
            'ohc_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'ohc_expiry_date' => 'nullable|date',
            'food_safety_status' => 'nullable|in:pending,assigned,passed,failed',
        ]);

        $compliance = ComplianceRecord::findOrFail($id);

        if ($request->hasFile('ohc_file')) {
            $path = $request->file('ohc_file')->store('onboarding/compliance', 'local');
            $compliance->update([
                'ohc_status' => 'issued',
                'ohc_file' => $path,
                'ohc_expiry_date' => $request->ohc_expiry_date
            ]);
        }
        
        if ($request->has('food_safety_status')) {
            $compliance->update([
                'food_safety_training_status' => $request->food_safety_status,
                'training_completion_date' => $request->food_safety_status == 'passed' ? now() : null
            ]);
        }

        // Check Readiness
        $this->checkOperationalReadiness($compliance->user_id);

        return redirect()->back()->with('success', 'Compliance record updated.');
    }

    /**
     * Check if user is ready for operations (Phase 4).
     */
    private function checkOperationalReadiness($userId)
    {
        $visa = VisaProcess::where('user_id', $userId)->first();
        $compliance = ComplianceRecord::where('user_id', $userId)->first();

        if (
            $visa && $visa->residency_visa_status == 'stamped' &&
            $compliance && $compliance->ohc_status == 'issued'
        ) {
            // Create Readiness Record if not exists
            $readiness = OperationalReadiness::firstOrCreate(['user_id' => $userId]);
            
            // 1. Trigger Branch Notification
            if (!$readiness->branch_notification_sent_at) {
                // In real app: Mail::to($manager)->send(new EmployeeCleared($user));
                $readiness->update(['branch_notification_sent_at' => now()]);
            }

            // 2. TRIGGER AUTO TASKS (Phase 10)
            // Call the TaskAutomationService created earlier
            \Modules\Onboarding\Services\TaskAutomationService::createProvisioningTasks($visa->user);
        }
    }

    /**
     * Convert Candidate to Employee (Phase 6).
     */
    public function convertToEmployee($id)
    {
        // $id is the User ID here
        $user = User::with(['complianceRecord', 'operationalReadiness'])->findOrFail($id);
        
        $compliance = $user->complianceRecord;
        $readiness = $user->operationalReadiness;

        // 1. Basic Readiness Check
        if (!$readiness) {
            return redirect()->back()->withErrors(['msg' => 'Operational Readiness record not found.']);
        }

        // 2. Compliance Block Logic (Phase 9)
        // Block if OHC is not issued or Food Safety is not passed
        if (!$compliance || $compliance->ohc_status !== 'issued' || $compliance->food_safety_training_status !== 'passed') {
            return redirect()->back()->withErrors(['msg' => 'Conversion blocked: Compliance requirements (OHC Issued & Food Safety Passed) not met.']);
        }

        // 3. Operational Readiness Check
        if (!$readiness->it_login_created || !$readiness->induction_completed) {
             return redirect()->back()->withErrors(['msg' => 'Conversion blocked: IT Setup and Induction must be completed.']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                $user = User::findOrFail($id);

                // 1. Update Role
                $user->syncRoles('employee'); // Requires Spatie Permission
                
                // 2. Generate Employee ID if missing
                if (!$user->employee_id) {
                    $lastUser = User::whereNotNull('employee_id')->orderBy('id', 'desc')->first();
                    $nextId = $lastUser ? (intval(substr($lastUser->employee_id, 4)) + 1) : 1001; 
                    $user->employee_id = 'EMP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                }
                
                // 3. Update Status and Department/Division from Onboarding Record
                $onboarding = $user->onboardingRecord;
                if ($onboarding) {
                    $user->department_id = $onboarding->department_id;
                    $user->division_id = $onboarding->division_id;
                }
                // $user->status = 'active'; // Assuming User model has status column which is standard
                $user->save();

                // 4. Update Joining Date in Work Details
                if ($user->workDetail) {
                    $joiningDate = $onboarding ? ($onboarding->joining_date ?? now()) : now();
                    $user->workDetail->update(['joining_date' => $joiningDate]);
                }
            });

            // 5. Send Welcome Email (Outside transaction to avoid rollback on email fail, but log it)
            try {
                $user = User::find($id); // Reload
                Mail::to($user->email)->send(new WelcomeEmployee($user));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send welcome email to User ID {$id}: " . $e->getMessage());
            }

            return redirect()->route('onboarding.tracker.index')->with('success', 'Candidate successfully converted to Employee! Welcome Email sent.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to convert User ID {$id} to employee: " . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Conversion failed. Please check logs.']);
        }
    }

    /**
     * Update Operational Readiness (IT, Uniform, Induction).
     */
    public function updateReadiness(Request $request, $id)
    {
        $readiness = OperationalReadiness::findOrFail($id);
        
        $data = $request->only(['uniform_status', 'asset_id', 'apparel_id']);
        
        // Handle checkboxes (boolean)
        $data['it_login_created'] = $request->has('it_login_created');
        $data['email_created'] = $request->has('email_created');
        $data['induction_completed'] = $request->has('induction_completed');

        $readiness->update($data);

        // Official Asset Assignment (if integration selected)
        if ($request->asset_id && $readiness->wasChanged('asset_id')) {
             \Modules\Asset\Entities\AssetAssignment::firstOrCreate([
                 'asset_id' => $request->asset_id,
                 'user_id' => $readiness->user_id,
                 'issue_date' => now()->toDateString(),
             ]);
        }

        // Official Apparel Request (if uniform selected)
        if ($request->apparel_id && $readiness->wasChanged('apparel_id')) {
             \Modules\Apparel\Entities\ApparelRequest::create([
                 'user_id' => $readiness->user_id,
                 'apparel_id' => $request->apparel_id,
                 'number_of_apparel' => 1,
                 'status' => 'pending'
             ]);
        }

        return redirect()->back()->with('success', 'Operational Readiness updated.');
    }
}
