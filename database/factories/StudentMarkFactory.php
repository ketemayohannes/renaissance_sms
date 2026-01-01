<?php

namespace Database\Factories;

use App\Models\StudentMark;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Subject;
use App\Models\AssessmentTemplate;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentMarkFactory extends Factory
{
    protected $model = StudentMark::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'term_id' => Term::factory(),
            'subject_id' => Subject::factory(),
            'assessment_template_id' => AssessmentTemplate::factory(),
            'section_id' => Section::factory(),
            'teacher_id' => User::factory(),
            'score' => $this->faker->randomFloat(2, 50, 100),
            'remarks' => null,
        ];
    }

    public function withScore(float $score): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $score,
        ]);
    }

    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $this->faker->randomFloat(2, 90, 100),
        ]);
    }

    public function good(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $this->faker->randomFloat(2, 75, 89),
        ]);
    }

    public function average(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $this->faker->randomFloat(2, 60, 74),
        ]);
    }

    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $this->faker->randomFloat(2, 40, 59),
        ]);
    }

    public function failing(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $this->faker->randomFloat(2, 0, 39),
        ]);
    }

    public function zero(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => 0,
        ]);
    }
}
