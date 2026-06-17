<?php

namespace Modules\Expense\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ExpneseTypePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Manage Expense Type', 'Create Expense Type', 'Edit Expense Type', 'Delete Expense Type'];
        foreach ($names as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
