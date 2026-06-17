<?php

namespace Modules\Recruitment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Recruitment\CommunicationService;
use Modules\Recruitment\Entities\Application;
use Modules\Recruitment\Entities\Interview;

class CommunicationController extends Controller
{
    protected CommunicationService $communicationService;

    public function __construct(CommunicationService $communicationService)
    {
        $this->communicationService = $communicationService;
    }

    /**
     * Get email templates
     */
    public function getTemplates(): JsonResponse
    {
        $templates = $this->communicationService->getEmailTemplates();
        
        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Create or update email template
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'subject' => 'required|string|max:200',
            'content' => 'required|string',
            'description' => 'nullable|string|max:500'
        ]);

        $success = $this->communicationService->saveEmailTemplate(
            $request->input('type'),
            $request->only(['name', 'subject', 'content', 'description'])
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Template saved successfully' : 'Failed to save template'
        ]);
    }

    /**
     * Send custom email to candidate
     */
    public function sendEmail(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:recruitment_applications,id',
            'subject' => 'required|string|max:200',
            'content' => 'required|string',
            'template_type' => 'nullable|string|max:50'
        ]);

        $application = Application::findOrFail($request->input('application_id'));
        $this->authorize('manage_applications');

        $emailData = [
            'subject' => $request->input('subject'),
            'content' => $request->input('content'),
            'template_type' => $request->input('template_type', 'custom_message')
        ];

        $success = $this->communicationService->sendCustomEmail($application, $emailData);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Email sent successfully' : 'Failed to send email'
        ]);
    }

    /**
     * Send SMS to candidate
     */
    public function sendSMS(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:recruitment_applications,id',
            'message' => 'required|string|max:160'
        ]);

        $application = Application::findOrFail($request->input('application_id'));
        $this->authorize('manage_applications');

        $success = $this->communicationService->sendSMS(
            $application,
            $request->input('message')
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'SMS sent successfully' : 'Failed to send SMS'
        ]);
    }

    /**
     * Send bulk communication
     */
    public function sendBulkCommunication(Request $request): JsonResponse
    {
        $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'exists:recruitment_applications,id',
            'type' => 'required|in:email,sms',
            'subject' => 'required_if:type,email|string|max:200',
            'content' => 'required_if:type,email|string',
            'message' => 'required_if:type,sms|string|max:160',
            'template_type' => 'nullable|string|max:50'
        ]);

        $this->authorize('manage_applications');

        $messageData = [
            'type' => $request->input('type'),
            'subject' => $request->input('subject'),
            'content' => $request->input('content'),
            'message' => $request->input('message'),
            'template_type' => $request->input('template_type', 'custom_message')
        ];

        $results = $this->communicationService->sendBulkCommunication(
            $request->input('application_ids'),
            $messageData
        );

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => "Sent to {$results['sent']} candidates, {$results['failed']} failed"
        ]);
    }

    /**
     * Schedule follow-up sequence
     */
    public function scheduleFollowUp(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:recruitment_applications,id',
            'sequence_type' => 'required|in:post_application,post_interview,custom'
        ]);

        $application = Application::findOrFail($request->input('application_id'));
        $this->authorize('manage_applications');

        $success = $this->communicationService->scheduleFollowUpSequence(
            $application,
            $request->input('sequence_type')
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Follow-up sequence scheduled' : 'Failed to schedule follow-up'
        ]);
    }

    /**
     * Get communication history for application
     */
    public function getCommunicationHistory(Request $request, int $applicationId): JsonResponse
    {
        $application = Application::findOrFail($applicationId);
        $this->authorize('view_applications');

        $history = $this->communicationService->getCommunicationHistory($application);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Schedule video interview
     */
    public function scheduleVideoInterview(Request $request): JsonResponse
    {
        $request->validate([
            'interview_id' => 'required|exists:interviews,id',
            'platform' => 'required|in:zoom,teams',
            'duration' => 'nullable|integer|min:15|max:480',
            'agenda' => 'nullable|string|max:1000'
        ]);

        $interview = Interview::findOrFail($request->input('interview_id'));
        $this->authorize('schedule_interview', $interview->application);

        $meetingData = [
            'platform' => $request->input('platform'),
            'duration' => $request->input('duration', 60),
            'agenda' => $request->input('agenda', '')
        ];

        $result = $this->communicationService->scheduleVideoInterview($interview, $meetingData);

        return response()->json($result);
    }

    /**
     * Preview email template with sample data
     */
    public function previewTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'template_type' => 'required|string',
            'content' => 'required|string',
            'sample_application_id' => 'nullable|exists:recruitment_applications,id'
        ]);

        // Use sample application or create mock data
        if ($request->has('sample_application_id')) {
            $application = Application::findOrFail($request->input('sample_application_id'));
        } else {
            // Create mock application for preview
            $application = new Application([
                'candidate_name' => 'John Doe',
                'candidate_email' => 'john.doe@example.com',
                'applied_on' => now(),
                'stage' => 'applied'
            ]);
            $application->setRelation('job', (object)[
                'title' => 'Software Developer',
                'recruiter' => (object)['name' => 'HR Manager']
            ]);
        }

        $variables = [
            'candidate_name' => $application->candidate_name ?? 'John Doe',
            'job_title' => $application->job?->title ?? 'Sample Position',
            'company_name' => config('app.name'),
            'application_date' => ($application->applied_on ?? now())->format('M d, Y'),
            'stage' => $application->formatted_stage ?? 'Applied',
            'score' => $application->score ?? 'Not scored',
            'recruiter_name' => $application->job?->recruiter?->name ?? 'Recruitment Team'
        ];

        $processedContent = $request->input('content');
        foreach ($variables as $key => $value) {
            $processedContent = str_replace("{{$key}}", $value, $processedContent);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'processed_content' => $processedContent,
                'available_variables' => $variables
            ]
        ]);
    }
}