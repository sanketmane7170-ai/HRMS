<?php

namespace App\Http\Requests;

use App\Enums\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserDocumentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'required', new Enum(Document::class)
            ],
            'expiry_date' => [
                'nullable',
                'date_format:d/m/Y',
                Rule::requiredIf(function () {
                    return $this->validateField();
                }),
                function ($attribute, $value, $fail) {
                    $issueDate = $this->input('issue_date');
    
                    if (!is_null($issueDate) && !is_null($value) && strtotime($value) <= strtotime($issueDate)) {
                        $fail('The expiry date must be greater than the issue date.');
                    }
                }
            ],
            'serial_number' => [
                'nullable',
                'string',
                Rule::requiredIf(function () {
                    return $this->validateField();
                })
            ],
            //'file' => ['mimes:png,jpg,jpeg','pdf']
        ];
    }

    public function messages(): array
{
    return [
        'required' => 'This field is required.',
    ];
}

    public function validateField()
    {
        $required = false;
        $type = request()->type;
        switch ($type) {
            case (Document::Passport->value):
                $required = true;
                break;
            case (Document::Visa->value):
                $required = true;
                break;
            case (Document::License->value):
                $required = true;
                break;
            case (Document::EidFront->value):
                $required = true;
                break;
            case (Document::EidBack->value):
                $required = true;
                break;
            case (Document::Insurance->value):
                $required = true;
                break;
        }

        return $required;
    }
}
