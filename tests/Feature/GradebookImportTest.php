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
use App\Services\GradeCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The admin and teacher gradebook CSV imports share one set of rules. Each test runs
 * through both portals' routes.
 */
class GradebookImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected AcademicYear $year;

    protected Term $term;

    protected Section $section;

    protected Subject $subject;

    protected AssessmentTemplate $template;

    protected TeacherAssignment $assignment;

    protected Student $student;

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

        $division = Division::factory()->create();
        $this->year = AcademicYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create(['division_id' => $division->id]);
        $this->section = Section::factory()->create([
            'grade_level_id' => $grade->id,
            'academic_year_id' => $this->year->id,
        ]);
        $this->term = Term::factory()->quarter(1)->create(['academic_year_id' => $this->year->id]);
        $this->subject = Subject::factory()->create();

        $this->template = AssessmentTemplate::factory()->create([
            'name' => 'Test 1',
            'max_score' => 20,
            'academic_year_id' => $this->year->id,
            'term_id' => $this->term->id,
            'assessment_type_id' => AssessmentType::factory()->create(['code' => 'QUIZ'])->id,
        ]);
        $this->template->assignments()->create(['grade_level_id' => $grade->id, 'subject_id' => $this->subject->id]);

        $this->assignment = TeacherAssignment::create([
            'teacher_id' => $this->teacher->id,
            'section_id' => $this->section->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->year->id,
        ]);

        $this->student = $this->enrolledStudent($this->section);
    }

    public static function portals(): array
    {
        return ['admin' => ['admin'], 'teacher' => ['teacher']];
    }

    private function enrolledStudent(Section $section): Student
    {
        $student = Student::factory()->create();
        $student->sections()->attach($section->id, [
            'academic_year_id' => $this->year->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        return $student;
    }

    /** @param  array<int, array<int, string>>  $rows */
    private function csv(array $rows): UploadedFile
    {
        $lines = array_map(fn ($r) => implode(',', $r), array_merge([['Student ID', 'Full Name', 'Test 1 (Max: 20)']], $rows));

        return UploadedFile::fake()->createWithContent('marks.csv', implode("\n", $lines)."\n");
    }

    private function import(string $portal, UploadedFile $file): TestResponse
    {
        if ($portal === 'admin') {
            return $this->actingAs($this->admin)->from('/back')->post(route('admin.gradebook.import'), [
                'file' => $file,
                'academic_year_id' => $this->year->id,
                'term_id' => $this->term->id,
                'section_id' => $this->section->id,
                'subject_id' => $this->subject->id,
            ]);
        }

        return $this->actingAs($this->teacher)->from('/back')->post(route('teacher.gradebook.import', $this->assignment), [
            'file' => $file,
            'term_id' => $this->term->id,
        ]);
    }

    private function scoreFor(Student $student): ?float
    {
        $score = StudentMark::where('student_id', $student->id)->where('assessment_template_id', $this->template->id)->value('score');

        return $score === null ? null : (float) $score;
    }

    #[Test]
    #[DataProvider('portals')]
    public function imports_valid_marks(string $portal): void
    {
        $this->import($portal, $this->csv([[$this->student->student_id, $this->student->full_name, '17']]))
            ->assertSessionHas('success', 'Imported 1 grades for 1 students.');

        $this->assertSame(17.0, $this->scoreFor($this->student));
    }

    #[Test]
    #[DataProvider('portals')]
    public function rejects_a_student_who_is_not_in_this_section(string $portal): void
    {
        $otherSection = Section::factory()->create([
            'grade_level_id' => $this->section->grade_level_id,
            'academic_year_id' => $this->year->id,
        ]);
        $outsider = $this->enrolledStudent($otherSection);

        $this->import($portal, $this->csv([[$outsider->student_id, $outsider->full_name, '15']]))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, "Student ID {$outsider->student_id} not found in this section"));

        $this->assertNull($this->scoreFor($outsider));
    }

    #[Test]
    #[DataProvider('portals')]
    public function rejects_a_row_whose_name_does_not_match_the_student_id(string $portal): void
    {
        $this->import($portal, $this->csv([[$this->student->student_id, 'Zzyzx Qwerty Plonk', '15']]))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'CRITICAL MISMATCH'));

        $this->assertNull($this->scoreFor($this->student));
    }

    #[Test]
    #[DataProvider('portals')]
    public function explains_why_when_every_row_was_rejected(string $portal): void
    {
        // Previously a bare "No grades were imported." hid the reasons.
        $this->import($portal, $this->csv([[$this->student->student_id, $this->student->full_name, '99']]))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'No grades were imported.')
                && str_contains($m, 'exceeds max 20'));
    }

    #[Test]
    #[DataProvider('portals')]
    public function rejects_scores_that_are_not_numbers_or_exceed_the_maximum(string $portal): void
    {
        $second = $this->enrolledStudent($this->section);

        $this->import($portal, $this->csv([
            [$this->student->student_id, $this->student->full_name, 'abc'],
            [$second->student_id, $second->full_name, '25'],
        ]))->assertSessionHas('warning');

        $this->assertNull($this->scoreFor($this->student));
        $this->assertNull($this->scoreFor($second));
    }

    #[Test]
    #[DataProvider('portals')]
    public function rejects_an_empty_file(string $portal): void
    {
        $this->import($portal, UploadedFile::fake()->createWithContent('empty.csv', ''))
            ->assertSessionHas('error', 'The uploaded file is empty or invalid.');
    }

    #[Test]
    #[DataProvider('portals')]
    public function clears_the_cached_roster_so_new_marks_show_immediately(string $portal): void
    {
        $cache = app(GradeCacheService::class);
        $cache->getRosterData($this->section->id, $this->term->id, $this->year->id, fn () => 'stale');

        $this->import($portal, $this->csv([[$this->student->student_id, $this->student->full_name, '12']]))
            ->assertSessionHas('success');

        $this->assertSame('fresh', $cache->getRosterData($this->section->id, $this->term->id, $this->year->id, fn () => 'fresh'));
    }

    #[Test]
    public function teacher_cannot_import_when_grading_is_closed(): void
    {
        $this->term->update(['is_grading_open' => false]);

        $this->import('teacher', $this->csv([[$this->student->student_id, $this->student->full_name, '12']]))
            ->assertSessionHas('error', 'Grading for this term is currently closed.');

        $this->assertNull($this->scoreFor($this->student));
    }

    #[Test]
    public function admin_store_clears_the_cached_roster(): void
    {
        $cache = app(GradeCacheService::class);
        $cache->getRosterData($this->section->id, $this->term->id, $this->year->id, fn () => 'stale');

        $this->actingAs($this->admin)->from('/back')->post(route('admin.gradebook.store'), [
            'academic_year_id' => $this->year->id,
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'section_id' => $this->section->id,
            'marks' => [$this->student->id => [$this->template->id => ['score' => 14]]],
        ])->assertSessionHas('success');

        $this->assertSame(14.0, $this->scoreFor($this->student));
        $this->assertSame('fresh', $cache->getRosterData($this->section->id, $this->term->id, $this->year->id, fn () => 'fresh'));
    }
}
