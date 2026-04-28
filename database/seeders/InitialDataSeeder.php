<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Division;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\ConductGrade;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@renaissance.edu.et'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Default password
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Super Admin');

        // 2. Create Divisions
        $divisions = [
            ['name' => 'Kindergarten', 'code' => 'KG', 'sort_order' => 1],
            ['name' => 'Elementary', 'code' => 'ES', 'sort_order' => 2],
            ['name' => 'High School', 'code' => 'HS', 'sort_order' => 3],
        ];

        foreach ($divisions as $div) {
            Division::firstOrCreate(['code' => $div['code']], $div);
        }

        // 3. Create Default Academic Year (Only if none exist)
        if (AcademicYear::count() === 0) {
            $year = AcademicYear::create([
                'name' => '2024/2025',
                'start_date' => '2024-09-11',
                'end_date' => '2025-06-30',
                'is_active' => true,
            ]);

            // 4. Create Terms (Quarters & Semesters)
            // Quarter 1
            Term::create([
                'name' => 'Quarter 1', 
                'academic_year_id' => $year->id,
                'type' => 'quarter', 
                'term_number' => 1, 
                'start_date' => '2024-09-11', 
                'end_date' => '2024-11-10'
            ]);
            // Quarter 2
            Term::create([
                'name' => 'Quarter 2', 
                'academic_year_id' => $year->id,
                'type' => 'quarter', 
                'term_number' => 2, 
                'start_date' => '2024-11-11', 
                'end_date' => '2025-01-31'
            ]);
            // Semester 1 (Aggregates Q1 & Q2)
            Term::create([
                'name' => 'Semester 1', 
                'academic_year_id' => $year->id,
                'type' => 'semester', 
                'term_number' => 1, 
                'start_date' => '2024-09-11', 
                'end_date' => '2025-01-31'
            ]);
        }

        // 5. Create Conduct Grades
        $conducts = [
            ['grade' => 'A', 'description' => 'Excellent Behavior', 'sort_order' => 1],
            ['grade' => 'B', 'description' => 'Good Behavior', 'sort_order' => 2],
            ['grade' => 'C', 'description' => 'Needs Improvement', 'sort_order' => 3],
            ['grade' => 'D', 'description' => 'Poor Behavior', 'sort_order' => 4],
        ];

        foreach ($conducts as $conduct) {
            ConductGrade::firstOrCreate(['grade' => $conduct['grade']], $conduct);
        }
    }
}
