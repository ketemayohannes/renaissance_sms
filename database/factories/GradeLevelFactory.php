<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        static $gradeNumber = 0;
        $gradeNumber++;
        
        return [
            'division_id' => Division::factory(),
            'name' => "Grade {$gradeNumber}",
            'code' => "G{$gradeNumber}",
            'sort_order' => $gradeNumber,
            'is_active' => true,
        ];
    }

    public function grade(int $number): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => "Grade {$number}",
            'code' => "G{$number}",
            'sort_order' => $number,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
