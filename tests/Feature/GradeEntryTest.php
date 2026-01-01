<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\AssessmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GradeEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected Section $section;
    protected GradeLevel $gradeLevel;
    protected Division $division;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user with role
        Role::create(['name' => 'Super Admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');
        
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
    }

    /** @test */
    public function admin_can_access_section_grade_index_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.section-grades.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_grade_entry_page(): void
    {
        // Assign subject to grade level
        $this->gradeLevel->subjects()->attach($this->subject->id, [
            'academic_year_id' => $this->academicYear->id,
            'is_required' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.section-grades.entry', [
                'section_id' => $this->section->id,
                'term_id' => $this->term->id,
                'subject_id' => $this->subject->id,
                'academic_year_id' => $this->academicYear->id,
                'grade_level_id' => $this->gradeLevel->id,
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_submit_grades_for_section(): void
    {
        // Create student and enroll
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        // Create assessment type for Term Total
        $type = AssessmentType::factory()->termTotal()->create();

        // Create assessment template
        $template = AssessmentTemplate::factory()->termTotal()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'assessment_type_id' => $type->id,
        ]);

        // Create assignment for template
        $template->assignments()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'subject_id' => $this->subject->id,
        ]);

        // Assign subject to grade level
        $this->gradeLevel->subjects()->attach($this->subject->id, [
            'academic_year_id' => $this->academicYear->id,
            'is_required' => true,
        ]);

        // Submit grades
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.section-grades.store'), [
                'section_id' => $this->section->id,
                'term_id' => $this->term->id,
                'subject_id' => $this->subject->id,
                'academic_year_id' => $this->academicYear->id,
                'marks' => [
                    $student->id => [
                        $this->subject->id => 85,
                    ],
                ],
            ]);

        $response->assertRedirect();

        // Verify grade was saved
        $this->assertDatabaseHas('student_marks', [
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'term_id' => $this->term->id,
            'score' => 85,
        ]);
    }

    /** @test */
    public function grades_are_saved_correctly_to_database(): void
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $type = AssessmentType::factory()->termTotal()->create();

        $template = AssessmentTemplate::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'assessment_type_id' => $type->id,
        ]);

        $template->assignments()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'subject_id' => $this->subject->id,
        ]);

        // Create the mark directly
        $mark = StudentMark::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $template->id,
            'section_id' => $this->section->id,
            'score' => 92.5,
        ]);

        // Verify
        $this->assertDatabaseHas('student_marks', [
            'id' => $mark->id,
            'score' => 92.5,
        ]);

        $savedMark = StudentMark::find($mark->id);
        $this->assertEquals(92.5, $savedMark->score);
    }

    /** @test */
    public function unauthorized_users_cannot_access_grade_entry(): void
    {
        // Create regular user without admin role
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get(route('admin.section-grades.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_grade_entry(): void
    {
        $response = $this->get(route('admin.section-grades.index'));

        $response->assertRedirect(route('login'));
    }
}
