<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * The 'admin' role used to bypass every gate via Gate::before(). That
     * shortcut has been removed so admins are now permission-driven. To keep
     * every CURRENT admin's access unchanged, grant the admin role all existing
     * web-guard permissions. A superadmin can then remove modules per role.
     */
    public function up(): void
    {
        // Make sure permissions the sidebar checks but that were never seeded
        // exist, so they can be assigned via the Roles picker (and granted to
        // existing admins just below).
        foreach ([
            'Manage Company Document',
            'Manage Training',
            'Manage Performance Review',
            'Manage Task',
            'Manage International Payroll',
            'Manage Company Policy',
            'Manage Resignation',
        ] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if (! $admin) {
            return;
        }

        $permissions = Permission::where('guard_name', 'web')->pluck('name')->all();
        $admin->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverting strips all permissions from the admin role. The previous
     * god-mode behaviour lived in code (Gate::before), not data, so there is no
     * data state to restore here.
     */
    public function down(): void
    {
        $admin = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if ($admin) {
            $admin->syncPermissions([]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
