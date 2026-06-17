<?php

namespace Modules\Announcement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Announcement\Entities\AnnouncementType;

class AnnouncementTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        $types = [
            ['name' => 'General Announcement', 'color' => '#6a97d2'],
            ['name' => 'Birthday Announcement', 'color' => '#168328'],
            ['name' => 'Holiday Announcement', 'color' => '#c42121'],
        ];

        foreach ($types as $type) {
            AnnouncementType::firstOrCreate(
                ['name' => $type['name']], // Unique key to check
                ['color' => $type['color']] // Values to insert if not found
            );
        }
    }
}
