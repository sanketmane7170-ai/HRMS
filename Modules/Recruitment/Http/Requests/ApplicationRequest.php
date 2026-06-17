<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'job_id' => ['required', 'exists:recruitment_jobs,id'],
            'stage' => ['required', Rule::in(['applied', 'screening', 'interview', 'final_review', 'offer', 'hired', 'rejected'])],
            'score' => ['nullable', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'interviewer_feedback' => ['nullable', 'string', 'max:1000'],
            'hr_notes' => ['nullable', 'string', 'max:1000'],
            'rejection_reason' => ['nullable', 'string', 'max:500', 'required_if:stage,rejected'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'availability_date' => ['nullable', 'date', 'after_or_equal:today'],
            'source' => ['nullable', 'string', 'max:255'],
            'referrer_name' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:100'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'education_level' => ['nullable', Rule::in(['high_school', 'associate', 'bachelor', 'master', 'doctorate', 'other'])],
            'university' => ['nullable', 'string', 'max:255'],
            'degree' => ['nullable', 'string', 'max:255'],
            'graduation_year' => ['nullable', 'integer', 'min:1950', 'max:' . (date('Y') + 10)],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
            'portfolio_url' => ['nullable', 'url'],
            'github_url' => ['nullable', 'url'],
            'is_willing_to_relocate' => ['boolean'],
            'requires_visa_sponsorship' => ['boolean'],
        ];

        // Additional rules for status updates
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status_changed_by'] = ['sometimes', 'exists:users,id'];
            $rules['status_change_reason'] = ['nullable', 'string', 'max:500'];
            $rules['next_interview_date'] = ['nullable', 'date', 'after:now'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'job_id.required' => 'Job selection is required.',
            'job_id.exists' => 'Selected job does not exist.',
            'stage.required' => 'Application stage is required.',
            'stage.in' => 'Invalid application stage selected.',
            'score.min' => 'Score must be at least 1.',
            'score.max' => 'Score cannot exceed 100.',
            'rejection_reason.required_if' => 'Rejection reason is required when rejecting an application.',
            'expected_salary.min' => 'Expected salary must be greater than 0.',
            'availability_date.after_or_equal' => 'Availability date cannot be in the past.',
            'years_of_experience.min' => 'Years of experience cannot be negative.',
            'years_of_experience.max' => 'Years of experience seems unrealistic.',
            'graduation_year.min' => 'Graduation year seems too old.',
            'graduation_year.max' => 'Graduation year cannot be more than 10 years in the future.',
            'portfolio_url.url' => 'Please provide a valid portfolio URL.',
            'github_url.url' => 'Please provide a valid GitHub URL.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Check if user has permission to manage applications
        if ($user->can('manage_applications') || $user->hasRole(['HR Manager', 'Recruiter', 'Admin'])) {
            return true;
        }

        // Allow hiring managers to manage applications for their department jobs
        if ($user->hasRole('Hiring Manager')) {
            $jobId = $this->input('job_id') ?? $this->route('application')?->job_id;
            if ($jobId) {
                $job = \Modules\Recruitment\Entities\Job::find($jobId);
                if ($job && $job->department_id === $user->department_id) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize skills array
        if ($this->has('skills') && is_string($this->skills)) {
            $this->merge([
                'skills' => array_filter(array_map('trim', explode(',', $this->skills)))
            ]);
        }

        // Normalize certifications array
        if ($this->has('certifications') && is_string($this->certifications)) {
            $this->merge([
                'certifications' => array_filter(array_map('trim', explode(',', $this->certifications)))
            ]);
        }
    }
}
