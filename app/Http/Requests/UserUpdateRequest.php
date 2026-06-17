<?php
namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Spatie\Permission\Models\Role;

class UserUpdateRequest extends FormRequest
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
    //     $user = $this->route('user');
    //     return [
    //         'first_name' => ['required', 'string', 'min:3'],
    //         'last_name' => ['required', 'string', 'min:3'],
    //         'email' => ['required', 'email', 'unique:users,email,' . $user->id],
    //         'phone' => ['required'],
    //         'department_id' => ['required', 'exists:departments,id'],
    //         'designation_id' => [
    //             'required',
    //             Rule::exists('designations', 'id', function ($query) use ($request) {
    //                 return $query->where('department_id', $request->department_id);
    //             })
    //         ],
    //         'emp_id' => ['required',Rule::unique('users', 'employee_id')->ignore($user->id)],
    //         'date_of_birth' => ['required', 'date_format:d/m/Y'],
    //         'gender' => ['required', new Enum(Gender::class)],
    //         'martial_status' => ['required'],
    //         'personal_phone' => ['required'],
    //         'visa_category' => ['nullable'],
    //         'personal_email' => ['required', 'email'],
    //         'address' => ['required'],
    //         'linkedIn' => ['nullable'],
    //         'skills' => ['nullable'],
    //         'country_id' => ['required', 'exists:countries,id'],
    //         'hobbies' => ['nullable'],
    //         'date_of_joining' => ['required', 'date'],
    //         'probation_end_date' => ['nullable', 'date'],
    //         'probation_month' => ['nullable', 'integer', 'min:0', 'max:24'],
    //         'company_name' => ['required'],
    //         'work_week' => ['required', 'numeric'],
    //         'location' => ['required'],
    //         'entity' => ['nullable'],
    //         // 'company_document_id' => ['required', 'exists:company_documents,id'],
    //         'mol_number' => ['nullable', 'string'],
    //         'insurance_number' => ['nullable', 'string'],
    //         'insurance_expiry' => ['nullable', 'date_format:d/m/Y'],
    //         'last_working_day' => ['nullable', 'date_format:d/m/Y'],
    //         'remarks' => ['nullable', 'string'],
    //         'visa_designation' => ['nullable', 'string'],
    //         'visa_type' => ['nullable', 'string'],
    //         'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
    //         'passport_number' => ['nullable', 'string', 'max:255'],
    //         'passport_issue_date' => ['nullable', 'date_format:d/m/Y'],
    //         'passport_expiry_date' => ['nullable', 'date_format:d/m/Y'],
    //         'passport_place_of_issue' => ['nullable', 'string', 'max:255'],
    //         'passport_country' => ['nullable', 'string', 'max:255'],
    //         'visa_number' => ['nullable', 'string', 'max:255'],
    //         'visa_issue_date' => ['nullable', 'date_format:d/m/Y'],
    //         'visa_expiry_date' => ['nullable', 'date_format:d/m/Y'],
    //         'visa_place_of_issue' => ['nullable', 'string', 'max:255'],
    //         'visa_country' => ['nullable', 'string', 'max:255'],
    //         'labor_card_number' => ['nullable', 'string', 'max:255'],
    //         'labor_card_personal_number' => ['nullable', 'string', 'max:255'],
    //         'labor_card_issue_date' => ['nullable', 'date_format:d/m/Y'],
    //         'labor_card_expiry_date' => ['nullable', 'date_format:d/m/Y'],
    //         'emirates_id_number' => ['nullable', 'string', 'max:255'],
    //         'emirates_id_issue_date' => ['nullable', 'date_format:d/m/Y'],
    //         'emirates_id_expiry_date' => ['nullable', 'date_format:d/m/Y'],
    //     ];
    // }
    public function rules(Request $request): array
    {
        $user = $this->route('user');

        $basicRules = [
            'first_name' => ['required', 'string', 'min:2'],
            'email'      => ['required', 'email', 'unique:users,email,' . $user->id],
            'role_id'    => ['required'],
        ];

        // Get selected role
        $role = Role::find($request->role_id);

        if ($role && in_array(strtolower($role->name), ['admin', 'hr'])) {
            // For Admin or HR → validate only minimal fields
            return $basicRules;
        }

        // For other roles → full validation
        return array_merge($basicRules, [
            'last_name'         => ['required', 'string', 'min:3'],
            'phone'             => ['required'],
            'department_id'     => ['required', 'exists:departments,id'],
            // 'designation_id'  => [
            //     'required',
            //     Rule::exists('designations', 'id')->where(function ($query) use ($request) {
            //         $query->where('department_id', $request->department_id);
            //     }),
            // ],
            'designation_id'    => ['required', 'exists:designations,id'],

            'emp_id'            => ['required', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'biometric_user_id' => ['nullable', Rule::unique('users', 'biometric_user_id')->ignore($user->id)],
            'date_of_birth'     => ['required', 'date_format:d/m/Y'],
            'gender'            => ['required', new Enum(Gender::class)],
            'martial_status'    => ['required'],
            'personal_phone'    => ['required'],
            'personal_email'    => ['required', 'email'],
            'address'           => ['required'],
            'country_id'        => ['required', 'exists:countries,id'],
            'date_of_joining'   => ['required', 'date_format:d/m/Y'],
            'company_name'      => ['required'],
            'work_week'         => ['required', 'numeric'],
            'location'          => ['required'],
        ]);
    }
}
