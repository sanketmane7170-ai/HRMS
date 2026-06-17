<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Manage Document', 'Create Document', 'Edit Document', 'Delete Document'];
        foreach($names as $name){
            Permission::updateOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
