<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ManagerAccess extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Leave Request Manager Access','Attendance Report Manager Access', 'Document Request Manager Access', 'General Request Manager Access'];
        foreach($names as $name){
            Permission::updateOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
