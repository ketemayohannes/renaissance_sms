<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_id' => 'EMP-' . $this->faker->unique()->numberBetween(1000, 99999),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-22 years'),
            'phone' => $this->faker->numerify('09########'),
            'email' => $this->faker->unique()->safeEmail(),
            'designation' => 'Teacher',
            'department' => 'Academic',
            'joining_date' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            'basic_salary' => $this->faker->numberBetween(8000, 30000),
            'employment_type' => 'full_time',
            'status' => 'active',
        ];
    }

    public function nonTeaching(): static
    {
        return $this->state(fn () => [
            'designation' => 'Guard',
            'department' => 'Administration',
        ]);
    }
}
