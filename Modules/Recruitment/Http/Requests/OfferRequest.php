<?php

namespace Modules\Recruitment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OfferRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'application_id' => ['required', 'exists:recruitment_applications,id'],
            'position_title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'salary_amount' => ['required', 'numeric', 'min:0', 'max:10000000'],
            // Author: Sanket - Added AED, JPY, CHF currencies per user requirement
            'salary_currency' => ['required', 'string', 'size:3', Rule::in(['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'CHF', 'AED'])],
            'salary_type' => ['required', Rule::in(['annual', 'monthly', 'hourly'])],
            'employment_type' => ['required', Rule::in(['full-time', 'part-time', 'contract', 'internship'])],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    $maxDate = Carbon::now()->addMonths(6);
                    if ($date->isAfter($maxDate)) {
                        $fail('Start date cannot be more than 6 months in the future.');
                    }
                }
            ],
            'probation_period_months' => ['nullable', 'integer', 'min:1', 'max:12'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'max:255'],
            'additional_terms' => ['nullable', 'string', 'max:2000'],
            'offer_valid_until' => [
                'required',
                'date',
                'after:today',
                'before:' . Carbon::now()->addMonths(1)->format('Y-m-d')
            ],
            'is_negotiable' => ['boolean'],
            'max_negotiation_amount' => ['nullable', 'numeric', 'min:0', 'required_if:is_negotiable,true'],
            'reporting_manager_id' => ['nullable', 'exists:users,id'],
            'work_location' => ['required', 'string', 'max:255'],
            'is_remote' => ['boolean'],
            'travel_required' => ['boolean'],
            'travel_percentage' => ['nullable', 'integer', 'min:0', 'max:100', 'required_if:travel_required,true'],
        ];

        // Additional rules for updating existing offers
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = ['sometimes', Rule::in(['draft', 'sent', 'accepted', 'rejected', 'withdrawn', 'expired'])];
            $rules['candidate_response'] = ['nullable', 'string', 'max:1000'];
            $rules['negotiation_notes'] = ['nullable', 'string', 'max:1000'];
            $rules['final_salary_amount'] = ['nullable', 'numeric', 'min:0'];
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
            'position_title.required' => 'Position title is required.',
            'salary_amount.required' => 'Salary amount is required.',
            'salary_amount.min' => 'Salary amount must be greater than 0.',
            'salary_amount.max' => 'Salary amount cannot exceed 10,000,000.',
            'salary_currency.required' => 'Please select salary currency.',
            'salary_currency.in' => 'Invalid currency selected.',
            'employment_type.in' => 'Invalid employment type selected.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'offer_valid_until.required' => 'Offer validity date is required.',
            'offer_valid_until.after' => 'Offer validity must be a future date.',
            'offer_valid_until.before' => 'Offer validity cannot exceed 1 month.',
            'max_negotiation_amount.required_if' => 'Maximum negotiation amount is required for negotiable offers.',
            'travel_percentage.required_if' => 'Travel percentage is required when travel is required.',
            'travel_percentage.max' => 'Travel percentage cannot exceed 100%.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Check if user has permission to manage offers
        if ($user->can('manage_offers') || $user->hasRole(['HR Manager', 'Admin'])) {
            return true;
        }

        // Allow hiring managers to create offers for their department
        if ($user->hasRole('Hiring Manager')) {
            $application = \Modules\Recruitment\Entities\Application::find($this->application_id);
            if ($application && $application->job && $application->job->department_id === $user->department_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validated data from the request with computed fields.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        // Set default probation period based on employment type
        if (!isset($validated['probation_period_months'])) {
            $validated['probation_period_months'] = match($validated['employment_type']) {
                'full-time' => 3,
                'part-time' => 2,
                'contract' => 1,
                'internship' => null,
                default => 3
            };
        }
        
        // Generate offer reference number
        $validated['offer_reference'] = 'OFF-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $validated;
    }
}
