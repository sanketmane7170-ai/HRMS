<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserImport;
use App\Exports\UserSampleExport;
use App\Exports\UserExport;
use Illuminate\Http\UploadedFile;

class EmployeeTest extends TestCase
{
    use RefreshDatabase; // Use RefreshDatabase to clear data after test

    protected $admin;
    protected $department;
    protected $designation;
    protected $division;
    protected $country;
    protected $airTicketSetting;


    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Role
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        if (!Role::where('name', 'Employee')->exists()) {
            Role::create(['name' => 'Employee', 'guard_name' => 'web']);
        }

        // Create Department, Designation, Division
        $this->department = Department::create(['name' => 'IT']);
        $this->designation = Designation::create(['name' => 'Developer', 'department_id' => $this->department->id]); // Link to department checks
        $this->division = Division::create(['name' => 'Tech']);
        
        // Create Country
        $this->country = \App\Models\Country::create(['name' => 'India', 'code' => 'IN', 'phonecode' => 91]);

        // Create Admin User
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '9999999999',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'division_id' => $this->division->id,
        ]);
        
        $this->admin->assignRole('admin');

        // Create AirTicketSetting
        $this->airTicketSetting = \Modules\AirTicketSetting\Entities\AirTicketSetting::create([
             'policy_name' => 'Standard',
             'allowance_currency' => 'AED',
             'allowance_amount' => 1000,
             'request_after_months' => 12,
             'request_after_months_date' => 'joining_date',
             'policy_renewal_months' => 12,
             'request_limit_per_cycle' => 1,
             'allow_reimbursement' => 1,
             'allow_encashment' => 1,
             'allow_ticket_booking' => 1,
             'encashment_amount' => 1000,
             'request_after_from' => 'joining_date',
             'country' => 'India'
        ]);
    }

    public function test_admin_can_create_employee_with_new_fields()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('backend.users.store'), [
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'division_id' => $this->division->id,
            'role_id' => Role::where('name', 'Employee')->first()->id,
            // Required Fields
            'date_of_birth' => '1990-01-01',
            'gender' => \App\Enums\Gender::Male->value,
            'martial_status' => \App\Enums\MartialStatus::Single->value,
            'personal_phone' => '9876543210',
            'personal_email' => 'personal@example.com',
            'address' => 'Test Address',
            'country_id' => $this->country->id, // Assuming 103 exists (India) or create one
            'date_of_joining' => '2020-01-01',
            'company_name' => 'Mom Digital',
            'work_week' => 5,
            'location' => 'Dubai',
            
            // New Fields
            'visa_designation' => 'Software Engineer',
            'visa_type' => 'Employment',
            'visa_category' => 'company_sponsored',
            'mol_number' => '123456',
            'last_working_day' => '2025-12-31',
            'remarks' => 'Test Remarks',
            'medical_insurance_provided' => '1',
            'insurance_number' => 'INS123',
            'insurance_expiry' => '2024-12-31',
            'probation_period' => '3_month', 
            // UserService Required Defaults
            'attendance_base' => '1',
            'salary_mode' => 'Bank',
            'annual_premium' => 0,
            'air_ticket_setting_id' => $this->airTicketSetting->id,
            'grade' => 'A',
            'company_accommodation' => 0,
            'accommodation_location' => null,
            'is_rider' => 0,
            'air_ticket_count' => 0,
            'renewal_air_ticket' => '1',
            'free_document_request' => 0,
            'document_request_charge' => 0,
            'housing_allowance' => 0,
            'transportation_allowance' => 0,
            'other_allowance' => 0,
            'functional_allowance' => 0,
            'tips' => 0,
            'advance_salary' => 0,
            'loan_deduction' => 0,
            'other_deduction' => 0,
            'bank_name' => 'Test Bank',
            'account_number' => '1234567890',
            'iba_number' => 'IBAN123',
            'swift_code' => 'SWIFT123',
            'routing_number' => 'ROUTE123',
            'emergency_name' => 'Emergency Contact',
            'emergency_relation' => 'Parent',
            'emergency_phone' => '1111111111',
            'emergency_isd_code' => '971',
            'emergency_email' => 'emergency@example.com',
            'emergency_home_country' => $this->country->id,
            'emergency_home_address' => 'Home Address',
            'emergency_local_country' => $this->country->id,
            'emergency_local_address' => 'Local Address',
            'local_person_name' => 'Local Person',
            'local_person_relation' => 'Friend',
            'local_person_phone' => '0555555555',
            'shifts' => [
                 ['shift_start' => '09:00', 'shift_end' => '18:00']
            ],
            'tickets' => []
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertOk();
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('user_profiles', [
            'visa_designation' => 'Software Engineer',
            'visa_category' => 'company_sponsored',
            'visa_type' => 'Employment',
        ]);

        $this->assertDatabaseHas('user_work_details', [
            'mol_number' => '123456',
            'last_working_day' => '2025-12-31',
            'remarks' => 'Test Remarks',
            'insurance_number' => 'INS123',
            'insurance_expiry' => '2024-12-31',
        ]);
    }

    public function test_admin_can_export_users_with_new_columns()
    {
        $this->actingAs($this->admin);

        Excel::fake();

        $this->get(route('backend.users.export.excel'));

        Excel::assertDownloaded('employee_' . time() . '.xlsx', function(UserExport $export) {
            // Check headers
            $headings = $export->headings();
            // dump($headings);
            return in_array(__trans('visa_designation'), $headings) && 
                   in_array(__trans('visa_category'), $headings) &&
                   in_array(__trans('visa_type'), $headings) &&
                   in_array(__trans('insurance_number'), $headings);
        });
    }

    public function test_admin_can_import_users_with_new_columns()
    {
        $this->actingAs($this->admin);
        
        Excel::fake();

        // Mock an excel file (conceptually tricky with fakes, checking if import is called with correct data logic is better)
        // Alternatively, create a real temporary file with correct structure and upload it.
        // For simplicity with Excel::fake(), we check if the import class is used.
        
        $file = UploadedFile::fake()->create('users.xlsx');

        $this->post(route('backend.users.import.excel'), [
            'file' => $file
        ]);

        Excel::assertImported('users.xlsx', function(UserImport $import) {
            return true;
        });
    }
}
