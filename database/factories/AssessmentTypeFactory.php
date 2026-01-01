<?php

namespace Database\Factories;

use App\Models\AssessmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentTypeFactory extends Factory
{
    protected $model = AssessmentType::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Quiz', 'Test', 'Assignment', 'Project', 'Midterm', 'Final Exam']);
        
        return [
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 4)),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
    public function termTotal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Term Total',
            'code' => 'TERM_TOTAL',
        ]);
    }
}
