<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserDependentRequest extends FormRequest
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
            'first_name' => ['required', 'string'],
            // 'middle_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'contact' => ['required', 'string'],
            'address' => ['nullable', 'string'],
            'date_of_birth' => ['required', 'date_format:d/m/Y'],
            'nationality' => ['required', 'string'],
            'relation' => ['required', new Enum(Relation::class)],
            'gender' => ['required', new Enum(Gender::class)]
        ];
    }
}
