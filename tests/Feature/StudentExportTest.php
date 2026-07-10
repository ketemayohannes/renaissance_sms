<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected Division $divisionPrimary;
    protected Division $divisionSecondary;
    protected GradeLevel $gradeLevel5;
    protected GradeLevel $gradeLevel10;
    protected Section $section5A;
    protected Section $section10A;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create admin user with role
        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        // Setup academic structure
        $this->academicYear = AcademicYear::factory()->active()->create();

        // Division 1: Primary
        $this->divisionPrimary = Division::factory()->create(['name' => 'Primary', 'is_active' => true]);
        $this->gradeLevel5 = GradeLevel::factory()->create([
            'division_id' => $this->divisionPrimary->id,
            'name' => 'Grade 5',
        ]);
        $this->section5A = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel5->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'A',
        ]);

        // Division 2: Secondary
        $this->divisionSecondary = Division::factory()->create(['name' => 'Secondary', 'is_active' => true]);
        $this->gradeLevel10 = GradeLevel::factory()->create([
            'division_id' => $this->divisionSecondary->id,
            'name' => 'Grade 10',
        ]);
        $this->section10A = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel10->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'A',
        ]);
    }

    public function test_admin_can_export_students_filtered_by_division(): void
    {
        // Create student in Primary Division
        $studentPrimary = Student::factory()->create([
            'first_name' => 'John',
            'father_name' => 'Doe',
            'is_active' => true,
        ]);
        $studentPrimary->enrollments()->create([
            'section_id' => $this->section5A->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
        ]);

        // Create student in Secondary Division
        $studentSecondary = Student::factory()->create([
            'first_name' => 'Jane',
            'father_name' => 'Smith',
            'is_active' => true,
        ]);
        $studentSecondary->enrollments()->create([
            'section_id' => $this->section10A->id,
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
        ]);

        // 1. Request export without filter (should export all active)
        $responseAll = $this->actingAs($this->adminUser)
            ->get(route('admin.students.export'));

        $responseAll->assertStatus(200);
        $contentAll = $responseAll->streamedContent();
        $this->assertStringContainsStringIgnoringCase('John', $contentAll);
        $this->assertStringContainsStringIgnoringCase('Jane', $contentAll);

        // 2. Request export filtered by Primary Division
        $responsePrimary = $this->actingAs($this->adminUser)
            ->get(route('admin.students.export', ['division_id' => $this->divisionPrimary->id]));

        $responsePrimary->assertStatus(200);
        $contentPrimary = $responsePrimary->streamedContent();
        $this->assertStringContainsStringIgnoringCase('John', $contentPrimary);
        $this->assertStringNotContainsStringIgnoringCase('Jane', $contentPrimary);

        // 3. Request export filtered by Secondary Division
        $responseSecondary = $this->actingAs($this->adminUser)
            ->get(route('admin.students.export', ['division_id' => $this->divisionSecondary->id]));

        $responseSecondary->assertStatus(200);
        $contentSecondary = $responseSecondary->streamedContent();
        $this->assertStringNotContainsStringIgnoringCase('John', $contentSecondary);
        $this->assertStringContainsStringIgnoringCase('Jane', $contentSecondary);
    }
}
