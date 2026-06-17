<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $logFile = storage_path('logs/laravel.log');  // Use a fixed log file

        // every 5 minutes
        $schedule->command('app:operation-checkin')->everyFiveMinutes()->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);

        // Shift observer runs every 10 minutes
        $schedule->command('command:shift_observer')->everyTenMinutes()->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);

        // Daily tasks
        $schedule->command('app:auto-add-user-extra-work')->dailyAt('07:01')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
        $schedule->command('email:daily-mail')->dailyAt('06:00')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
        $schedule->command('leave:update-balance')->dailyAt('04:01')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
        $schedule->command('app:check-user-birthdays')->dailyAt('03:30')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
        $schedule->command('app:check-today-is-user-offday')->dailyAt('04:40')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
        $schedule->command('app:auto-add-air-ticket')->dailyAt('06:04')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
        $schedule->command('app:auto-add-user-attendance-warnings')->dailyAt('06:20')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);

        // Monthly task
        $schedule->command('email:monthly-mail')->monthlyOn(1, '06:05')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);

        // Document expiration and operations check-in
        $schedule->command('filemanager:doc-exprire')->dailyAt('06:06')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);

        $schedule->command('app:assign-kpis-to-employees')->dailyAt('06:10')->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
        
        // BUG-ONB-016 Fix: Sync onboarding data from recruitment module hourly - Author: Sanket
        $schedule->call(function () {
            try {
                $controller = app(\Modules\Onboarding\Http\Controllers\OnboardingController::class);
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('syncRecruitmentData');
                $method->setAccessible(true);
                $method->invoke($controller);
                
                \Log::info('Scheduled onboarding sync completed');
            } catch (\Exception $e) {
                \Log::error('Scheduled onboarding sync failed: ' . $e->getMessage());
            }
        })->hourly()->withoutOverlapping()->onOneServer()->appendOutputTo($logFile);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
