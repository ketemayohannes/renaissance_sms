<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    private static array $subjects = [
        ['name' => 'Mathematics', 'code' => 'MATH'],
        ['name' => 'English', 'code' => 'ENG'],
        ['name' => 'Physics', 'code' => 'PHY'],
        ['name' => 'Chemistry', 'code' => 'CHEM'],
        ['name' => 'Biology', 'code' => 'BIO'],
        ['name' => 'History', 'code' => 'HIST'],
        ['name' => 'Geography', 'code' => 'GEO'],
        ['name' => 'Amharic', 'code' => 'AMH'],
        ['name' => 'Civics', 'code' => 'CIV'],
        ['name' => 'Information Technology', 'code' => 'IT'],
    ];

    private static int $subjectIndex = 0;

    public function definition(): array
    {
        $subject = self::$subjects[self::$subjectIndex % count(self::$subjects)];
        self::$subjectIndex++;
        
        return [
            'name' => $subject['name'],
            'code' => $subject['code'] . '_' . self::$subjectIndex,
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'is_elective' => false,
            'sort_order' => self::$subjectIndex,
        ];
    }

    public function math(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);
    }

    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'English',
            'code' => 'ENG',
        ]);
    }

    public function elective(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_elective' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
