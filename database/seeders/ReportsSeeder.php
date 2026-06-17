<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class ReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reports = [
            'Leave Reports',
            'Attendance Reports',
            'Late Comers Reports',
            'Early Comers Reports',
            'Salary Increments Reports',
            'Expense Reports',
            'Gratuity Reports',
        ];
        foreach ($reports as $report) {
            // Check if the permission already exists
            if (!Permission::where('name', $report)->where('guard_name', 'web')->exists()) {
                Permission::firstOrCreate(['name' => $report, 'guard_name' => 'web']);
            }
        }
    }
}
