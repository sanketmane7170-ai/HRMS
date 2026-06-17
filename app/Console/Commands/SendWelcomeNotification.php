<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\User\WelcomeNotification;
use App\Notifications\User\WelcomeNotificationImmediately;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWelcomeNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-welcome-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
            })->where('status', User::STATUS_ACTIVE)
            ->get();

        $response = Http::asForm()->post('https://superadmin.WorkPilot.io/api/v1/portal_data', [
            'subdomain' => request()->getHost()
        ]);
        $unique_code = "";
        if ($response->successful()) {
            $unique_code = $response->json('data.data.unique_code');
        } else {
            Log::error('Failed to fetch portal data', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }
        // $data = $response->json();
        // dd($unique_code);


        // foreach ($users as $user) {
        //     if ($user->id == 38) {
        //         $this->info('[' . now() . '] user: ' . json_encode($user));
        //         // $user->notifyNow(new WelcomeNotificationImmediately());
        //         try {
        //             $user->notifyNow(new \App\Notifications\User\WelcomeNotificationImmediately());
        //             Log::info('Notification sent (notifyNow) to ' . $user->email);
        //         } catch (\Throwable $e) {
        //             Log::error('Notification send failed for ' . $user->email, [
        //                 'error' => $e->getMessage(),
        //                 'trace' => $e->getTraceAsString(),
        //             ]);
        //         }
        //     }
        // }
        $unique_code = $response->json('data.data.unique_code') ?? null;

        // create a password (or fetch real password if you create & store it)
        $password = 'Welcome' . date('Y');

        foreach ($users as $user) {
            if ($user->id == 38) {
                $this->info('[' . now() . '] user: ' . json_encode($user));

                try {
                    // pass employee_id, password and unique_code to the notification
                    $user->notifyNow(new \App\Notifications\User\WelcomeNotificationImmediately(
                        $user->employee_id,
                        $user->email,
                        $user->phone,
                        $password,
                        $unique_code,
                    ));

                    Log::info('Notification sent (notifyNow) to ' . $user->email . ' user_id:' . $user->id);
                } catch (\Throwable $e) {
                    Log::error('Notification send failed for ' . $user->email, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }
    }
}
