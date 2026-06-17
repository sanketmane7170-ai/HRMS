<?php

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public application submission or authenticated users
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_id' => 'required|exists:recruitment_jobs,id',
            'user_id' => 'nullable|exists:users,id',
            'candidate_name' => 'required_without:user_id|string|max:255',
            'candidate_email' => 'required_without:user_id|email|max:255|unique:recruitment_applications,candidate_email,NULL,id,job_id,' . $this->job_id,
            'candidate_phone' => 'nullable|string|max:20',
            'linkedin_url' => 'nullable|url|max:255',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'cover_letter' => 'nullable|string|max:2000',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability_date' => 'nullable|date_format:d/m/Y|after_or_equal:today',
            'notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'job_id.required' => 'Job selection is required.',
            'job_id.exists' => 'Selected job does not exist.',
            'candidate_name.required_without' => 'Candidate name is required.',
            'candidate_email.required_without' => 'Email address is required.',
            'candidate_email.email' => 'Please provide a valid email address.',
            'candidate_email.unique' => 'You have already applied for this position.',
            'linkedin_url.url' => 'Please provide a valid LinkedIn URL.',
            'resume.required' => 'Resume file is required.',
            'resume.mimes' => 'Resume must be a PDF, DOC, or DOCX file.',
            'resume.max' => 'Resume file size must not exceed 10MB.',
            'cover_letter.max' => 'Cover letter must not exceed 2000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('linkedin_url') && !empty($this->linkedin_url)) {
            $this->merge([
                'linkedin_url' => filter_var($this->linkedin_url, FILTER_SANITIZE_URL)
            ]);
        }
    }
}
