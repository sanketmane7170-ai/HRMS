<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'User', 'Role', 'Department', 'Designation', 'Dependent', 'Asset', 'Asset Type', 'Asset Manufacturer',
            'User Document', 'Announcement Type', 'Announcement', 'Leave Type', 'Leave', 'Apparel', 'Attendance',
            'Holiday', 'Document Type', 'Warning','Performance'
        ];

        $commonPermission = ["Manage", "Create", "Edit", 'Delete'];

        foreach ($modules as $module) {
            foreach ($commonPermission as $cp) {
                $name = "$cp $module";
                Permission::firstOrCreate(['name' => $name]);
            }
        }

        $arrPermissions = [
            ['name' => 'Export User'],
            ['name' => "Assign Manager Department"],
            ['name' => 'Export Department'],
            ['name' => 'Import Department'],
            ['name' => 'Export Designation'],
            ['name' => 'Import Designation'],
            ['name' => 'Manage Languages'],
            ['name' => 'Export Attendance'],
            ['name' => 'Generate Attendance'],
            ['name' => 'Manage Document Request'],
            ['name' => 'View Document Request'],
            ['name' => 'Manage Settings'],
            ['name' => 'General Settings'],
            ['name' => 'Smtp Settings'],
            ['name' => 'Advance Settings'],
            ['name' => 'Clear Cache Settings'],
            ['name' => 'Mamage Document Request'], // Note: Typo?
            ['name' => 'Show Document Request'],
            ['name' => 'Generate Document Request'],
            ['name' => 'Assign Asset'],
            ['name' => 'Manage Salary'],
            ['name' => 'Create Salary'],
            ['name' => 'Edit Salary'],
            ['name' => 'Documents User'],
            ['name' => 'Salary Details User'],
            ['name' => 'Leave User'],
            ['name' => 'Service History User'],
            ['name' => 'Assets Details User'],
            ['name' => 'Dependent Details User'],
            ['name' => 'Teams User'],
            ['name' => 'Hierarchy User'],
            ['name' => 'Hierarchy1 User'],
        ];

        foreach ($arrPermissions as $data) {
            Permission::firstOrCreate($data);
        }

        // Assign permissions to employee role if it exists
        if ($role = Role::where('name', 'employee')->first()) {
            $role->givePermissionTo([
                'Manage Dependent',
                'Create Dependent',
                'Edit Dependent',
                'Manage User Document',
                'Create User Document',
                'Create Leave',
                'Edit Leave',
                'Delete Leave'
            ]);
        }
    }
}
