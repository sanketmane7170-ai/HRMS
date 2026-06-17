<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class UserSalaryEndOfServicePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'Create Salary User']);
        Permission::firstOrCreate(['name' => 'Edit Salary User']);
        Permission::firstOrCreate(['name' => 'View Salary User']);
        Permission::firstOrCreate(['name' => 'End of Service User']);
    }
}
