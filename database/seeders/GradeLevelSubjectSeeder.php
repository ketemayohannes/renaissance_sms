<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class GradeLevelSubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get divisions
        $kg = Division::where('code', 'KG')->first();
        $es = Division::where('code', 'ES')->first();
        $hs = Division::where('code', 'HS')->first();

        // Create Grade Levels for Kindergarten
        if ($kg) {
            GradeLevel::firstOrCreate(['code' => 'KG1'], [
                'division_id' => $kg->id,
                'name' => 'KG 1',
                'sort_order' => 1
            ]);
            GradeLevel::firstOrCreate(['code' => 'KG2'], [
                'division_id' => $kg->id,
                'name' => 'KG 2',
                'sort_order' => 2
            ]);
            GradeLevel::firstOrCreate(['code' => 'KG3'], [
                'division_id' => $kg->id,
                'name' => 'KG 3',
                'sort_order' => 3
            ]);
        }

        // Create Grade Levels for Elementary
        if ($es) {
            for ($i = 1; $i <= 8; $i++) {
                GradeLevel::firstOrCreate(['code' => 'G' . $i], [
                    'division_id' => $es->id,
                    'name' => 'Grade ' . $i,
                    'sort_order' => $i
                ]);
            }
        }

        // Create Grade Levels for High School
        if ($hs) {
            for ($i = 9; $i <= 12; $i++) {
                GradeLevel::firstOrCreate(['code' => 'G' . $i], [
                    'division_id' => $hs->id,
                    'name' => 'Grade ' . $i,
                    'sort_order' => $i
                ]);
            }
        }

        // Create Common Subjects
        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Amharic', 'code' => 'AMH'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Social Studies', 'code' => 'SOC'],
            ['name' => 'Physical Education', 'code' => 'PE'],
            ['name' => 'Art', 'code' => 'ART'],
            ['name' => 'Music', 'code' => 'MUS'],
            ['name' => 'Biology', 'code' => 'BIO'],
            ['name' => 'Chemistry', 'code' => 'CHEM'],
            ['name' => 'Physics', 'code' => 'PHY'],
            ['name' => 'History', 'code' => 'HIST'],
            ['name' => 'Geography', 'code' => 'GEO'],
            ['name' => 'Civics', 'code' => 'CIV'],
            ['name' => 'Information Technology', 'code' => 'IT'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['code' => $subject['code']], $subject);
        }

        // Link Subjects to Grade Levels (Pivot Table)
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        
        if ($activeYear) {
            $allSubjects = Subject::all();
            $allGrades = GradeLevel::all();

            foreach ($allGrades as $grade) {
                foreach ($allSubjects as $subject) {
                    // Simple logic: Assign all subjects to all grades for now
                    // In a real app, you'd have specific subjects per grade
                    DB::table('grade_level_subjects')->updateOrInsert([
                        'grade_level_id' => $grade->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $activeYear->id,
                    ], [
                        'is_required' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
