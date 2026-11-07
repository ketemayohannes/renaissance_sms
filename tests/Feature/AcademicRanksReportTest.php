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
            'sort_order' => 1,
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

    /**
     * Enrol students with the given first names and give each the same term average.
     *
     * @return array<string, Student> keyed by first name
     */
    private function tiedStudents(array $names, float $average, ?Term $term = null): array
    {
        $students = [];
        foreach ($names as $name) {
            $student = Student::factory()->create(['first_name' => $name]);
            $student->sections()->attach($this->section->id, [
                'academic_year_id' => $this->academicYear->id,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
            StudentTermRecord::create([
                'student_id' => $student->id,
                'term_id' => ($term ?? $this->term)->id,
                'academic_year_id' => $this->academicYear->id,
                'total_score' => $average,
                'average_score' => $average,
                'rank' => 1,
                'rank_out_of' => count($names),
            ]);
            $students[$name] = $student;
        }

        return $students;
    }

    private function listedNames($response, string $variable, ?string $category = null): array
    {
        $records = $category
            ? $response->viewData($variable)[$category]['records']
            : $response->viewData($variable);

        return collect($records)->pluck('first_name')->all();
    }

    /** @test */
    public function category_ranks_break_ties_alphabetically_including_at_the_top_10_cut_off()
    {
        // 12 students all on 98.86: only 10 fit, and the same 10 must be chosen every time.
        // Created out of alphabetical order so insertion order can't make the test pass by luck.
        $names = ['LULU', 'CHALA', 'KIDUS', 'ABEBE', 'JEMAL', 'HANA', 'BETI', 'FIKIR', 'DAWIT', 'GELILA', 'EDEN', 'IMAN'];
        $this->tiedStudents($names, 98.86);
        $this->tiedStudents(['ZED'], 99.5); // a clear winner stays first

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.academic-reports.category-ranks', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
            ]));

        $response->assertOk();
        $this->assertSame(
            ['ZED', 'ABEBE', 'BETI', 'CHALA', 'DAWIT', 'EDEN', 'FIKIR', 'GELILA', 'HANA', 'IMAN'],
            $this->listedNames($response, 'results', 'grade_1_6')
        );
    }

    /** @test */
    public function category_ranks_yearly_breaks_ties_alphabetically()
    {
        $semester1 = Term::factory()->create(['academic_year_id' => $this->academicYear->id, 'type' => 'semester', 'term_number' => 1, 'name' => 'Semester 1']);
        $this->tiedStudents(['MIKI', 'ABEL', 'SARA'], 97.0, $semester1);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.academic-reports.category-ranks', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => 'yearly',
            ]));

        $response->assertOk();
        $this->assertSame(['ABEL', 'MIKI', 'SARA'], $this->listedNames($response, 'results', 'grade_1_6'));
    }

    /** @test */
    public function academic_excellence_breaks_ties_alphabetically()
    {
        $this->tiedStudents(['TOMAS', 'MARTA', 'DANI'], 93.0);
        $this->tiedStudents(['YONAS'], 96.0);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.academic-reports.academic-excellence', [
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
            ]));

        $response->assertOk();
        $this->assertSame(['YONAS', 'DANI', 'MARTA', 'TOMAS'], $this->listedNames($response, 'records'));
    }
}
