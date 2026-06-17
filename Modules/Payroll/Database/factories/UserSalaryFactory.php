<?php

namespace Modules\Payroll\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserSalaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Payroll\Entities\UserSalary::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'basic' => rand(500, 10000),
            'hra' => rand(1, 2000),
            'food_allowance' => rand(1, 1000),
            'travel_allowance' => rand(1, 1000),
            'other_allowance' => rand(1, 1000),
        ];
    }
}
