<?php

namespace Modules\Attendance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Attendance\Entities\Holiday;

class HolidayTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        $holidays = [
            [
                'start_date' => '2023-01-01',
                'end_date' => '2023-01-01',
                'detail' => 'New year annual Holiday',
            ],
            [
                'start_date' => '2023-07-19',
                'end_date' => '2023-07-19',
                'detail' => 'Islamic New Year Holiday',
            ],
            [
                'start_date' => '2023-04-20',
                'end_date' => '2023-04-20',
                'detail' => 'Eid Al Fitr Holiday',
            ],
            [
                'start_date' => '2023-06-27',
                'end_date' => '2023-06-27',
                'detail' => 'Arafat Day Holiday',
            ],
            [
                'start_date' => '2023-07-28',
                'end_date' => '2023-07-28',
                'detail' => 'Eid Al Adha Holiday',
            ],
            [
                'start_date' => '2023-09-29',
                'end_date' => '2023-09-29',
                'detail' => "The Prophet's Birthday Holiday",
            ],
            [
                'start_date' => '2023-12-01',
                'end_date' => '2023-12-01',
                'detail' => 'Commemoration Day Holiday',
            ],
            [
                'start_date' => '2023-12-02',
                'end_date' => '2023-12-02',
                'detail' => 'UAE National Day Holiday',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                [
                    'start_date' => $holiday['start_date'],
                    'detail' => $holiday['detail']
                ],
                [
                    'end_date' => $holiday['end_date'],
                    'is_recurring' => 1,
                    'created_by_id' => 1
                ]
            );
        }
    }
}
