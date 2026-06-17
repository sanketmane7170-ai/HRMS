<?php
namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserService
{

    /**
     * create a user in the storage
     */
    public function add(Request $request): User | Exception
    {
        DB::beginTransaction();
        try {
            $user = new User();

            $isAdmin = false;

            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            if ($role && strtolower($role->name) === 'admin') {
                $isAdmin = true;
            }

            $isHr = false;

            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            if ($role && strtolower($role->name) === 'hr') {
                $isHr = true;
            }

            $user->name     = "$request->first_name $request->last_name";
            $user->phone    = $request->phone;
            $user->email    = $request->email;
            $user->password = bcrypt("Welcome" . date('Y'));
            if (! $isAdmin && ! $isHr) {
                $user->designation_id = $request->designation_id ?? null;
                $user->department_id  = $request->department_id ?? null;
                $user->division_id    = $request->division_id ?? null;
            }
            $user->status = $request->status;
            if (isset($request->company_document_id)) {
                $user->company_document_id = $request->company_document_id;
            }
            if ($request->emp_id != null) {
                if (strlen($request->emp_id) > 2) {
                    $user->employee_id = $request->emp_id;
                }
            }
            if ($request->biometric_user_id != null) {
                $user->biometric_user_id = $request->biometric_user_id;
            }
            // if ($request->hasFile('profile_image')) {
            //     $file     = $request->file('profile_image');
            //     $filename = time() . '_' . $file->getClientOriginalName();
            //     $file->move(public_path('uploads/profile'), $filename);
            //     $user->profile_image = $filename;
            // }
            $user->save();

            if (! $isAdmin && ! $isHr) {

                $user->profile()->create([
                    'date_of_birth'    => ! empty($request->date_of_birth) ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null, // Updated by Sanket
                    'personal_email'   => $request->personal_email,
                    'personal_phone'   => $request->personal_phone,
                    'martial_status'   => $request->martial_status,
                    'country_id'       => $request->country_id,
                    'gender'           => $request->gender,
                    'linkedin_url'     => $request->linkedin_profile_url,
                    'skills'           => $request->skills,
                    'hobbies'          => $request->hobbies,
                    'address'          => $request->address,
                    'visa_designation' => $request->visa_designation,
                    'visa_category'    => $request->visa_category,
                    'visa_type'        => $request->visa_type,
                ]);

                $joiningDate     = ! empty($request->date_of_joining) ? Carbon::createFromFormat('d/m/Y', $request->date_of_joining) : now(); // Updated by Sanket
                $probationMonths = getSetting('probation_period_month');
                if ($probationMonths == '1_month') {
                    $probationMonths = 1;
                } elseif ($probationMonths == '3_month') {
                    $probationMonths = 3;
                } else {
                    $probationMonths = 6;
                }

                $probationEndDate = $joiningDate->copy()->addMonths($probationMonths); // Updated by Sanket
                $user->workDetail()->create([
                    'joining_date'               => $joiningDate->format('Y-m-d'),      // Updated by Sanket
                    'probation_end_date'         => $probationEndDate->format('Y-m-d'), // Updated by Sanket
                    'company_name'               => $request->company_name,
                    'work_week'                  => $request->work_week,
                    'weekend'                    => implode(',', $request->weekend ?? []),
                    'location'                   => $request->location,
                    'shift_start'                => $request->shift_start,
                    'shift_end'                  => $request->shift_end,
                    // 'report_to_ids' => $request->report_to_id,
                    'report_to_ids'              => is_array($request->report_to_id) ? $request->report_to_id : [$request->report_to_id],
                    'approved_first_level'       => $request->has('approved_first_level') ? $request->approved_first_level : false,

                    'medical_insurance_provided' => $request->medical_insurance_provided,
                    'salary_mode'                => $request->salary_mode,
                    'annual_premium'             => $request->medical_insurance_provided == 1 ? $request->annual_premium : 0,
                    'air_ticket_setting_id'      => $request->air_ticket_setting_id,
                    'attendance_base'            => $request->attendance_base,
                    'grade'                      => $request->grade,
                    'company_accommodation'      => $request->company_accommodation,
                    'accommodation_location'     => $request->accommodation_location,
                    'is_rider'                   => isset($request->is_rider) ? $request->is_rider : 0,
                    'air_ticket_count'           => isset($request->air_ticket_count) ? $request->air_ticket_count : 0,
                    'renewal_air_ticket'         => isset($request->renewal_air_ticket) ? $request->renewal_air_ticket : "",
                    'free_document_request'      => isset($request->free_document_request) ? $request->free_document_request : 0,
                    'document_request_charge'    => isset($request->document_request_charge) ? $request->document_request_charge : 0,
                    'mol_number'                 => $request->mol_number,
                    'insurance_number'           => $request->insurance_number,
                    'insurance_expiry'           => ! empty($request->insurance_expiry) ? Carbon::createFromFormat('d/m/Y', $request->insurance_expiry)->format('Y-m-d') : null, // Updated by Sanket
                    'last_working_day'           => ! empty($request->last_working_day) ? Carbon::createFromFormat('d/m/Y', $request->last_working_day)->format('Y-m-d') : null, // Updated by Sanket
                    'remarks'                    => $request->remarks,
                    'entity'                     => $request->entity,

                ]);

                // Save Passport Details - Added by Sanket
                if ($request->passport_number) {
                    $user->documents()->create([
                        'original_name'  => 'Passport',
                        'path'           => '/uploads/default-document.jpeg',
                        'serial_number'  => $request->passport_number,
                        'issue_date'     => ! empty($request->passport_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->passport_issue_date)->format('Y-m-d') : null,
                        'expiry_date'    => ! empty($request->passport_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->passport_expiry_date)->format('Y-m-d') : null,
                        'type'           => \App\Enums\Document::Passport,
                        'place_of_issue' => $request->passport_place_of_issue,
                        'country_name'   => $request->passport_country,
                    ]);
                }

                // Save Visa Details - Added by Sanket
                if ($request->visa_number) {
                    $user->documents()->create([
                        'original_name'  => 'Visa',
                        'path'           => '/uploads/default-document.jpeg',
                        'serial_number'  => $request->visa_number,
                        'issue_date'     => ! empty($request->visa_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->visa_issue_date)->format('Y-m-d') : null,
                        'expiry_date'    => ! empty($request->visa_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->visa_expiry_date)->format('Y-m-d') : null,
                        'type'           => \App\Enums\Document::Visa,
                        'place_of_issue' => $request->visa_place_of_issue,
                        'country_name'   => $request->visa_country,
                    ]);
                }

                // Save Labor Card Details - Added by Sanket
                if ($request->labor_card_number) {
                    $user->documents()->create([
                        'original_name'                 => 'Labor Card',
                        'path'                          => '/uploads/default-document.jpeg',
                        'serial_number'                 => $request->labor_card_number,
                        'ministry_of_labor_personal_no' => $request->labor_card_personal_number,
                        'issue_date'                    => ! empty($request->labor_card_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->labor_card_issue_date)->format('Y-m-d') : null,
                        'expiry_date'                   => ! empty($request->labor_card_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->labor_card_expiry_date)->format('Y-m-d') : null,
                        'type'                          => \App\Enums\Document::LaborCard,
                    ]);
                }

                // Save Emirates ID Details - Added by Sanket
                if ($request->emirates_id_number) {
                    $user->documents()->create([
                        'original_name' => 'Emirates ID',
                        'path'          => '/uploads/default-document.jpeg',
                        'serial_number' => $request->emirates_id_number,
                        'issue_date'    => ! empty($request->emirates_id_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->emirates_id_issue_date)->format('Y-m-d') : null,
                        'expiry_date'   => ! empty($request->emirates_id_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->emirates_id_expiry_date)->format('Y-m-d') : null,
                        'type'          => \App\Enums\Document::EmiratesID,
                    ]);
                }

                $user->bankDetail()->create([
                    'bank_name'      => $request->bank_name,
                    'account_number' => $request->account_number,
                    'iba_number'     => $request->iba_number,
                    'swift_code'     => $request->swift_code,
                    'routing_number' => $request->routing_number,
                ]);
                // Add Salary Details for solve issues
                $user->salary()->create([
                    'basic'            => 0,
                    'fixed_allowances' => json_encode([
                        "housing_allowance"        => 0,
                        "transportation_allowance" => 0,
                        "other_allowance"          => 0,
                        "functional_allowance"     => 0,
                        "tips"                     => 0,
                    ]),
                    'fixed_deductions' => json_encode(["advance_salary" => 0, "loan_deduction" => 0, "other_deduction" => 0]),
                ]);

                $user->emergencyContacts()->create([
                    'emergency_name'          => $request->emergency_name,
                    'emergency_relation'      => $request->emergency_relation,
                    'emergency_phone'         => $request->emergency_phone,
                    'emergency_isd_code'      => $request->emergency_isd_code,
                    'emergency_email'         => $request->emergency_email,
                    'emergency_home_country'  => $request->emergency_home_country,
                    'emergency_home_address'  => $request->emergency_home_address,
                    'emergency_local_country' => $request->emergency_local_country,
                    'local_person_name'       => $request->local_person_name,
                    'local_person_relation'   => $request->local_person_relation,
                    'local_person_phone'      => $request->local_person_phone,
                    'emergency_local_address' => $request->emergency_local_address,
                ]);

                if ($request->has('shifts') && is_array($request->input('shifts'))) {
                    foreach ($request->input('shifts') as $shiftData) {
                        $user->shifts()->create([
                            'shift_start' => $shiftData['shift_start'],
                            'shift_end'   => $shiftData['shift_end'],
                        ]);
                    }
                }

                if ($request->has('tickets') && is_array($request->input('tickets'))) {
                    foreach ($request->input('tickets') as $ticketData) {
                        if (isset($ticketData['title']) && $ticketData['title'] != "" && $ticketData['percentage'] != "") {
                            $user->airTicketsDetail()->create([
                                'title'      => $ticketData['title'],
                                'qty'        => $ticketData['qty'],
                                'percentage' => $ticketData['percentage'],
                            ]);
                        }
                    }
                }
            }

            /// assigning role to user
            $user->assignRole($request->role_id);
            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * update a particular user From the Storage
     */

    public function update(User $user, Request $request)
    {
        DB::beginTransaction();
        try {
            $old_department_id = $user->department_id;

            // dd($request->file('profile_image')->isValid());
            // if ($request->hasFile('profile_image')) {
            //     $file     = $request->file('profile_image');
            //     $filename = time() . '_' . $file->getClientOriginalName();
            //     $file->move(asset('uploads/profile'), $filename);
            //     $user->profile_image = $filename;
            // }

            $isAdmin = false;

            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            if ($role && strtolower($role->name) === 'admin') {
                $isAdmin = true;

            }

            $isHr = false;

            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            if ($role && strtolower($role->name) === 'hr') {
                $isHr = true;
            }

            $user->name  = "$request->first_name $request->last_name";
            $user->phone = $request->phone;
            $user->email = $request->email;
            if (! $isAdmin && ! $isHr) {
                $user->designation_id = $request->designation_id ?? 0;
                $user->department_id  = $request->department_id ?? 0;
                $user->division_id    = $request->division_id ?? 0;
            }
            if ($request->emp_id != null) {
                if (strlen($request->emp_id) > 2) {
                    $user->employee_id = $request->emp_id;
                }
            }
            if ($request->biometric_user_id != null) {
                $user->biometric_user_id = $request->biometric_user_id;
            }
            $user->status = $request->status;
            $user->save();
            if (! $isAdmin && ! $isHr) {

                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'date_of_birth'    => ! empty($request->date_of_birth) ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null, // Updated by Sanket
                        'personal_email'   => $request->personal_email,
                        'personal_phone'   => $request->personal_phone,
                        'martial_status'   => $request->martial_status,
                        'country_id'       => $request->country_id,
                        'gender'           => $request->gender,
                        'linkedin_url'     => $request->linkedin_profile_url,
                        'skills'           => $request->skills,
                        'hobbies'          => $request->hobbies,
                        'address'          => $request->address,
                        'visa_category'    => $request->visa_category,
                        'visa_designation' => $request->visa_designation,
                        'visa_type'        => $request->visa_type,
                    ]
                );
                $joiningDate = ! empty($request->date_of_joining) ? Carbon::createFromFormat('d/m/Y', $request->date_of_joining) : now(); // Updated by Sanket

                // $probationMonths = getSetting('probation_period_month');
                // if ($probationMonths == '1_month') {
                //     $probationMonths = 1;
                // } elseif ($probationMonths == '3_month') {
                //     $probationMonths = 3;
                // } else {
                //     $probationMonths = 6;
                // }

                // $probationEndDate = $joiningDate->addMonths($probationMonths)->format('Y-m-d');
                if (! empty($request->probation_month)) {
                    $probationMonths = (int) $request->probation_month;
                } else {
                    // 2️⃣ Fallback to system setting
                    $setting = getSetting('probation_period_month');

                    switch ($setting) {
                        case '1_month':
                            $probationMonths = 1;
                            break;

                        case '3_month':
                            $probationMonths = 3;
                            break;

                        case '6_month':
                        default:
                            $probationMonths = 6;
                            break;
                    }
                }

                $probationEndDate = $joiningDate->copy()->addMonths($probationMonths); // Updated by Sanket

                $user->workDetail()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'joining_date'               => $joiningDate->format('Y-m-d'),      // Updated by Sanket
                        'probation_end_date'         => $probationEndDate->format('Y-m-d'), // Updated by Sanket
                        'company_name'               => $request->company_name,
                        'work_week'                  => $request->work_week,
                        'weekend'                    => implode(',', $request->weekend ?? []),
                        'location'                   => $request->location,
                        'shift_start'                => $request->shift_start,
                        'shift_end'                  => $request->shift_end,
                        // 'report_to_id' => $request->report_to_id,
                        'report_to_ids'              => is_array($request->report_to_id) ? $request->report_to_id : [$request->report_to_id],
                        'approved_first_level'       => $request->has('approved_first_level') ? $request->approved_first_level : false,

                        'medical_insurance_provided' => $request->medical_insurance_provided,
                        'salary_mode'                => $request->salary_mode,
                        'annual_premium'             => $request->medical_insurance_provided == 1 ? $request->annual_premium : 0,
                        'air_ticket_setting_id'      => $request->air_ticket_setting_id,
                        'attendance_base'            => $request->attendance_base,
                        'grade'                      => $request->grade,
                        'company_accommodation'      => $request->company_accommodation,
                        'accommodation_location'     => $request->accommodation_location,
                        'is_rider'                   => $request->is_rider,
                        'air_ticket_count'           => isset($request->air_ticket_count) ? $request->air_ticket_count : 0,
                        'renewal_air_ticket'         => isset($request->renewal_air_ticket) ? $request->renewal_air_ticket : "",
                        'free_document_request'      => isset($request->free_document_request) ? $request->free_document_request : 0,
                        'document_request_charge'    => isset($request->document_request_charge) ? $request->document_request_charge : 0,
                        'mol_number'                 => $request->mol_number,
                        'insurance_number'           => $request->insurance_number,
                        'insurance_expiry'           => ! empty($request->insurance_expiry) ? Carbon::createFromFormat('d/m/Y', $request->insurance_expiry)->format('Y-m-d') : null, // Updated by Sanket
                        'last_working_day'           => ! empty($request->last_working_day) ? Carbon::createFromFormat('d/m/Y', $request->last_working_day)->format('Y-m-d') : null, // Updated by Sanket
                        'remarks'                    => $request->remarks,
                        'entity'                     => $request->entity,
                    ]
                );

                // Update Passport Details - Added by Sanket
                if ($request->passport_number) {
                    $user->documents()->updateOrCreate(
                        ['type' => \App\Enums\Document::Passport],
                        [
                            'original_name'  => 'Passport',
                            // 'path' => '/uploads/default-document.jpeg',
                            'serial_number'  => $request->passport_number,
                            'issue_date'     => ! empty($request->passport_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->passport_issue_date)->format('Y-m-d') : null,
                            'expiry_date'    => ! empty($request->passport_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->passport_expiry_date)->format('Y-m-d') : null,
                            'place_of_issue' => $request->passport_place_of_issue,
                            'country_name'   => $request->passport_country,
                        ]
                    );
                }

                // Update Visa Details - Added by Sanket
                if ($request->visa_number) {
                    $user->documents()->updateOrCreate(
                        ['type' => \App\Enums\Document::Visa],
                        [
                            'original_name'  => 'Visa',
                            'serial_number'  => $request->visa_number,
                            'issue_date'     => ! empty($request->visa_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->visa_issue_date)->format('Y-m-d') : null,
                            'expiry_date'    => ! empty($request->visa_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->visa_expiry_date)->format('Y-m-d') : null,
                            'place_of_issue' => $request->visa_place_of_issue,
                            'country_name'   => $request->visa_country,
                        ]
                    );
                }

                // Update Labor Card Details - Added by Sanket
                if ($request->labor_card_number) {
                    $user->documents()->updateOrCreate(
                        ['type' => \App\Enums\Document::LaborCard],
                        [
                            'original_name'                 => 'Labor Card',
                            'serial_number'                 => $request->labor_card_number,
                            'ministry_of_labor_personal_no' => $request->labor_card_personal_number,
                            'issue_date'                    => ! empty($request->labor_card_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->labor_card_issue_date)->format('Y-m-d') : null,
                            'expiry_date'                   => ! empty($request->labor_card_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->labor_card_expiry_date)->format('Y-m-d') : null,
                        ]
                    );
                }

                // Update Emirates ID Details - Added by Sanket
                if ($request->emirates_id_number) {
                    $user->documents()->updateOrCreate(
                        ['type' => \App\Enums\Document::EmiratesID],
                        [
                            'original_name' => 'Emirates ID',
                            'serial_number' => $request->emirates_id_number,
                            'issue_date'    => ! empty($request->emirates_id_issue_date) ? Carbon::createFromFormat('d/m/Y', $request->emirates_id_issue_date)->format('Y-m-d') : null,
                            'expiry_date'   => ! empty($request->emirates_id_expiry_date) ? Carbon::createFromFormat('d/m/Y', $request->emirates_id_expiry_date)->format('Y-m-d') : null,
                        ]
                    );
                }
                if ($old_department_id != $request->department_id) {
                    $department = \App\Models\Department::find($request->department_id);
                    if ($department && $department->manager_id) {
                        $managers = explode(',', $department->manager_id);
                        if ($managers != null) {
                            $user->workDetail()->updateOrCreate(
                                ['user_id' => $user->id],
                                [
                                    'report_to_ids' => $managers,
                                ]
                            );
                        }
                    }
                }

                // $user->salary()->updateOrCreate([
                //     'fixed_allowances' => json_encode([
                //         "housing_allowance" => $request->housing_allowance,
                //         "transportation_allowance" => $request->transportation_allowance,
                //         "other_allowance" => $request->other_allowance,
                //         "tips" => $request->tips
                //     ]),
                //     'fixed_deductions' => json_encode([
                //         "advance_salary" => $request->advance_salary,
                //         "loan_deduction" => $request->loan_deduction,
                //         "other_deduction" =>$request->other_deduction
                //     ])
                // ]);
                $check_query = DB::table('user_bank_details')->where('user_id', $user->id)->first();
                if (empty($check_query)) {
                    $user->bankDetail()->create([
                        'bank_name'      => $request->bank_name,
                        'account_number' => $request->account_number,
                        'iba_number'     => $request->iba_number,
                        'swift_code'     => $request->swift_code,
                        'routing_number' => $request->routing_number,
                    ]);
                } else {
                    $user->bankDetail()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'bank_name'      => $request->bank_name,
                            'account_number' => $request->account_number,
                            'iba_number'     => $request->iba_number,
                            'swift_code'     => $request->swift_code,
                            'routing_number' => $request->routing_number,
                        ]
                    );
                }

                $check_emergency_query = DB::table('user_emergency_contacts')->where('user_id', $user->id)->first();
                if (empty($check_emergency_query)) {
                    $user->emergencyContacts()->create([
                        'emergency_name'          => $request->emergency_name,
                        'emergency_relation'      => $request->emergency_relation,
                        'emergency_phone'         => $request->emergency_phone,
                        'emergency_isd_code'      => $request->emergency_isd_code,
                        'emergency_email'         => $request->emergency_email,
                        'emergency_home_country'  => $request->emergency_home_country,
                        'emergency_home_address'  => $request->emergency_home_address,
                        'emergency_local_country' => $request->emergency_local_country,
                        'emergency_local_address' => $request->emergency_local_address,
                        'local_person_name'       => $request->local_person_name,
                        'local_person_relation'   => $request->local_person_relation,
                        'local_person_phone'      => $request->local_person_phone,
                    ]);
                } else {
                    $user->emergencyContacts()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'emergency_name'          => $request->emergency_name,
                            'emergency_relation'      => $request->emergency_relation,
                            'emergency_phone'         => $request->emergency_phone,
                            'emergency_isd_code'      => $request->emergency_isd_code,
                            'emergency_email'         => $request->emergency_email,
                            'emergency_home_country'  => $request->emergency_home_country,
                            'emergency_home_address'  => $request->emergency_home_address,
                            'emergency_local_country' => $request->emergency_local_country,
                            'emergency_local_address' => $request->emergency_local_address,
                            'local_person_name'       => $request->local_person_name,
                            'local_person_relation'   => $request->local_person_relation,
                            'local_person_phone'      => $request->local_person_phone,
                        ]
                    );
                }
                // updateOrCreate Exisiting Shift
                if ($request->input('shifts')) {
                    foreach ($request->input('shifts') as $index => $shiftData) {
                        $shift = $user->shifts[$index]; // Get the specific shift by index

                        $shift->update(
                            [
                                'shift_start' => $shiftData['shift_start'],
                                'shift_end'   => $shiftData['shift_end'],
                                // Add any other fields you need to updateOrCreate
                            ]
                        );
                    }
                }
                // Create New Shift On Edit Page
                if ($request->input('create_shifts')) {
                    foreach ($request->input('create_shifts') as $shiftData) {
                        $user->shifts()->create([
                            'shift_start' => $shiftData['shift_start'],
                            'shift_end'   => $shiftData['shift_end'],
                        ]);
                    }
                }
                $existingTicketIds = $user->airTicketsDetail->pluck('id')->toArray();

                // $submittedTicketIds = collect($request->input('tickets', []))
                //     ->pluck('id')
                //     ->filter() // remove null
                //     ->toArray();
                $submittedTickets = collect($request->input('tickets', []))
                    ->reject(fn($t) => isset($t['_delete']) && $t['_delete'] == 1)
                    ->pluck('id')
                    ->filter()
                    ->toArray();
                $toDelete = array_diff($existingTicketIds, $submittedTickets);

                if (! empty($toDelete)) {
                    $user->airTicketsDetail()->whereIn('id', $toDelete)->delete();
                }
                // Update existing tickets
                if ($request->input('tickets')) {
                    foreach ($request->input('tickets') as $index => $ticketData) {
                        // Get the existing ticket by index (make sure tickets are loaded)
                        $ticket = $user->airTicketsDetail[$index] ?? null;

                        if ($ticket) {

                            if (isset($ticketData['title']) && $ticketData['title'] != "" && $ticketData['percentage'] != "") {
                                $ticket->update([
                                    'title'      => $ticketData['title'],
                                    'qty'        => $ticketData['qty'] ? $ticketData['qty'] : 1,
                                    'percentage' => $ticketData['percentage'],
                                ]);
                            }
                        }
                    }
                }

                // Create new tickets if any
                if ($request->input('create_tickets')) {
                    foreach ($request->input('create_tickets') as $ticketData) {
                        if (isset($ticketData['title']) && $ticketData['title'] != "" && $ticketData['percentage'] != "") {
                            $return = $user->airTicketsDetail()->create([
                                'title'      => $ticketData['title'],
                                'qty'        => $ticketData['qty'] ? $ticketData['qty'] : 1,
                                'percentage' => $ticketData['percentage'],
                            ]);
                        }
                    }
                }
            }

            /// assigning role to user
            $user->syncRoles($request->role_id);
            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
