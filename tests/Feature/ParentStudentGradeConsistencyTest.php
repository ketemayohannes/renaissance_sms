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
 * The parent and student grade views must render the same GradingService-computed
 * history as the admin profile and the report card: quarters resolved components-first,
 * and Semester 2 / Yearly computed live (they appear even with no semester marks).
 */
class ParentStudentGradeConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $academicYear;
    protected GradeLevel $gradeLevel;
    protected Section $section;
    protected Subject $subject;
    protected AssessmentType $componentType;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $division = Division::factory()->create(['name' => 'Primary']);
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->gradeLevel = GradeLevel::factory()->create(['division_id' => $division->id, 'name' => 'Grade 5']);
        $this->section = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'A',
            'is_active' => true,
        ]);

        // Term hierarchy: Semester 1 (Q1,Q2), Semester 2 (Q3,Q4).
        $sem1 = Term::factory()->semester(1)->create(['academic_year_id' => $this->academicYear->id]);
        $sem2 = Term::factory()->semester(2)->create(['academic_year_id' => $this->academicYear->id]);
        $q1 = Term::factory()->quarter(1)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $sem1->id, 'start_date' => now()->subMonth()]);
        $q2 = Term::factory()->quarter(2)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $sem1->id]);
        $q3 = Term::factory()->quarter(3)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $sem2->id]);
        $q4 = Term::factory()->quarter(4)->create(['academic_year_id' => $this->academicYear->id, 'parent_term_id' => $sem2->id]);

        AssessmentType::factory()->termTotal()->create();
        $this->componentType = AssessmentType::factory()->create(['code' => 'QZ1', 'name' => 'Quiz 1']);

        $this->subject = Subject::factory()->create(['is_elective' => false]);
        $this->gradeLevel->subjects()->attach($this->subject->id, [
            'academic_year_id' => $this->academicYear->id,
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->quarters = compact('q1', 'q2', 'q3', 'q4');
    }

    protected array $quarters = [];

    /** @test */
    public function parent_grade_view_shows_computed_semester_and_yearly_matching_the_report_card(): void
    {
        Role::findOrCreate('Parent');
        $parent = User::factory()->create();
        $parent->assignRole('Parent');

        $child = $this->makeEnrolledStudentWithQuarterMarks();
        StudentGuardian::create([
            'student_id' => $child->id,
            'user_id' => $parent->id,
            'guardian_type' => 'primary',
            'first_name' => 'Test', 'father_name' => 'Parent', 'grandfather_name' => 'Guardian', 'phone' => '0900000000',
        ]);

        $response = $this->actingAs($parent)->get(route('parent.student.grades.index', $child));
        $response->assertOk();

        $this->assertTermsPresentAndCorrect($response->viewData('groupedMarks'), $response->viewData('termRecords'));
    }

    /** @test */
    public function student_grade_view_shows_computed_semester_and_yearly_matching_the_report_card(): void
    {
        Role::findOrCreate('Student');
        $student = $this->makeEnrolledStudentWithQuarterMarks();
        $user = User::factory()->create();
        $user->assignRole('Student');
        $student->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('student.grades.index'));
        $response->assertOk();

        $this->assertTermsPresentAndCorrect($response->viewData('grades'), $response->viewData('termRecords'));
    }

    /** @test */
    public function parent_and_student_views_split_into_report_and_assessment_tabs(): void
    {
        Role::findOrCreate('Parent');
        Role::findOrCreate('Student');

        $child = $this->makeEnrolledStudentWithQuarterMarks();

        $parent = User::factory()->create();
        $parent->assignRole('Parent');
        StudentGuardian::create([
            'student_id' => $child->id,
            'user_id' => $parent->id,
            'guardian_type' => 'primary',
            'first_name' => 'Test', 'father_name' => 'Parent', 'grandfather_name' => 'Guardian', 'phone' => '0900000000',
        ]);

        $parentResponse = $this->actingAs($parent)->get(route('parent.student.grades.index', $child));
        $parentResponse->assertOk();
        $parentResponse->assertSee('Report');
        $parentResponse->assertSee('Assessment');
        // The component template name only appears on the Assessment tab (quarter matrix);
        // its presence confirms that tab actually rendered real per-component data.
        $parentResponse->assertSee('Quiz 1');
        $parentResponse->assertDontSee('No quarter assessment data');

        $studentUser = User::factory()->create();
        $studentUser->assignRole('Student');
        $child->update(['user_id' => $studentUser->id]);

        $studentResponse = $this->actingAs($studentUser)->get(route('student.grades.index'));
        $studentResponse->assertOk();
        $studentResponse->assertSee('Report');
        $studentResponse->assertSee('Assessment');
        $studentResponse->assertSee('Quiz 1');
        $studentResponse->assertDontSee('No quarter assessment data');
    }

    private function makeEnrolledStudentWithQuarterMarks(): Student
    {
        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Only quarter components: Q1=60, Q2=80 => Sem1 70 ; Q3=90, Q4=100 => Sem2 95 ; Yearly 82.5
        $scores = [$this->quarters['q1']->id => 60, $this->quarters['q2']->id => 80, $this->quarters['q3']->id => 90, $this->quarters['q4']->id => 100];
        foreach ($scores as $termId => $score) {
            $template = AssessmentTemplate::factory()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $termId,
                'assessment_type_id' => $this->componentType->id,
                'name' => 'Quiz 1',
            ]);
            StudentMark::factory()->create([
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $termId,
                'subject_id' => $this->subject->id,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
                'score' => $score,
            ]);
        }

        return $student;
    }

    /** @test */
    public function parent_and_student_grade_views_do_not_truncate_scores_to_one_decimal(): void
    {
        // Regression for a real reported bug: Yearly Environmental Science was computed as
        // 98.25, but the parent/student/admin views hard-rounded to 1 decimal (number_format
        // ($x, 1)) and showed "98.3" while the report card (NumberFormatter::format, full
        // precision) correctly showed "98.25". Use scores that produce a value with a
        // non-trivial second decimal digit so 1-decimal truncation would be visible.
        Role::findOrCreate('Parent');
        Role::findOrCreate('Student');

        $child = $this->makeEnrolledStudentWithQuarterMarks();

        // Q1=98, Q2=98 => Sem1=98 ; Q3=98.5, Q4=99 => Sem2=98.75 ; Yearly=(98+98.75)/2=98.375,
        // rounded to 2dp by GradingService => 98.38 (not representable in 1 decimal without
        // rounding to 98.4).
        StudentMark::where('student_id', $child->id)->delete();
        $scores = [
            $this->quarters['q1']->id => 98,
            $this->quarters['q2']->id => 98,
            $this->quarters['q3']->id => 98.5,
            $this->quarters['q4']->id => 99,
        ];
        foreach ($scores as $termId => $score) {
            $template = AssessmentTemplate::factory()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $termId,
                'assessment_type_id' => $this->componentType->id,
            ]);
            StudentMark::factory()->create([
                'student_id' => $child->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $termId,
                'subject_id' => $this->subject->id,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
                'score' => $score,
            ]);
        }

        $parent = User::factory()->create();
        $parent->assignRole('Parent');
        StudentGuardian::create([
            'student_id' => $child->id,
            'user_id' => $parent->id,
            'guardian_type' => 'primary',
            'first_name' => 'Test', 'father_name' => 'Parent', 'grandfather_name' => 'Guardian', 'phone' => '0900000000',
        ]);

        $parentResponse = $this->actingAs($parent)->get(route('parent.student.grades.index', $child));
        $parentResponse->assertOk();
        $parentResponse->assertSee('98.38');
        $parentResponse->assertDontSee('98.4');

        $studentUser = User::factory()->create();
        $studentUser->assignRole('Student');
        $child->update(['user_id' => $studentUser->id]);

        $studentResponse = $this->actingAs($studentUser)->get(route('student.grades.index'));
        $studentResponse->assertOk();
        $studentResponse->assertSee('98.38');
        $studentResponse->assertDontSee('98.4');
    }

    private function assertTermsPresentAndCorrect($groupedMarks, $termRecords): void
    {
        $groupedMarks = collect($groupedMarks);
        $termRecords = collect($termRecords);

        foreach (['Quarter 1', 'Quarter 2', 'Quarter 3', 'Quarter 4', 'Semester 1', 'Semester 2', 'Yearly'] as $term) {
            $this->assertTrue($groupedMarks->has($term), "$term should appear in the grade view.");
        }

        $subjectTotal = fn ($term) => $groupedMarks->get($term)->where('subject_id', $this->subject->id)->sum('score');
        $this->assertEquals(70, $subjectTotal('Semester 1'));
        $this->assertEquals(95, $subjectTotal('Semester 2'));
        $this->assertEquals(82.5, $subjectTotal('Yearly'));

        // Badge data present for the computed rows.
        $this->assertEquals(95, $termRecords->get('Semester 2')->average_score);
        $this->assertEquals(82.5, $termRecords->get('Yearly')->average_score);
    }
}
