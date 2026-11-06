<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentType;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The admin and teacher gradebook entry screens share one score grid. A student whose
 * only mark for the subject+term is the Master Sheet TERM_TOTAL must show that total
 * (the documented fallback), not 0.00, on both screens.
 */
class GradebookEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected AcademicYear $year;

    protected Term $term;

    protected Section $section;

    protected Subject $subject;

    protected TeacherAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Teacher']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');

        $this->year = AcademicYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create(['division_id' => Division::factory()->create()->id]);
        $this->section = Section::factory()->create(['grade_level_id' => $grade->id, 'academic_year_id' => $this->year->id]);
        $this->term = Term::factory()->quarter(1)->create(['academic_year_id' => $this->year->id]);
        $this->subject = Subject::factory()->create();

        // One editable component (no marks entered) plus the Master Sheet TERM_TOTAL.
        $component = AssessmentTemplate::factory()->create([
            'name' => 'Test 1', 'max_score' => 100,
            'academic_year_id' => $this->year->id, 'term_id' => $this->term->id,
            'assessment_type_id' => AssessmentType::factory()->create(['code' => 'QUIZ'])->id,
        ]);
        $termTotal = AssessmentTemplate::factory()->termTotal()->create([
            'academic_year_id' => $this->year->id, 'term_id' => $this->term->id,
            'assessment_type_id' => AssessmentType::factory()->create(['code' => 'TERM_TOTAL'])->id,
        ]);
        foreach ([$component, $termTotal] as $template) {
            $template->assignments()->create(['grade_level_id' => $grade->id, 'subject_id' => $this->subject->id]);
        }

        $this->assignment = TeacherAssignment::create([
            'teacher_id' => $this->teacher->id, 'section_id' => $this->section->id,
            'subject_id' => $this->subject->id, 'academic_year_id' => $this->year->id,
        ]);

        $student = Student::factory()->create();
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->year->id, 'enrollment_date' => now(), 'status' => 'active',
        ]);
        StudentMark::create([
            'student_id' => $student->id, 'academic_year_id' => $this->year->id, 'term_id' => $this->term->id,
            'subject_id' => $this->subject->id, 'section_id' => $this->section->id,
            'assessment_template_id' => $termTotal->id, 'teacher_id' => $this->admin->id, 'score' => 87,
        ]);
    }

    public static function portals(): array
    {
        return ['admin' => ['admin'], 'teacher' => ['teacher']];
    }

    private function entryPage(string $portal): TestResponse
    {
        if ($portal === 'admin') {
            return $this->actingAs($this->admin)->get(route('admin.gradebook.entry', [
                'academic_year_id' => $this->year->id, 'section_id' => $this->section->id,
                'subject_id' => $this->subject->id, 'term_id' => $this->term->id,
            ]));
        }

        return $this->actingAs($this->teacher)->get(route('teacher.gradebook.entry', [$this->assignment, 'term_id' => $this->term->id]));
    }

    #[Test]
    #[DataProvider('portals')]
    public function shows_the_master_sheet_total_when_a_student_has_no_component_marks(string $portal): void
    {
        $html = $this->entryPage($portal)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/student-total[^>]*data-master-total="87(\.0+)?"[^>]*>\s*87\.00\s*</', $html);
    }
}
