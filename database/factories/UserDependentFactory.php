<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDependent>
 */
class UserDependentFactory extends Factory
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
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->lastName(),
            'last_name' => $this->faker->lastName(),
            'relation' => Relation::cases()[rand(0, 6)],
            'nationality' => config('default.nationalities')[rand(0, 100)],
            'contact' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'gender' => Gender::cases()[rand(0, 1)],
            'date_of_birth' => $this->faker->date('Y-m-d')
        ];
    }
}
