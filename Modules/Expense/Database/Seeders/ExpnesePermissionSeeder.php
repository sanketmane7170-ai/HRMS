<?php

namespace Modules\Expense\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ExpnesePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $names = ['Permission Expense','Manage Expense', 'Create Expense', 'Edit Expense', 'Delete Expense'];
        foreach ($names as $name) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
