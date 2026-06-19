<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\AssessmentType;
use App\Models\StudentTermRecord;
use App\Services\GradingService;
use App\Actions\Grades\StoreBulkGrades;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected Section $section;
    protected GradeLevel $gradeLevel;
    protected Division $division;
    protected Subject $subject;
    protected AssessmentTemplate $assessmentTemplate;

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
            'name' => 'Section-A',
        ]);
        $this->term = Term::factory()->quarter(1)->masterGradingOpen()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
        $this->subject = Subject::factory()->create();

        // Create Assessment Type & Template
        $type = AssessmentType::factory()->create(['code' => 'TEST_TYPE']);
        $this->assessmentTemplate = AssessmentTemplate::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'assessment_type_id' => $type->id,
        ]);
        $this->assessmentTemplate->assignments()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'subject_id' => $this->subject->id,
        ]);
    }

    /** @test */
    public function auditable_trait_resolves_section_id_for_student_mark()
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->adminUser);

        // Create a StudentMark which triggers the auditable event
        $mark = StudentMark::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $this->assessmentTemplate->id,
            'section_id' => $this->section->id,
            'teacher_id' => $this->adminUser->id,
            'score' => 88.00,
        ]);

        // Assert AuditLog was created and has correct section_id
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => StudentMark::class,
            'auditable_id' => $mark->id,
            'section_id' => $this->section->id,
            'event' => 'created',
        ]);
    }

    /** @test */
    public function auditable_trait_resolves_section_id_for_student_attendance()
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->adminUser);

        // Create a StudentAttendance which triggers the auditable event
        $attendance = StudentAttendance::create([
            'student_id' => $student->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'attendance_date' => now(),
            'status' => 'present',
            'marked_by' => $this->adminUser->id,
        ]);

        // Assert AuditLog was created and has correct section_id
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => StudentAttendance::class,
            'auditable_id' => $attendance->id,
            'section_id' => $this->section->id,
            'event' => 'created',
        ]);
    }

    /** @test */
    public function manual_grading_service_recalculation_includes_section_id()
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->adminUser);

        // Run GradingService recalculation
        $service = new GradingService();
        $service->recalculateSectionStatistics($this->section, $this->term, $this->academicYear);

        // Assert manual audit injection occurred with the section_id
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => StudentTermRecord::class,
            'section_id' => $this->section->id,
            'event' => 'bulk_recalculate_term',
        ]);
    }

    /** @test */
    public function store_bulk_grades_action_includes_section_id_in_audit()
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->adminUser);

        // Run StoreBulkGrades action
        StoreBulkGrades::run([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'section_id' => $this->section->id,
            'marks' => [
                $student->id => [
                    $this->assessmentTemplate->id => [
                        'score' => 95.00,
                        'remarks' => 'Great work'
                    ]
                ]
            ]
        ], $this->adminUser->id);

        // Assert bulk grade entry audit log has section_id
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => StudentMark::class,
            'section_id' => $this->section->id,
            'event' => 'bulk_grade_entry',
        ]);
    }

    /** @test */
    public function admin_can_view_section_grade_context_on_audit_logs_page()
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->adminUser);

        // Generate an audit log entry with section_id
        StudentMark::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $this->assessmentTemplate->id,
            'section_id' => $this->section->id,
            'teacher_id' => $this->adminUser->id,
            'score' => 90.00,
        ]);

        // Call the audit log index route
        $response = $this->get(route('admin.audit-logs.index'));

        // Verify page loads and contains section and grade level context
        $response->assertStatus(200);
        $response->assertSee('Grade 5');
        $response->assertSee('Section-A');
    }

    /** @test */
    public function admin_can_search_audit_logs_by_grade_or_section()
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->adminUser);

        // Log entry with section_id (Grade 5, Section-A)
        StudentMark::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $this->assessmentTemplate->id,
            'section_id' => $this->section->id,
            'teacher_id' => $this->adminUser->id,
            'score' => 90.00,
        ]);

        // Call with a matching search query
        $responseMatch = $this->get(route('admin.audit-logs.index', ['search' => 'Grade 5']));
        $responseMatch->assertStatus(200);
        $responseMatch->assertSee('Section-A');

        $responseMatchSection = $this->get(route('admin.audit-logs.index', ['search' => 'Section-A']));
        $responseMatchSection->assertStatus(200);
        $responseMatchSection->assertSee('Grade 5');

        // Call with a non-matching search query
        $responseNoMatch = $this->get(route('admin.audit-logs.index', ['search' => 'NonExistentSection']));
        $responseNoMatch->assertStatus(200);
        $responseNoMatch->assertDontSee('Section-A');
    }
}
