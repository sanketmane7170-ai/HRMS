<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserWorkDetail>
 */
class UserWorkDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date =$this->faker->date('Y-m-d');
        return [
            'user_id' => 1,
            'joining_date' => $date,
            'probation_end_date' => now()->parse($date)->addMonths(6),
            'company_name' => $this->faker->company(),
            'work_week' => rand(0, 10),
            'location' => $this->faker->address()
        ];
    }
}
