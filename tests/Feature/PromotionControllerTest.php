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
use App\Models\StudentEnrollment;
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

        $response->assertRedirect(route('admin.promotions.process'));

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
    public function executes_promotion_and_reexam_decisions_correctly_and_allows_enrollment_and_reversal(): void
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

        $response->assertRedirect(route('admin.promotions.process'));

        // Promoted student check:
        // 1. Previous enrollment stays 'active' so current-year grade book
        //    and master sheet data remain visible (only graduation closes it)
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);
        // 2. Next year enrollment should NOT be created automatically (held until registrar clicks enroll)
        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->nextAcademicYear->id,
        ]);

        // Re-exam student check:
        // 1. Previous enrollment stays 'active' (only graduation closes it)
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $studentReExam->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);
        // 2. Next year enrollment should NOT be created automatically
        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $studentReExam->id,
            'academic_year_id' => $this->nextAcademicYear->id,
        ]);
        // 3. Promotion history status should be 're_exam'
        $this->assertDatabaseHas('student_promotions', [
            'student_id' => $studentReExam->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->gradeLevel->id,
            'status' => 're_exam',
            'is_enrolled' => false,
        ]);

        // Retrieve the promotion record for student
        $promo = \App\Models\StudentPromotion::where('student_id', $this->student->id)->first();
        $this->assertNotNull($promo);
        $this->assertFalse($promo->is_enrolled);

        // 3. Admin/Registrar enrolls the student
        $enrollResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.enroll', $promo));

        $enrollResponse->assertRedirect(route('admin.promotions.history'));

        // Next year enrollment should now exist
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $nextSection->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'status' => 'active',
        ]);

        // Promotion record should show is_enrolled = true
        $promo->refresh();
        $this->assertTrue($promo->is_enrolled);

        // 4. Test Reverse Promotion
        $reverseResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.reverse', $promo));

        $reverseResponse->assertRedirect(route('admin.promotions.history'));

        // Next year enrollment should be deleted
        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->nextAcademicYear->id,
        ]);

        // Previous enrollment should be restored to 'active'
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $this->section->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        // Promotion record should be deleted
        $this->assertDatabaseMissing('student_promotions', [
            'id' => $promo->id,
        ]);
    }

    /** @test */
    public function admin_can_filter_promotion_history(): void
    {
        // Create another student promotion record
        $anotherStudent = Student::factory()->create();
        $promo1 = \App\Models\StudentPromotion::create([
            'student_id' => $this->student->id,
            'from_academic_year_id' => $this->academicYear->id,
            'to_academic_year_id' => $this->nextAcademicYear->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'status' => 'promoted',
            'is_enrolled' => false,
            'processed_by' => $this->adminUser->id,
        ]);

        $promo2 = \App\Models\StudentPromotion::create([
            'student_id' => $anotherStudent->id,
            'from_academic_year_id' => $this->academicYear->id,
            'to_academic_year_id' => $this->nextAcademicYear->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->gradeLevel->id,
            'status' => 'retained',
            'is_enrolled' => false,
            'processed_by' => $this->adminUser->id,
        ]);

        // Filter by status = 'retained'
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.promotions.history', ['status' => 'retained']));

        $response->assertStatus(200);
        $response->assertSee($anotherStudent->full_name);
        $response->assertDontSee($this->student->full_name);

        // Filter by search name
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.promotions.history', ['search' => $this->student->first_name]));

        $response->assertStatus(200);
        $response->assertSee($this->student->full_name);
        $response->assertDontSee($anotherStudent->full_name);
    }

    /** @test */
    public function admin_can_bulk_enroll_students(): void
    {
        $nextSection = Section::factory()->create([
            'grade_level_id' => $this->nextGradeLevel->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'name' => 'A',
        ]);

        $anotherStudent = Student::factory()->create();
        $anotherStudent->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $promo1 = \App\Models\StudentPromotion::create([
            'student_id' => $this->student->id,
            'from_academic_year_id' => $this->academicYear->id,
            'to_academic_year_id' => $this->nextAcademicYear->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'to_section_id' => $nextSection->id,
            'status' => 'promoted',
            'is_enrolled' => false,
            'processed_by' => $this->adminUser->id,
        ]);

        $promo2 = \App\Models\StudentPromotion::create([
            'student_id' => $anotherStudent->id,
            'from_academic_year_id' => $this->academicYear->id,
            'to_academic_year_id' => $this->nextAcademicYear->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'to_section_id' => $nextSection->id,
            'status' => 'promoted',
            'is_enrolled' => false,
            'processed_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.bulk-enroll', [
                'ids' => [$promo1->id, $promo2->id]
            ]));

        $response->assertRedirect(route('admin.promotions.history'));

        // Both should be enrolled
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $this->student->id,
            'section_id' => $nextSection->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $anotherStudent->id,
            'section_id' => $nextSection->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'status' => 'active',
        ]);

        $this->assertTrue($promo1->refresh()->is_enrolled);
        $this->assertTrue($promo2->refresh()->is_enrolled);
    }

    /** @test */
    public function admin_can_bulk_reverse_promotions(): void
    {
        $nextSection = Section::factory()->create([
            'grade_level_id' => $this->nextGradeLevel->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'name' => 'A',
        ]);

        $anotherStudent = Student::factory()->create();
        $anotherStudent->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $promo1 = \App\Models\StudentPromotion::create([
            'student_id' => $this->student->id,
            'from_academic_year_id' => $this->academicYear->id,
            'to_academic_year_id' => $this->nextAcademicYear->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'to_section_id' => $nextSection->id,
            'status' => 'promoted',
            'is_enrolled' => true,
            'processed_by' => $this->adminUser->id,
        ]);

        $promo2 = \App\Models\StudentPromotion::create([
            'student_id' => $anotherStudent->id,
            'from_academic_year_id' => $this->academicYear->id,
            'to_academic_year_id' => $this->nextAcademicYear->id,
            'from_grade_level_id' => $this->gradeLevel->id,
            'to_grade_level_id' => $this->nextGradeLevel->id,
            'to_section_id' => $nextSection->id,
            'status' => 'promoted',
            'is_enrolled' => true,
            'processed_by' => $this->adminUser->id,
        ]);

        // Create the active next-year enrollments that we are going to reverse
        StudentEnrollment::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'section_id' => $nextSection->id,
            'enrollment_date' => $this->nextAcademicYear->start_date,
            'status' => 'active',
        ]);

        StudentEnrollment::create([
            'student_id' => $anotherStudent->id,
            'academic_year_id' => $this->nextAcademicYear->id,
            'section_id' => $nextSection->id,
            'enrollment_date' => $this->nextAcademicYear->start_date,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.promotions.bulk-reverse', [
                'ids' => [$promo1->id, $promo2->id]
            ]));

        $response->assertRedirect(route('admin.promotions.history'));

        // Next-year enrollments must be deleted
        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $this->student->id,
            'academic_year_id' => $this->nextAcademicYear->id,
        ]);

        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $anotherStudent->id,
            'academic_year_id' => $this->nextAcademicYear->id,
        ]);

        // Promotion logs should be deleted
        $this->assertDatabaseMissing('student_promotions', [
            'id' => $promo1->id,
        ]);
        $this->assertDatabaseMissing('student_promotions', [
            'id' => $promo2->id,
        ]);
    }
}
