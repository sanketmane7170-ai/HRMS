<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiJobRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'description' => ['required', 'string', 'min:100', 'max:5000'],
            'requirements' => ['required', 'string', 'min:50', 'max:3000'],
            'responsibilities' => ['required', 'string', 'min:50', 'max:3000'],
            'employment_type' => ['required', Rule::in(['full-time', 'part-time', 'contract', 'internship'])],
            'experience_level' => ['required', Rule::in(['entry', 'junior', 'mid', 'senior', 'lead'])],
            'min_experience_years' => ['required', 'integer', 'min:0', 'max:30'],
            'max_experience_years' => ['nullable', 'integer', 'min:0', 'max:40', 'gte:min_experience_years'],
            'location' => ['required', 'string', 'max:255'],
            'is_remote' => ['boolean'],
            'required_skills' => ['required', 'array', 'min:1'],
            'required_skills.*' => ['string', 'max:100'],
            'preferred_skills' => ['nullable', 'array'],
            'preferred_skills.*' => ['string', 'max:100'],
            'application_deadline' => ['required', 'date', 'after:today'],
            'positions_available' => ['required', 'integer', 'min:1', 'max:50'],
            'hiring_manager_id' => ['required', 'exists:users,id'],
            'min_salary' => ['nullable', 'numeric', 'min:0'],
            'max_salary' => ['nullable', 'numeric', 'min:0', 'gte:min_salary'],
            'salary_currency' => ['required_with:min_salary,max_salary', 'string', 'size:3'],
        ];

        // Update-specific rules
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = ['sometimes', Rule::in(['active', 'paused', 'closed'])];
        }

        return $rules;
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Job title is required.',
            'department_id.exists' => 'Invalid department selected.',
            'description.min' => 'Job description must be at least 100 characters.',
            'description.max' => 'Job description cannot exceed 5000 characters.',
            'employment_type.in' => 'Invalid employment type.',
            'experience_level.in' => 'Invalid experience level.',
            'required_skills.required' => 'At least one required skill must be specified.',
            'application_deadline.after' => 'Application deadline must be in the future.',
            'positions_available.max' => 'Cannot hire more than 50 positions via API.',
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
            
        if (!$apiClient || !$apiClient->hasPermission('manage_jobs')) {
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
}