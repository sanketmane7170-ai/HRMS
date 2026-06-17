<?php

namespace Modules\Asset\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Asset\Entities\AssetManufacturer;
use Modules\Asset\Entities\AssetType;

class AssetPropertiesSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        $assetTypes = ['Laptop', 'Mobile', 'Keyboard', 'Mouse', 'Sim'];
        foreach ($assetTypes as $type) {
            AssetType::firstOrCreate(['name' => $type]);
        }

        $manufacturers = ['Apple', 'Samsung', 'Lenovo', 'HP', 'Dell'];
        foreach ($manufacturers as $maker) {
            AssetManufacturer::firstOrCreate(['name' => $maker]);
        }
    }
}
