<?php
namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\MartialStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Spatie\Permission\Models\Role;

class UserStoreRequest extends FormRequest
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
    // public function rules(Request $request): array
    // {
    //     return [
    //         'first_name' => ['required', 'string', 'min:3'],
    //         'last_name' => ['required', 'string', 'min:3'],
    //         'email' => ['required', 'email', 'unique:users,email'],
    //         'phone' => ['required'],
    //         'department_id' => ['required', 'exists:departments,id'],
    //         'designation_id' => [
    //             'required',
    //             Rule::exists('designations', 'id', function ($query) use ($request) {
    //                 return $query->where('department_id', $request->department_id);
    //             })
    //         ],
    //         'emp_id' => ['nullable', 'unique:users,employee_id'],
    //         'date_of_birth' => ['required', 'date'],
    //         'gender' => ['required', new Enum(Gender::class)],
    //         'martial_status' => ['required', new Enum(MartialStatus::class)],
    //         'personal_phone' => ['required'],
    //         'visa_category' => ['nullable'],
    //         'personal_email' => ['required', 'email'],
    //         'address' => ['required'],
    //         'linkedIn' => ['nullable'],
    //         // 'skills' => ['required'], commented skill & hobbies as required | Gagan 02-08-2023
    //         'country_id' => ['required', 'exists:countries,id'],
    //         // 'hobbies' => ['required'],
    //         'date_of_joining' => ['required', 'date'],
    //          'probation_month' => 'nullable|integer|min:0|max:24',
    //         'probation_end_date' => ['nullable', 'date'],
    //         'company_name' => ['required'],
    //         'work_week' => ['required', 'numeric'],
    //         'location' => ['required'],
    //         'role_id' => ['required'],
    //         'entity' => ['nullable'],

    //         // 'company_document_id' => ['required', 'exists:company_documents,id'],
    //     ];
    // }
    public function rules(Request $request): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'min:2'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'role_id'    => ['required'],
        ];

        // Check if selected role is Admin
        $adminRole = Role::where('name', 'Admin')->first();

        if ($adminRole && $request->role_id == $adminRole->id) {
            // For Admin → return only minimal required fields
            return $rules;
        }

        $hrRole = Role::where('name', 'hr')->first();

        if ($hrRole && $request->role_id == $hrRole->id) {
            // For Admin → return only minimal required fields
            return $rules;
        }

        // For other users → merge full validation
        return array_merge($rules, [

            'phone'                      => ['required'],
            'department_id'              => ['required', 'exists:departments,id'],
            'designation_id'             => ['required', 'exists:designations,id'],
            // 'designation_id'             => [
            //     'required',
            //     Rule::exists('designations', 'id', function ($query) use ($request) {
            //         return $query->where('department_id', $request->department_id);
            //     }),
            // ],
            'emp_id'                     => ['nullable', 'unique:users,employee_id'],
            'biometric_user_id'          => ['nullable', 'unique:users,biometric_user_id'],
            'date_of_birth'              => ['required', 'date_format:d/m/Y'],
            'gender'                     => ['required', new Enum(Gender::class)],
            'martial_status'             => ['required', new Enum(MartialStatus::class)],
            'personal_phone'             => ['required'],
            'personal_email'             => ['required', 'email'],
            'address'                    => ['required'],
            'linkedIn'                   => ['nullable'],
            // 'skills' => ['required'], commented skill & hobbies as required | Gagan 02-08-2023
            'country_id'                 => ['required', 'exists:countries,id'],
            // 'hobbies' => ['required'],
            'date_of_joining'            => ['required', 'date_format:d/m/Y'],
            'probation_end_date'         => ['nullable', 'date_format:d/m/Y'],
            'company_name'               => ['required'],
            'work_week'                  => ['required', 'numeric'],
            'location'                   => ['required'],
            'role_id'                    => ['required'],
            'entity'                     => ['nullable'],
            // 'company_document_id' => ['required', 'exists:company_documents,id'],
            'mol_number'                 => ['nullable', 'string'],
            'insurance_number'           => ['nullable', 'string'],
            'insurance_expiry'           => ['nullable', 'date_format:d/m/Y'],
            'last_working_day'           => ['nullable', 'date_format:d/m/Y'],
            'remarks'                    => ['nullable', 'string'],
            'visa_designation'           => ['nullable', 'string'],
            'visa_category'              => ['nullable', 'string'],
            'profile_image'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'passport_number'            => ['nullable', 'string', 'max:255'],
            'passport_issue_date'        => ['nullable', 'date_format:d/m/Y'],
            'passport_expiry_date'       => ['nullable', 'date_format:d/m/Y'],
            'passport_place_of_issue'    => ['nullable', 'string', 'max:255'],
            'passport_country'           => ['nullable', 'string', 'max:255'],
            'visa_number'                => ['nullable', 'string', 'max:255'],
            'visa_issue_date'            => ['nullable', 'date_format:d/m/Y'],
            'visa_expiry_date'           => ['nullable', 'date_format:d/m/Y'],
            'visa_place_of_issue'        => ['nullable', 'string', 'max:255'],
            'visa_country'               => ['nullable', 'string', 'max:255'],
            'labor_card_number'          => ['nullable', 'string', 'max:255'],
            'labor_card_personal_number' => ['nullable', 'string', 'max:255'],
            'labor_card_issue_date'      => ['nullable', 'date_format:d/m/Y'],
            'labor_card_expiry_date'     => ['nullable', 'date_format:d/m/Y'],
            'emirates_id_number'         => ['nullable', 'string', 'max:255'],
            'emirates_id_issue_date'     => ['nullable', 'date_format:d/m/Y'],
            'emirates_id_expiry_date'    => ['nullable', 'date_format:d/m/Y'],
        ]);

    }
}
