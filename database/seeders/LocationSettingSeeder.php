<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class LocationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = array(
            // Location Details
            'radius' => '100',
            'latitude' => '',
            'longitude' => ''
        );

        foreach ($config as $key => $value) {

            Setting::firstOrCreate([
                'key' => $key,
                'value' => $value
            ]);
        }
    }
}
