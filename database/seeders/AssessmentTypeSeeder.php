<?php

namespace Database\Seeders;

use App\Models\AssessmentType;
use Illuminate\Database\Seeder;

class AssessmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Quiz',
                'code' => 'QUIZ',
                'description' => 'Short assessment covering specific topics',
            ],
            [
                'name' => 'Test',
                'code' => 'TEST',
                'description' => 'Standard assessment covering a unit or chapter',
            ],
            [
                'name' => 'Assignment',
                'code' => 'ASSIGN',
                'description' => 'Homework or classwork assignment',
            ],
            [
                'name' => 'Project',
                'code' => 'PROJ',
                'description' => 'Long-term project or presentation',
            ],
            [
                'name' => 'Midterm Exam',
                'code' => 'MIDTERM',
                'description' => 'Examination covering the first half of the term',
            ],
            [
                'name' => 'Final Exam',
                'code' => 'FINAL',
                'description' => 'Comprehensive examination covering the entire term',
            ],
            [
                'name' => 'Participation',
                'code' => 'PARTIC',
                'description' => 'Class participation and engagement',
            ],
            [
                'name' => 'Homework',
                'code' => 'HW',
                'description' => 'Take-home exercises',
            ],
            [
                'name' => 'Lab Work',
                'code' => 'LAB',
                'description' => 'Practical laboratory work',
            ],
            [
                'name' => 'Presentation',
                'code' => 'PRES',
                'description' => 'Oral presentation',
            ],
        ];

        foreach ($types as $type) {
            AssessmentType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
