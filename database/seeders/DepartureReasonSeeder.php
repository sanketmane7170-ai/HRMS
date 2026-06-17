<?php

namespace Database\Seeders;

use App\Models\DepartureReason;
use Illuminate\Database\Seeder;

class DepartureReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sanket: Populating standard departure reasons
        $reasons = [
            'Better Opportunity',
            'Personal Reasons',
            'Relocation',
            'Career Change',
            'Further Education',
            'Resignation', // Default expected ID 6
            'Termination',
            'Contract End',
            'Retirement'
        ];

        foreach ($reasons as $reason) {
            DepartureReason::firstOrCreate(['name' => $reason]);
        }
    }
}
