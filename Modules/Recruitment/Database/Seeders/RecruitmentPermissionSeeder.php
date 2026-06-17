<?php

namespace Modules\Recruitment\Database\Seeders;

use Illuminate\Database\Seeder;

class RecruitmentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds recruitment-Related permissions and assigns them to roles - Sanket
     */
    public function run(): void
    {
        // Basic recruitment permissions following the project pattern
        $recruitmentPermissions = [
            // Module Root Permissions (matching sidebar and config)
            ['name' => 'Manage Recruitment'],
            ['name' => 'Create Recruitment'],
            ['name' => 'Edit Recruitment'],
            ['name' => 'Delete Recruitment'],
            ['name' => 'View Recruitment'],

            // Job Management
            ['name' => 'Manage Recruitment Jobs'],
            ['name' => 'Create Recruitment Jobs'], 
            ['name' => 'Edit Recruitment Jobs'],
            ['name' => 'Delete Recruitment Jobs'],
            ['name' => 'View Recruitment Jobs'],
            
            // Application Management
            ['name' => 'Manage Applications'],
            ['name' => 'View Applications'],
            ['name' => 'Move Application Stage'],
            ['name' => 'Add Application Notes'],
            ['name' => 'Export Applications'],
            
            // Employee Features
            ['name' => 'Apply Internal Jobs'],
            ['name' => 'View Internal Jobs'],
            ['name' => 'Track Application Status'],
            
            // Interview Management
            ['name' => 'Manage Interviews'],
            ['name' => 'Schedule Interviews'],
            ['name' => 'Submit Interview Feedback'],
            ['name' => 'View Interview Details'],
            ['name' => 'Conduct Interviews'],
            
            // Offer Management
            ['name' => 'Manage Offers'],
            ['name' => 'Generate Offers'],
            ['name' => 'Approve Offers'],
            ['name' => 'View Offers'],
            
            // Reporting & Analytics
            ['name' => 'View Recruitment Reports'],
            ['name' => 'Export Recruitment Data'],
            ['name' => 'View Recruitment Analytics'],
            
            // Administrative
            ['name' => 'Manage Recruitment Settings'],
            ['name' => 'View All Recruitment Data']
        ];

        foreach($recruitmentPermissions as $permission) {
            \Spatie\Permission\Models\Permission::updateOrCreate(
                ['name' => $permission['name']],
                ['guard_name' => 'web']
            );
        }
        
        // Assign permissions to existing roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to existing roles based on the project pattern.
     */
    private function assignPermissionsToRoles(): void
    {
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin')->first();
        $hrRole = \Spatie\Permission\Models\Role::where('name', 'HR')->first();  
        $employeeRole = \Spatie\Permission\Models\Role::where('name', 'Employee')->first();
        $managerRole = \Spatie\Permission\Models\Role::where('name', 'Manager')->first();

        // Admin gets all recruitment permissions
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'Manage Recruitment', 'Create Recruitment', 'Edit Recruitment', 'Delete Recruitment', 'View Recruitment',
                'Manage Recruitment Jobs', 'Create Recruitment Jobs', 'Edit Recruitment Jobs', 
                'Delete Recruitment Jobs', 'View Recruitment Jobs', 'Manage Applications', 
                'View Applications', 'Move Application Stage', 'Add Application Notes',
                'Export Applications', 'Manage Interviews', 'Schedule Interviews',
                'Submit Interview Feedback', 'View Interview Details', 'Conduct Interviews',
                'Manage Offers', 'Generate Offers', 'Approve Offers', 'View Offers',
                'View Recruitment Reports', 'Export Recruitment Data', 'View Recruitment Analytics',
                'Manage Recruitment Settings', 'View All Recruitment Data'
            ]);
        }

        // HR gets core recruitment management permissions
        if ($hrRole) {
            $hrRole->givePermissionTo([
                'Manage Recruitment', 'Create Recruitment', 'View Recruitment',
                'Manage Recruitment Jobs', 'Create Recruitment Jobs', 'Edit Recruitment Jobs',
                'View Recruitment Jobs', 'Manage Applications', 'View Applications',
                'Move Application Stage', 'Add Application Notes', 'Export Applications',
                'Manage Interviews', 'Schedule Interviews', 'View Interview Details',
                'Manage Offers', 'Generate Offers', 'View Offers',
                'View Recruitment Reports', 'Export Recruitment Data', 'View Recruitment Analytics'
            ]);
        }

        // Employees get basic application permissions
        if ($employeeRole) {
            $employeeRole->givePermissionTo([
                'Apply Internal Jobs', 'View Internal Jobs', 'Track Application Status'
            ]);
        }

        // Managers get interview and evaluation permissions
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'View Recruitment Jobs', 'View Applications', 'Add Application Notes',
                'Submit Interview Feedback', 'View Interview Details', 'Conduct Interviews',
                'View Offers', 'View Recruitment Reports'
            ]);
        }
    }
}
