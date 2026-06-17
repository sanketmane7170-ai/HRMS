<?php

namespace Modules\Leave\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Enums\LeaveType as EnumsLeaveType;

class LeaveTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $leaveTypes = [
            ['name' => 'Maternity', 'days' => 45, 'type' => EnumsLeaveType::Calendar],
            ['name' => 'Vacation', 'days' => 30, 'type' => EnumsLeaveType::Calendar],
            ['name' => 'Sick', 'days' => 15, 'type' => EnumsLeaveType::Working],
            // ['name' => 'Casual', 'days' => 3, 'type' => EnumsLeaveType::Working],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(
                ['name' => $type['name']], // unique check
                ['days' => $type['days'], 'type' => $type['type']]
            );
        }
    }
}
