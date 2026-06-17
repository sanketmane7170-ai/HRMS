<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $department = Department::firstOrCreate(
            ['name' => 'IT'],
            ['slug' => 'it', 'code' => $faker->ean8()]
        );

        $designations = [
            'Manager',
            'Team Lead',
            'Server Admin'
        ];

        foreach ($designations as $designation) {
            Designation::firstOrCreate(
                ['name' => $designation, 'department_id' => $department->id],
                ['code' => $faker->ean8()]
            );
        }
    }
}
