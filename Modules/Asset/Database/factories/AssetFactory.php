<?php

namespace Modules\Asset\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetType;

class AssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Asset\Entities\Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = AssetType::inRandomOrder()->first();
        $manufacturer = AssetType::inRandomOrder()->first();
        return [
            'model' => $this->faker->word(),
            'unique_id' => $this->faker->imei(),
            'asset_type_id' => $type->id,
            'asset_manufacturer_id' => $manufacturer->id,
            'description' => $this->faker->sentence()
        ];
    }
}
