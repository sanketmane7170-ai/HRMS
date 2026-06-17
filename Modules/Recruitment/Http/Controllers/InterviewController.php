<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Recruitment\Entities\Interview;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\ApplicationLog;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use App\Notifications\Recruitment\NewJobPostedNotification;
use App\Notifications\Recruitment\InterviewCompletedNotification;

class InterviewController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'recruitment-interviews');
    }

    /**
     * Display a listing of interviews.
     */
    public function index()
    {
        // Check for AJAX/DataTable request first
        if (request()->ajax() || request()->wantsJson() || request()->has('draw')) {
            return $this->datatable();
        }
        
        // Check authentication and permissions for regular page requests
        if (!auth()->check()) {
            abort(401, 'Authentication required');
        }
        
        $user = auth()->user();
        $hasAccess = $user->hasRole(['admin', 'hr']) || 
                    $user->can('View Interview Details') || 
                    $user->can('Manage Interviews');
                    
        if (!$hasAccess) {
            abort(403, 'Insufficient permissions to view interviews');
        }

        $interviewers = User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'Employee');
        })->get();
        
        // Get interviews with pagination (10 per page)
        $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
            ->when(request('interviewer_id'), function ($query, $interviewerId) {
                return $query->where('interviewer_id', $interviewerId);
            })
            ->when(request('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when(request('date'), function ($query, $date) {
                return $query->whereDate('scheduled_at', $date);
            })
            ->when(request('type'), function ($query, $type) {
                return $query->where('type', $type);
            })
            ->orderBy('scheduled_at', 'desc')
            ->paginate(10);
            
        \Log::info('Interview index: Passing ' . $interviews->count() . ' interviews to view (paginated)');
        
        return view('recruitment::interviews.index', compact('interviewers', 'interviews'));
    }

    /**
     * DataTable for interviews listing
     */
    public function datatable()
    {
        try {
            \Log::info('DataTable request received', [
                'user_authenticated' => auth()->check(),
                'request_params' => request()->all(),
                'is_ajax' => request()->ajax()
            ]);

            // Check authentication first
            if (!auth()->check()) {
                \Log::error('DataTable: User not authenticated');
                return response()->json(['error' => 'Authentication required'], 401);
            }
            
            // Enforce strict permission check (Sanket - REC-SEC-020)
            $user = auth()->user();
            if (!$user->hasRole(['admin', 'hr']) && !$user->can('View Interview Details') && !$user->can('Manage Interviews')) {
                 \Log::error('DataTable: Insufficient permissions');
                 return response()->json(['error' => 'Insufficient permissions'], 403);
            }
            
            \Log::info('DataTable: User authenticated', ['user_id' => $user->id, 'user_email' => $user->email]);
            
            $query = Interview::with(['application.job', 'application.user', 'interviewer'])
                ->select(['interviews.*']);

        // Apply filters
        if (request()->filled('interviewer_id')) {
            $query->where('interviewer_id', request('interviewer_id'));
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('type')) {
            $query->where('type', request('type'));
        }

        if (request()->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', request('date_from'));
        }

        if (request()->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', request('date_to'));
        }

        // Show all interviews by default (don't filter by date unless specifically requested)
        // if (!request()->filled('date_from') && !request()->filled('date_to')) {
        //     $query->whereDate('scheduled_at', '>=', now()->toDateString());
        // }

        $interviews = $query;
        
        \Log::info('DataTable: Query built', [
            'total_interviews' => Interview::count(),
            'query_count' => $interviews->count(),
            'filters' => [
                'interviewer_id' => request('interviewer_id'),
                'status' => request('status'),
                'type' => request('type')
            ]
        ]);

            return DataTables::of($interviews)
            ->addIndexColumn()
            ->addColumn('candidate', function ($interview) {
                if ($interview->application && $interview->application->user) {
                    return $interview->application->user->name;
                }
                if ($interview->application && $interview->application->candidate_name) {
                    return $interview->application->candidate_name;
                }
                return 'Unknown Candidate';
            })
            ->addColumn('job_title', function ($interview) {
                return optional($interview->application)->job->title ?? 'N/A';
            })
            ->addColumn('interviewer_name', function ($interview) {
                return $interview->interviewer->name ?? 'N/A';
            })
            ->addColumn('scheduled_date', function ($interview) {
                return $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A';
            })
            ->addColumn('type_badge', function ($interview) {
                $type = $interview->type ?: 'phone';
                $badges = [
                    'phone' => 'badge-info',
                    'video' => 'badge-primary',
                    'in_person' => 'badge-success'
                ];
                $class = $badges[$type] ?? 'badge-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst(str_replace('_', ' ', $type)) . '</span>';
            })
            ->addColumn('status_badge', function ($interview) {
                $badges = [
                    'scheduled' => 'badge-warning',
                    'completed' => 'badge-success',
                    'cancelled' => 'badge-danger',
                    'rescheduled' => 'badge-info'
                ];
                $class = $badges[$interview->status] ?? 'badge-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($interview->status) . '</span>';
            })
            ->addColumn('duration', function ($interview) {
                return $interview->duration_minutes . ' minutes';
            })
            ->addColumn('action', function ($interview) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<a href="' . route('recruitment.interviews.show', $interview->id) . '" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></a>';
                
                $user = auth()->user();
                if ($user && ($user->hasRole(['admin', 'hr']) || $user->can('Manage Interviews'))) {
                    $actions .= '<a href="' . route('recruitment.interviews.edit', $interview->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fa fa-edit"></i></a>';
                    
                    if ($interview->status === 'scheduled') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success complete-interview" data-id="' . $interview->id . '" title="Complete"><i class="fa fa-check"></i></button>';
                        $actions .= '<button type="button" class="btn btn-sm btn-warning reschedule-interview" data-id="' . $interview->id . '" title="Reschedule"><i class="fa fa-calendar"></i></button>';
                    }
                }
                
                if (auth()->user() && (auth()->user()->hasRole(['admin', 'hr']) || auth()->user()->can('Manage Interviews'))) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger delete-interview" data-id="' . $interview->id . '" title="Delete"><i class="fa fa-trash"></i></button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['type_badge', 'status_badge', 'action'])
            ->make(true);
        } catch (\Exception $e) {
            \Log::error('Interview datatable error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load interviews: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check for existing interviews for a candidate
     */
    public function checkExistingInterview(Request $request)
    {
        try {
            canPerform('Schedule Interviews'); // Enforce permission (Sanket - REC-SEC-021)
            $applicationId = $request->input('application_id');
            
            if (!$applicationId) {
                return response()->json(['exists' => false]);
            }
            
            // Find existing scheduled interviews for this application
            $existingInterviews = Interview::where('application_id', $applicationId)
                ->where('status', 'scheduled')
                ->with(['application.user', 'interviewer'])
                ->get();
            
            if ($existingInterviews->count() > 0) {
                $interviewData = $existingInterviews->map(function ($interview) {
                    return [
                        'id' => $interview->id,
                        'scheduled_at' => $interview->scheduled_at->format('M d, Y H:i'),
                        'interviewer' => $interview->interviewer->name ?? 'Unknown',
                        'type' => ucfirst(str_replace('_', ' ', $interview->type)),
                        'candidate' => $interview->application->user->name ?? $interview->application->candidate_name ?? 'Unknown'
                    ];
                });
                
                return response()->json([
                    'exists' => true,
                    'interviews' => $interviewData,
                    'message' => 'This candidate already has ' . $existingInterviews->count() . ' scheduled interview(s).'
                ]);
            }
            
            return response()->json(['exists' => false]);
            
        } catch (\Exception $e) {
            \Log::error('Error checking existing interviews: ' . $e->getMessage());
            return response()->json(['exists' => false, 'error' => 'Unable to check existing interviews']);
        }
    }

    /**
     * Show the form for creating a new interview.
     */
    public function create(Request $request)
    {
        if (!auth()->check()) {
            abort(401, 'Authentication required');
        }
        
        $user = auth()->user();
        $hasAccess = $user->hasRole(['admin', 'hr']) || 
                    $user->can('Schedule Interviews') || 
                    $user->can('Manage Interviews');
                    
        if (!$hasAccess) {
            abort(403, 'Insufficient permissions to schedule interviews');
        }
        
        $applicationId = $request->get('application_id');
        
        $applications = Application::with(['job', 'user'])
            ->where(function($query) use ($applicationId) {
                $query->whereIn('stage', ['screening', 'interview', 'applied']);
                
                if ($applicationId) {
                    $query->orWhere('id', $applicationId);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        $interviewers = User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'Employee');
        })->select(['id', 'name', 'email'])
        ->orderBy('name')
        ->get();
        
        return view('recruitment::interviews.create', compact('applications', 'interviewers'));
    }

    /**
     * Store a newly created interview.
     */
    public function store(Request $request): RedirectResponse
    {
        canPerform('Schedule Interviews');
        
        // Combine date and time if they're separate
        if ($request->has('interview_date') && $request->has('interview_time')) {
            $scheduledAt = $request->interview_date . ' ' . $request->interview_time;
            $request->merge(['scheduled_at' => $scheduledAt]);
        }
        
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:recruitment_applications,id',
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'type' => 'required|in:phone,video,in_person',
            'location' => 'required_if:type,phone,video,in_person|string|max:255',
            'duration' => 'nullable|integer|min:15|max:480', // 15 min to 8 hours
            'notes' => 'nullable|string',
            'round' => 'nullable|integer|min:1|max:10' // Support up to 10 interview rounds
        ], [
            'application_id.required' => 'Please select a candidate application.',
            'application_id.exists' => 'The selected application is invalid.',
            'interviewer_id.required' => 'Please select an interviewer.',
            'interviewer_id.exists' => 'The selected interviewer is invalid.',
            'scheduled_at.required' => 'Please provide interview date and time.',
            'scheduled_at.date' => 'Please provide a valid date and time.',
            'scheduled_at.after' => 'Interview must be scheduled for a future date.',
            'type.required' => 'Please select an interview type.',
            'type.in' => 'Please select a valid interview type.',
            'location.required_if' => 'Location is required for the selected interview type.',
            'duration.min' => 'Interview duration must be at least 15 minutes.',
            'duration.max' => 'Interview duration cannot exceed 8 hours.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for duplicate interviews on the same date
        $existingInterview = Interview::where('application_id', $request->application_id)
            ->whereDate('scheduled_at', Carbon::parse($request->scheduled_at)->toDateString())
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingInterview) {
            return redirect()->back()
                ->with('error', 'An interview for this candidate on the selected date already exists.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $interview = Interview::create([
                'application_id' => $request->application_id,
                'interviewer_id' => $request->interviewer_id,
                'scheduled_at' => $request->scheduled_at,
                'type' => $request->type,
                'location' => $request->location,
                'duration_minutes' => $request->duration_minutes ?? $request->duration ?? 60,
                'status' => 'scheduled',
                'notes' => $request->notes,
                'round' => $request->round ?? 1 // Default to round 1 if not specified
            ]);

            // Update application stage to interview
            $application = Application::with(['user', 'job'])->find($request->application_id);
            $oldStage = $application->stage;
            
            // Always update to interview stage when interview is scheduled
            if (!in_array($application->stage, ['offer', 'hired'])) {
                $application->update(['stage' => 'interview']);
                
                \Log::info('Updated application stage', [
                    'application_id' => $application->id,
                    'old_stage' => $oldStage,
                    'new_stage' => 'interview'
                ]);
                
                // Send notification to candidate if they exist
                $this->sendStatusChangeNotification($application, $oldStage, 'interview');
            }

            // Always send specific interview scheduled notification
            if ($application->user) {
                $application->user->notify(new \App\Notifications\Recruitment\InterviewScheduledNotification($interview));
            } elseif ($application->candidate_email) {
                 $candidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name);
                 $candidate->notify(new \App\Notifications\Recruitment\InterviewScheduledNotification($interview));
            }

            // Send notification to interviewer
            if ($interview->interviewer) {
                $interview->interviewer->notify(new \App\Notifications\Recruitment\InterviewScheduledNotification($interview));
            }

            // Log the interview scheduling
            ApplicationLog::create([
                'application_id' => $request->application_id,
                'action' => 'interview_scheduled',
                'description' => 'Interview scheduled for ' . Carbon::parse($request->scheduled_at)->format('M d, Y H:i'),
                'changed_by' => auth()->id(),
                'metadata' => [
                    'interview_id' => $interview->id,
                    'interviewer_id' => $request->interviewer_id,
                    'type' => $request->type
                ]
            ]);

            DB::commit();

            return redirect()->route('recruitment.interviews.index')
                ->with('success', 'Interview scheduled successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to schedule interview: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the specified interview.
     */
    public function show($id)
    {
        $interview = Interview::with(['application.job', 'application.user', 'interviewer', 'application.logs', 'feedback'])
            ->findOrFail($id);
            
        return view('recruitment::interviews.show', compact('interview'));
    }

    /**
     * Schedule next round of interview
     */
    public function scheduleNextRound(Request $request, $id)
    {
        $request->validate([
            'application_id' => 'required|exists:recruitment_applications,id',
            'round' => 'required|integer|min:2',
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'type' => 'required|in:phone,video,in_person,panel',
            'duration_minutes' => 'required|integer|min:30|max:480'
        ]);

        // Verify that previous round is completed
        if (!Interview::canScheduleNextRound($request->application_id, $request->round)) {
            return response()->json([
                'success' => false,
                'message' => 'Previous round must be completed before scheduling the next round.'
            ], 400);
        }

        try {
            $interview = Interview::create([
                'application_id' => $request->application_id,
                'round' => $request->round,
                'interviewer_id' => $request->interviewer_id,
                'scheduled_by' => auth()->id(),
                'scheduled_at' => $request->scheduled_at,
                'duration_minutes' => $request->duration_minutes ?? 60,
                'type' => $request->type,
                'status' => 'scheduled',
                'location' => $request->location,
                'meeting_link' => $request->meeting_link,
                'agenda' => $request->agenda,
                'preparation_notes' => $request->preparation_notes,
            ]);

            // Send notification to candidate
            $application = $interview->application;
            if ($application->user) {
                $application->user->notify(new \App\Notifications\Recruitment\InterviewScheduledNotification($interview));
            } elseif ($application->candidate_email) {
                $candidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name);
                $candidate->notify(new \App\Notifications\Recruitment\InterviewScheduledNotification($interview));
            }

            // Send notification to interviewer
            $interview->interviewer->notify(new \App\Notifications\Recruitment\InterviewScheduledNotification($interview));

            return response()->json([
                'success' => true,
                'message' => "Round {$request->round} interview scheduled successfully!",
                'interview_id' => $interview->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to schedule next round interview: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'current_interview_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule interview. Please try again.'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified interview.
     */
    public function edit($id)
    {
        $interview = Interview::findOrFail($id);
        $applications = Application::with(['job', 'user'])
            ->where('stage', 'screening')
            ->orWhere('stage', 'interview')
            ->get();
        
        $interviewers = User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'Employee');
        })->get();
        
        return view('recruitment::interviews.edit', compact('interview', 'applications', 'interviewers'));
    }

    /**
     * Update the specified interview.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $interview = Interview::findOrFail($id);
        
        // Store old scheduled time for notification comparison - Author: Sanket
        $oldScheduledAt = $interview->scheduled_at;

        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:recruitment_applications,id',
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'type' => 'required|in:phone,video,in_person',
            'location' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:15|max:480',
            'score' => 'nullable|numeric|min:0|max:10',
            'feedback' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $interview->update([
                'application_id' => $request->application_id,
                'interviewer_id' => $request->interviewer_id,
                'scheduled_at' => $request->scheduled_at,
                'type' => $request->type,
                'location' => $request->location,
                'duration_minutes' => $request->duration ?? $interview->duration_minutes,
                'score' => $request->score,
                'feedback' => $request->feedback,
                'notes' => $request->notes
            ]);

            // Log the update
            ApplicationLog::create([
                'application_id' => $interview->application_id,
                'action' => 'interview_updated',
                'description' => 'Interview details updated',
                'changed_by' => auth()->id(),
                'metadata' => json_encode($request->only(['scheduled_at', 'type', 'score']))
            ]);

            // Send reschedule notifications if date/time changed - Author: Sanket
            if ($oldScheduledAt != $request->scheduled_at) {
                try {
                    // Reload interview with relationships for notifications
                    $interview->load(['application.user', 'application.job', 'interviewer']);
                    $application = $interview->application;
                    
                    // Notify candidate
                    if ($application->user) {
                        $application->user->notify(
                            new \Modules\Recruitment\Notifications\InterviewRescheduledNotification($interview, $oldScheduledAt)
                        );
                    } elseif ($application->candidate_email) {
                        // For external candidates, send email directly
                        \Illuminate\Support\Facades\Notification::route('mail', $application->candidate_email)
                            ->notify(new \Modules\Recruitment\Notifications\InterviewRescheduledNotification($interview, $oldScheduledAt));
                    }

                    // Notify interviewer
                    if ($interview->interviewer) {
                        $interview->interviewer->notify(
                            new \Modules\Recruitment\Notifications\InterviewRescheduledNotification($interview, $oldScheduledAt)
                        );
                    }

                    // Notify HR team
                    $hrUsers = \App\Models\User::role(['hr', 'admin'])->get();
                    foreach ($hrUsers as $hr) {
                        $hr->notify(
                            new \Modules\Recruitment\Notifications\InterviewRescheduledNotification($interview, $oldScheduledAt)
                        );
                    }

                    \Log::info('Interview rescheduled notifications sent', [
                        'interview_id' => $interview->id,
                        'old_time' => $oldScheduledAt,
                        'new_time' => $request->scheduled_at
                    ]);
                } catch (\Exception $notificationError) {
                    \Log::error('Failed to send reschedule notifications: ' . $notificationError->getMessage());
                    // Continue even if notifications fail
                }
            }

            DB::commit();

            return redirect()->route('recruitment.interviews.show', $interview->id)
                ->with('success', 'Interview updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to update interview: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Complete an interview
     */
    public function complete(Request $request, $id)
    {
        canPerform('Schedule Interviews');
        try {
            $interview = Interview::with(['application.user', 'interviewer'])->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'score' => 'required|numeric|min:1|max:10',
                'feedback' => 'required|string',
                'recommendation' => 'required|in:hire,reject,second_interview,on_hold',
                'next_steps' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            DB::beginTransaction();

        // Safeguard: Ensure application exists
        if (!$interview->application) {
            throw new \Exception("The associated application for this interview could not be found.");
        }

        // Store feedback directly in interview or create feedback record
        $interview->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Create interview feedback record using the correct relationship
        $interview->feedback()->create([
            'interview_id' => $interview->id,
            'application_id' => $interview->application_id,
            'interviewer_id' => auth()->id() ?? $interview->interviewer_id, // Fallback to assigned interviewer
            'interview_date' => $interview->scheduled_at,
            'interview_type' => $this->mapInterviewType($interview->type),
            'interview_round' => $interview->round ?? 1,
            'duration_minutes' => $interview->duration_minutes,
            'status' => 'completed',
            'interviewer_observations' => $request->feedback,
            'recommendation' => $this->mapRecommendation($request->recommendation),
            'recommendation_reason' => $request->next_steps,
            'overall_rating' => $request->score,
            'completed_at' => now()
        ]);

        // Update application stage based on recommendation
        $newStage = match($request->recommendation) {
            'hire' => 'offer_pending',
            'reject' => 'rejected',
            'second_interview' => 'second_interview',
            'on_hold' => 'on_hold',
            default => $interview->application->stage
        };
        
        $interview->application->update([
            'stage' => $newStage
        ]);

        DB::commit();

            // Send notification to candidate (Decoupled from transaction)
            try {
                $this->sendInterviewCompletedNotification($interview, $request->recommendation);
            } catch (\Exception $e) {
                \Log::error('Failed to send interview completion notification: ' . $e->getMessage());
                // Do not fail the request if notification fails
            }

            // Handle AJAX vs regular requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Interview completed successfully!'
                ]);
            }

            return redirect()->route('recruitment.interviews.show', $interview->id)
                ->with('success', 'Interview completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to complete interview: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to complete interview: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to complete interview: ' . $e->getMessage());
        }
    }

    /**
     * Reschedule an interview
     */
    public function reschedule(Request $request, $id)
    {
        canPerform('Schedule Interviews'); // Enforce permission (Sanket - REC-SEC-021)
        try {
            $interview = Interview::with(['application.user', 'interviewer'])->findOrFail($id);
            
            // Combine date and time into datetime
            $newDateTime = $request->new_date . ' ' . $request->new_time;
            
            // Convert checkbox value to boolean
            $notifyCandidate = in_array($request->notify_candidate, ['on', '1', 1, true], true);
            
            $validator = Validator::make($request->all(), [
                'new_date' => 'required|date|after_or_equal:today',
                'new_time' => 'required|date_format:H:i',
                'reschedule_reason' => 'required|string|min:10'
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first(),
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Fixed by Sanket: Ensure new date/time is in the future
            $newScheduledAt = \Carbon\Carbon::parse($newDateTime);
            if ($newScheduledAt->isPast()) {
                $errorMsg = 'Rescheduled date and time must be in the future.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->with('error', $errorMsg)->withInput();
            }

            DB::beginTransaction();

            $oldDateTime = $interview->scheduled_at;
            
            $interview->update([
                'scheduled_at' => $newScheduledAt,
                'status' => 'scheduled' // Keep as scheduled, not rescheduled
            ]);

            // Send notification to candidate if requested
            if ($notifyCandidate) {
                $this->sendInterviewRescheduledNotification($interview, $oldDateTime, $request->reschedule_reason);
            }

            \Log::info('Interview rescheduled successfully', [
                'interview_id' => $interview->id,
                'old_date' => $oldDateTime,
                'new_date' => $newScheduledAt,
                'reason' => $request->reschedule_reason
            ]);

            DB::commit();

            // Handle AJAX vs regular requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Interview rescheduled successfully!',
                    'redirect' => route('recruitment.interviews.show', $interview->id)
                ]);
            }

            return redirect()->route('recruitment.interviews.show', $interview->id)
                ->with('success', 'Interview rescheduled successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to reschedule interview: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to reschedule interview: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to reschedule interview: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an interview
     */
    public function cancel(Request $request, $id)
    {
        try {
            $interview = Interview::with(['application.user', 'interviewer'])->findOrFail($id);
            
            // Convert checkbox value to boolean
            $notifyCandidate = in_array($request->notify_candidate_cancel, ['on', '1', 1, true], true);
            
            $validator = Validator::make($request->all(), [
                'cancellation_reason' => 'required|string|min:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            DB::beginTransaction();

            $interview->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason
            ]);

            DB::commit();

            // Send notification to candidate if requested (Decoupled from transaction)
            if ($notifyCandidate && $interview->application->user) {
                try {
                    $this->sendInterviewCancelledNotification($interview, $request->cancellation_reason);
                } catch (\Exception $e) {
                    \Log::error('Failed to send interview cancellation notification: ' . $e->getMessage());
                    // Do not fail the request if notification fails
                }
            }

            // Handle AJAX vs regular requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Interview cancelled successfully!',
                    'redirect' => route('recruitment.interviews.show', $interview->id)
                ]);
            }

            return redirect()->route('recruitment.interviews.show', $interview->id)
                ->with('success', 'Interview cancelled successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to cancel interview: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to cancel interview: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to cancel interview: ' . $e->getMessage());
        }
    }

    /**
     * Get interview feedback
     */
    public function feedback($id)
    {
        canPerform('View Interview Details'); // Enforce permission (Sanket - REC-SEC-019)
        $interview = Interview::with(['application.job', 'application.user', 'interviewer'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'feedback' => $interview->feedback,
                'score' => $interview->score,
                'recommendation' => $interview->recommendation,
                'completed_at' => $interview->completed_at
            ]
        ]);
    }

    /**
     * Remove the specified interview.
     */
    public function destroy($id)
    {
        try {
            // Check permissions
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            $user = auth()->user();
            $hasAccess = $user->hasRole(['admin', 'hr']) || 
                        $user->can('Manage Interviews') || 
                        $user->can('Delete Interviews');
                        
            if (!$hasAccess) {
                return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
            }

            $interview = Interview::with(['application.user', 'application.job', 'interviewer'])->findOrFail($id);
            
            // Send cancellation notifications before deleting - Author: Sanket
            try {
                $application = $interview->application;
                
                // Notify candidate
                if ($application->user) {
                    $application->user->notify(
                        new \Modules\Recruitment\Notifications\InterviewCancelledNotification($interview, 'Interview cancelled by HR')
                    );
                } elseif ($application->candidate_email) {
                    // For external candidates
                    \Illuminate\Support\Facades\Notification::route('mail', $application->candidate_email)
                        ->notify(new \Modules\Recruitment\Notifications\InterviewCancelledNotification($interview, 'Interview cancelled by HR'));
                }

                // Notify interviewer
                if ($interview->interviewer) {
                    $interview->interviewer->notify(
                        new \Modules\Recruitment\Notifications\InterviewCancelledNotification($interview, 'Interview cancelled by HR')
                    );
                }

                // Notify HR team
                $hrUsers = \App\Models\User::role(['hr', 'admin'])->get();
                foreach ($hrUsers as $hr) {
                    if ($hr->id != auth()->id()) { // Don't notify the person who cancelled
                        $hr->notify(
                            new \Modules\Recruitment\Notifications\InterviewCancelledNotification($interview, 'Interview cancelled by ' . auth()->user()->name)
                        );
                    }
                }

                \Log::info('Interview cancellation notifications sent', [
                    'interview_id' => $interview->id,
                    'cancelled_by' => auth()->id()
                ]);
            } catch (\Exception $notificationError) {
                \Log::error('Failed to send cancellation notifications: ' . $notificationError->getMessage());
                // Continue with deletion even if notifications fail
            }
            
            // Log the deletion
            ApplicationLog::create([
                'application_id' => $interview->application_id,
                'action' => 'interview_cancelled',
                'description' => 'Interview cancelled/deleted',
                'changed_by' => auth()->id(),
                'metadata' => json_encode(['interview_id' => $interview->id])
            ]);
            
            $interview->delete();

            // Handle AJAX vs regular requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Interview deleted successfully']);
            }

            return redirect()->route('recruitment.interviews.index')
                ->with('success', 'Interview deleted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Interview not found'], 404);
            }
            return redirect()->back()->with('error', 'Interview not found');
        } catch (Exception $e) {
            \Log::error('Failed to delete interview: ' . $e->getMessage(), [
                'interview_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete interview: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete interview: ' . $e->getMessage());
        }
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->interviewer_id, function ($query, $interviewerId) {
                    return $query->where('interviewer_id', $interviewerId);
                })
                ->when($request->date, function ($query, $date) {
                    return $query->whereDate('scheduled_at', $date);
                })
                ->orderBy('scheduled_at', 'asc')
                ->paginate($request->get('per_page', 15));

            return response()->json(['success' => true, 'data' => $interviews]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch interviews'], 500);
        }
    }

    public function apiShow($id): JsonResponse
    {
        try {
            $interview = Interview::with(['application.job', 'application.user', 'interviewer'])
                ->findOrFail($id);
            
            return response()->json(['success' => true, 'data' => $interview]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Interview not found'], 404);
        }
    }

    public function apiStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:recruitment_applications,id',
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'type' => 'required|in:phone,video,in_person',
            'duration' => 'nullable|integer|min:15|max:480',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $interview = Interview::create([
                'application_id' => $request->application_id,
                'interviewer_id' => $request->interviewer_id,
                'scheduled_at' => $request->scheduled_at,
                'type' => $request->type,
                'location' => $request->location ?? 'Virtual/TBD',
                'duration_minutes' => $request->duration ?? 60,
                'status' => 'scheduled',
            ]);
            DB::commit();
            return response()->json(['success' => true, 'data' => $interview], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $interview = Interview::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'type' => 'required|in:phone,video,in_person',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            $interview->update($request->all());
            DB::commit();
            return response()->json(['success' => true, 'data' => $interview]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiDestroy($id): JsonResponse
    {
        return $this->destroy($id);
    }

    public function apiComplete(Request $request, $id): JsonResponse
    {
        return $this->complete($request, $id);
    }

    public function apiReschedule(Request $request, $id): JsonResponse
    {
        return $this->reschedule($request, $id);
    }

    public function apiFeedback($id): JsonResponse
    {
        return $this->feedback($id);
    }

    public function apiUpcomingInterviews(): JsonResponse
    {
        try {
            $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at', 'asc')
                ->limit(10)
                ->get();
            
            return response()->json(['success' => true, 'data' => $interviews]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to reschedule interview'], 500);
        }
    }

    /**
     * Test endpoint to return raw interview data
     */
    public function test()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])
            ->orderBy('scheduled_at', 'desc')
            ->get()
            ->map(function($interview) {
                return [
                    'id' => $interview->id,
                    'candidate' => $interview->application && $interview->application->user 
                        ? $interview->application->user->name 
                        : ($interview->application->candidate_name ?? 'Unknown'),
                    'job_title' => optional($interview->application)->job->title ?? 'N/A',
                    'interviewer_name' => optional($interview->interviewer)->name ?? 'N/A',
                    'scheduled_date' => $interview->scheduled_at ? $interview->scheduled_at->format('M d, Y H:i') : 'N/A',
                    'type' => $interview->type ?: 'phone',
                    'status' => $interview->status,
                    'duration' => ($interview->duration_minutes ?? 60) . ' minutes'
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $interviews->count(),
            'data' => $interviews
        ]);
    }

    /**
     * Debug method to check interview data
     */
    public function debug()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Not authenticated']);
        }

        $user = auth()->user();
        $interviews = Interview::with(['application.job', 'application.user', 'interviewer'])->get();
        
        return response()->json([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles' => $user->roles->pluck('name'),
            'interviews_count' => Interview::count(),
            'applications_count' => Application::count(),
            'interviews' => $interviews->map(function($interview) {
                return [
                    'id' => $interview->id,
                    'scheduled_at' => $interview->scheduled_at,
                    'status' => $interview->status,
                    'application_id' => $interview->application_id,
                    'application_exists' => $interview->application ? 'yes' : 'no',
                    'job_title' => $interview->application && $interview->application->job ? $interview->application->job->title : 'N/A',
                    'candidate' => $interview->application && $interview->application->user ? $interview->application->user->name : ($interview->application->candidate_name ?? 'N/A'),
                    'interviewer' => $interview->interviewer ? $interview->interviewer->name : 'N/A'
                ];
            })
        ]);
    }

    /**
     * Send notification to candidate about interview feedback
     */
    private function sendInterviewCompletedNotification($interview, $recommendation)
    {
        try {
            $candidate = $interview->application->user;
            
            if ($candidate) {
                $candidate->notify(new InterviewCompletedNotification($interview, $recommendation));
                $candidateId = $candidate->id;
            } elseif ($interview->application->candidate_email) {
                $candidate = new \App\Models\Recruitment\ExternalCandidate($interview->application->candidate_email, $interview->application->candidate_name);
                $candidate->notify(new InterviewCompletedNotification($interview, $recommendation));
                $candidateId = 'external_' . $interview->application->id;
            } else {
                return;
            }
            
            \Log::info('Interview completed notification sent to candidate', [
                'candidate_id' => $candidateId,
                'interview_id' => $interview->id,
                'recommendation' => $recommendation
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send interview completion notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to candidate about interview rescheduling
     */
    private function sendInterviewRescheduledNotification($interview, $oldDateTime, $reason)
    {
        try {
            $candidate = $interview->application->user;
            
            if ($candidate) {
                $candidate->notify(new \App\Notifications\Recruitment\InterviewRescheduledNotification(
                    $interview, 
                    $oldDateTime, 
                    $reason
                ));
                $candidateId = $candidate->id;
            } elseif ($interview->application->candidate_email) {
                $candidate = new \App\Models\Recruitment\ExternalCandidate($interview->application->candidate_email, $interview->application->candidate_name);
                $candidate->notify(new \App\Notifications\Recruitment\InterviewRescheduledNotification(
                    $interview, 
                    $oldDateTime, 
                    $reason
                ));
                $candidateId = 'external_' . $interview->application->id;
            } else {
                return;
            }
            
            \Log::info('Interview rescheduled notification sent to candidate', [
                'candidate_id' => $candidateId,
                'interview_id' => $interview->id,
                'old_date' => $oldDateTime,
                'new_date' => $interview->scheduled_at
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send interview reschedule notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to candidate about interview cancellation
     */
    private function sendInterviewCancelledNotification($interview, $reason)
    {
        try {
            $candidate = $interview->application->user;
            
            if ($candidate) {
                $candidate->notify(new \App\Notifications\Recruitment\InterviewCancelledNotification(
                    $interview, 
                    $reason
                ));
                $candidateId = $candidate->id;
            } elseif ($interview->application->candidate_email) {
                $candidate = new \App\Models\Recruitment\ExternalCandidate($interview->application->candidate_email, $interview->application->candidate_name);
                $candidate->notify(new \App\Notifications\Recruitment\InterviewCancelledNotification(
                    $interview, 
                    $reason
                ));
                $candidateId = 'external_' . $interview->application->id;
            } else {
                return;
            }
            
            \Log::info('Interview cancelled notification sent to candidate', [
                'candidate_id' => $candidateId,
                'interview_id' => $interview->id,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send interview cancellation notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to candidate about status change
     */
    private function sendStatusChangeNotification($application, $oldStage, $newStage)
    {
        try {
            if ($application->user) {
                // Create database notification
                $application->user->notify(new \App\Notifications\Recruitment\ApplicationStatusChangedNotification(
                    $application, $oldStage, $newStage
                ));
                $candidateId = $application->user->id;
            } elseif ($application->candidate_email) {
                $candidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name);
                $candidate->notify(new \App\Notifications\Recruitment\ApplicationStatusChangedNotification(
                    $application, $oldStage, $newStage
                ));
                $candidateId = 'external_' . $application->id;
            } else {
                return;
            }
            
            \Log::info('Status change notification sent to candidate', [
                'candidate_id' => $candidateId,
                'application_id' => $application->id,
                'old_stage' => $oldStage,
                'new_stage' => $newStage,
                'position' => $application->job->title ?? 'N/A'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send status change notification: ' . $e->getMessage());
        }
    }

    /**
     * Map interview type to feedback table enum values
     */
    private function mapInterviewType($type)
    {
        return match($type) {
            'phone' => 'phone_screening',
            'video', 'in-person' => 'technical',
            'panel' => 'panel',
            default => 'technical'
        };
    }

    /**
     * Map recommendation to feedback table enum values
     */
    private function mapRecommendation($recommendation)
    {
        return match($recommendation) {
            'hire' => 'hire',
            'reject' => 'reject',
            'second_interview' => 'next_round',
            'on_hold' => 'hold',
            default => 'hold'
        };
    }
}
