<?php

namespace Modules\IndianPayroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class IndianPayrollPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'Manage Indian Payroll',          // master permission, gates the module dashboard/menu
            'Manage Employee Statutory Profile',
            'Manage Salary Structures',
            'Manage Statutory Settings',       // PF/ESI/PT/LWF/Gratuity/Tax-slab configuration
            'Run Payroll',
            'Approve Payroll',
            'Lock Payroll',
            'View Payslips',
            'Verify Tax Declarations',
            'Manage Full Final Settlement',
            'Export Compliance Reports',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Matches this app's actual seeded role names (lowercase, no spaces — verified
        // against spatie's roles table; 'Super Admin'/'HR Manager' do not exist here).
        $rolesToAssign = ['superadmin', 'admin', 'hr'];

        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Employees only need to view their own payslips/declarations — handled by
        // self-service controllers scoped to auth()->id(), not by these admin permissions.
    }
}
