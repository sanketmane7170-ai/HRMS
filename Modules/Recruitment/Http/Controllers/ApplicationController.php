<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\User;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\ApplicationLog;
use Modules\Recruitment\Entities\Interview;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Recruitment\StoreApplicationRequest;
use App\Services\Recruitment\FileUploadService;
use App\Notifications\Recruitment\ApplicationReceivedNotification;
use App\Notifications\Recruitment\ApplicationSubmittedNotification;
use App\Notifications\Recruitment\ApplicationStatusChangedNotification;
use App\Notifications\Recruitment\InterviewScheduledNotification;
use App\Models\Recruitment\ExternalCandidate;
use App\Services\Recruitment\ApplicationStageService;
use Exception;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    protected ApplicationStageService $stageService;

    public function __construct(ApplicationStageService $stageService)
    {
        $this->stageService = $stageService;
        view()->share('activeLink', 'recruitment-applications');
    }

    /**
     * Display a listing of the applications.
     */
    public function index()
    {
        $this->authorize('viewAny', Application::class);
        
        if (request()->ajax()) {
            \Log::info('🔄 AJAX request received for applications datatable');
            return $this->datatable();
        }

        \Log::info('📄 Non-AJAX request for applications index');
        
        $jobs = Job::where('status', 'active')->get();
        $stages = Application::getStages();
        
        // Apply filters and pagination for regular page requests
        $applications = Application::with(['job', 'user', 'logs'])
            ->when(request('job_id'), function ($query, $jobId) {
                return $query->where('job_id', $jobId);
            })
            ->when(request('stage'), function ($query, $stage) {
                return $query->where('stage', $stage);
            })
            ->when(request('date_range'), function ($query, $dateRange) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    return $query->whereBetween('applied_on', [
                        \Carbon\Carbon::parse($dates[0])->startOfDay(),
                        \Carbon\Carbon::parse($dates[1])->endOfDay()
                    ]);
                }
            })
            ->orderBy('applied_on', 'desc')
            ->paginate(10);
        
        \Log::info('📊 Applications loaded with filters', [
            'count' => $applications->total(),
            'filters' => request()->only(['job_id', 'stage', 'date_range'])
        ]);
        
        return view('recruitment::applications.index', compact('jobs', 'stages', 'applications'));
    }

    /**
     * DataTable for applications listing
     */
    public function datatable()
    {
        \Log::info('📊 Applications datatable method called', [
            'filters' => request()->all(),
            'user' => auth()->user() ? auth()->user()->name : 'not authenticated'
        ]);

        $applications = Application::with(['job', 'user', 'logs'])
            ->select(['id', 'job_id', 'user_id', 'candidate_name', 'candidate_email', 'stage', 'applied_on', 'created_at']);

        // Apply filters
        if (request('job_id')) {
            $applications->where('job_id', request('job_id'));
        }
        
        if (request('stage')) {
            $applications->where('stage', request('stage'));
        }
        
        if (request('date_range')) {
            $dates = explode(' - ', request('date_range'));
            if (count($dates) == 2) {
                $applications->whereBetween('applied_on', [
                    \Carbon\Carbon::parse($dates[0])->startOfDay(),
                    \Carbon\Carbon::parse($dates[1])->endOfDay()
                ]);
            }
        }

        $query = $applications->toSql();
        $count = $applications->count();
        \Log::info('📊 Applications query', ['sql' => $query, 'count' => $count]);

        return DataTables::of($applications)
            ->addColumn('checkbox', function ($application) {
                return '<div class="form-check p-0 ms-2"><input class="form-check-input application-checkbox" type="checkbox" value="' . $application->id . '"></div>';
            })
            ->addColumn('job_title', function ($application) {
                return $application->job->title ?? 'N/A';
            })
            ->addColumn('candidate', function ($application) {
                return '<div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2 bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                        <span class="small font-weight-bold">' . strtoupper(substr($application->candidate_name ?? 'C', 0, 1)) . '</span>
                    </div>
                    <div>
                        <div class="font-weight-bold">' . ($application->candidate_name ?? 'N/A') . '</div>
                        <div class="small text-muted">' . ($application->candidate_email ?? '') . '</div>
                    </div>
                </div>';
            })
            ->addColumn('stage_badge', function ($application) {
                $badges = [
                    'applied' => 'bg-info-light text-info',
                    'screening' => 'bg-warning-light text-warning',
                    'shortlisted' => 'bg-warning-light text-warning',
                    'interview' => 'bg-primary-light text-primary',
                    'offer' => 'bg-success-light text-success',
                    'hired' => 'bg-success-light text-success',
                    'rejected' => 'bg-danger-light text-danger',
                    'offer_declined' => 'bg-danger-light text-danger',
                    'withdrawn' => 'bg-secondary-light text-secondary'
                ];
                $class = $badges[$application->stage] ?? 'bg-secondary-light text-secondary';
                return '<span class="badge rounded-pill ' . $class . '">' . ucfirst($application->stage) . '</span>';
            })
            ->addColumn('score_display', function ($application) {
                return 'N/A';
            })
            ->addColumn('applied_date', function ($application) {
                return $application->applied_on ? '<span class="text-muted"><i class="far fa-calendar-alt me-1"></i>' . $application->applied_on->format('M d, Y') . '</span>' : 'N/A';
            })
            ->addColumn('action', function ($application) {
                $actions = '<div class="dropdown dropdown-action">
                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="' . route('recruitment.applications.show', $application->id) . '"><i class="fas fa-eye me-2 text-info"></i> View</a>';
                
                if (auth()->user()->can('recruitment.applications.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('recruitment.applications.edit', $application->id) . '"><i class="fas fa-edit me-2 text-primary"></i> Edit</a>';
                }
                
                if (auth()->user()->can('recruitment.applications.delete')) {
                    $actions .= '<a class="dropdown-item delete-application" href="javascript:void(0);" data-id="' . $application->id . '"><i class="fas fa-trash me-2 text-danger"></i> Delete</a>';
                }
                
                $actions .= '</div></div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'candidate', 'stage_badge', 'applied_date', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new application.
     */
    public function create()
    {
        $jobs = Job::where('status', 'active')->with('department')->get();
        return view('recruitment::applications.create', compact('jobs'));
    }

    /**
     * Store a newly created application.
     */
    public function store(StoreApplicationRequest $request, FileUploadService $fileUploadService): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $resumePath = null; // Initialize $resumePath here
            // Handle file upload using the service
            if ($request->hasFile('resume')) {
                $resumePath = $fileUploadService->uploadResume(
                    $request->file('resume'), 
                    $request->job_id,
                    $request->candidate_email
                );
            }

            $data = $request->validated();
            $data['resume_path'] = $resumePath; // Store in resume_path
            $data['resume_url'] = null; // Clear resume_url if it was a path
            $data['stage'] = 'applied';
            $data['applied_on'] = now();

            $application = Application::create($data);

            // Log the application creation
            ApplicationLog::create([
                'application_id' => $application->id,
                'action' => 'application_created',
                'description' => 'Application submitted by ' . ($application->candidate_name ?? $application->user->name ?? 'candidate'),
                'changed_by' => auth()->id(),
                'metadata' => ['stage' => 'applied']
            ]);

            // Send confirmation email to candidate
            if ($application->candidate_email) {
                $candidate = new ExternalCandidate($application->candidate_email, $application->candidate_name);
                $candidate->notify(new ApplicationReceivedNotification($application));
            } elseif ($application->user) {
                $application->user->notify(new ApplicationReceivedNotification($application));
            }

            DB::commit();

            // Notify HR/Admin about new application
            $this->notifyHRAboutNewApplication($application);

            return redirect()->route('recruitment.applications.index')
                ->with('success', 'Application created successfully. Confirmation email sent to candidate.');

        } catch (Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if ($resumePath && Storage::disk('public')->exists($resumePath)) {
                Storage::disk('public')->delete($resumePath);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to create application: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified application.
     */
    public function show($id)
    {
        canPerform('View Applications');
        
        $application = Application::with(['job', 'user', 'logs.changedBy', 'interviews', 'offer'])
            ->findOrFail($id);
            
        // Ensure relationships are collections not arrays
        if (!$application->logs) {
            $application->setRelation('logs', collect());
        }
        if (!$application->interviews) {
            $application->setRelation('interviews', collect());
        }
        
        // Debug: Log any array values that might cause issues
        \Log::info('🔍 Application Show Debug:', [
            'application_id' => $id,
            'stage_type' => gettype($application->stage),
            'stage_value' => $application->stage,
            'user_name_type' => $application->user ? gettype($application->user->name) : 'null',
            'user_email_type' => $application->user ? gettype($application->user->email) : 'null',
            'candidate_name_type' => gettype($application->candidate_name),
            'candidate_email_type' => gettype($application->candidate_email),
            'job_title_type' => $application->job ? gettype($application->job->title) : 'null',
            'job_location_type' => $application->job ? gettype($application->job->location) : 'null',
            'applied_on_type' => gettype($application->applied_on),
            'notes_type' => gettype($application->notes),
        ]);
        
        // Safeguard missing relationships to avoid 500 errors in view
    if (!$application->user) {
        $application->setRelation('user', new User());
    }
    if (!$application->job) {
        // Create a dummy job to avoid null pointer exceptions in view if needed
        $application->setRelation('job', new \Modules\Recruitment\Entities\Job(['title' => 'N/A']));
    }
    
    // Debug: Log more context if needed
    if (config('app.debug')) {
        \Log::info('🔍 Application Show Debug Context:', [
            'application_id' => $id,
            'has_user' => (bool)$application->user->id,
            'has_job' => (bool)$application->job->id,
            'stage' => $application->stage,
        ]);
    }
    
    // Fetch interviewers for the scheduling modal - Sanket
    $interviewers = User::orderBy('name')->get();
    
    return view('recruitment::applications.show', compact('application', 'interviewers'));
    }

    /**
     * Show the form for editing the specified application.
     */
    public function edit($id)
    {
        canPerform('Edit Applications');
        
        $application = Application::findOrFail($id);
        $jobs = Job::where('status', 'active')->get();
        $stages = Application::getStages();
        
        return view('recruitment::applications.edit', compact('application', 'jobs', 'stages'));
    }

    /**
     * Update the specified application.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $application = Application::findOrFail($id);

        $validator = Validator::make($request->all(), [
            // BUG-REC-012 Fix: Add foreign key validation - Author: Sanket
            'job_id' => 'required|exists:recruitment_jobs,id',
            'candidate_name' => 'required_without:user_id|string|max:255',
            // BUG-REC-006 Fix: Add email uniqueness validation - Author: Sanket
            'candidate_email' => 'required_without:user_id|email|max:255|unique:recruitment_applications,candidate_email,' . $id,
            'candidate_phone' => 'nullable|string|max:20',
            'cover_letter' => 'nullable|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability_date' => 'nullable|date',
            'score' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $oldResumePosition = $application->resume_path;

            // Handle file upload
            if ($request->hasFile('resume')) {
                $application->resume_path = $request->file('resume')->store('recruitment/resumes', 'public');
            }

            $applicationData = [
                'job_id' => $request->job_id,
                'candidate_name' => $request->candidate_name,
                'candidate_email' => $request->candidate_email,
                'candidate_phone' => $request->candidate_phone,
                'cover_letter' => $request->cover_letter,
                'expected_salary' => $request->expected_salary,
                'availability_date' => $request->availability_date,
                'score' => $request->score,
                'notes' => $request->notes
            ];

            if ($request->hasFile('resume')) {
                $applicationData['resume_path'] = $application->resume_path;
                $applicationData['resume_url'] = null; // Clear if it was an external link
            }

            $application->update($applicationData);

            // Log the update
            ApplicationLog::create([
                'application_id' => $application->id,
                'action' => 'application_updated',
                'description' => 'Application details updated',
                'changed_by' => auth()->id(),
                'metadata' => $request->only(['job_id', 'score', 'notes'])
            ]);

            DB::commit();

            // Delete old resume only after successful commit - Sanket
            if ($request->hasFile('resume') && $oldResumePosition && Storage::disk('public')->exists($oldResumePosition)) {
                Storage::disk('public')->delete($oldResumePosition);
            }

            return redirect()->route('recruitment.applications.show', $application->id)
                ->with('success', 'Application updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            // BUG-REC-007 Fix: Delete newly uploaded resume if update fails - Author: Sanket
            if ($request->hasFile('resume') && isset($application->resume_path) && Storage::disk('public')->exists($application->resume_path)) {
                Storage::disk('public')->delete($application->resume_path);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to update application: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update application stage
     */
    public function updateStage(Request $request, $id)
    {
        canPerform('Edit Applications'); // Enforce permission (Sanket - REC-SEC-018)
        $application = Application::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'stage' => 'required|in:applied,screening,shortlisted,interview,offer,hired,rejected,withdrawn,offer_declined',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $success = $this->stageService->moveToStage(
                $application, 
                $request->stage, 
                $request->notes ?? "Stage changed to {$request->stage}"
            );

            if ($success) {
                // Background notifications already handled by logic if needed, 
                // but let's send the status change notification here for manual updates
                // Background notifications
                try {
                    $this->notifyStatusChange($application, $application->stage);
                } catch (Exception $e) {
                    \Log::error('Failed to send status change notification: ' . $e->getMessage());
                }
                return response()->json(['success' => true, 'message' => 'Stage updated successfully']);
            }
            
            return response()->json(['success' => false, 'message' => 'Failed to update stage'], 500);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update stage: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add note to application
     */
    public function addNote(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'note' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            ApplicationLog::create([
                'application_id' => $application->id,
                'action' => 'note_added',
                'description' => $request->note,
                'changed_by' => auth()->id(),
                'metadata' => ['type' => 'note']
            ]);

            return response()->json(['success' => true, 'message' => 'Note added successfully']);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add note'], 500);
        }
    }

    /**
     * Get application timeline
     */
    public function timeline($id)
    {
        $application = Application::with(['logs.changedBy'])->findOrFail($id);
        $timeline = $application->logs()->with('changedBy')->orderBy('created_at', 'desc')->get();
        
        return response()->json(['timeline' => $timeline]);
    }

    /**
     * Schedule interview for application (handles both API and form requests)
     */
    public function scheduleInterview(Request $request, $id)
    {
        try {
            $application = Application::findOrFail($id);
            
            // Handle both form and API request formats
            if ($request->has('date') && $request->has('time')) {
                // Form request format
                $request->validate([
                    'date' => 'required|date|after_or_equal:today',
                    'time' => 'required',
                    'type' => 'required|in:phone,video,in_person',
                    'interviewer_id' => 'nullable|exists:users,id',
                    'interviewer' => 'nullable|string|max:255',
                    'notes' => 'nullable|string|max:1000'
                ]);
                
                // Ensure at least one interviewer option is provided
                if (!$request->interviewer_id && !$request->interviewer) {
                    return redirect()->back()->with('error', 'Please select an interviewer or enter an interviewer name.');
                }

                // Check for existing scheduled interview - Sanket
                $existingInterview = Interview::where('application_id', $application->id)
                    ->where('status', 'scheduled')
                    ->exists();
                
                if ($existingInterview) {
                    throw ValidationException::withMessages([
                        'time' => ['An interview is already scheduled for this application. Please reschedule the existing interview instead.']
                    ]);
                }

                // Check for existing scheduled interview - Sanket
                $existingInterview = Interview::where('application_id', $application->id)
                    ->where('status', 'scheduled')
                    ->exists();
                
                if ($existingInterview) {
                    throw ValidationException::withMessages([
                        'time' => ['An interview is already scheduled for this application. Please reschedule the existing interview instead.']
                    ]);
                }
                
                $scheduledAt = Carbon::parse($request->date . ' ' . $request->time);

                // Custom validation for past time on current day - Sanket
                if ($scheduledAt->isPast()) {
                    throw ValidationException::withMessages([
                        'time' => ['Interview time must be in the future.']
                    ]);
                }
                
                // Determine interviewer info
                if ($request->interviewer_id) {
                    $interviewer = User::find($request->interviewer_id);
                    $interviewerName = $interviewer ? $interviewer->name : 'Unknown';
                    $interviewerId = $request->interviewer_id;
                } else {
                    $interviewerName = $request->interviewer;
                    $interviewerId = null; // Correctly allow null for external interviewers - Sanket
                }
                
                $notes = "Interviewer: " . $interviewerName;
                if ($request->notes) {
                    $notes .= "\nNotes: " . $request->notes;
                }
            } else {
                // API request format
                $request->validate([
                    'interview_date' => 'required|date|after:now',
                    'interview_type' => 'required|in:phone,video,in_person',
                    'interviewer_id' => 'required|exists:users,id',
                    'location' => 'nullable|string',
                    'notes' => 'nullable|string'
                ]);
                
                $scheduledAt = Carbon::parse($request->interview_date);
                $interviewerName = User::find($request->interviewer_id)->name ?? 'Unknown';
                $interviewerId = $request->interviewer_id; // Set explicitly for API - Sanket
                $notes = $request->notes;
            }

            DB::beginTransaction();

            // Create interview record
            $interview = Interview::create([
                'application_id' => $application->id,
                'interviewer_id' => $interviewerId, // Use the explicit ID (can be null) - Sanket
                'scheduled_by' => auth()->id(),
                'scheduled_at' => $scheduledAt,
                'type' => $request->type ?? $request->interview_type,
                'status' => 'scheduled',
                'duration_minutes' => 60,
                'preparation_notes' => $notes,
                'location' => $request->location ?? null
            ]);

            // Auto-progress through stages when scheduling interview
            $this->stageService->progressToStage($application, 'interview', 'Interview scheduling');

            // Log the final interview scheduling action
            $logData = [
                'application_id' => $application->id,
                'action' => 'interview_scheduled',
                'description' => 'Interview scheduled for ' . $scheduledAt->format('M d, Y H:i') . ' with ' . $interviewerName,
                'created_at' => now()
            ];
            
            // Only add changed_by if user is authenticated
            if (auth()->check() && auth()->id()) {
                $logData['changed_by'] = auth()->id();
            }
            
            ApplicationLog::create($logData);

            DB::commit();

            // Notify candidate and interviewer (After commit - Sanket)
            // This ensures data is saved even if notification fails
            try {
                if ($application->user) {
                    // Internal applicant
                    $application->user->notify(new \App\Notifications\Recruitment\CandidateInterviewScheduledNotification($interview));
                } elseif ($application->candidate_email) {
                    // External applicant
                    $candidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name);
                    $candidate->notify(new \App\Notifications\Recruitment\CandidateInterviewScheduledNotification($interview));
                }
                
                if ($interview->interviewer) {
                    // Notify HR/Interviewer using the staff-facing version
                    $interview->interviewer->notify(new InterviewScheduledNotification($interview));
                }
            } catch (Exception $e) {
                \Log::error('Failed to send interview notifications: ' . $e->getMessage());
                // We don't re-throw because the interview is already scheduled successfully
            }

        // Return appropriate response
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Interview scheduled successfully']);
            } else {
                return redirect()->back()->with('success', 'Interview scheduled successfully!');
            }

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Failed to schedule interview: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to schedule interview: ' . $e->getMessage()], 500);
            } else {
                return redirect()->back()->with('error', 'Failed to schedule interview: ' . $e->getMessage());
            }
        }
    }

    /**
     * Remove the specified application.
     */
    public function destroy($id)
    {
        // BUG-REC-002 Fix: Add authorization check - Author: Sanket
        if (!auth()->user()->hasAnyRole(['admin', 'hr'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized: Only admins and HR can delete applications'
            ], 403);
        }

        try {
            $application = Application::findOrFail($id);
            
            // BUG-REC-010 Fix: Check for related data before deletion - Author: Sanket
            DB::beginTransaction();
            
            // Check for scheduled interviews
            if ($application->interviews()->where('status', 'scheduled')->count() > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot delete: Application has scheduled interviews. Please cancel them first.'
                ], 400);
            }
            
            // Check for active offer
            if ($application->offer && in_array($application->offer->status, ['pending', 'sent'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot delete: Application has an active offer. Please withdraw the offer first.'
                ], 400);
            }
            
            // BUG-REC-004 Fix: Add logging - Author: Sanket
            \Log::info('Application deleted', [
                'application_id' => $id,
                'candidate_name' => $application->candidate_name,
                'candidate_email' => $application->candidate_email,
                'job_id' => $application->job_id,
                'deleted_by' => auth()->id(),
                'deleted_at' => now()
            ]);
            
            // Delete related records first
            $application->logs()->delete();
            $application->interviews()->delete();
            
            // Delete resume file if exists
            if ($application->resume_path && Storage::disk('public')->exists($application->resume_path)) {
                Storage::disk('public')->delete($application->resume_path);
            }
            
            $application->delete();
            
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Application deleted successfully']);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete application: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete application'], 500);
        }
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $applications = Application::with(['job', 'user', 'logs'])
                ->when($request->stage, function ($query, $stage) {
                    return $query->where('stage', $stage);
                })
                ->when($request->job_id, function ($query, $jobId) {
                    return $query->where('job_id', $jobId);
                })
                ->paginate($request->get('per_page', 15));

            return response()->json(['success' => true, 'data' => $applications]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch applications'], 500);
        }
    }

    public function apiShow($id): JsonResponse
    {
        try {
            $application = Application::with(['job', 'user', 'logs.changedBy', 'interviews', 'offer'])
                ->findOrFail($id);
            
            return response()->json(['success' => true, 'data' => $application]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Application not found'], 404);
        }
    }

    public function apiStore(Request $request): JsonResponse
    {
        $fileUploadService = app(FileUploadService::class);
        $validator = Validator::make($request->all(), (new StoreApplicationRequest())->rules());

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $resumePath = null;
            if ($request->hasFile('resume')) {
                $resumePath = $fileUploadService->uploadResume(
                    $request->file('resume'), 
                    $request->job_id,
                    $request->candidate_email
                );
            }

            $data = $request->all();
            $data['resume_path'] = $resumePath;
            $data['stage'] = 'applied';
            $data['applied_on'] = now();

            $application = Application::create($data);
            DB::commit();

            return response()->json(['success' => true, 'data' => $application], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $application = Application::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:recruitment_jobs,id',
            'candidate_name' => 'required_without:user_id|string|max:255',
            'candidate_email' => 'required_without:user_id|email|max:255|unique:recruitment_applications,candidate_email,' . $id,
            'expected_salary' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $application->update($request->all());
            DB::commit();
            return response()->json(['success' => true, 'data' => $application]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiDestroy($id): JsonResponse
    {
        return $this->destroy($id);
    }

    public function apiUpdateStage(Request $request, $id): JsonResponse
    {
        return $this->updateStage($request, $id);
    }

    public function apiAddNote(Request $request, $id): JsonResponse
    {
        return $this->addNote($request, $id);
    }

    public function apiTimeline($id): JsonResponse
    {
        return $this->timeline($id);
    }

    public function apiScheduleInterview(Request $request, $id): JsonResponse
    {
        return $this->scheduleInterview($request, $id);
    }

    public function apiJobApplications($jobId): JsonResponse
    {
        try {
            $applications = Application::with(['user'])
                ->where('job_id', $jobId)
                ->paginate(15);
            
            return response()->json(['success' => true, 'data' => $applications]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch applications'], 500);
        }
    }

    public function apiRecentApplications(): JsonResponse
    {
        try {
            $applications = Application::with(['job', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return response()->json(['success' => true, 'data' => $applications]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch recent applications'], 500);
        }
    }

    /**
     * Move application to different stage
     */
    public function moveStage(Request $request, $id)
    {
        try {
            $application = Application::findOrFail($id);
            
            $request->validate([
                'stage' => 'required|in:applied,screening,shortlisted,interview,offer,hired,rejected,withdrawn',
                'notes' => 'nullable|string|max:1000'
            ]);
            
            $oldStage = $application->stage;
            $success = $this->stageService->moveToStage(
                $application, 
                $request->stage, 
                $request->notes ?? "Stage moved to {$request->stage}"
            );
            
            if ($success) {
                $this->notifyStatusChange($application, $oldStage);
                return redirect()->back()->with('success', 'Application stage updated successfully!');
            }
            
            return redirect()->back()->with('error', 'Failed to update application stage.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to update application stage: ' . $e->getMessage());
        }
    }

    /**
     * Notify candidate about status change
     */
    private function notifyStatusChange(Application $application, string $oldStage)
    {
        if ($application->user) {
            $application->user->notify(new ApplicationStatusChangedNotification(
                $application, $oldStage, $application->stage
            ));
        } elseif ($application->candidate_email) {
            $candidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name);
            $candidate->notify(new ApplicationStatusChangedNotification(
                $application, $oldStage, $application->stage
            ));
        }
    }

    /**
     * Add notes to application
     */
    public function addNotes(Request $request, $id)
    {
        try {
            $application = Application::findOrFail($id);
            
            $request->validate([
                'notes' => 'required|string|max:1000'
            ]);
            
            // Update existing notes or create new ones
            $existingNotes = $application->notes ? $application->notes . "\n\n" : '';
            $newNotes = $existingNotes . "[" . now()->format('Y-m-d H:i') . " by " . auth()->user()->name . "]\n" . $request->notes;
            
            $application->notes = $newNotes;
            $application->save();
            
            // Log the action
            ApplicationLog::create([
                'application_id' => $application->id,
                'previous_stage' => $application->stage,
                'new_stage' => $application->stage,
                'changed_by' => auth()->id(),
                'description' => "Notes added by " . auth()->user()->name,
                'created_at' => now()
            ]);
            
            return redirect()->back()->with('success', 'Notes added successfully!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to add notes: ' . $e->getMessage());
        }
    }

    /**
     * Download application resume
     */
    public function downloadResume($id)
    {
        try {
            $application = Application::findOrFail($id);
            
            // Check if user has permission to view applications
            canPerform('View Applications');
            
            if (!$application->resume_url && !$application->resume_path) {
                return redirect()->back()->with('error', 'Resume not found.');
            }
            
            // Use the accessor or the direct path
            $path = $application->resume_path ?: $application->resume_url;
            
            // Fix: Strip 'storage/' or 'public/' prefix if present in the stored path
            if ($application->resume_path) {
                 $path = $application->resume_path;
            } elseif ($path) {
                $path = preg_replace('/^(storage\/|public\/|\/storage\/|\/public\/)/', '', $path);
            }
            
            if (!$path || !Storage::disk('public')->exists($path)) {
                // If it's a full URL, we might want to redirect to it instead of downloading
                if ($application->resume_url && (str_starts_with($application->resume_url, 'http'))) {
                    return redirect()->away($application->resume_url);
                }
                return redirect()->back()->with('error', 'Resume file not found on server.');
            }
            
            return Storage::disk('public')->download($path);

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to download resume: ' . $e->getMessage());
        }
    }

    /**
     * Notify HR/Admin users about new application
     */
    private function notifyHRAboutNewApplication($application)
    {
        // Get users with HR or Admin roles
        $hrUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'hr']);
        })->get();

        if ($hrUsers->count() > 0) {
            \Illuminate\Support\Facades\Notification::send($hrUsers, new ApplicationSubmittedNotification($application));
        }
    }
}
