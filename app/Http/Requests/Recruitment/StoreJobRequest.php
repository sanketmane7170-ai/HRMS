<?php

namespace App\Http\Requests\Recruitment;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('Create Recruitment Jobs');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
            'hiring_type' => 'required|in:internal,external,internal_external',
            'job_type' => 'required|in:full_time,part_time,contract,internship',
            'experience_level' => 'nullable|in:entry,mid,senior,executive',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string|min:50',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'skills' => 'nullable|json',
            'benefits' => 'nullable|string',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'remote_work' => 'nullable|boolean',
            'positions_available' => 'nullable|integer|min:1|max:100',
            'application_deadline' => 'nullable|date_format:d/m/Y|after:today',
            'is_featured' => 'nullable|boolean',
            'status' => 'required|in:draft,active,paused,closed,on-hold'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Job title is required.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'Selected department does not exist.',
            'description.required' => 'Job description is required.',
            'description.min' => 'Job description must be at least 50 characters.',
            'max_salary.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
            'application_deadline.after' => 'Application deadline must be a future date.',
            'positions_available.max' => 'Maximum 100 positions can be available for a single job.'
        ];
    }
}
