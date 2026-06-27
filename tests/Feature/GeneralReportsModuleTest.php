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

class GeneralReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected Division $division;
    protected GradeLevel $gradeLevel;
    protected Section $section;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->term = Term::factory()->quarter(1)->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->division = Division::firstOrCreate(['code' => 'ES'], [
            'name' => 'Elementary',
            'sort_order' => 2
        ]);

        $this->gradeLevel = GradeLevel::firstOrCreate(['code' => 'G1'], [
            'division_id' => $this->division->id,
            'name' => 'Grade 1',
            'sort_order' => 1
        ]);

        $this->section = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->student = Student::factory()->create(['first_name' => 'EMILY']);
        $this->student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_access_general_reports_dashboard()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('General Reports');
        $response->assertSee('Top 3 Per Section');
    }

    /** @test */
    public function admin_can_download_top_3_per_section_pdf()
    {
        // Add a term record for Emily
        StudentTermRecord::create([
            'student_id' => $this->student->id,
            'term_id' => $this->term->id,
            'academic_year_id' => $this->academicYear->id,
            'total_score' => 95.5,
            'average_score' => 95.5,
            'rank' => 1,
            'rank_out_of' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.top3-per-section', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'division_id' => $this->division->id,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename=top3_per_section_summary_elementary_quarter_1.pdf');
    }
}
