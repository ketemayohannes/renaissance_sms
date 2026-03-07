<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Section;
use App\Models\TeacherAssignment;

class ExpandedPortalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a Department
        $mathDept = Department::firstOrCreate(
            ['code' => 'MATH'],
            [
                'name' => 'Mathematics Department',
                'description' => 'Department overseeing all mathematics-related subjects.',
                'is_active' => true,
            ]
        );

        // 2. Find a Teacher to be the Head of Department
        $teacher = User::role('Teacher')->first();
        if ($teacher) {
            $mathDept->update(['head_id' => $teacher->id]);
            
            // Link math subjects to this department
            Subject::where('name', 'like', '%Math%')->update(['department_id' => $mathDept->id]);
            
            $this->command->info("Teacher '{$teacher->name}' is now Head of Mathematics.");
        }

        // 3. Find another Teacher (or the same) to be a Homeroom Teacher
        $section = Section::where('is_active', true)->first();
        if ($section && $teacher) {
            $section->update(['homeroom_teacher_id' => $teacher->id]);
            $this->command->info("Teacher '{$teacher->name}' is now Homeroom Teacher for {$section->name}.");
        }
    }
}
