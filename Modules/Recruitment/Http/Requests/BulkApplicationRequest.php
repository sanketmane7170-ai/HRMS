<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['update_stage', 'assign_interviewer', 'bulk_reject', 'export_data', 'send_emails'])],
            'application_ids' => ['required', 'array', 'min:1', 'max:100'],
            'application_ids.*' => ['integer', 'exists:recruitment_applications,id'],
            
            // Rules for update_stage action
            'new_stage' => ['required_if:action,update_stage', Rule::in(['applied', 'screening', 'interview', 'final_review', 'offer', 'hired', 'rejected'])],
            'stage_notes' => ['nullable', 'string', 'max:1000'],
            
            // Rules for assign_interviewer action
            'interviewer_id' => ['required_if:action,assign_interviewer', 'exists:users,id'],
            'interview_date' => ['required_if:action,assign_interviewer', 'date', 'after:now'],
            'interview_type' => ['required_if:action,assign_interviewer', Rule::in(['phone', 'video', 'in-person', 'panel'])],
            
            // Rules for bulk_reject action
            'rejection_reason' => ['required_if:action,bulk_reject', 'string', 'max:500'],
            'send_rejection_email' => ['boolean'],
            
            // Rules for send_emails action
            'email_template' => ['required_if:action,send_emails', Rule::in(['interview_invitation', 'application_received', 'status_update', 'custom'])],
            'email_subject' => ['required_if:email_template,custom', 'string', 'max:255'],
            'email_body' => ['required_if:email_template,custom', 'string', 'max:2000'],
            
            // Rules for export_data action
            'export_format' => ['required_if:action,export_data', Rule::in(['csv', 'excel', 'pdf'])],
            'export_fields' => ['required_if:action,export_data', 'array', 'min:1'],
            'export_fields.*' => ['string', Rule::in(['name', 'email', 'phone', 'stage', 'score', 'applied_date', 'job_title', 'department'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Please select an action to perform.',
            'action.in' => 'Invalid bulk action selected.',
            'application_ids.required' => 'Please select at least one application.',
            'application_ids.min' => 'Please select at least one application.',
            'application_ids.max' => 'Cannot process more than 100 applications at once.',
            'application_ids.*.exists' => 'One or more selected applications do not exist.',
            'new_stage.required_if' => 'New stage is required for stage update action.',
            'interviewer_id.required_if' => 'Interviewer selection is required.',
            'interview_date.required_if' => 'Interview date is required.',
            'interview_date.after' => 'Interview date must be in the future.',
            'rejection_reason.required_if' => 'Rejection reason is required for bulk rejection.',
            'email_template.required_if' => 'Email template is required.',
            'email_subject.required_if' => 'Email subject is required for custom emails.',
            'email_body.required_if' => 'Email body is required for custom emails.',
            'export_format.required_if' => 'Export format is required.',
            'export_fields.required_if' => 'Please select at least one field to export.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Check if user has permission for bulk operations
        if ($user->can('bulk_manage_applications') || $user->hasRole(['HR Manager', 'Admin'])) {
            return true;
        }

        // Allow hiring managers for their department applications
        if ($user->hasRole('Hiring Manager')) {
            $applicationIds = $this->input('application_ids', []);
            
            // Check if all applications belong to jobs in user's department
            $applications = \Modules\Recruitment\Entities\Application::whereIn('id', $applicationIds)
                ->with('job')
                ->get();
                
            foreach ($applications as $application) {
                if (!$application->job || $application->job->department_id !== $user->department_id) {
                    return false;
                }
            }
            
            return true;
        }

        return false;
    }

    /**
     * Get validated data with action-specific processing.
     */
    public function getProcessedData(): array
    {
        $validated = $this->validated();
        
        // Add metadata for tracking
        $validated['processed_by'] = $this->user()->id;
        $validated['processed_at'] = now();
        $validated['total_applications'] = count($validated['application_ids']);
        
        return $validated;
    }
}