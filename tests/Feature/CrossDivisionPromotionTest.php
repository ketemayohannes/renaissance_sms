<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrossDivisionPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AcademicYear $year;
    protected AcademicYear $nextYear;

    // Divisions
    protected Division $kg;
    protected Division $elementary;
    protected Division $highSchool;

    // Boundary grades
    protected GradeLevel $gradePrep;   // KG final
    protected GradeLevel $grade1;      // Elementary first
    protected GradeLevel $grade8;      // Elementary final
    protected GradeLevel $grade9;      // High School first
    protected GradeLevel $grade12;     // High School final

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->year = AcademicYear::factory()->active()->create();
        $this->nextYear = AcademicYear::factory()->create([
            'start_date' => now()->addYear()->startOfYear(),
            'end_date' => now()->addYear()->endOfYear(),
            'is_active' => false,
        ]);

        // Quarter 4 already ended so the promotion window is open.
        Term::factory()->create([
            'academic_year_id' => $this->year->id,
            'type' => 'quarter',
            'name' => 'Quarter 4',
            'term_number' => 4,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(),
        ]);

        // Divisions in order: KG (1) < Elementary (2) < High School (3).
        $this->kg = Division::factory()->create(['name' => 'Kindergarten', 'sort_order' => 1]);
        $this->elementary = Division::factory()->create(['name' => 'Elementary', 'sort_order' => 2]);
        $this->highSchool = Division::factory()->create(['name' => 'High School', 'sort_order' => 3]);

        // Grade sort_order restarts per division (mirrors real data).
        $this->gradePrep = GradeLevel::factory()->create(['division_id' => $this->kg->id, 'name' => 'Grade Prep', 'sort_order' => 1]);
        $this->grade1 = GradeLevel::factory()->create(['division_id' => $this->elementary->id, 'name' => 'Grade 1', 'sort_order' => 0]);
        $this->grade8 = GradeLevel::factory()->create(['division_id' => $this->elementary->id, 'name' => 'Grade 8', 'sort_order' => 7]);
        $this->grade9 = GradeLevel::factory()->create(['division_id' => $this->highSchool->id, 'name' => 'Grade 9', 'sort_order' => 0]);
        $this->grade12 = GradeLevel::factory()->create(['division_id' => $this->highSchool->id, 'name' => 'Grade 12', 'sort_order' => 3]);
    }

    private function promote(GradeLevel $fromGrade): StudentPromotion
    {
        $section = Section::factory()->create([
            'grade_level_id' => $fromGrade->id,
            'academic_year_id' => $this->year->id,
            'name' => 'A',
        ]);

        $student = Student::factory()->create();
        $student->sections()->attach($section->id, [
            'academic_year_id' => $this->year->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.promotions.execute'), [
                'section_id' => $section->id,
                'next_academic_year_id' => $this->nextYear->id,
                'decisions' => [$student->id => 'promoted'],
            ])
            ->assertRedirect(route('admin.promotions.process'));

        return StudentPromotion::where('student_id', $student->id)->firstOrFail();
    }

    /** @test */
    public function kindergarten_final_grade_promotes_into_elementary(): void
    {
        $promo = $this->promote($this->gradePrep);

        $this->assertEquals('promoted', $promo->status);
        $this->assertEquals($this->grade1->id, $promo->to_grade_level_id, 'Grade Prep should promote to Elementary Grade 1.');
    }

    /** @test */
    public function elementary_final_grade_promotes_into_high_school(): void
    {
        $promo = $this->promote($this->grade8);

        $this->assertEquals('promoted', $promo->status);
        $this->assertEquals($this->grade9->id, $promo->to_grade_level_id, 'Grade 8 should promote to High School Grade 9.');
    }

    /** @test */
    public function final_grade_of_last_division_graduates(): void
    {
        // No division beyond High School, so a Grade 12 "promotion" becomes graduation.
        $promo = $this->promote($this->grade12);

        $this->assertEquals('graduated', $promo->status);
    }
}
