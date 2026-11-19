<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Whether a subject is an elective is set per grade and year: CTE Marketing was an
 * elective last year and is a regular subject this year. Last year must be unaffected.
 */
class PerYearElectiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $lastYear;

    protected AcademicYear $thisYear;

    protected GradeLevel $grade;

    protected Section $section;

    protected Subject $maths;

    protected Subject $marketing;

    /** @var Student[] */
    protected array $students;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::flush();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Permission::firstOrCreate(['name' => 'manage subjects']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->lastYear = AcademicYear::factory()->create(['is_active' => false, 'start_date' => '2025-09-15', 'end_date' => '2026-06-26']);
        $this->thisYear = AcademicYear::factory()->active()->create(['start_date' => '2026-09-14', 'end_date' => '2027-07-11']);

        $division = Division::factory()->create(['is_active' => true]);
        $this->grade = GradeLevel::factory()->create(['division_id' => $division->id, 'name' => 'Grade 12 (Natural)']);
        $this->section = Section::factory()->create(['grade_level_id' => $this->grade->id, 'name' => 'A']);
        $this->maths = Subject::factory()->create(['name' => 'Mathematics', 'is_elective' => false]);
        $this->marketing = Subject::factory()->create(['name' => 'CTE(Marketing)', 'is_elective' => true]);

        foreach ([$this->lastYear, $this->thisYear] as $year) {
            foreach ([$this->maths, $this->marketing] as $subject) {
                DB::table('grade_level_subjects')->insert([
                    'grade_level_id' => $this->grade->id, 'subject_id' => $subject->id,
                    'academic_year_id' => $year->id, 'is_required' => true,
                ]);
            }
        }

        // Two students in both years; only the first chose Marketing last year.
        $this->students = Student::factory()->count(2)->create(['is_active' => true])->all();
        foreach ($this->students as $student) {
            foreach ([$this->lastYear, $this->thisYear] as $year) {
                $student->enrollments()->create([
                    'section_id' => $this->section->id, 'academic_year_id' => $year->id,
                    'enrollment_date' => $year->start_date, 'status' => 'active',
                ]);
            }
        }
        DB::table('student_electives')->insert([
            'student_id' => $this->students[0]->id, 'subject_id' => $this->marketing->id, 'academic_year_id' => $this->lastYear->id,
        ]);
    }

    private function makeMarketingRegularThisYear(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.subject-assignments.update', $this->grade), [
                'academic_year_id' => $this->thisYear->id,
                'subjects' => [$this->maths->id, $this->marketing->id],
                'electives' => [],
            ])->assertSessionHasNoErrors();
    }

    private function quarter(AcademicYear $year): Term
    {
        $semester = Term::create([
            'academic_year_id' => $year->id, 'name' => 'Semester 1', 'type' => 'semester', 'term_number' => 1,
            'start_date' => $year->start_date, 'end_date' => $year->start_date->copy()->addMonths(4),
        ]);

        return Term::create([
            'academic_year_id' => $year->id, 'parent_term_id' => $semester->id, 'name' => 'Quarter 1', 'type' => 'quarter',
            'term_number' => 1, 'start_date' => $year->start_date, 'end_date' => $year->start_date->copy()->addMonths(2),
            'is_grading_open' => true,
        ]);
    }

    /** @test */
    public function the_setting_is_stored_for_this_year_only(): void
    {
        $this->makeMarketingRegularThisYear();

        $this->assertDatabaseHas('grade_level_subjects', ['subject_id' => $this->marketing->id, 'academic_year_id' => $this->thisYear->id, 'is_elective' => false]);
        $this->assertDatabaseHas('grade_level_subjects', ['subject_id' => $this->marketing->id, 'academic_year_id' => $this->lastYear->id, 'is_elective' => null]);

        $this->assertFalse($this->grade->subjectsForYear($this->thisYear->id)->find($this->marketing->id)->is_elective);
        $this->assertTrue($this->grade->subjectsForYear($this->lastYear->id)->find($this->marketing->id)->is_elective);
        // The subject's own setting is unchanged.
        $this->assertTrue($this->marketing->fresh()->is_elective);
        // Maths matches its own setting, so nothing is stored for it.
        $this->assertDatabaseHas('grade_level_subjects', ['subject_id' => $this->maths->id, 'academic_year_id' => $this->thisYear->id, 'is_elective' => null]);
    }

    /** @test */
    public function the_gradebook_lists_every_student_this_year_and_only_electives_last_year(): void
    {
        $this->makeMarketingRegularThisYear();

        $entry = fn (AcademicYear $year) => $this->actingAs($this->admin)->get(route('admin.gradebook.entry', [
            'section_id' => $this->section->id, 'subject_id' => $this->marketing->id,
            'academic_year_id' => $year->id, 'term_id' => $this->quarter($year)->id,
        ]))->assertOk()->viewData('students');

        $this->assertCount(2, $entry($this->thisYear));
        $this->assertCount(1, $entry($this->lastYear));
    }

    /** @test */
    public function last_years_averages_still_treat_marketing_as_an_elective(): void
    {
        $quarter = $this->quarter($this->lastYear);
        $typeId = DB::table('assessment_types')->insertGetId(['name' => 'Final Exam', 'code' => 'FINAL', 'is_active' => true]);
        $templateId = DB::table('assessment_templates')->insertGetId([
            'academic_year_id' => $this->lastYear->id, 'term_id' => $quarter->id, 'assessment_type_id' => $typeId,
            'name' => 'Final', 'weight' => 100, 'max_score' => 100, 'order' => 0, 'is_active' => true,
        ]);
        foreach ($this->students as $student) {
            DB::table('student_marks')->insert([
                'student_id' => $student->id, 'subject_id' => $this->maths->id, 'term_id' => $quarter->id,
                'academic_year_id' => $this->lastYear->id, 'assessment_template_id' => $templateId, 'section_id' => $this->section->id, 'teacher_id' => $this->admin->id,
                'score' => 80, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $average = fn () => app(GradingService::class)->calculateStudentTotals(
            $this->students[1], $quarter, $this->lastYear, $this->grade->subjectsForYear($this->lastYear->id)->get()
        )['average'];

        $before = $average();
        $this->makeMarketingRegularThisYear();
        Cache::flush();

        // The second student never took Marketing; it must not count as a missing mark.
        $this->assertEquals(80, $before);
        $this->assertEquals($before, $average());
    }
}
