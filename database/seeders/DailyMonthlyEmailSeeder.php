<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DailyMonthlyEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $daily_emails = [
            'Leave Daily Email',
            'Attendance Daily Email',
            'Late Comers Daily Email',
            'Early Comers Daily Email',
            'Expense Daily Email',
        ];
        foreach ($daily_emails as $daily_email) {
            // Check if the permission already exists
            if (!Permission::where('name', $daily_email)->where('guard_name', 'web')->exists()) {
                Permission::updateOrCreate(['name' => $daily_email, 'guard_name' => 'web']);
            }
        }
        $monthly_emails = [
            'Leave Monthly Email',
            'Attendance Monthly Email',
            'Late Comers Monthly Email',
            'Early Comers Monthly Email',
            'Salary Increments Monthly Email',
            'Expense Monthly Email',
            'Gratuity Accrual Monthly Email',
            'Medical Insurance Accrual Monthly Email',
            'Air Ticket Accrual Monthly Email',
            'Leave Salary Accrual Monthly Email',
            'Accrual Monthly Email',
            'PH Leave Monthly Email',
            'Leave Balance Monthly Email',
        ];
        foreach ($monthly_emails as $monthly_email) {
            // Check if the permission already exists
            if (!Permission::where('name', $monthly_email)->where('guard_name', 'web')->exists()) {
                Permission::updateOrCreate(['name' => $monthly_email, 'guard_name' => 'web']);
            }
        }
    }
}
