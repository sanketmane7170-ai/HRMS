<?php

namespace Modules\Apparel\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ApparelDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Manage Apparel', 'Create Apparel', 'Edit Apparel', 'Delete Apparel'];
        foreach ($names as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
