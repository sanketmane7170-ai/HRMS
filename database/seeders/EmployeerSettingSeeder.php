<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class EmployeerSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = array(
            'employer_unique_id' => '',
            'bank_code' => '',
            'minumum_working_hour' => 9,
            'employer_reference_number' => ''
        );

        foreach ($config as $key => $value) {

            Setting::firstOrCreate([
                'key' => $key,
                'value' => $value
            ]);
        }
    }
}
