<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserWorkDetail;
use App\Models\UserDocument;
use App\Models\UserBankDetail;
use App\Enums\Document;
use Modules\Payroll\Entities\UserSalary;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterSheetExport;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MasterSheetExportTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Department, Designation, Division
        $department = \App\Models\Department::create(['name' => 'IT']);
        $designation = \App\Models\Designation::create(['name' => 'Developer']);
        $division = \App\Models\Division::create(['name' => 'Tech']);

        // Create Roles first because Factory uses them
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        if (!Role::where('name', 'Employee')->exists()) {
            Role::create(['name' => 'Employee', 'guard_name' => 'web']);
        }

        // Create Admin
        $this->admin = User::factory()->create([
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'division_id' => $division->id
        ]);
        $this->admin->assignRole('admin');

        // Create Employee
        $this->employee = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'division_id' => $division->id
        ]);
        $this->employee->assignRole('Employee');

        // Create Country
        $country = \App\Models\Country::create(['name' => 'UAE', 'code' => 'AE', 'phone_code' => 971]);

        // Create Profile
        UserProfile::create([
            'user_id' => $this->employee->id,
            'gender' => 'Male',
            'personal_phone' => '0987654321',
            'date_of_birth' => '1990-01-01',
            'visa_category' => 'company_sponsored',
            'visa_designation' => 'Developer',
            'country_id' => $country->id,
        ]);

        // Create Work Detail
        UserWorkDetail::create([
            'user_id' => $this->employee->id,
            'joining_date' => '2023-01-01',
            'location' => 'Dubai',
            'mol_location' => 'Dubai MOL',
            'mol_number' => 'MOL123',
        ]);

        // Create Bank Detail
        UserBankDetail::create([
            'user_id' => $this->employee->id,
            'bank_name' => 'Test Bank',
            'account_number' => '123456',
        ]);

        // Create Salary
        UserSalary::create([
            'user_id' => $this->employee->id,
            'basic' => 5000,
            'hra' => 2000,
            'travel_allowance' => 1000,
            'other_allowance' => 500,
            'food_allowance' => 500,
        ]);

        // Create Documents
        UserDocument::create([
            'user_id' => $this->employee->id,
            'type' => Document::Passport,
            'serial_number' => 'P123456',
            'expiry_date' => '2030-01-01',
        ]);
    }

    public function test_admin_can_download_master_sheet_export()
    {
        Excel::fake();

        $response = $this->actingAs($this->admin)->get(route('backend.users.export.master'));

        $response->assertStatus(200);

        Excel::assertDownloaded('master_sheet_' . time() . '.xlsx', function(MasterSheetExport $export) {
            // Assert that the collection contains our employee
            return $export->collection()->contains('id', $this->employee->id);
        });
    }

    public function test_master_sheet_export_content()
    {
        $export = new MasterSheetExport();
        $collection = $export->collection();
        $mapped = $export->map($collection->firstWhere('id', $this->employee->id));

        // Basic Assertions
        $this->assertEquals($this->employee->employee_id, $mapped[1]); // Emp Id
        $this->assertEquals('John', $mapped[2]); // First Name
        $this->assertEquals('Doe', $mapped[3]); // Last Name
        $this->assertEquals('Male', $mapped[4]); // Gender
        $this->assertEquals('john.doe@example.com', $mapped[5]); // Email

        // Salary Assertions
        $this->assertEquals(9000, $mapped[27]); // Total Salary (5000+2000+1000+500+500)
        $this->assertEquals(5000, $mapped[28]); // Basic
        $this->assertEquals(500, $mapped[29]); // Functional (Other)
        $this->assertEquals(1000, $mapped[30]); // Transport
        $this->assertEquals(2000, $mapped[31]); // Housing (HRA)

        // Document Assertions
        $this->assertEquals('P123456', $mapped[32]); // Passport No
    }
}
