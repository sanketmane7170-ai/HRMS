<?php

namespace Modules\IndianPayroll\Database\Seeders;

use Illuminate\Database\Seeder;

class IndianPayrollDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StatesOfIndiaSeeder::class,
            SalaryComponentSeeder::class,
            IndianPayrollPermissionsSeeder::class,
            DefaultStatutoryRatesSeeder::class,
        ]);
    }
}
