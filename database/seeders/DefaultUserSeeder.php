<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates the core WorkPilot administrative team and 
     * a pool of 25 demo employees with standardized credentials.
     */
    public function run(): void
    {
        // 1. Prepare common dependencies
        $departmentId = Department::value('id') ?? 1;
        $designationId = Designation::value('id') ?? 1;
        $countryId = Country::value('id') ?? 1;
        $password = Hash::make('Sanket@3030');

        // 2. Create Super Admin
        $this->createUser([
            'name'     => 'Super Admin',
            'email'    => 'contactsanket1@gmail.com',
            'username' => 'super-admin',
            'role'     => 'superadmin'
        ], $password, $departmentId, $designationId, $countryId);

        // 3. Create Admin
        $this->createUser([
            'name'     => 'WorkPilot Admin',
            'email'    => 'admin@workpilot.com',
            'username' => 'admin-user',
            'role'     => 'admin'
        ], $password, $departmentId, $designationId, $countryId);

        // 4. Create HR Manager
        $this->createUser([
            'name'     => 'WorkPilot HR',
            'email'    => 'hr@workpilot.com',
            'username' => 'hr-user',
            'role'     => 'hr'
        ], $password, $departmentId, $designationId, $countryId);

        // 5. Create Line Manager
        $this->createUser([
            'name'     => 'WorkPilot Manager',
            'email'    => 'manager@workpilot.com',
            'username' => 'line-manager',
            'role'     => 'linemanager'
        ], $password, $departmentId, $designationId, $countryId);

        // 6. Create 25 Employees
        $this->command->info('Seeding 25 employees...');
        for ($i = 1; $i <= 25; $i++) {
            $this->createUser([
                'name'     => "Employee $i",
                'email'    => "employee$i@workpilot.com",
                'username' => "employee-$i",
                'role'     => 'employee'
            ], $password, $departmentId, $designationId, $countryId);
        }

        $this->command->info('✅ DefaultUserSeeder completed successfully.');
    }

    /**
     * Helper to create user, profile, work details and assign role
     */
    private function createUser($data, $password, $dept, $desig, $country)
    {
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'           => $data['name'],
                'username'       => $data['username'],
                'password'       => $password,
                'status'         => 'active',
                'department_id'  => $dept,
                'designation_id' => $desig,
            ]
        );

        // Assign Role
        $user->syncRoles($data['role']);

        // Create Profile if not exists
        if (!$user->profile) {
            $user->profile()->create([
                'country_id'    => $country,
                'gender'        => ($data['role'] === 'employee' && ($user->id % 2 === 0)) ? 'Female' : 'Male', // Just for variety
                'date_of_birth' => '1990-01-01',
            ]);
        }

        // Create Work Details if not exists
        if (!$user->workDetail) {
            $user->workDetail()->create([
                'joining_date'       => now()->subYear()->toDateString(),
                'probation_end_date' => now()->addMonths(6)->toDateString(),
            ]);
        }

        return $user;
    }
}
