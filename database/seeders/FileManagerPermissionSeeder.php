<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FileManagerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Manage FileManager','Download FileManager', 'Create FileManager', 'Edit FileManager', 'Delete FileManager'];
        foreach($names as $name){
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
