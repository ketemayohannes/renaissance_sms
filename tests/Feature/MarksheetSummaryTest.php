<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\TeacherAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarksheetSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $teacherUser;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected Section $section;
    protected GradeLevel $gradeLevel;
    protected Division $division;
    protected Subject $subject;
    protected TeacherAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Teacher']);

        // Create Users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        $this->teacherUser = User::factory()->create();
        $this->teacherUser->assignRole('Teacher');

        // Create base data structure
        $this->division = Division::factory()->create(['name' => 'Primary']);
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->gradeLevel = GradeLevel::factory()->create([
            'division_id' => $this->division->id,
            'name' => 'Grade 5',
        ]);
        $this->section = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'A',
        ]);
        $this->term = Term::factory()->quarter(1)->masterGradingOpen()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
        $this->subject = Subject::factory()->create();

        // Create teacher assignment
        $this->assignment = TeacherAssignment::create([
            'academic_year_id' => $this->academicYear->id,
            'teacher_id' => $this->teacherUser->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->subject->id,
        ]);
    }

    /** @test */
    public function admin_can_download_summary_marksheet(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.gradebook.marksheet', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'section_id' => $this->section->id,
                'subject_id' => $this->subject->id,
                'summary' => 1,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('summary_marksheet', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function teacher_can_download_summary_marksheet(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.gradebook.marksheet', [
                'assignment' => $this->assignment->id,
                'term_id' => $this->term->id,
                'summary' => 1,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('summary_marksheet', $response->headers->get('Content-Disposition'));
    }
}
