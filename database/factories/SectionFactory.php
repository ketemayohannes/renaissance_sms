<?php

namespace Database\Factories;

use App\Models\Section;
use App\Models\GradeLevel;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'grade_level_id' => GradeLevel::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'name' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'capacity' => $this->faker->numberBetween(25, 40),
            'homeroom_teacher_id' => null,
            'is_active' => true,
        ];
    }

    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
