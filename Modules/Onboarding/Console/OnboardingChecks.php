<?php

namespace Modules\Onboarding\Console;

use Illuminate\Console\Command;
use Modules\Onboarding\Entities\VisaProcess;
use Modules\Onboarding\Entities\ComplianceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OnboardingChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onboarding:daily-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring Visas and OHC cards.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Onboarding Daily Checks...');
        
        // 1. Check Visa Expiry (Warning at 90, 60, 30 days)
        $expiryCheckDays = [90, 60, 30, 7];
        foreach ($expiryCheckDays as $days) {
            $targetDate = Carbon::today()->addDays($days);
            
            VisaProcess::whereDate('visa_expiry_date', $targetDate)
                ->with('user')
                ->chunk(100, function ($visas) use ($days) {
                    foreach ($visas as $visa) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($visa->user->email)->send(new \Modules\Onboarding\Emails\VisaExpiringSoon($visa->user, $days));
                            Log::info("Visa Expiry Notification sent to UID: " . $visa->user_id . " for {$days} days.");
                        } catch (\Exception $e) {
                            Log::error("Failed to send Visa Expiry notification to UID {$visa->user_id}: " . $e->getMessage());
                        }
                    }
                });
        }
        
        // 2. Check OHC Expiry (Warning at 30, 15, 7 days)
        $ohcCheckDays = [30, 15, 7];
        foreach ($ohcCheckDays as $days) {
            $targetOhcDate = Carbon::today()->addDays($days);
            
            ComplianceRecord::whereDate('ohc_expiry_date', $targetOhcDate)
                ->with('user')
                ->chunk(100, function ($records) use ($days) {
                    foreach ($records as $ohc) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($ohc->user->email)->send(new \Modules\Onboarding\Emails\OHCExpiringSoon($ohc->user, $days));
                            Log::info("OHC Expiry Notification sent to UID: " . $ohc->user_id . " for {$days} days.");
                        } catch (\Exception $e) {
                            Log::error("Failed to send OHC Expiry notification to UID {$ohc->user_id}: " . $e->getMessage());
                        }
                    }
                });
        }
        
        // 3. Probation Review Trigger (80 Days Post-Join)
        $targetJoinDate = Carbon::today()->subDays(80);
        
        \App\Models\UserWorkDetail::whereDate('joining_date', $targetJoinDate)
            ->with(['user.department'])
            ->chunk(100, function ($workDetails) {
                foreach ($workDetails as $workDetail) {
                     // Avoid duplicates for same cycle
                     $exists = \Modules\Onboarding\Entities\ProbationReview::where('employee_id', $workDetail->user_id)
                                 ->where('status', 'pending')
                                 ->exists();
                    
                     if (!$exists) {
                         $review = \Modules\Onboarding\Entities\ProbationReview::create([
                             'employee_id' => $workDetail->user_id,
                             'scheduled_date' => Carbon::today()->addDays(10),
                             'status' => 'pending',
                             'cycle_number' => 1
                         ]);

                         // Notify Manager
                         if ($workDetail->report_to_ids && is_array($workDetail->report_to_ids) && count($workDetail->report_to_ids) > 0) {
                             $manager = \App\Models\User::find($workDetail->report_to_ids[0]);
                             if ($manager) {
                                 try {
                                     \Illuminate\Support\Facades\Mail::to($manager->email)->send(new \Modules\Onboarding\Emails\ProbationReviewReminder($manager, $workDetail->user, $review));
                                     Log::info("Probation Review Reminder sent to Manager UID: " . $manager->id);
                                 } catch (\Exception $e) {
                                     Log::error("Failed to send Probation Reminder: " . $e->getMessage());
                                 }
                             }
                         }
                         Log::info("Probation Review Record Created for User: " . $workDetail->user_id);
                     }
                }
            });

        // 4. Missing Document Alert (7 Days Post-Join)
        $complianceCkDate = Carbon::today()->subDays(7);
        
        // Use UserWorkDetail as SSOT for joining_date
        \App\Models\UserWorkDetail::whereDate('joining_date', $complianceCkDate)
            ->with('user')
            ->chunk(100, function ($details) {
                foreach ($details as $workDetail) {
                    $hasIban = \App\Models\UserDocument::where('user_id', $workDetail->user_id)
                                ->where('type', \App\Enums\Document::IbanCertificate)
                                ->exists();
                    
                    if (!$hasIban) {
                        Log::warning("Compliance Alert: IBAN missing for user " . $workDetail->user_id . " after 7 days.");
                        // Trigger alert to Admin/Finance if needed
                    }
                }
            });

        $this->info('Checks complete.');
    }
}
