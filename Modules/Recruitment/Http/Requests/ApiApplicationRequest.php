<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'job_id' => ['required', 'exists:recruitment_jobs,id'],
            'candidate_name' => ['required', 'string', 'max:255'],
            'candidate_email' => ['required', 'email', 'max:255'],
            'candidate_phone' => ['nullable', 'string', 'max:20'],
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB max
            'cover_letter' => ['nullable', 'string', 'max:2000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'availability_date' => ['nullable', 'date', 'after_or_equal:today'],
            'source' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:100'],
            'education_level' => ['nullable', Rule::in(['high_school', 'associate', 'bachelor', 'master', 'doctorate'])],
            'is_willing_to_relocate' => ['boolean'],
            'requires_visa_sponsorship' => ['boolean'],
            'additional_info' => ['nullable', 'string', 'max:1000'],
        ];

        // Update-specific rules
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['stage'] = ['sometimes', Rule::in(['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'])];
            $rules['notes'] = ['nullable', 'string', 'max:1000'];
            $rules['resume_file'] = ['sometimes', 'file', 'mimes:pdf,doc,docx', 'max:5120'];
        }

        return $rules;
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'job_id.required' => 'Job ID is required.',
            'job_id.exists' => 'Invalid job ID provided.',
            'candidate_name.required' => 'Candidate name is required.',
            'candidate_email.required' => 'Candidate email is required.',
            'candidate_email.email' => 'Please provide a valid email address.',
            'resume_file.required' => 'Resume file is required.',
            'resume_file.mimes' => 'Resume must be a PDF, DOC, or DOCX file.',
            'resume_file.max' => 'Resume file cannot exceed 5MB.',
            'linkedin_url.url' => 'Please provide a valid LinkedIn URL.',
            'portfolio_url.url' => 'Please provide a valid portfolio URL.',
            'years_of_experience.min' => 'Years of experience cannot be negative.',
            'years_of_experience.max' => 'Years of experience seems unrealistic.',
            'expected_salary.min' => 'Expected salary must be greater than 0.',
            'availability_date.after_or_equal' => 'Availability date cannot be in the past.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For API requests, check API key permissions
        $apiKey = $this->header('X-API-Key');
        
        if (!$apiKey) {
            return false;
        }

        // Validate API key and permissions
        $apiClient = \App\Models\ApiClient::where('api_key', hash('sha256', $apiKey))
            ->where('is_active', true)
            ->first();
            
        if (!$apiClient || !$apiClient->hasPermission('manage_applications')) {
            return false;
        }

        // Store API client info for later use
        $this->merge(['api_client_id' => $apiClient->id]);
        
        return true;
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('Invalid API credentials or insufficient permissions.');
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize skills array if provided as comma-separated string
        if ($this->has('skills') && is_string($this->skills)) {
            $this->merge([
                'skills' => array_filter(array_map('trim', explode(',', $this->skills)))
            ]);
        }

        // Convert boolean strings to actual booleans
        if ($this->has('is_willing_to_relocate')) {
            $this->merge([
                'is_willing_to_relocate' => filter_var($this->is_willing_to_relocate, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        if ($this->has('requires_visa_sponsorship')) {
            $this->merge([
                'requires_visa_sponsorship' => filter_var($this->requires_visa_sponsorship, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}