<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\PromotionRule;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PromotionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected AcademicYear $nextAcademicYear;
    protected Term $term;
    protected Section $section;
    protected GradeLevel $gradeLevel;
    protected GradeLevel $nextGradeLevel;
    protected Division $division;
    protected Subject $subjectAmharic;
    protected Subject $subjectMath;
    protected Subject $subjectGeography;
    protected Student $student;
    protected Term $semester;
    protected Term $quarter;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user with role
        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');
        
        // Create base academic structure
        $this->division = Division::factory()->create(['name' => 'Primary']);
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->nextAcademicYear = AcademicYear::factory()->create([
            'start_date' => now()->addYear()->startOfYear(),
            'end_date' => now()->addYear()->endOfYear(),
            'is_active' => false,
        ]);

        $this->gradeLevel = GradeLevel::factory()->create([
            'division_id' => $this->division->id,
            'name' => 'Grade 4',
            'sort_order' => 4,
        ]);

        $this->nextGradeLevel = GradeLevel::factory()->create([
            'division_id' => $this->division->id,
            'name' => 'Grade 5',
            'sort_order' => 5,
        ]);

        $this->section = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'A',
        ]);

        // Virtual yearly term
        $this->term = Term::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'type' => 'yearly',
            'name' => 'Yearly',
        ]);

        // Semester and Quarter for yearly calculation
        $this->semester = Term::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'type' => 'semester',
            'name' => 'Semester 1',
        ]);

        $this->quarter = Term::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'parent_term_id' => $this->semester->id,
            'type' => 'quarter',
            'name' => 'Quarter 1',
        ]);

        // Create subjects
        $this->subjectAmharic = Subject::factory()->create(['name' => 'Amharic']);
        $this->subjectMath = Subject::factory()->create(['name' => 'Mathematics']);
        $this->subjectGeography = Subject::factory()->create(['name' => 'Geography']);

        // Link subjects to grade
        $this->gradeLevel->subjects()->attach([
            $this->subjectAmharic->id,
            $this->subjectMath->id,
            $this->subjectGeography->id
        ], ['academic_year_id' => $this->academicYear->id]);

        // Create student and enroll
        $this->student = Student::factory()->create();
        $this->student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_access_promotions_index_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.promotions.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_custom_promotion_rule(): void
    {
        $payload = [
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'min_average' => 50.00,
            'min_attendance_percent' => 75.00,
            'max_failed_subjects' => 3,
            'failed_action' => 're_exam',
            'major_subjects' => [$this->subjectMath->id, $this->subjectAmharic->id],
            'conditional_rules' => [
                ['fails' => 1, 'avg' => 51],
                ['fails' => 2, 'avg' => 52],
                ['fails' => 3, 'avg' => 54],
            ],
            'description' => 'Test Rule',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.store-rule'), $payload);

        $response->assertRedirect(route('admin.promotions.index'));

        $this->assertDatabaseHas('promotion_rules', [
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'max_failed_subjects' => 3,
            'failed_action' => 're_exam',
        ]);

        $rule = PromotionRule::where('from_grade_level_id', $this->gradeLevel->id)->first();
        $this->assertContains((string)$this->subjectMath->id, array_map('strval', $rule->major_subjects));
        $this->assertCount(3, $rule->conditional_rules);
        $this->assertEquals(52, $rule->conditional_rules[1]['avg']);
    }

    /** @test */
    public function promotion_preview_applies_customizable_rules_correctly(): void
    {
        // 1. Create a custom rule: math/amharic are majors. If student fails 1 non-major (e.g. Geography), they pass if average >= 51%.
        PromotionRule::create([
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'min_average' => 50.00,
            'min_attendance_percent' => 75.00,
            'max_failed_subjects' => 3,
            'failed_action' => 're_exam', // Grade 1-3 style fails to re_exam
            'major_subjects' => [$this->subjectMath->id, $this->subjectAmharic->id],
            'conditional_rules' => [
                ['fails' => 1, 'avg' => 51.00],
            ],
        ]);

        // 2. Set marks:
        // Math: 80 (Pass)
        // Amharic: 85 (Pass)
        // Geography: 40 (Fail - non-major)
        // Sum: 205 / 3 = 68.33% average. Since average is 68.33% (>= 51%), recommended should be promoted!
        $marks = [
            $this->subjectMath->id => 80,
            $this->subjectAmharic->id => 85,
            $this->subjectGeography->id => 40
        ];

        foreach ($marks as $subId => $score) {
            $template = AssessmentTemplate::factory()->termTotal()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->quarter->id,
            ]);

            StudentMark::factory()->create([
                'student_id' => $this->student->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->quarter->id,
                'subject_id' => $subId,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
                'score' => $score,
            ]);
        }

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.preview', ['section_id' => $this->section->id]));

        $response->assertStatus(200);
        $response->assertSee('Promoted'); // Should see Promoted badge
    }

    /** @test */
    public function promotion_preview_applies_major_subject_failure_restriction(): void
    {
        // 1. Math is major. Student fails Math but gets high average.
        PromotionRule::create([
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'min_average' => 50.00,
            'min_attendance_percent' => 75.00,
            'max_failed_subjects' => 3,
            'failed_action' => 'retained',
            'major_subjects' => [$this->subjectMath->id],
            'conditional_rules' => [
                ['fails' => 1, 'avg' => 51.00],
            ],
        ]);

        // Math: 45 (Fail - Major)
        // Amharic: 95 (Pass)
        // Geography: 90 (Pass)
        // Sum = 230 / 3 = 76.67% average. Even with 76.67% average, they failed a major, so they must be retained!
        $marks = [
            $this->subjectMath->id => 45,
            $this->subjectAmharic->id => 95,
            $this->subjectGeography->id => 90
        ];

        foreach ($marks as $subId => $score) {
            $template = AssessmentTemplate::factory()->termTotal()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->quarter->id,
            ]);

            StudentMark::factory()->create([
                'student_id' => $this->student->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->quarter->id,
                'subject_id' => $subId,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
                'score' => $score,
            ]);
        }

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.preview', ['section_id' => $this->section->id]));

        $response->assertStatus(200);
        $response->assertSee('Retained'); // Should see Retained badge
    }

    /** @test */
    public function executes_graduation_correctly(): void
    {
        // 1. Rule with to_grade_level_id = null represents graduation
        PromotionRule::create([
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => null,
            'academic_year_id' => $this->academicYear->id,
            'min_average' => 50.00,
        ]);

        $payload = [
            'section_id' => $this->section->id,
            'next_academic_year_id' => $this->nextAcademicYear->id,
            'decisions' => [
                $this->student->id => 'graduated',
            ],
            'remarks' => [
                $this->student->id => 'Excellent graduation!',
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.execute'), $payload);

        $response->assertRedirect(route('admin.promotions.index'));

        // Current enrollment should be marked as graduated
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'graduated',
        ]);

        // Student model should be marked as inactive
        $this->student->refresh();
        $this->assertFalse($this->student->is_active);

        // Status history should be logged
        $this->assertDatabaseHas('student_status_history', [
            'student_id' => $this->student->id,
            'new_status' => 'graduated',
            'reason' => 'academic',
        ]);
    }

    /** @test */
    public function executes_promotion_and_reexam_decisions_correctly(): void
    {
        // Setup a next grade level section (so promoted students can be enrolled)
        $nextSection = Section::factory()->create([
            'grade_level_id' => $this->nextGradeLevel->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'name' => 'A', // Matches our current section name
        ]);

        // Create a second student to test re-exam decision
        $studentReExam = Student::factory()->create();
        $studentReExam->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $payload = [
            'section_id' => $this->section->id,
            'next_academic_year_id' => $this->nextAcademicYear->id,
            'decisions' => [
                $this->student->id => 'promoted',
                $studentReExam->id => 're_exam',
            ],
            'remarks' => [
                $this->student->id => 'Promoted automatically',
                $studentReExam->id => 'Needs re-exam',
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.execute'), $payload);

        $response->assertRedirect(route('admin.promotions.index'));

        // Promoted student check:
        // 1. Previous enrollment should be 'completed'
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'completed',
        ]);
        // 2. Next year enrollment should be 'active' in Grade 5 section
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $nextSection->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'status' => 'active',
        ]);

        // Re-exam student check:
        // 1. Previous enrollment should be 'completed'
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $studentReExam->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'completed',
        ]);
        // 2. Next year enrollment should be retained in Grade 4 section
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $studentReExam->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'status' => 'active',
        ]);
        // 3. Promotion history status should be 're_exam'
        $this->assertDatabaseHas('student_promotions', [
            'student_id' => $studentReExam->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->gradeLevel->id,
            'status' => 're_exam',
        ]);
    }
}
