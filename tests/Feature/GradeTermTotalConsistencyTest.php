<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentType;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regression coverage for the TERM_TOTAL handling bugs found in views/dashboards
 * that aggregate StudentMark rows outside GradingService.
 *
 * A TERM_TOTAL StudentMark is an auto-synced copy of the component sum. Any code
 * that aggregates a student's marks must be "components-first": use live component
 * marks when present, and treat TERM_TOTAL only as a fallback when no components
 * exist. Summing every row (components + TERM_TOTAL) double-counts; preferring a
 * stale TERM_TOTAL over edited components shows outdated numbers.
 */
class GradeTermTotalConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $academicYear;
    protected Division $division;
    protected GradeLevel $gradeLevel;
    protected Section $section;
    protected Term $quarter;
    protected AssessmentType $termTotalType;
    protected AssessmentType $componentType;

    protected function setUp(): void
    {
        parent::setUp();

        // CachedData::activeAcademicYear() memoises via Cache; clear so each test
        // sees its own freshly-created active year rather than a leaked value.
        Cache::flush();

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
            'is_active' => true,
        ]);
        $this->quarter = Term::factory()->quarter(1)->create([
            'academic_year_id' => $this->academicYear->id,
            'start_date' => now()->subMonth(),
        ]);

        $this->termTotalType = AssessmentType::factory()->termTotal()->create();
        $this->componentType = AssessmentType::factory()->create(['code' => 'QZ1', 'name' => 'Quiz 1']);
    }

    /** @test */
    public function student_profile_academic_tab_sums_components_only_not_the_term_total_copy(): void
    {
        Role::findOrCreate('Super Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $student = Student::factory()->create();
        $this->enroll($student);
        $subject = Subject::factory()->create(['name' => 'Amharic']);

        // Components 40 + 33 = 73, plus a stale/auto TERM_TOTAL copy of 73.
        // Old (buggy) view summed all rows -> 146. Correct total is 73.
        $this->makeMark($student, $subject, $this->componentType, 40);
        $this->makeMark($student, $subject, $this->componentType, 33);
        $this->makeMark($student, $subject, $this->termTotalType, 73);

        $response = $this->actingAs($admin)->get(route('admin.students.show', $student));
        $response->assertOk();

        $records = collect($response->viewData('academicRecords'))
            ->flatMap(fn ($terms) => $terms->flatMap(fn ($marks) => $marks));

        // No TERM_TOTAL row should reach the view.
        $this->assertTrue(
            $records->every(fn ($m) => $m->assessmentTemplate->assessment_type_id !== $this->termTotalType->id),
            'TERM_TOTAL marks must be excluded from the academic tab data.'
        );
        $this->assertEquals(73, $records->where('subject_id', $subject->id)->sum('score'));

        // Rendered total is the component sum, not the doubled value.
        $response->assertSee('73.0');
        $response->assertDontSee('146.0');
    }

    /** @test */
    public function student_profile_resolves_term_total_per_subject_per_term_and_never_drops_a_term(): void
    {
        Role::findOrCreate('Super Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $student = Student::factory()->create();
        $this->enroll($student);
        $subject = Subject::factory()->create();

        // Quarter 1 ($this->quarter): TERM_TOTAL only (e.g. Master Sheet entry, no
        // components). Must still appear, using the TERM_TOTAL as the fallback value.
        $q1 = $this->quarter;
        $this->makeMark($student, $subject, $this->termTotalType, 80, $q1);

        // Quarter 2: live components (40 + 33 = 73) PLUS a stale duplicate TERM_TOTAL (73).
        // Must appear once as 73, not doubled to 146.
        $q2 = Term::factory()->quarter(2)->create(['academic_year_id' => $this->academicYear->id]);
        $this->makeMark($student, $subject, $this->componentType, 40, $q2);
        $this->makeMark($student, $subject, $this->componentType, 33, $q2);
        $this->makeMark($student, $subject, $this->termTotalType, 73, $q2);

        // Quarter 3: components only (44 + 45 = 89). Unaffected.
        $q3 = Term::factory()->quarter(3)->create(['academic_year_id' => $this->academicYear->id]);
        $this->makeMark($student, $subject, $this->componentType, 44, $q3);
        $this->makeMark($student, $subject, $this->componentType, 45, $q3);

        $response = $this->actingAs($admin)->get(route('admin.students.show', $student));
        $response->assertOk();

        $terms = collect($response->viewData('academicRecords'))->first();
        $subjectTotal = fn ($termName) => $terms->get($termName)->where('subject_id', $subject->id)->sum('score');

        // 1. TERM_TOTAL-only term still present, shown at its fallback value.
        $this->assertTrue($terms->has('Quarter 1'), 'A term with only a TERM_TOTAL entry must not vanish.');
        $this->assertEquals(80, $subjectTotal('Quarter 1'));

        // 2. Components + stale duplicate TERM_TOTAL: shown once, not doubled.
        $this->assertTrue($terms->has('Quarter 2'));
        $this->assertEquals(73, $subjectTotal('Quarter 2'));
        $this->assertCount(2, $terms->get('Quarter 2')->where('subject_id', $subject->id), 'Only the 2 component marks should survive, not the TERM_TOTAL.');

        // 3. Components-only term: unaffected.
        $this->assertTrue($terms->has('Quarter 3'));
        $this->assertEquals(89, $subjectTotal('Quarter 3'));
    }

    /** @test */
    public function student_profile_shows_computed_semester_and_yearly_rows_from_quarter_data_only(): void
    {
        Role::findOrCreate('Super Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        // Full term hierarchy: Semester 1 (Q1,Q2), Semester 2 (Q3,Q4).
        $sem1 = Term::factory()->semester(1)->create(['academic_year_id' => $this->academicYear->id]);
        $sem2 = Term::factory()->semester(2)->create(['academic_year_id' => $this->academicYear->id]);
        $q1 = $this->quarter;
        $q1->update(['parent_term_id' => $sem1->id]);
        $q2 = Term::factory()->quarter(2)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $sem1->id]);
        $q3 = Term::factory()->quarter(3)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $sem2->id]);
        $q4 = Term::factory()->quarter(4)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $sem2->id]);

        $student = Student::factory()->create();
        $this->enroll($student);
        $subject = Subject::factory()->create(['is_elective' => false]);
        $this->gradeLevel->subjects()->attach($subject->id, [
            'academic_year_id' => $this->academicYear->id,
            'is_required' => true,
            'sort_order' => 1,
        ]);

        // ONLY quarter component data — no semester marks are ever written.
        // Q1=60, Q2=80 => Semester 1 = 70 ; Q3=90, Q4=100 => Semester 2 = 95 ;
        // Yearly = (70 + 95) / 2 = 82.5.
        $this->makeMark($student, $subject, $this->componentType, 60, $q1);
        $this->makeMark($student, $subject, $this->componentType, 80, $q2);
        $this->makeMark($student, $subject, $this->componentType, 90, $q3);
        $this->makeMark($student, $subject, $this->componentType, 100, $q4);

        $response = $this->actingAs($admin)->get(route('admin.students.show', $student));
        $response->assertOk();

        $terms = collect($response->viewData('academicRecords'))->first();
        $subjectTotal = fn ($termName) => $terms->get($termName)->where('subject_id', $subject->id)->sum('score');

        // Semester and Yearly rows appear even though no semester marks exist — computed live.
        $this->assertTrue($terms->has('Semester 1'), 'Semester 1 must be computed from quarters.');
        $this->assertTrue($terms->has('Semester 2'), 'Semester 2 must appear even with no semester marks.');
        $this->assertTrue($terms->has('Yearly'), 'Yearly must be computed from semesters.');

        $this->assertEquals(70, $subjectTotal('Semester 1'));   // (60 + 80) / 2
        $this->assertEquals(95, $subjectTotal('Semester 2'));   // (90 + 100) / 2
        $this->assertEquals(82.5, $subjectTotal('Yearly'));     // (70 + 95) / 2

        // Quarters still render from their real component marks.
        $this->assertEquals(60, $subjectTotal('Quarter 1'));
        $this->assertEquals(100, $subjectTotal('Quarter 4'));

        // The Yearly badge (avg) is present and matches, sourced from the same computation.
        $records = collect($response->viewData('termRecords'));
        $yearRecords = $records->first();
        $this->assertNotNull($yearRecords->get('Yearly'), 'Yearly should carry an avg/rank badge record.');
        $this->assertEquals(82.5, $yearRecords->get('Yearly')->average_score);
    }

    /** @test */
    public function admin_dashboard_subject_average_prefers_components_over_stale_term_total(): void
    {
        Role::findOrCreate('Super Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $subject = Subject::factory()->create();
        $this->gradeLevel->subjects()->attach($subject->id, [
            'academic_year_id' => $this->academicYear->id,
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $student = Student::factory()->create();
        $this->enroll($student);

        // Live components average to 80; a stale TERM_TOTAL still says 40.
        $this->makeMark($student, $subject, $this->componentType, 40);
        $this->makeMark($student, $subject, $this->componentType, 40);
        $this->makeMark($student, $subject, $this->termTotalType, 40);

        $response = $this->actingAs($admin)->get(
            route('admin.dashboard', [
                'fetch_academic_excellence' => 1,
                'term_id' => $this->quarter->id,
                'grade_level_id' => $this->gradeLevel->id,
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $averages = collect($response->json('subjectAverages'));
        $row = $averages->firstWhere('subject_name', $subject->name);

        $this->assertNotNull($row, 'Expected the subject to appear in dashboard averages.');
        $this->assertEquals(80, $row['average'], 'Dashboard must use live components (80), not the stale TERM_TOTAL (40).');
    }

    /** @test */
    public function parent_dashboard_alerts_use_components_not_stale_term_total(): void
    {
        Role::findOrCreate('Parent');
        $parent = User::factory()->create();
        $parent->assignRole('Parent');

        $student = Student::factory()->create();
        $this->enroll($student);
        StudentGuardian::create([
            'student_id' => $student->id,
            'user_id' => $parent->id,
            'guardian_type' => 'primary',
            'first_name' => 'Test',
            'father_name' => 'Parent',
            'grandfather_name' => 'Guardian',
            'phone' => '0900000000',
        ]);

        $subject = Subject::factory()->create();

        // Components sum to 80 (a healthy grade, no alert). A stale TERM_TOTAL of 40
        // would, under the old precedence, wrongly fire a "critical" low-grade alert.
        $this->makeMark($student, $subject, $this->componentType, 40);
        $this->makeMark($student, $subject, $this->componentType, 40);
        $this->makeMark($student, $subject, $this->termTotalType, 40);

        $response = $this->actingAs($parent)->get(route('parent.dashboard'));
        $response->assertOk();

        $child = collect($response->viewData('children'))->firstWhere('id', $student->id);
        $this->assertNotNull($child);

        $alerts = $child->alerts_by_term[$this->quarter->id] ?? ['critical' => [], 'warning' => []];
        $this->assertEmpty($alerts['critical'], 'No critical alert should fire: real grade is 80, not the stale 40.');
        $this->assertEmpty($alerts['warning'], 'No warning alert should fire for a component grade of 80.');
    }

    private function enroll(Student $student): void
    {
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeMark(Student $student, Subject $subject, AssessmentType $type, float $score, ?Term $term = null): void
    {
        $term = $term ?? $this->quarter;

        $template = AssessmentTemplate::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $term->id,
            'assessment_type_id' => $type->id,
        ]);

        StudentMark::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $term->id,
            'subject_id' => $subject->id,
            'assessment_template_id' => $template->id,
            'section_id' => $this->section->id,
            'score' => $score,
        ]);
    }
}
