<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Employee;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DivisionRestrictionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_student_promoted_out_of_a_division_is_no_longer_visible_to_that_divisions_staff(): void
    {
        Role::firstOrCreate(['name' => 'Principal']);

        $elementary = Division::factory()->create(['name' => 'Elementary']);
        $highSchool = Division::factory()->create(['name' => 'High School']);

        $lastYear = AcademicYear::factory()->create([
            'is_active' => false,
            'start_date' => now()->subYear()->startOfYear(),
            'end_date' => now()->subYear()->endOfYear(),
        ]);
        $activeYear = AcademicYear::factory()->active()->create();

        $grade8 = GradeLevel::factory()->create(['division_id' => $elementary->id, 'name' => 'Grade 8', 'sort_order' => 7]);
        $grade9 = GradeLevel::factory()->create(['division_id' => $highSchool->id, 'name' => 'Grade 9', 'sort_order' => 0]);

        $oldSection = Section::factory()->create(['grade_level_id' => $grade8->id, 'academic_year_id' => $lastYear->id, 'name' => 'A']);
        $newSection = Section::factory()->create(['grade_level_id' => $grade9->id, 'academic_year_id' => $activeYear->id, 'name' => 'A']);

        $student = Student::factory()->create();

        // Old enrollment is kept open (end_date IS NULL) on purpose so its gradebook stays
        // visible, per this app's hold-until-enroll promotion design.
        StudentEnrollment::create([
            'student_id' => $student->id,
            'section_id' => $oldSection->id,
            'academic_year_id' => $lastYear->id,
            'enrollment_date' => $lastYear->start_date,
            'status' => 'active',
        ]);

        // New enrollment for the now-active year, in the new division.
        StudentEnrollment::create([
            'student_id' => $student->id,
            'section_id' => $newSection->id,
            'academic_year_id' => $activeYear->id,
            'enrollment_date' => $activeYear->start_date,
            'status' => 'active',
        ]);

        $elementaryPrincipalUser = User::factory()->create();
        $elementaryPrincipalUser->assignRole('Principal');
        Employee::factory()->create(['user_id' => $elementaryPrincipalUser->id, 'division_id' => $elementary->id]);

        $highSchoolPrincipalUser = User::factory()->create();
        $highSchoolPrincipalUser->assignRole('Principal');
        Employee::factory()->create(['user_id' => $highSchoolPrincipalUser->id, 'division_id' => $highSchool->id]);

        $this->actingAs($elementaryPrincipalUser);
        $this->assertFalse(
            Student::whereKey($student->id)->exists(),
            'Elementary staff should no longer see a student now enrolled in High School for the active year.'
        );

        $this->actingAs($highSchoolPrincipalUser);
        $this->assertTrue(
            Student::whereKey($student->id)->exists(),
            'High School staff should see the student now that their active-year enrollment is in High School.'
        );
    }
}
