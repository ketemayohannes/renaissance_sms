<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected Section $section;
    protected GradeLevel $gradeLevel;
    protected Division $division;
    protected Subject $subject;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create admin user with role
        Role::firstOrCreate(['name' => 'Super Admin']);
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
        $this->term = Term::factory()->quarter(1)->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
        $this->subject = Subject::factory()->create();
        
        // Create and enroll student
        $this->student = Student::factory()->create();
        $this->student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        // Create report card settings
        ReportCardSetting::create([
            'school_name' => 'Renaissance School',
        ]);
    }

    /** @test */
    public function admin_can_access_report_card_settings(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.report-cards.settings'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_generate_student_report_card(): void
    {
        // Create grade for student
        $template = AssessmentTemplate::factory()->termTotal()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
        ]);
        
        StudentMark::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $template->id,
            'section_id' => $this->section->id,
            'score' => 85,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.report-cards.pdf', [
                'student' => $this->student->id,
                'term_id' => $this->term->id,
                'academic_year_id' => $this->academicYear->id,
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function report_card_contains_correct_student_data(): void
    {
        // Create grade for student
        $template = AssessmentTemplate::factory()->termTotal()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
        ]);
        
        StudentMark::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $template->id,
            'section_id' => $this->section->id,
            'score' => 92,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.report-cards.pdf', [
                'student' => $this->student->id,
                'term_id' => $this->term->id,
                'academic_year_id' => $this->academicYear->id,
            ]));

        $response->assertStatus(200);
        // Check student name is in the view
        $response->assertSee($this->student->first_name);
    }

    /** @test */
    public function admin_can_access_bulk_print_page(): void
    {
        // Create grade for student
        $template = AssessmentTemplate::factory()->termTotal()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
        ]);
        
        StudentMark::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $template->id,
            'section_id' => $this->section->id,
            'score' => 88,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.section-grades.bulk-print-report-cards', [
                'section' => $this->section->id,
                'term_id' => $this->term->id,
                'academic_year_id' => $this->academicYear->id,
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_exports_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.report-cards.exports'));

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_users_cannot_access_report_cards(): void
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->get(route('admin.report-cards.settings'));

        $response->assertStatus(403);
    }

    /** @test */
    public function grade_12_yearly_report_card_hides_unassigned_electives(): void
    {
        // Change grade level name to Grade 12
        $this->gradeLevel->update(['name' => 'Grade 12']);

        // Create semesters and quarters
        $semester1 = Term::factory()->semester(1)->create(['academic_year_id' => $this->academicYear->id]);
        $semester2 = Term::factory()->semester(2)->create(['academic_year_id' => $this->academicYear->id]);
        
        $q1 = Term::factory()->quarter(1)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $semester1->id]);
        $q2 = Term::factory()->quarter(2)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $semester1->id]);
        $q3 = Term::factory()->quarter(3)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $semester2->id]);
        $q4 = Term::factory()->quarter(4)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $semester2->id]);

        // Create an elective subject
        $electiveSubject = Subject::factory()->create(['is_elective' => true, 'name' => 'Art Elective']);
        $this->gradeLevel->subjects()->attach($electiveSubject->id, [
            'academic_year_id' => $this->academicYear->id,
            'is_required' => false,
            'sort_order' => 10,
        ]);

        // Attach regular subject mark so student has a record
        $template = AssessmentTemplate::factory()->termTotal()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $q1->id,
        ]);
        StudentMark::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $q1->id,
            'subject_id' => $this->subject->id,
            'assessment_template_id' => $template->id,
            'section_id' => $this->section->id,
            'score' => 85,
        ]);

        // Generate yearly report card
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.report-cards.pdf', [
                'student' => $this->student->id,
                'term_id' => 'yearly',
                'academic_year_id' => $this->academicYear->id,
            ]));

        $response->assertStatus(200);
        // The elective subject should NOT show up
        $response->assertDontSee('Art Elective');

        // Now, assign the elective to the student
        \Illuminate\Support\Facades\DB::table('student_electives')->insert([
            'student_id' => $this->student->id,
            'subject_id' => $electiveSubject->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Generate yearly report card again
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.report-cards.pdf', [
                'student' => $this->student->id,
                'term_id' => 'yearly',
                'academic_year_id' => $this->academicYear->id,
            ]));

        $response->assertStatus(200);
        // The elective subject should now show up
        $response->assertSee('Art Elective');
    }

    /** @test */
    public function admin_can_delete_their_own_export_request(): void
    {
        $exportRequest = \App\Models\ExportRequest::create([
            'user_id' => $this->adminUser->id,
            'type' => 'section_report_cards_zip',
            'status' => 'failed',
            'error_message' => 'Test error message',
            'params' => ['section_id' => 1, 'term_id' => 1],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.report-cards.destroy-export', $exportRequest));

        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseMissing('export_requests', ['id' => $exportRequest->id]);
    }

    /** @test */
    public function admin_cannot_delete_someone_elses_export_request(): void
    {
        $otherUser = User::factory()->create();
        $exportRequest = \App\Models\ExportRequest::create([
            'user_id' => $otherUser->id,
            'type' => 'section_report_cards_zip',
            'status' => 'failed',
            'error_message' => 'Test error message',
            'params' => ['section_id' => 1, 'term_id' => 1],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.report-cards.destroy-export', $exportRequest));

        $response->assertStatus(403);
        $this->assertDatabaseHas('export_requests', ['id' => $exportRequest->id]);
    }
}
