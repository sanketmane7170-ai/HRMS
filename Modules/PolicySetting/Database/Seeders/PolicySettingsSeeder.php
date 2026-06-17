<?php

namespace Modules\PolicySetting\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\PolicySetting\Entities\PolicySettings;

class PolicySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $policy_settings = [
            // [
            //     'type' => 'leave',
            //     'name' => 'leave_policy_1',
            //     'policy' => 'Within 6 months accrual of 2 days.',
            //     'status' => 0,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'type' => 'leave',
            //     'name' => 'leave_policy_2',
            //     'policy' => 'More than 6 months accrual of 2.5 days.',
            //     'status' => 0,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'type' => 'leave',
            //     'name' => 'leave_policy_3',
            //     'policy' => 'Upto 1 Year accrual of 2.5 days',
            //     'status' => 0,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'type' => 'leave',
            //     'name' => 'leave_policy_4',
            //     'policy' => 'No Leave Allowed in probation.',
            //     'status' => 0,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'type' => 'leave',
            //     'name' => 'leave_policy_5',
            //     'policy' => 'Addition of 30 days annual leave under no obligation.',
            //     'status' => 0,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'type' => 'leave',
            //     'name' => 'leave_policy_6',
            //     'policy' => 'Previous Year balance to be added.',
            //     'status' => 0,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            [
                'type' => 'attendance',
                'name' => 'attendance_policy_1',
                'policy' => 'Late comers by 15 mt ,30 to be considered as 1 hour or 2 hours late.',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'attendance',
                'name' => 'attendance_policy_2',
                'policy' => 'Payroll to be run by attendance basis or no attendance basis.',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($policy_settings as $policy_setting) {
            
            PolicySettings::firstOrCreate(
                ['type' => $policy_setting['type'],'name' => $policy_setting['name'],], 
                $policy_setting                    
            );
        }
        
    }
}