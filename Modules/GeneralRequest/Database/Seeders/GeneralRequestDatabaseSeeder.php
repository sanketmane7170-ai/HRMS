<?php

namespace Modules\GeneralRequest\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class GeneralRequestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Manage General Request', 'Create General Request', 'Edit General Request', 'Delete General Request'];
        foreach ($names as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
