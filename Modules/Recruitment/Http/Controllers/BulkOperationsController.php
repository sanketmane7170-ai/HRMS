<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Job;
use Modules\Recruitment\Http\Requests\BulkApplicationRequest;
use App\Notifications\Recruitment\InterviewScheduledNotification;
use App\Notifications\Recruitment\ApplicationRejectedNotification;
use App\Notifications\Recruitment\ApplicationStatusChangedNotification;
use App\Notifications\Recruitment\CustomRecruitmentNotification;
use App\Services\Recruitment\ExportService;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApplicationsExport;

class BulkOperationsController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
        view()->share('activeLink', 'recruitment-bulk');
    }

    /**
     * Display bulk operations interface.
     */
    public function index(Request $request): Renderable
    {
        // Enforce permission (Sanket - REC-SEC-024)
        if (!auth()->user()->hasRole(['admin', 'hr']) && !auth()->user()->can('Manage Applications')) {
            abort(403, 'Unauthorized access to bulk operations');
        }
        
        $query = Application::with(['job', 'user', 'latestScore']);
        
        // Apply filters
        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }
        
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        
        if ($request->filled('date_from')) {
            $query->where('applied_on', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('applied_on', '<=', $request->date_to);
        }

        $applications = $query->paginate(50);
        $jobs = Job::active()->get();
        $stages = Application::getStages();
        
        return view('recruitment::bulk.index', compact(
            'applications', 
            'jobs', 
            'stages'
        ));
    }

    /**
     * Process bulk operations on applications.
     */
    public function process(BulkApplicationRequest $request): JsonResponse
    {
        // Enforce permission (Sanket - REC-SEC-024)
        if (!auth()->user()->hasRole(['admin', 'hr']) && !auth()->user()->can('Manage Applications')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        try {
            $data = $request->getProcessedData();
            $action = $data['action'];
            $applicationIds = $data['application_ids'];
            
            $result = match($action) {
                'update_stage' => $this->updateStage($data),
                'assign_interviewer' => $this->assignInterviewer($data),
                'bulk_reject' => $this->bulkReject($data),
                'send_emails' => $this->sendEmails($data),
                'export_data' => $this->exportData($data),
                default => throw new \InvalidArgumentException('Invalid bulk action')
            };

            // Log bulk operation
            $this->logBulkOperation($data, $result);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stage for multiple applications.
     */
    private function updateStage(array $data): array
    {
        $updated = 0;
        $failed = [];

        $applications = Application::whereIn('id', $data['application_ids'])->get();
        
        foreach ($applications as $application) {
            DB::beginTransaction();
            try {
                $oldStage = $application->stage;
                $application->update([
                    'stage' => $data['new_stage']
                ]);
                
                // Add notes if provided
                if (!empty($data['stage_notes'])) {
                    $application->logs()->create([
                        'previous_stage' => $oldStage,
                        'new_stage' => $data['new_stage'],
                        'changed_by' => $data['processed_by'],
                        'description' => "Bulk stage update: {$data['stage_notes']}",
                    ]);
                }
                
                DB::commit();
                $updated++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed[] = [
                    'application_id' => $application->id,
                    'candidate_name' => $application->candidate_name ?? $application->user->name,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'message' => "Successfully updated {$updated} applications to stage: " . ucwords($data['new_stage']),
            'data' => [
                'updated' => $updated,
                'failed' => count($failed),
                'failures' => $failed
            ]
        ];
    }

    /**
     * Assign interviewer to multiple applications.
     */
    private function assignInterviewer(array $data): array
    {
        $scheduled = 0;
        $failed = [];
        
        $applications = Application::whereIn('id', $data['application_ids'])->get();
        $interviewer = \App\Models\User::findOrFail($data['interviewer_id']);
        
        foreach ($applications as $application) {
            DB::beginTransaction();
            try {
                // Create interview record
                $interview = $application->interviews()->create([
                    'interviewer_id' => $data['interviewer_id'],
                    'scheduled_at' => $data['interview_date'],
                    'type' => $data['interview_type'],
                    'status' => 'scheduled',
                    'notes' => 'Bulk scheduled interview',
                ]);
                
                // Update application stage to interview if not already (Sanket - REC-LOGIC-008)
                if ($application->stage !== 'interview' && !in_array($application->stage, ['hired', 'rejected'])) {
                    $application->update(['stage' => 'interview']);
                }
                
                DB::commit();

                // Send interview invitation (Decoupled - Sanket)
                try {
                    $candidate = $application->user ?: new \App\Models\Recruitment\ExternalCandidate(
                        $application->candidate_email, 
                        $application->candidate_name
                    );
                    
                    $candidate->notify(new InterviewScheduledNotification($interview));
                } catch (\Exception $e) {
                     // Log notification failure but don't fail the operation
                    \Log::error('Bulk interview schedule notification failed', [
                        'application_id' => $application->id, 
                        'error' => $e->getMessage()
                    ]);
                    // We could add this to a 'warnings' array if the frontend supported it, 
                    // but for now counting it as scheduled is safer than failing it.
                }
                
                $scheduled++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed[] = [
                    'application_id' => $application->id,
                    'candidate_name' => $application->candidate_name ?? $application->user->name,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'message' => "Successfully scheduled {$scheduled} interviews with {$interviewer->name}",
            'data' => [
                'scheduled' => $scheduled,
                'failed' => count($failed),
                'failures' => $failed
            ]
        ];
            

    }

    /**
     * Reject multiple applications.
     */
    private function bulkReject(array $data): array
    {
        $rejected = 0;
        $failed = [];
        
        $applications = Application::whereIn('id', $data['application_ids'])->get();
        
        foreach ($applications as $application) {
            DB::beginTransaction();
            try {
                // Prevent rejecting hired/rejected candidates (Sanket - REC-DATA-007)
                if (in_array($application->stage, ['hired', 'rejected'])) {
                    throw new \Exception("Cannot reject a candidate who is already " . $application->stage);
                }

                $application->update(['stage' => 'rejected']);
                
                // Add rejection log
                $application->logs()->create([
                    'previous_stage' => $application->getOriginal('stage'),
                    'new_stage' => 'rejected',
                    'changed_by' => $data['processed_by'],
                    'description' => "Bulk rejection: {$data['rejection_reason']}",
                ]);
                
                DB::commit();

                // Send rejection email if requested (Decoupled - Sanket)
                if ($data['send_rejection_email'] ?? false) {
                    try {
                        $candidate = $application->user ?: new \App\Models\Recruitment\ExternalCandidate(
                            $application->candidate_email, 
                            $application->candidate_name
                        );
                        
                        $candidate->notify(new ApplicationRejectedNotification(
                            $application, 
                            $data['rejection_reason']
                        ));
                    } catch (\Exception $e) {
                            \Log::error('Bulk rejection notification failed', [
                            'application_id' => $application->id, 
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                $rejected++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed[] = [
                    'application_id' => $application->id,
                    'candidate_name' => $application->candidate_name ?? $application->user->name,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'message' => "Successfully rejected {$rejected} applications",
            'data' => [
                'rejected' => $rejected,
                'failed' => count($failed),
                'failures' => $failed
            ]
        ];
    }

    /**
     * Send emails to multiple candidates.
     */
    private function sendEmails(array $data): array
    {
        $sent = 0;
        $failed = [];
        
        try {
            $applications = Application::whereIn('id', $data['application_ids'])->get();
            
            foreach ($applications as $application) {
                try {
                    $candidate = $application->user ?: new \App\Models\Recruitment\ExternalCandidate(
                        $application->candidate_email, 
                        $application->candidate_name
                    );
                    
                    $notification = $this->createEmailNotification($data, $application);
                    $candidate->notify($notification);
                    
                    $sent++;
                } catch (\Exception $e) {
                    $failed[] = [
                        'application_id' => $application->id,
                        'candidate_name' => $application->candidate_name ?? $application->user->name,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return [
                'message' => "Successfully sent {$sent} emails",
                'data' => [
                    'sent' => $sent,
                    'failed' => count($failed),
                    'failures' => $failed
                ]
            ];
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Export application data.
     */
    private function exportData(array $data): array
    {
        try {
            $applications = Application::with(['job', 'user', 'latestScore'])
                ->whereIn('id', $data['application_ids'])
                ->get();
            
            $exportData = $this->exportService->prepareApplicationData($applications, $data['export_fields']);
            
            $filename = $this->exportService->generateFilename($data['export_format']);
            
            switch ($data['export_format']) {
                case 'csv':
                    $filePath = $this->exportService->exportToCsv($exportData, $filename);
                    break;
                case 'excel':
                    $filePath = $this->exportService->exportToExcel($exportData, $filename);
                    break;
                case 'pdf':
                    $filePath = $this->exportService->exportToPdf($exportData, $filename);
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid export format');
            }
            
            return [
                'message' => 'Data exported successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => route('recruitment.bulk.download', ['file' => basename($filePath)]),
                    'total_records' => count($exportData)
                ]
            ];
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Download exported file.
     */
    public function download(Request $request, string $file): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filePath = storage_path("app/exports/{$file}");
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return response()->download($filePath)->deleteFileAfterSend();
    }

    /**
     * Get bulk operation statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        
        $stats = [
            'total_applications' => Application::whereBetween('applied_on', [$dateFrom, $dateTo])->count(),
            'stage_distribution' => Application::whereBetween('applied_on', [$dateFrom, $dateTo])
                ->groupBy('stage')
                ->selectRaw('stage, count(*) as count')
                ->pluck('count', 'stage'),
            'recent_bulk_operations' => DB::table('bulk_operation_logs')
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Create appropriate email notification based on template.
     */
    private function createEmailNotification(array $data, Application $application): object
    {
        // Fix for REC-CRASH-009 (Sanket)
        if ($data['email_template'] === 'interview_invitation' && !$application->latestInterview) {
            throw new \Exception("Candidate has no interview to invite to.");
        }

        return match($data['email_template']) {
            'interview_invitation' => new InterviewScheduledNotification($application->latestInterview),
            'application_received' => new \App\Notifications\Recruitment\ApplicationReceivedNotification($application),
            'status_update' => new ApplicationStatusChangedNotification($application, $application->stage, $application->stage),
            'custom' => new CustomRecruitmentNotification(
                $data['email_subject'], 
                $data['email_body']
            ),
            default => throw new \InvalidArgumentException('Invalid email template')
        };
    }

    /**
     * Log bulk operation for audit trail.
     */
    private function logBulkOperation(array $data, array $result): void
    {
        DB::table('bulk_operation_logs')->insert([
            'user_id' => $data['processed_by'],
            'action' => $data['action'],
            'application_count' => $data['total_applications'],
            'success_count' => $result['data']['updated'] ?? $result['data']['scheduled'] ?? $result['data']['rejected'] ?? $result['data']['sent'] ?? 0,
            'failure_count' => $result['data']['failed'] ?? 0,
            'details' => json_encode($data),
            'result' => json_encode($result),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}