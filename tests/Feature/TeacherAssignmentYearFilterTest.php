<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Employee;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The Teacher Assignments page has an academic year filter. It defaults to the active year
 * (editable); any other year is shown read-only and lists whoever was assigned then.
 */
class TeacherAssignmentYearFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $lastYear;

    protected AcademicYear $thisYear;

    protected Employee $current;

    protected Employee $resigned;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::flush();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Teacher']);
        Permission::firstOrCreate(['name' => 'manage employees']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->lastYear = AcademicYear::factory()->create(['name' => '2025/26', 'is_active' => false, 'start_date' => '2025-09-15', 'end_date' => '2026-06-26']);
        $this->thisYear = AcademicYear::factory()->active()->create(['name' => '2026/27', 'start_date' => '2026-09-14', 'end_date' => '2027-07-11']);

        $division = Division::factory()->create(['is_active' => true]);
        $grade = GradeLevel::factory()->create(['division_id' => $division->id]);
        $section = Section::factory()->create(['grade_level_id' => $grade->id, 'name' => 'A']);
        $maths = Subject::factory()->create(['name' => 'Mathematics']);
        $physics = Subject::factory()->create(['name' => 'Physics']);

        $this->current = Employee::factory()->create(['first_name' => 'Currenta', 'status' => 'active']);
        $this->resigned = Employee::factory()->create(['first_name' => 'Resignedo', 'status' => 'resigned']);

        TeacherAssignment::create(['teacher_id' => $this->current->user_id, 'section_id' => $section->id, 'subject_id' => $maths->id, 'academic_year_id' => $this->thisYear->id]);
        TeacherAssignment::create(['teacher_id' => $this->current->user_id, 'section_id' => $section->id, 'subject_id' => $physics->id, 'academic_year_id' => $this->lastYear->id]);
        TeacherAssignment::create(['teacher_id' => $this->resigned->user_id, 'section_id' => $section->id, 'subject_id' => $maths->id, 'academic_year_id' => $this->lastYear->id]);
    }

    private function page(array $query = [])
    {
        return $this->actingAs($this->admin)->get(route('admin.teacher-assignments.index', $query));
    }

    /** @test */
    public function it_defaults_to_the_active_year_and_stays_editable(): void
    {
        $response = $this->page()->assertOk();

        $response->assertSee('CURRENTA')->assertDontSee('RESIGNEDO');
        $response->assertSee('Mathematics')->assertDontSee('Physics');
        $response->assertSee('Remove this assignment?', false);
        $response->assertSee('teacher_user_id='.$this->current->user_id, false);
        $response->assertDontSee('read-only');
    }

    /** @test */
    public function another_year_shows_that_years_assignments_read_only(): void
    {
        $response = $this->page(['academic_year_id' => $this->lastYear->id])->assertOk();

        // Last year's assignments, including a teacher who has since resigned.
        $response->assertSee('CURRENTA')->assertSee('RESIGNEDO');
        $response->assertSee('Physics');
        $response->assertSee('read-only');
        $response->assertDontSee('Remove this assignment?', false);
        $response->assertDontSee('teacher_user_id=', false);
        $this->assertSame($this->lastYear->id, $response->viewData('selectedYear')->id);
    }

    /** @test */
    public function an_unknown_year_falls_back_to_the_active_year(): void
    {
        $response = $this->page(['academic_year_id' => 999999])->assertOk();

        $this->assertSame($this->thisYear->id, $response->viewData('selectedYear')->id);
        $this->assertTrue($response->viewData('isActiveYear'));
    }
}
