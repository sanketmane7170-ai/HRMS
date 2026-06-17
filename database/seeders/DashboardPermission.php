<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DashboardPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['View Dashboard'];
        foreach ($names as $name) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
