<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * After promotion last year's enrollment stays open, so a student has an open row in
 * both years. The capacity report must count each student once, in the active year.
 */
class AdminDashboardCapacityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function capacity_report_counts_only_the_active_years_enrollments(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Cache::flush();
        foreach (['Super Admin', 'Principal', 'Teacher', 'Accountant', 'HR Manager', 'Librarian', 'Staff', 'IT / System Admin', 'Registrar', 'General Manager', 'Student', 'Parent'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $lastYear = AcademicYear::factory()->create(['is_active' => false]);
        $thisYear = AcademicYear::factory()->active()->create();
        $division = Division::factory()->create(['is_active' => true]);
        $grade8 = GradeLevel::factory()->create(['division_id' => $division->id, 'name' => 'Grade 8', 'sort_order' => 8]);
        $grade9 = GradeLevel::factory()->create(['division_id' => $division->id, 'name' => 'Grade 9', 'sort_order' => 9]);
        $s8 = Section::factory()->create(['grade_level_id' => $grade8->id, 'name' => 'A']);
        $s9 = Section::factory()->create(['grade_level_id' => $grade9->id, 'name' => 'A']);

        foreach (Student::factory()->count(3)->create(['is_active' => true]) as $student) {
            $student->enrollments()->create(['section_id' => $s8->id, 'academic_year_id' => $lastYear->id, 'enrollment_date' => '2025-09-15', 'status' => 'active']);
            $student->enrollments()->create(['section_id' => $s9->id, 'academic_year_id' => $thisYear->id, 'enrollment_date' => '2026-09-14', 'status' => 'active']);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $byGrade = $response->viewData('studentsByGrade')->pluck('count', 'grade_name');
        $this->assertEquals(['Grade 9' => 3], $byGrade->map(fn ($c) => (int) $c)->all());

        // The division-filtered view uses the same year filter for its student counts.
        $filtered = $this->actingAs($admin)->get(route('admin.dashboard', ['division_id' => $division->id]))->assertOk();
        $this->assertSame(3, $filtered->viewData('stats')['total_students']);
    }
}
