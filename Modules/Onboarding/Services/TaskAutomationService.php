<?php

namespace Modules\Onboarding\Services;

use Modules\Task\Entities\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TaskAutomationService
{
    /**
     * Create onboarding-related tasks for IT and Finance departments.
     * 
     * Author: Sanket
     */
    public static function createProvisioningTasks(User $candidate)
    {
        try {
            // 1. IT Task: Equipment Setup
            Task::create([
                'title' => "IT Provisioning: " . $candidate->name,
                'description' => "Prepare laptop, email account, and system access for " . $candidate->name . ". Expected joining: " . ($candidate->onboardingRecord->joining_date ?? 'TBD'),
                'priority' => 'high',
                'status' => 'pending',
                'end_date' => Carbon::now()->addDays(3)->toDateString(),
                'assigned_by' => auth()->id() ?? User::whereHas('roles', fn($q) => $q->where('name', 'Super Admin'))->first()->id,
            ]);

            // 2. Finance Task: Payroll Setup
            Task::create([
                'title' => "Payroll Onboarding: " . $candidate->name,
                'description' => "Verify IBAN and setup employee in payroll system. Candidate and Visa details are ready.",
                'priority' => 'medium',
                'status' => 'pending',
                'end_date' => Carbon::now()->addDays(5)->toDateString(),
                'assigned_by' => auth()->id() ?? User::whereHas('roles', fn($q) => $q->where('name', 'Super Admin'))->first()->id,
            ]);

            Log::info("Provisioning tasks created for User ID: " . $candidate->id);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create provisioning tasks: " . $e->getMessage());
            return false;
        }
    }
}
