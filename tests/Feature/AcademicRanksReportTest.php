<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentTermRecord;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicRanksReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected GradeLevel $gradeLevel;
    protected Section $section;
    protected Student $student1;
    protected Student $student2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup user and role
        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');
        
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->term = Term::factory()->quarter(1)->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
        
        $division = Division::firstOrCreate(['code' => 'ES'], ['name' => 'Elementary', 'sort_order' => 2]);
        $this->gradeLevel = GradeLevel::firstOrCreate(['code' => 'G1'], [
            'division_id' => $division->id,
            'name' => 'Grade 1',
            'sort_order' => 1
        ]);
        
        $this->section = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->student1 = Student::factory()->create(['first_name' => 'ALEX']);
        $this->student1->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $this->student2 = Student::factory()->create(['first_name' => 'BOB']);
        $this->student2->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_access_section_top_10_report()
    {
        // Populate student term records
        StudentTermRecord::create([
            'student_id' => $this->student1->id,
            'term_id' => $this->term->id,
            'academic_year_id' => $this->academicYear->id,
            'total_score' => 95.0,
            'average_score' => 95.0,
            'rank' => 1,
            'rank_out_of' => 2,
        ]);

        StudentTermRecord::create([
            'student_id' => $this->student2->id,
            'term_id' => $this->term->id,
            'academic_year_id' => $this->academicYear->id,
            'total_score' => 88.0,
            'average_score' => 88.0,
            'rank' => 2,
            'rank_out_of' => 2,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.academic-reports.show', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'section_id' => $this->section->id,
                'report_type' => 'section_top_10',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Section Top 10');
        $response->assertSee('ALEX');
        $response->assertSee('BOB');
    }

    /** @test */
    public function admin_can_access_category_rankings_report()
    {
        // Populate records for category rankings (ES division G1)
        StudentTermRecord::create([
            'student_id' => $this->student1->id,
            'term_id' => $this->term->id,
            'academic_year_id' => $this->academicYear->id,
            'total_score' => 96.0,
            'average_score' => 96.0,
            'rank' => 1,
            'rank_out_of' => 2,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.academic-reports.category-ranks', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Category Rankings');
        $response->assertSee('ALEX');
    }

    /** @test */
    public function admin_can_access_academic_excellence_report()
    {
        // ALEX achieved 95.0% (Excellence)
        StudentTermRecord::create([
            'student_id' => $this->student1->id,
            'term_id' => $this->term->id,
            'academic_year_id' => $this->academicYear->id,
            'total_score' => 95.0,
            'average_score' => 95.0,
            'rank' => 1,
            'rank_out_of' => 2,
        ]);

        // BOB achieved 88.0% (Below excellence threshold 90%)
        StudentTermRecord::create([
            'student_id' => $this->student2->id,
            'term_id' => $this->term->id,
            'academic_year_id' => $this->academicYear->id,
            'total_score' => 88.0,
            'average_score' => 88.0,
            'rank' => 2,
            'rank_out_of' => 2,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.academic-reports.academic-excellence', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'section_id' => $this->section->id,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Academic Excellence');
        $response->assertSee('ALEX');
        $response->assertDontSee('BOB'); // BOB is not listed on the honor roll
    }
}
