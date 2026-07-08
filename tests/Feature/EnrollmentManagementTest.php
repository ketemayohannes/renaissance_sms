<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentPromotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnrollmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private AcademicYear $currentYear;

    private AcademicYear $nextYear;

    private Section $nextSection;

    private Student $student;

    private StudentPromotion $promo;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $division = Division::factory()->create();
        $this->currentYear = AcademicYear::factory()->active()->create();
        $this->nextYear = AcademicYear::factory()->create([
            'is_active' => false,
            'start_date' => now()->addYear()->startOfYear(),
            'end_date' => now()->addYear()->endOfYear(),
        ]);

        $fromGrade = GradeLevel::factory()->create(['division_id' => $division->id, 'name' => 'Grade 5', 'sort_order' => 5]);
        $toGrade = GradeLevel::factory()->create(['division_id' => $division->id, 'name' => 'Grade 6', 'sort_order' => 6]);
        $fromSection = Section::factory()->create(['grade_level_id' => $fromGrade->id, 'academic_year_id' => $this->currentYear->id, 'name' => 'A']);
        $this->nextSection = Section::factory()->create(['grade_level_id' => $toGrade->id, 'academic_year_id' => $this->nextYear->id, 'name' => 'A']);
        $this->student = Student::factory()->create();

        // Current-year active enrollment (the "from" state).
        StudentEnrollment::create([
            'student_id' => $this->student->id,
            'section_id' => $fromSection->id,
            'academic_year_id' => $this->currentYear->id,
            'enrollment_date' => $this->currentYear->start_date,
            'status' => 'active',
        ]);

        // Held promotion decision awaiting enrollment.
        $this->promo = StudentPromotion::create([
            'student_id' => $this->student->id,
            'from_academic_year_id' => $this->currentYear->id,
            'to_academic_year_id' => $this->nextYear->id,
            'from_grade_level_id' => $fromGrade->id,
            'to_grade_level_id' => $toGrade->id,
            'to_section_id' => $this->nextSection->id,
            'status' => 'promoted',
            'is_enrolled' => false,
            'processed_by' => $this->admin->id,
        ]);
    }

    private function enrollmentsUrl(array $query = []): string
    {
        return route('admin.students.enrollments', $query);
    }

    /** @test */
    public function pending_promotions_are_listed_on_the_enrollment_page(): void
    {
        $this->actingAs($this->admin)->get($this->enrollmentsUrl())
            ->assertOk()
            ->assertSee('Pending Enrollment')
            ->assertSee($this->student->full_name);
    }

    /** @test */
    public function enrolling_from_the_enrollment_page_creates_next_year_enrollment_and_returns_here(): void
    {
        $back = $this->enrollmentsUrl(['tab' => 'pending']);

        $this->actingAs($this->admin)
            ->post(route('admin.promotions.enroll', $this->promo), ['redirect_to' => $back])
            ->assertRedirect($back);

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $this->nextSection->id,
            'academic_year_id' => $this->nextYear->id,
            'status' => 'active',
        ]);
        $this->assertTrue($this->promo->fresh()->is_enrolled);
    }

    /** @test */
    public function reversing_from_the_enrollment_page_returns_here_and_undoes_the_promotion(): void
    {
        // Put the promotion in the enrolled state first.
        $this->promo->update(['is_enrolled' => true]);
        StudentEnrollment::create([
            'student_id' => $this->student->id,
            'section_id' => $this->nextSection->id,
            'academic_year_id' => $this->nextYear->id,
            'enrollment_date' => $this->nextYear->start_date,
            'status' => 'active',
        ]);

        $back = $this->enrollmentsUrl(['tab' => 'enrollments']);

        $this->actingAs($this->admin)
            ->post(route('admin.promotions.reverse', $this->promo), ['redirect_to' => $back])
            ->assertRedirect($back);

        $this->assertDatabaseMissing('student_promotions', ['id' => $this->promo->id]);
        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->nextYear->id,
        ]);
    }

    /** @test */
    public function a_foreign_redirect_target_is_rejected_and_falls_back_to_history(): void
    {
        // Open-redirect guard: only same-app enrollments URLs are honoured.
        $this->actingAs($this->admin)
            ->post(route('admin.promotions.enroll', $this->promo), ['redirect_to' => 'https://evil.example.com/phish'])
            ->assertRedirect(route('admin.promotions.history'));
    }
}
