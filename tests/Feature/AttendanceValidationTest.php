<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Section $section;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $year = AcademicYear::factory()->active()->create();
        $division = Division::factory()->create();
        $gradeLevel = GradeLevel::factory()->create(['division_id' => $division->id]);
        $this->section = Section::factory()->create([
            'grade_level_id' => $gradeLevel->id,
            'academic_year_id' => $year->id,
        ]);
        $this->student = Student::factory()->create();
        // Attendance is only saved for students enrolled in the section (any other id in
        // the request is ignored), matching what the register screen lists.
        $this->student->sections()->attach($this->section->id, [
            'academic_year_id' => $year->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_store_rejects_an_invalid_attendance_status(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.attendance.store'), [
                'section_id' => $this->section->id,
                'date' => now()->format('Y-m-d'),
                'attendance' => [$this->student->id => 'skipped'], // not in the allowed enum
            ])
            ->assertSessionHasErrors('attendance.'.$this->student->id);

        $this->assertDatabaseCount('student_attendance', 0);
    }

    /** @test */
    public function admin_store_rejects_a_future_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.attendance.store'), [
                'section_id' => $this->section->id,
                'date' => now()->addDay()->format('Y-m-d'),
                'attendance' => [$this->student->id => 'present'],
            ])
            ->assertSessionHasErrors('attendance_date');

        $this->assertDatabaseCount('student_attendance', 0);
    }

    /** @test */
    public function admin_store_accepts_valid_attendance(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.attendance.store'), [
                'section_id' => $this->section->id,
                'date' => now()->format('Y-m-d'),
                'attendance' => [$this->student->id => 'present'],
            ])
            ->assertRedirect(route('admin.attendance.index'));

        $this->assertDatabaseHas('student_attendance', [
            'section_id' => $this->section->id,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);
    }
}
