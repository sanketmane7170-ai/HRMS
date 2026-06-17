<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class JobRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $jobId = $this->route('job') ? $this->route('job')->id : null;
        
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['required', 'string', 'min:100'],
            'requirements' => ['required', 'string', 'min:50'],
            'responsibilities' => ['required', 'string', 'min:50'],
            'employment_type' => ['required', Rule::in(['full-time', 'part-time', 'contract', 'internship', 'temporary'])],
            'experience_level' => ['required', Rule::in(['entry', 'junior', 'mid', 'senior', 'lead', 'executive'])],
            'min_experience_years' => ['required', 'integer', 'min:0', 'max:30'],
            'max_experience_years' => ['nullable', 'integer', 'min:0', 'max:40', 'gte:min_experience_years'],
            'min_salary' => ['nullable', 'numeric', 'min:0'],
            'max_salary' => ['nullable', 'numeric', 'min:0', 'gte:min_salary'],
            'salary_currency' => ['required_with:min_salary,max_salary', 'string', 'size:3'],
            'salary_type' => ['required_with:min_salary,max_salary', Rule::in(['annual', 'monthly', 'hourly'])],
            'location' => ['required', 'string', 'max:255'],
            'is_remote' => ['boolean'],
            'remote_type' => ['nullable', Rule::in(['fully_remote', 'hybrid', 'remote_friendly']), 'required_if:is_remote,true'],
            'required_skills' => ['required', 'array', 'min:1'],
            'required_skills.*' => ['string', 'max:100'],
            'preferred_skills' => ['nullable', 'array'],
            'preferred_skills.*' => ['string', 'max:100'],
            'education_requirements' => ['nullable', Rule::in(['high_school', 'associate', 'bachelor', 'master', 'doctorate', 'any'])],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'max:255'],
            'application_deadline' => [
                'required',
                'date',
                'after:today',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    $maxDate = Carbon::now()->addYear();
                    if ($date->isAfter($maxDate)) {
                        $fail('Application deadline cannot be more than 1 year in the future.');
                    }
                }
            ],
            'positions_available' => ['required', 'integer', 'min:1', 'max:100'],
            'hiring_manager_id' => ['required', 'exists:users,id'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'paused', 'closed', 'cancelled'])],
            'is_urgent' => ['boolean'],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'external_posting_sites' => ['nullable', 'array'],
            'external_posting_sites.*' => ['string', 'max:255'],
            'requires_visa_sponsorship' => ['boolean'],
            'travel_required' => ['boolean'],
            'travel_percentage' => ['nullable', 'integer', 'min:0', 'max:100', 'required_if:travel_required,true'],
            'company_size_preference' => ['nullable', Rule::in(['startup', 'small', 'medium', 'large', 'enterprise'])],
        ];

        // Additional rules for updates
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['closure_reason'] = ['nullable', 'string', 'max:500', 'required_if:status,closed,cancelled'];
            $rules['applications_count'] = ['sometimes', 'integer', 'min:0'];
            $rules['hired_count'] = ['sometimes', 'integer', 'min:0'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Job title is required.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'Selected department does not exist.',
            'description.required' => 'Job description is required.',
            'description.min' => 'Job description must be at least 100 characters.',
            'requirements.required' => 'Job requirements are required.',
            'requirements.min' => 'Job requirements must be at least 50 characters.',
            'responsibilities.required' => 'Job responsibilities are required.',
            'responsibilities.min' => 'Job responsibilities must be at least 50 characters.',
            'employment_type.required' => 'Please select employment type.',
            'employment_type.in' => 'Invalid employment type selected.',
            'experience_level.required' => 'Please select experience level.',
            'experience_level.in' => 'Invalid experience level selected.',
            'max_experience_years.gte' => 'Maximum experience must be greater than or equal to minimum experience.',
            'max_salary.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'salary_currency.required_with' => 'Currency is required when salary is specified.',
            'salary_type.required_with' => 'Salary type is required when salary is specified.',
            'remote_type.required_if' => 'Remote work type is required for remote positions.',
            'required_skills.required' => 'At least one required skill must be specified.',
            'required_skills.min' => 'At least one required skill must be specified.',
            'application_deadline.required' => 'Application deadline is required.',
            'application_deadline.after' => 'Application deadline must be in the future.',
            'positions_available.required' => 'Number of positions is required.',
            'positions_available.min' => 'At least 1 position must be available.',
            'positions_available.max' => 'Cannot hire more than 100 positions at once.',
            'hiring_manager_id.required' => 'Please assign a hiring manager.',
            'hiring_manager_id.exists' => 'Selected hiring manager does not exist.',
            'travel_percentage.required_if' => 'Travel percentage is required when travel is required.',
            'closure_reason.required_if' => 'Closure reason is required when closing or cancelling a job.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Check if user has permission to manage jobs
        if ($user->can('manage_jobs') || $user->hasRole(['HR Manager', 'Admin'])) {
            return true;
        }

        // Allow hiring managers to create/edit jobs for their department
        if ($user->hasRole('Hiring Manager')) {
            $departmentId = $this->input('department_id') ?? $this->route('job')?->department_id;
            if ($departmentId && $departmentId === $user->department_id) {
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
        // Normalize skills arrays
        if ($this->has('required_skills') && is_string($this->required_skills)) {
            $this->merge([
                'required_skills' => array_filter(array_map('trim', explode(',', $this->required_skills)))
            ]);
        }

        if ($this->has('preferred_skills') && is_string($this->preferred_skills)) {
            $this->merge([
                'preferred_skills' => array_filter(array_map('trim', explode(',', $this->preferred_skills)))
            ]);
        }

        // Normalize benefits array
        if ($this->has('benefits') && is_string($this->benefits)) {
            $this->merge([
                'benefits' => array_filter(array_map('trim', explode(',', $this->benefits)))
            ]);
        }

        // Set priority based on urgency
        if ($this->has('is_urgent') && $this->is_urgent && !$this->has('priority')) {
            $this->merge(['priority' => 'urgent']);
        }
    }

    /**
     * Get validated data with computed fields.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        // Set default status for new jobs
        if (!isset($validated['status'])) {
            $validated['status'] = 'draft';
        }
        
        // Generate job reference code
        if (!$this->route('job')) {
            $validated['job_code'] = 'JOB-' . date('Y') . '-' . strtoupper(substr($validated['title'], 0, 3)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        return $validated;
    }
}
