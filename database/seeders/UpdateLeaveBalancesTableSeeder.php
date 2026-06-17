<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateLeaveBalancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement("ALTER TABLE `leave_balances` CHANGE `available` `available` FLOAT(11) NOT NULL DEFAULT '0.0';");
    }
}
