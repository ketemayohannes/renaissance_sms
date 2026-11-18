<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Services\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * A transfer within a year closes the old enrollment and opens a new one in the same
 * year, so the student has two rows for that year. Reports must follow the open row.
 */
class StudentProfileAfterTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $year;

    protected Section $from;

    protected Section $to;

    protected Term $semester;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->year = AcademicYear::factory()->active()->create();
        $division = Division::factory()->create(['is_active' => true]);
        $grade = GradeLevel::factory()->create(['division_id' => $division->id]);
        $this->from = Section::factory()->create(['grade_level_id' => $grade->id, 'name' => 'A']);
        $this->to = Section::factory()->create(['grade_level_id' => $grade->id, 'name' => 'B']);

        $this->semester = Term::create([
            'academic_year_id' => $this->year->id, 'name' => 'Semester 1', 'type' => 'semester', 'term_number' => 1,
            'start_date' => '2026-09-14', 'end_date' => '2027-02-05',
        ]);
        Term::create([
            'academic_year_id' => $this->year->id, 'parent_term_id' => $this->semester->id, 'name' => 'Quarter 1',
            'type' => 'quarter', 'term_number' => 1, 'start_date' => '2026-09-14', 'end_date' => '2026-11-20',
        ]);
    }

    private function transferredStudent(): Student
    {
        $student = Student::factory()->create();
        $student->enrollments()->create([
            'section_id' => $this->from->id, 'academic_year_id' => $this->year->id,
            'enrollment_date' => '2026-09-14', 'end_date' => '2026-11-18', 'status' => 'transferred',
        ]);
        $student->enrollments()->create([
            'section_id' => $this->to->id, 'academic_year_id' => $this->year->id,
            'enrollment_date' => '2026-11-18', 'status' => 'active',
        ]);

        return $student;
    }

    /** @test */
    public function profile_of_a_student_transferred_within_the_year_loads(): void
    {
        $student = $this->transferredStudent();

        $this->actingAs($this->admin)->get(route('admin.students.show', $student))->assertOk();
    }

    /** @test */
    public function report_uses_the_section_the_student_was_transferred_into(): void
    {
        $student = $this->transferredStudent();

        $report = app(GradingService::class)->getStudentReportData($student, $this->semester, $this->year);

        $this->assertNotEmpty($report);
        $this->assertSame($this->to->id, $report['section']->id);
    }

    /** @test */
    public function profile_of_a_withdrawn_student_loads(): void
    {
        $student = Student::factory()->create(['is_active' => false]);
        $student->enrollments()->create([
            'section_id' => $this->from->id, 'academic_year_id' => $this->year->id,
            'enrollment_date' => '2026-09-14', 'end_date' => '2026-11-18', 'status' => 'withdrawn',
        ]);

        $this->actingAs($this->admin)->get(route('admin.students.show', $student))->assertOk();
    }
}
