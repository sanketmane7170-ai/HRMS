<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class InterviewRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'application_id' => ['required', 'exists:recruitment_applications,id'],
            'interviewer_id' => ['required', 'exists:users,id'],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    if ($date->isWeekend()) {
                        $fail('Interview cannot be scheduled on weekends.');
                    }
                    if ($date->hour < 9 || $date->hour > 17) {
                        $fail('Interview must be scheduled between 9 AM and 5 PM.');
                    }
                }
            ],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:180'],
            'interview_type' => ['required', Rule::in(['phone', 'video', 'in-person', 'panel'])],
            'location' => ['nullable', 'string', 'max:255', 'required_if:interview_type,in-person'],
            'meeting_link' => ['nullable', 'url', 'required_if:interview_type,video'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'send_reminder' => ['boolean'],
            'reminder_minutes' => ['nullable', 'integer', 'min:5', 'max:1440', 'required_if:send_reminder,true'],
            'is_final_round' => ['boolean'],
        ];

        // Additional rules for updating existing interviews
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = ['sometimes', Rule::in(['scheduled', 'completed', 'cancelled', 'rescheduled'])];
            $rules['feedback'] = ['nullable', 'string', 'max:2000'];
            $rules['score'] = ['nullable', 'integer', 'min:1', 'max:10'];
            $rules['recommendation'] = ['nullable', Rule::in(['hire', 'reject', 'next_round', 'hold'])];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'application_id.required' => 'Please select a candidate application.',
            'application_id.exists' => 'Selected application does not exist.',
            'interviewer_id.required' => 'Please select an interviewer.',
            'interviewer_id.exists' => 'Selected interviewer does not exist.',
            'scheduled_at.required' => 'Interview date and time is required.',
            'scheduled_at.after' => 'Interview must be scheduled for a future date.',
            'duration_minutes.min' => 'Interview duration must be at least 15 minutes.',
            'duration_minutes.max' => 'Interview duration cannot exceed 3 hours.',
            'interview_type.in' => 'Invalid interview type selected.',
            'location.required_if' => 'Location is required for in-person interviews.',
            'meeting_link.required_if' => 'Meeting link is required for video interviews.',
            'meeting_link.url' => 'Please provide a valid meeting link.',
            'reminder_minutes.required_if' => 'Reminder time is required when reminder is enabled.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Check if user has permission to manage interviews
        if ($user->can('manage_interviews') || $user->hasRole(['HR Manager', 'Recruiter', 'Admin'])) {
            return true;
        }

        // Allow hiring managers to schedule interviews for their department
        if ($user->hasRole('Hiring Manager')) {
            $application = \Modules\Recruitment\Entities\Application::find($this->application_id);
            if ($application && $application->job && $application->job->department_id === $user->department_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('scheduled_at')) {
            $this->merge([
                'scheduled_at' => Carbon::parse($this->scheduled_at)->format('Y-m-d H:i:s')
            ]);
        }
    }
}
