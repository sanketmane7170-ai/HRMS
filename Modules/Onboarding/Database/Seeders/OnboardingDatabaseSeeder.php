<?php

namespace Modules\Onboarding\Database\Seeders;

use Illuminate\Database\Seeder;

class OnboardingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            OnboardingPermissionsSeeder::class,
        ]);
    }
}
