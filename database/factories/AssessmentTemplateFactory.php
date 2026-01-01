<?php

namespace Database\Factories;

use App\Models\AssessmentTemplate;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\AssessmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentTemplateFactory extends Factory
{
    protected $model = AssessmentTemplate::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'term_id' => Term::factory(),
            'assessment_type_id' => AssessmentType::factory(),
            'name' => $this->faker->randomElement(['Test 1', 'Quiz 1', 'Assignment 1', 'Mid-Term', 'Final']),
            'weight' => $this->faker->randomElement([10, 15, 20, 25, 30]),
            'max_score' => 100,
            'order' => 1,
            'is_active' => true,
        ];
    }

    public function test(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Test',
            'weight' => 30,
            'max_score' => 100,
        ]);
    }

    public function quiz(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Quiz',
            'weight' => 10,
            'max_score' => 20,
        ]);
    }

    public function midterm(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Mid-Term Exam',
            'weight' => 25,
            'max_score' => 100,
        ]);
    }

    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Final Exam',
            'weight' => 40,
            'max_score' => 100,
        ]);
    }

    public function termTotal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Term Total',
            'weight' => 100,
            'max_score' => 100,
        ]);
    }
}
