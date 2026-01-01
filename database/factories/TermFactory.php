<?php

namespace Database\Factories;

use App\Models\Term;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermFactory extends Factory
{
    protected $model = Term::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'parent_term_id' => null,
            'name' => 'Quarter 1',
            'type' => 'quarter',
            'term_number' => 1,
            'start_date' => now()->subMonths(3),
            'end_date' => now(),
            'is_grading_open' => true,
            'is_master_grading_open' => false,
        ];
    }

    public function quarter(int $number = 1): static
    {
        $names = [1 => 'Quarter 1', 2 => 'Quarter 2', 3 => 'Quarter 3', 4 => 'Quarter 4'];
        
        return $this->state(fn (array $attributes) => [
            'name' => $names[$number] ?? 'Quarter 1',
            'type' => 'quarter',
            'term_number' => $number,
        ]);
    }

    public function semester(int $number = 1): static
    {
        $names = [1 => 'Semester 1', 2 => 'Semester 2'];
        
        return $this->state(fn (array $attributes) => [
            'name' => $names[$number] ?? 'Semester 1',
            'type' => 'semester',
            'term_number' => $number,
            'parent_term_id' => null,
        ]);
    }

    public function gradingOpen(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_grading_open' => true,
        ]);
    }

    public function gradingClosed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_grading_open' => false,
        ]);
    }
    public function masterGradingOpen(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_master_grading_open' => true,
        ]);
    }
}
