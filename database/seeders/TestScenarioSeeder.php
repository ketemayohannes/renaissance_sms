<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentTemplateAssignment;
use Illuminate\Support\Facades\DB;

class TestScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->firstOrFail();
        
        // 1. Setup Grade 12
        $grade12 = GradeLevel::where('name', 'Grade 12')->first();
        if (!$grade12) {
            // Fallback if not seeded
            $hs = \App\Models\Division::where('code', 'HS')->first();
            $grade12 = GradeLevel::create([
                'code' => 'G12',
                'name' => 'Grade 12',
                'division_id' => $hs?->id,
                'sort_order' => 12
            ]);
        }

        // 2. Create Section "Grade 12 (Natural) - A"
        $section = Section::firstOrCreate(
            ['name' => 'A', 'grade_level_id' => $grade12->id],
            ['academic_year_id' => $year->id, 'capacity' => 40]
        );

        // 3. Create Students
        $faker = \Faker\Factory::create();
        for ($i = 1; $i <= 20; $i++) {
            $studentId = 'STU-' . $year->name . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $email = 'student' . $i . '@renaissance.edu.et';
            
            // Create User
            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $faker->name,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]
            );
            $user->assignRole('Student');
            
            $student = Student::firstOrCreate(
                ['student_id' => $studentId],
                [
                    'user_id' => $user->id,
                    'first_name' => $faker->firstName,
                    'father_name' => $faker->firstNameMale,
                    'grandfather_name' => $faker->firstNameMale,
                    'middle_name' => $faker->firstNameMale, // Redundant but required by schema
                    'last_name' => $faker->lastName,
                    'admission_number' => 'ADM-' . $year->name . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'gender' => $faker->randomElement(['M', 'F']),
                    'date_of_birth' => $faker->date('Y-m-d', '-18 years'),
                    'admission_date' => now(),
                    'is_active' => true,
                ]
            );

            // Create Enrollment
            \App\Models\StudentEnrollment::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $year->id,
                ],
                [
                    'section_id' => $section->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]
            );
        }

        // Recalculate Roll Numbers
        \App\Models\StudentEnrollment::recalculateRollNumbers($section->id, $year->id);

        // 4. Setup Subject (Biology)
        $biology = Subject::where('code', 'BIO')->first();
        if (!$biology) {
            $biology = Subject::create(['name' => 'Biology', 'code' => 'BIO']);
        }

        // Ensure Subject is linked to Grade Level
        DB::table('grade_level_subjects')->updateOrInsert([
            'grade_level_id' => $grade12->id,
            'subject_id' => $biology->id,
            'academic_year_id' => $year->id,
        ], ['is_required' => true]);

        // 5. Create Assessment Templates
        $templates = [
            ['name' => 'Assignment', 'max_score' => 10, 'weight' => 10],
            ['name' => 'Project', 'max_score' => 10, 'weight' => 10],
            ['name' => 'Mid Exam', 'max_score' => 30, 'weight' => 30],
            ['name' => 'Ex.Book and C.W', 'max_score' => 10, 'weight' => 10],
            ['name' => 'Final Exam', 'max_score' => 40, 'weight' => 40],
        ];

        $term = Term::where('academic_year_id', $year->id)->where('name', 'Quarter 1')->first();
        if (!$term) {
            $term = Term::create([
                'name' => 'Quarter 1', 
                'academic_year_id' => $year->id,
                'type' => 'quarter',
                'term_number' => 1,
                'start_date' => now(),
                'end_date' => now()->addMonths(2)
            ]);
        }

        foreach ($templates as $idx => $tpl) {
            $template = AssessmentTemplate::firstOrCreate(
                [
                    'name' => $tpl['name'],
                    'academic_year_id' => $year->id,
                    'term_id' => $term->id,
                ],
                [
                    'max_score' => $tpl['max_score'],
                    'weight' => $tpl['weight'],
                    'description' => $tpl['name'] . ' for ' . $year->name,
                    'is_active' => true,
                    'order' => $idx + 1,
                ]
            );

            // Assign to Grade 12 Biology
            AssessmentTemplateAssignment::firstOrCreate([
                'assessment_template_id' => $template->id,
                'grade_level_id' => $grade12->id,
                'subject_id' => $biology->id,
            ]);
        }
    }
}
