<?php

namespace Modules\Recruitment\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Http\Requests\ApiJobRequest;
use Modules\Recruitment\Http\Requests\ApiApplicationRequest;
use App\Services\Recruitment\FileUploadService;
use App\Notifications\Recruitment\ApplicationReceivedNotification;
use App\Models\Recruitment\ExternalCandidate;

class RecruitmentApiController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Get all active jobs.
     */
    public function getJobs(Request $request): JsonResponse
    {
        try {
            $query = Job::with(['department:id,name'])
                ->where('status', 'active')
                ->where('application_deadline', '>', now());

            // Apply filters
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->filled('employment_type')) {
                $query->where('employment_type', $request->employment_type);
            }

            if ($request->filled('experience_level')) {
                $query->where('experience_level', $request->experience_level);
            }

            if ($request->filled('is_remote')) {
                $query->where('is_remote', filter_var($request->is_remote, FILTER_VALIDATE_BOOLEAN));
            }

            $perPage = min($request->get('per_page', 20), 100); // Max 100 per page
            $jobs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $jobs->items(),
                'meta' => [
                    'current_page' => $jobs->currentPage(),
                    'last_page' => $jobs->lastPage(),
                    'per_page' => $jobs->perPage(),
                    'total' => $jobs->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs', $e->getMessage());
        }
    }

    /**
     * Get a specific job by ID.
     */
    public function getJob(int $jobId): JsonResponse
    {
        try {
            $job = Job::with(['department:id,name'])
                ->where('id', $jobId)
                ->where('status', 'active')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $job
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job', $e->getMessage());
        }
    }

    /**
     * Create a new job posting.
     */
    public function createJob(ApiJobRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $validatedData['created_by'] = $request->input('api_client_id');
            $validatedData['status'] = 'active';

            $job = Job::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job created successfully',
                'data' => $job->load('department:id,name')
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to create job', $e->getMessage());
        }
    }

    /**
     * Update an existing job.
     */
    public function updateJob(ApiJobRequest $request, int $jobId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $job = Job::findOrFail($jobId);
            $validatedData = $request->validated();

            $job->update($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job updated successfully',
                'data' => $job->load('department:id,name')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to update job', $e->getMessage());
        }
    }

    /**
     * Get applications for a job.
     */
    public function getJobApplications(Request $request, int $jobId): JsonResponse
    {
        try {
            // Verify job exists
            Job::findOrFail($jobId);

            $query = Application::with(['user:id,name,email'])
                ->where('job_id', $jobId);

            // Apply filters
            if ($request->filled('stage')) {
                $query->where('stage', $request->stage);
            }

            if ($request->filled('date_from')) {
                $query->where('applied_on', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('applied_on', '<=', $request->date_to);
            }

            $perPage = min($request->get('per_page', 20), 100);
            $applications = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $applications->items(),
                'meta' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found'
            ], 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch applications', $e->getMessage());
        }
    }

    /**
     * Submit a job application.
     */
    public function submitApplication(ApiApplicationRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            
            // Handle resume file upload
            if ($request->hasFile('resume_file')) {
                $resumeUrl = $this->fileUploadService->uploadResume(
                    $request->file('resume_file'),
                    $validatedData['candidate_name']
                );
                $validatedData['resume_url'] = $resumeUrl;
            }

            // Set default stage
            $validatedData['stage'] = 'applied';
            $validatedData['applied_on'] = now();

            $application = Application::create($validatedData);

            // Send confirmation notification
            try {
                $candidate = new ExternalCandidate(
                    $validatedData['candidate_email'],
                    $validatedData['candidate_name']
                );
                $candidate->notify(new ApplicationReceivedNotification($application));
            } catch (\Exception $notificationError) {
                // Don't fail the entire request if notification fails
                \Log::warning('Failed to send application confirmation', [
                    'application_id' => $application->id,
                    'error' => $notificationError->getMessage()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => [
                    'application_id' => $application->id,
                    'reference_number' => 'APP-' . str_pad((string)$application->id, 6, '0', STR_PAD_LEFT),
                    'status' => $application->stage,
                    'submitted_at' => $application->applied_on->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to submit application', $e->getMessage());
        }
    }

    /**
     * Get application status.
     */
    public function getApplicationStatus(int $applicationId): JsonResponse
    {
        try {
            $application = Application::with(['job:id,title', 'latestScore'])
                ->findOrFail($applicationId);

            $responseData = [
                'application_id' => $application->id,
                'reference_number' => 'APP-' . str_pad((string)$application->id, 6, '0', STR_PAD_LEFT),
                'job_title' => $application->job->title,
                'candidate_name' => $application->candidate_name ?? $application->user->name,
                'stage' => $application->stage,
                'stage_description' => $application->formatted_stage,
                'applied_date' => $application->applied_on->toISOString(),
                'last_updated' => $application->updated_at->toISOString(),
            ];

            // Add score if available
            if ($application->latestScore) {
                $responseData['score'] = [
                    'overall_score' => $application->latestScore->overall_score,
                    'recommendation' => $application->latestScore->recommendation_text,
                    'scored_at' => $application->latestScore->scored_at->toISOString(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch application status', $e->getMessage());
        }
    }

    /**
     * Update application stage.
     */
    public function updateApplicationStage(Request $request, int $applicationId): JsonResponse
    {
        try {
            $request->validate([
                'stage' => 'required|string|in:applied,screening,interview,final_review,offer,hired,rejected',
                'notes' => 'nullable|string|max:1000'
            ]);

            $application = Application::findOrFail($applicationId);
            
            $oldStage = $application->stage;
            $application->update([
                'stage' => $request->stage
            ]);

            // Add log entry
            if ($request->filled('notes')) {
                $application->logs()->create([
                    'previous_stage' => $oldStage,
                    'new_stage' => $request->stage,
                    'changed_by' => $request->input('api_client_id'),
                    'description' => "API stage update: {$request->notes}",
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Application stage updated successfully',
                'data' => [
                    'application_id' => $application->id,
                    'previous_stage' => $oldStage,
                    'stage' => $application->stage,
                    'updated_at' => $application->updated_at->toISOString()
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update application stage', $e->getMessage());
        }
    }

    /**
     * Get recruitment statistics.
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $dateFrom = $request->input('date_from', now()->subMonths(3)->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));

            $stats = [
                'jobs' => [
                    'total_active' => Job::where('status', 'active')->count(),
                    'total_posted' => Job::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                    'by_department' => Job::join('departments', 'recruitment_jobs.department_id', '=', 'departments.id')
                        ->where('recruitment_jobs.status', 'active')
                        ->groupBy('departments.name')
                        ->selectRaw('departments.name, count(*) as count')
                        ->pluck('count', 'name'),
                ],
                'applications' => [
                    'total_received' => Application::whereBetween('applied_on', [$dateFrom, $dateTo])->count(),
                    'by_stage' => Application::whereBetween('applied_on', [$dateFrom, $dateTo])
                        ->groupBy('stage')
                        ->selectRaw('stage, count(*) as count')
                        ->pluck('count', 'stage'),
                    'conversion_rate' => $this->calculateConversionRate($dateFrom, $dateTo),
                ],
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                    'generated_at' => now()->toISOString()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch statistics', $e->getMessage());
        }
    }

    /**
     * Calculate application conversion rate.
     */
    private function calculateConversionRate(string $dateFrom, string $dateTo): array
    {
        $totalApplications = Application::whereBetween('applied_on', [$dateFrom, $dateTo])->count();
        
        if ($totalApplications === 0) {
            return ['rate' => 0, 'hired' => 0, 'total' => 0];
        }

        $hiredApplications = Application::whereBetween('applied_on', [$dateFrom, $dateTo])
            ->where('stage', 'hired')
            ->count();

        return [
            'rate' => round(($hiredApplications / $totalApplications) * 100, 2),
            'hired' => $hiredApplications,
            'total' => $totalApplications
        ];
    }

    /**
     * Return standardized error response.
     */
    private function errorResponse(string $message, ?string $details = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($details && app()->hasDebugModeEnabled()) {
            $response['details'] = $details;
        }

        return response()->json($response, 500);
    }
}