<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\MartialStatus;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'email_verified_at' => now(),
            'password' => bcrypt('Welcome' . date('Y')),
            'remember_token' => Str::random(10),
            'department_id' => 1,
            'designation_id' => 1,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Employee');
        });
    }

    /**
     * Create a user with a specific department
     */
    public function withDepartment(string $departmentName): static
    {
        return $this->afterMaking(function (User $user) use ($departmentName) {
            $department = Department::firstOrCreate([
                'name' => $departmentName,
            ], [
                'code' => strtoupper(substr($departmentName, 0, 3)),
                'slug' => Str::slug($departmentName),
            ]);
            $user->department_id = $department->id;
        });
    }

    /**
     * Create an active user
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Create a user suitable for leave testing
     */
    public function forLeaveTesting(): static
    {
        return $this->active()
            ->state(fn (array $attributes) => [
                'joining_date' => now()->subYear()->format('Y-m-d'),
                'username' => $this->faker->unique()->userName(),
                'status' => 'active',
            ])
            ->afterMaking(function (User $user) {
                // Ensure we have valid foreign keys
                if (!Department::find($user->department_id)) {
                    $department = Department::firstOrCreate([
                        'name' => 'Test Department',
                    ], [
                        'code' => 'TEST',
                        'slug' => 'test-department',
                    ]);
                    $user->department_id = $department->id;
                }

                if (!Designation::find($user->designation_id)) {
                    $designation = Designation::firstOrCreate([
                        'name' => 'Test Designation',
                    ], [
                        'code' => 'TEST_DES',
                        'slug' => 'test-designation',
                    ]);
                    $user->designation_id = $designation->id;
                }
            });
    }
}
