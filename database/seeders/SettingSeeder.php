<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = array(
            'site_title' => 'WorkPilot',
            'site_email' => 'support@workpilot.app',
            'site_phone' => '+91 9874561230',
            'site_address' => 'Dummy Address, ',
            'site_support_email' => 'support@project.com',
            'site_short_description' => 'You Tag Line',
            'site_timezone' => config('app.timezone'),
            'site_debug_mode' => config('app.debug'),

            ////Smtp Details
            'smtp_driver'     =>     'smtp',
            'smtp_host'       =>    env('SMTP_HOST', ''),
            'smtp_port'       =>     '587',
            'smtp_username'   =>     env('SMTP_USERNAME', ''),
            'smtp_password'   =>     env('SMTP_PASSWORD', ''),
            'smtp_encryption' =>     'tls',
            'smtp_sender_name' => env('APP_NAME'),
            'smtp_sender_email' => 'no-reply@workpilot.app',
            'smtp_test_email' => 'support@asktech.tech',
            ///// stripe
            'stripe_key' => env('STRIPE_KEY', ''),
            'stripe_secret' => env('STRIPE_SECRET', ''),
            'stripe_currency' => 'EUR',
            'firebase_server_key' => env('FIREBASE_SERVER_KEY', '')
        );

        foreach ($config as $key => $value) {

            Setting::firstOrCreate([
                'key' => $key,
                'value' => $value
            ]);
        }
    }
}
