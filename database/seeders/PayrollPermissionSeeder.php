<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PayrollPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Set Salary and PayRoll Payroll','Generate SIF Payroll','Export Payroll'];
        foreach($names as $name){
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
