<?php

namespace Modules\Asset\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Asset\Database\factories\AssetFactoryFactory;
use Modules\Asset\Entities\Asset;

class AssetDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call([
            AssetPropertiesSeederTableSeeder::class
        ]);

        Asset::factory(20)->create();
    }
}
