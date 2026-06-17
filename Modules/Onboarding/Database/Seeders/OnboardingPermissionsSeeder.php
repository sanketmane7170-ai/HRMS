<?php

namespace Modules\Onboarding\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OnboardingPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Define Permissions
        $permissions = [
            'Manage Onboarding', // Added by Sanket - Master permission
            'view-onboarding-tracker',
            'manage-visa-process',
            'approve-documents',
            'convert-candidate',
            'manage-probation-reviews',
            'view-secure-documents',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Setup Roles
        $rolesToAssign = ['Super Admin', 'admin', 'hr', 'HR Manager'];

        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // 3. Create New Hire Role (for portal access)
        Role::firstOrCreate(['name' => 'new-hire', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'new-hire', 'guard_name' => 'portal']);

        // Employee/New Hire should NOT have these permissions
    }
}
