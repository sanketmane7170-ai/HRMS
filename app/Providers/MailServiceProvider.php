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
            // Only override when SMTP has actually been configured (host present),
            // otherwise fall back to the .env / config/mail.php defaults.
            if (getSetting('smtp_host')) {
                $driver = getSetting('smtp_driver') ?: 'smtp';

                // Override individual keys on the existing Laravel mail config
                // structure (mail.default + mail.mailers.smtp.*). Replacing the
                // whole "mail" array would wipe out "mailers"/"default" and break
                // the mailer in Laravel 10+.
                Config::set('mail.default', $driver);
                Config::set("mail.mailers.{$driver}.transport", $driver);
                Config::set("mail.mailers.{$driver}.host", getSetting('smtp_host'));
                Config::set("mail.mailers.{$driver}.port", getSetting('smtp_port'));
                Config::set("mail.mailers.{$driver}.username", getSetting('smtp_username'));
                Config::set("mail.mailers.{$driver}.password", getSetting('smtp_password'));
                Config::set("mail.mailers.{$driver}.encryption", getSetting('smtp_encryption') ?: null);

                Config::set('mail.from.name', getSetting('smtp_sender_name') ?: getSetting('site_title'));
                Config::set('mail.from.address', getSetting('smtp_sender_email') ?: getSetting('site_suppor_email'));
            }
        }
    }
}
