<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class EditUpdateLeavePermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Edit Update Leave Balance EditUpdateLeave','View Leave Update Logs EditUpdateLeave'];
        foreach($names as $name){
            Permission::updateOrCreate(['name' => $name]);
        }
    }
}
