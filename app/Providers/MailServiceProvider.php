<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            $exists = Setting::where('key', 'like', "%smtp_%")->exists();
            if ($exists) {
                $config = array(
                    'driver'     =>     getSetting('smtp_driver'),
                    'host'       =>    getSetting('smtp_host'),
                    'port'       =>     getSetting('smtp_port'),
                    'username'   =>     getSetting('smtp_username'),
                    'password'   =>     getSetting('smtp_password'),
                    'encryption' =>     getSetting('smtp_encryption'),
                    'from'       =>     [
                        'name' => getSetting('smtp_sender_name') ?? getSetting('site_title'),
                        'address' => getSetting('smtp_sender_email') ?? getSetting('site_suppor_email')
                    ]
                );
                Config::set('mail', $config);
            }
        }
    }
}
