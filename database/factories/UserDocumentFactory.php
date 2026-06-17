<?php

namespace Database\Factories;

use App\Enums\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDocument>
 */
class UserDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'original_name' => $this->faker->name(),
            'path' => "/uploads/temp/".$this->faker->image(public_path('uploads/temp'), 400, 400, null, false),
            'type' => Document::cases()[rand(0, 7)],
            'serial_number' => $this->faker->isbn10(),
            'expiry_date' => $this->faker->date('Y-m-d')
        ];
    }
}
