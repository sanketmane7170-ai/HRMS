<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DashboardViewPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Dashboard Leave'];
        foreach ($names as $name) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
