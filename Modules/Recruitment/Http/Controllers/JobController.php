<?php

namespace Modules\Recruitment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Recruitment\Entities\Job;
use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\Recruitment\NewJobPostedNotification;
use App\Http\Requests\Recruitment\StoreJobRequest;
use App\Http\Requests\Recruitment\UpdateJobRequest;
use Illuminate\Support\Facades\Notification;
use Exception;
use Modules\Recruitment\Services\MomAIService;

class JobController extends Controller
{
    protected $aiService;

    public function __construct(MomAIService $aiService)
    {
        view()->share('activeLink', 'recruitment-jobs');
        $this->aiService = $aiService;
    }

    /**
     * Generate Job Description using AI.
     */
    public function generateAi(Request $request)
    {
        canPerform('Create Recruitment Jobs'); // Permission check restored (Sanket - REC-SEC-010)

        $request->validate([
            'title' => 'required|string|min:3',
            'field' => 'required|string|in:description,requirements,responsibilities,skills'
        ]);

        try {
            // Build Context Array
            $context = [
                'title' => $request->title,
                'skills' => $request->skills ?? '',
                'job_type' => $request->job_type,
                'experience_level' => $request->experience_level,
                'location' => $request->location,
                'remote_work' => $request->boolean('remote_work')
            ];

            // Resolve Department
            if ($request->department_id) {
                $dept = \App\Models\Department::find($request->department_id);
                if ($dept) $context['department'] = $dept->name;
            }

            $result = $this->aiService->generateJobField($request->field, $context);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'data' => $result // Returns string (html) or array (skills)
                ]);
            }
            
            return response()->json(['success' => false, 'message' => 'AI generation failed.'], 422);

        } catch (\Exception $e) {
            \Log::error('AI Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.'
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Job::class);
        
        if ($request->ajax()) {
            \Log::info('AJAX request detected for jobs index');
            $data = Job::with(['department', 'role', 'creator'])
                       ->select('recruitment_jobs.*');
                       
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('title', function ($row) {
                    return '<strong>' . $row->title . '</strong>';
                })
                ->addColumn('department_name', function ($row) {
                    return $row->department ? $row->department->name : 'N/A';
                })
                ->addColumn('location', function ($row) {
                    return $row->location ?? 'Not specified';
                })
                ->addColumn('type_badge', function ($row) {
                    $badges = [
                        'internal' => 'badge-primary',
                        'external' => 'badge-info', 
                        'internal_external' => 'badge-success'
                    ];
                    $class = $badges[$row->hiring_type] ?? 'badge-secondary';
                    $typeText = ucwords(str_replace('_', ' ', $row->hiring_type));
                    return '<span class="badge ' . $class . '">' . $typeText . '</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'active' => 'badge-success',
                        'closed' => 'badge-danger',
                        'on-hold' => 'badge-warning'
                    ];
                    $class = $badges[$row->status] ?? 'badge-secondary';
                    $statusText = ucfirst(str_replace('-', ' ', $row->status));
                    return '<span class="badge ' . $class . '">' . $statusText . '</span>';
                })
                ->addColumn('applications_count', function ($row) {
                    return $row->applications()->count();
                })
                ->addColumn('posted_date', function ($row) {
                    return $row->created_at->format('M d, Y');
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('recruitment.jobs.edit', $row->id);
                    $deleteUrl = route('recruitment.jobs.destroy', $row->id);
                    $viewUrl = route('recruitment.jobs.show', $row->id);
                    
                    $btn = '<div class="btn-group" role="group">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                        <a href="' . $editUrl . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <button type="button" class="btn btn-sm btn-danger delete-job" data-id="' . $row->id . '" title="Delete" onclick="confirmDelete(' . $row->id . ')"><i class="fas fa-trash"></i></button>
                    </div>';
                    return $btn;
                })
                ->rawColumns(['title', 'department_name', 'type_badge', 'status_badge', 'action'])
                ->make(true);
        }
        
        \Log::info('Non-AJAX request for jobs index');
        // Get departments for filtering (no status filter as departments table doesn't have status column)
        $departments = Department::orderBy('name')->get();
        $roles = Role::all();
        
        // Apply filters and pagination for regular page requests
        $jobs = Job::with(['department', 'role'])
            ->when(request('department_id'), function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when(request('type'), function ($query, $type) {
                return $query->where('job_type', $type);
            })
            ->when(request('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        \Log::info('Jobs loaded with filters', [
            'count' => $jobs->total(),
            'filters' => request()->only(['department_id', 'type', 'status'])
        ]);
        
        return view('recruitment::jobs.index', compact('departments', 'roles', 'jobs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        canPerform('Create Recruitment Jobs');
        
        // Get departments for job posting (no status filter as departments table doesn't have status column)
        $departments = Department::orderBy('name')->get();
        $roles = Role::all();
        
        \Log::info('Job create page loaded', [
            'departments_count' => $departments->count(),
            'roles_count' => $roles->count()
        ]);
        
        return view('recruitment::jobs.create', compact('departments', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobRequest $request)
    {
        canPerform('Create Recruitment Jobs');
        try {
            $data = $request->validated();
            
            // Convert requirements string to array by splitting on newlines
            if ($request->requirements) {
                $requirements = array_filter(array_map('trim', explode("\n", $request->requirements)));
                $data['requirements'] = $requirements;
            } else {
                $data['requirements'] = [];
            }
            
            // Handle skills JSON conversion
            if ($request->skills) {
                try {
                    $skills = json_decode($request->skills, true);
                    $data['skills'] = is_array($skills) ? $skills : [];
                } catch (\Exception $e) {
                    $data['skills'] = [];
                }
            } else {
                $data['skills'] = [];
            }
            
            // Handle boolean fields
            $data['remote_work'] = $request->input('remote_work', 0) ? 1 : 0;
            $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
            
            // Add created_by field
            $data['created_by'] = auth()->id();
            
            \Log::info('Creating job with data:', $data);
            
            $job = Job::create($data);
            
            \Log::info('Job created successfully:', ['job_id' => $job->id]);
            
            // Notify all employees about the new job posting
            $this->notifyEmployeesAboutNewJob($job);
            
            return redirect()->route('recruitment.jobs.index')
                ->with('success', 'Job created successfully!');
        } catch (Exception $e) {
            \Log::error('Job creation failed:', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Failed to create job: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        canPerform('View Recruitment Jobs');
        
        $job = Job::with(['department', 'role', 'creator', 'applications.user'])->findOrFail($id);
        
        return view('recruitment::jobs.show', compact('job'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        canPerform('Edit Recruitment Jobs');
        
        $job = Job::findOrFail($id);
        // Get departments for job editing (no status filter as departments table doesn't have status column)
        $departments = Department::orderBy('name')->get();
        $roles = Role::all();
        
        return view('recruitment::jobs.edit', compact('job', 'departments', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        canPerform('Edit Recruitment Jobs');
        
        $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
            'hiring_type' => 'nullable|in:internal,external,internal_external',
            'job_type' => 'required|in:full_time,part_time,contract,internship',
            'experience_level' => 'nullable|in:entry,mid,senior,executive',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'skills' => 'nullable|json',
            'benefits' => 'nullable|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'remote_work' => 'nullable|in:0,1',
            'positions_available' => 'nullable|integer|min:1',
            'application_deadline' => 'nullable|date|after:today',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|in:draft,active,paused,closed,on-hold'
        ]);
        
        try {
            $job = Job::findOrFail($id);
            
            // BUG-REC-001 Fix: Use only() to prevent mass assignment vulnerability - Author: Sanket
            $data = $request->only([
                'title',
                'department_id',
                'role_id',
                'hiring_type',
                'job_type',
                'experience_level',
                'location',
                'description',
                'requirements',
                'responsibilities',
                'skills',
                'benefits',
                'min_salary',
                'max_salary',
                'remote_work',
                'positions_available',
                'application_deadline',
                'is_featured',
                'status'
            ]);
            
            // Convert requirements string to array by splitting on newlines
            if ($request->requirements) {
                $requirements = array_filter(array_map('trim', explode("\n", $request->requirements)));
                $data['requirements'] = $requirements;
            } else {
                $data['requirements'] = [];
            }
            
            // Handle skills JSON conversion
            if ($request->skills) {
                try {
                    $skills = json_decode($request->skills, true);
                    $data['skills'] = is_array($skills) ? $skills : [];
                } catch (\Exception $e) {
                    $data['skills'] = [];
                }
            } else {
                $data['skills'] = [];
            }
            
            // Handle boolean fields
            $data['remote_work'] = $request->input('remote_work', 0) ? 1 : 0;
            $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
            
            // Set updated_by
            $data['updated_by'] = auth()->id();
            
            $oldDeadline = $job->application_deadline;
            
            $job->update($data);
            
            // Check for deadline extension
            if ($oldDeadline && $job->application_deadline && $job->application_deadline->gt($oldDeadline)) {
                // Notify all applicants about extension
                $applications = $job->applications;
                foreach ($applications as $application) {
                    if ($application->user) {
                        \Illuminate\Support\Facades\Notification::send($application->user, new \App\Notifications\Recruitment\JobDeadlineExtendedNotification($job));
                    } elseif ($application->candidate_email) {
                         $externalCandidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name ?? 'Candidate');
                         \Illuminate\Support\Facades\Notification::send($externalCandidate, new \App\Notifications\Recruitment\JobDeadlineExtendedNotification($job));
                    }
                }
            }
            
            return redirect()->route('recruitment.jobs.index')
                ->with('success', 'Job updated successfully!');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Failed to update job: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        canPerform('Delete Recruitment Jobs');
        
        $response = getErrorResponse();
        
        try {
            \Log::info('Attempting to delete job', ['job_id' => $id, 'user_id' => auth()->id()]);
            
            $job = Job::findOrFail($id);
            $jobTitle = $job->title;
            
            // BUG-REC-003 Fix: Add transaction and cascade delete protection - Author: Sanket
            \DB::beginTransaction();
            
            // BUG-REC-010 Fix: Check for related data - Author: Sanket
            $applicationsCount = $job->applications()->count();
            $activeApplicationsCount = $job->applications()
                ->whereNotIn('stage', ['rejected', 'withdrawn', 'hired'])
                ->count();
            
            if ($activeApplicationsCount > 0) {
                \DB::rollBack();
                \Log::warning('Cannot delete job with active applications', [
                    'job_id' => $id,
                    'active_applications' => $activeApplicationsCount
                ]);
                $response['message'] = "Cannot delete job: {$activeApplicationsCount} active application(s) exist. Please close or reject them first.";
                return response()->json($response, 400);
            }
            
            $job->delete();
            
            \DB::commit();
            
            // Notify all applicants after successful deletion (Decoupled - Sanket)
            try {
                if ($applications->count() > 0) {
                    foreach ($applications as $application) {
                        if ($application->user) {
                            \Illuminate\Support\Facades\Notification::send($application->user, new \App\Notifications\Recruitment\JobDeletedNotification($jobTitle));
                        } elseif ($application->candidate_email) {
                             $externalCandidate = new \App\Models\Recruitment\ExternalCandidate($application->candidate_email, $application->candidate_name ?? 'Candidate');
                             \Illuminate\Support\Facades\Notification::send($externalCandidate, new \App\Notifications\Recruitment\JobDeletedNotification($jobTitle));
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log but don't fail the deletion
                \Log::error('Failed to send job deletion notifications', ['job_id' => $id, 'error' => $e->getMessage()]);
            }
            
            \Log::info('Job deleted successfully', ['job_id' => $id, 'applications_count' => $applicationsCount]);
            
            $response = getSuccessResponse('Job position deleted successfully. Applicants have been notified.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \DB::rollBack();
            \Log::error('Job not found for deletion', ['job_id' => $id]);
            $response['message'] = 'Job not found.';
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Job deletion failed', ['job_id' => $id, 'error' => $e->getMessage()]);
            $response['message'] = 'Failed to delete job: ' . $e->getMessage();
        }
        
        return response()->json($response);
    }
    
    /**
     * Notify all employees about new job posting
     */
    private function notifyEmployeesAboutNewJob(Job $job)
    {
        try {
            // Get all active employees except the job creator
            $employees = User::where('status', 'active')
                ->where('id', '!=', auth()->id())
                ->get();
            
            // Send notification to all employees
            Notification::send($employees, new NewJobPostedNotification($job));
            
            \Log::info('New job notifications sent:', [
                'job_id' => $job->id,
                'employee_count' => $employees->count()
            ]);
        } catch (Exception $e) {
            \Log::error('Failed to send job posting notifications:', [
                'job_id' => $job->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
