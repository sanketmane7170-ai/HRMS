<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ShiftPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Manage Shift Shift','Manage Scheduling Shift'];
        foreach($names as $name){
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
