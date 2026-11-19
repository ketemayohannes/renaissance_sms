<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Grade ↔ subject assignments are kept per academic year, so changing this year's list
 * must not change last year's (which past report cards and averages are built from).
 */
class YearScopedSubjectAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $lastYear;

    protected AcademicYear $thisYear;

    protected GradeLevel $grade;

    protected Subject $maths;

    protected Subject $marketing;

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
        $this->maths = Subject::factory()->create(['name' => 'Mathematics']);
        $this->marketing = Subject::factory()->create(['name' => 'CTE(Marketing)', 'is_elective' => true]);

        foreach ([$this->lastYear, $this->thisYear] as $year) {
            foreach ([$this->maths, $this->marketing] as $subject) {
                DB::table('grade_level_subjects')->insert([
                    'grade_level_id' => $this->grade->id, 'subject_id' => $subject->id,
                    'academic_year_id' => $year->id, 'is_required' => true,
                ]);
            }
        }
    }

    private function subjectNames(?AcademicYear $year = null): array
    {
        $relation = $year ? $this->grade->subjectsForYear($year->id) : $this->grade->subjects();

        return $relation->orderBy('subjects.name')->pluck('subjects.name')->all();
    }

    /** @test */
    public function removing_a_subject_for_this_year_keeps_it_for_last_year(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.subject-assignments.update', $this->grade), [
                'academic_year_id' => $this->thisYear->id,
                'subjects' => [$this->maths->id],
            ])
            ->assertRedirect(route('admin.subject-assignments.index', ['academic_year_id' => $this->thisYear->id]));

        $this->assertSame(['Mathematics'], $this->subjectNames($this->thisYear));
        $this->assertSame(['CTE(Marketing)', 'Mathematics'], $this->subjectNames($this->lastYear));
        // The default relation follows the active year.
        $this->assertSame(['Mathematics'], $this->subjectNames());
    }

    /** @test */
    public function the_assignment_screens_show_the_selected_years_list(): void
    {
        DB::table('grade_level_subjects')->where('academic_year_id', $this->thisYear->id)
            ->where('subject_id', $this->marketing->id)->delete();

        $this->actingAs($this->admin)
            ->get(route('admin.subject-assignments.edit', ['grade_level' => $this->grade, 'academic_year_id' => $this->lastYear->id]))
            ->assertOk()
            ->assertViewHas('assignedSubjectIds', fn ($ids) => count($ids) === 2);

        $this->actingAs($this->admin)
            ->get(route('admin.subject-assignments.edit', $this->grade))
            ->assertOk()
            ->assertViewHas('academicYear', fn ($y) => $y->is($this->thisYear))
            ->assertViewHas('assignedSubjectIds', fn ($ids) => $ids === [$this->maths->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.subject-assignments.index', ['academic_year_id' => $this->lastYear->id]))
            ->assertOk()
            ->assertViewHas('subjectCounts', fn ($c) => (int) $c[$this->grade->id] === 2);
    }

    /** @test */
    public function bulk_assign_only_changes_the_chosen_year(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.subject-assignments.bulk-assign.store'), [
                'academic_year_id' => $this->lastYear->id,
                'grade_levels' => [$this->grade->id],
                'subjects' => [$this->marketing->id],
            ])->assertSessionHasNoErrors();

        $this->assertSame(['CTE(Marketing)'], $this->subjectNames($this->lastYear));
        $this->assertSame(['CTE(Marketing)', 'Mathematics'], $this->subjectNames($this->thisYear));
    }

    /** @test */
    public function a_new_year_starts_with_the_previous_years_subjects(): void
    {
        DB::table('grade_level_subjects')->where('academic_year_id', $this->thisYear->id)
            ->where('subject_id', $this->marketing->id)->delete();

        $nextYear = AcademicYear::factory()->create(['is_active' => false, 'start_date' => '2027-09-13', 'end_date' => '2028-07-10']);

        $this->assertSame(['Mathematics'], $this->subjectNames($nextYear));
        $this->assertSame(['CTE(Marketing)', 'Mathematics'], $this->subjectNames($this->lastYear));
    }

    /** @test */
    public function attaching_through_the_relation_records_the_active_year(): void
    {
        $physics = Subject::factory()->create(['name' => 'Physics']);
        $this->grade->subjects()->attach($physics->id, ['is_required' => true, 'sort_order' => 3]);

        $this->assertDatabaseHas('grade_level_subjects', [
            'grade_level_id' => $this->grade->id, 'subject_id' => $physics->id, 'academic_year_id' => $this->thisYear->id,
        ]);
        $this->assertNotContains('Physics', $this->subjectNames($this->lastYear));
    }
}
