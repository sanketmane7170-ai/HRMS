<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\MartialStatus;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => 1,
            'date_of_birth' => $this->faker->date('Y-m-d'),
            'personal_email' => $this->faker->safeEmail(),
            'personal_phone' => $this->faker->phoneNumber(),
            'martial_status' => MartialStatus::cases()[rand(0, 3)],
            'gender' =>  Gender::cases()[rand(0, 1)],
            'linkedin_url' => $this->faker->url(),
            'skills' => $this->faker->sentence(),
            'hobbies' => $this->faker->sentence(),
            'address' => $this->faker->address()
        ];
    }
}
