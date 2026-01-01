<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use App\Services\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GradingService $gradingService;
    protected AcademicYear $academicYear;
    protected Term $term;
    protected Section $section;
    protected GradeLevel $gradeLevel;
    protected Division $division;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->gradingService = app(GradingService::class);
        
        // Create base data structure
        $this->division = Division::factory()->create(['name' => 'Primary']);
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->gradeLevel = GradeLevel::factory()->create([
            'division_id' => $this->division->id,
            'name' => 'Grade 5',
        ]);
        $this->section = Section::factory()->create([
            'grade_level_id' => $this->gradeLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'A',
        ]);
        $this->term = Term::factory()->quarter(1)->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
    }

    /** @test */
    public function it_calculates_student_totals_correctly(): void
    {
        // Arrange
        $student = Student::factory()->create();
        $this->enrollStudent($student);
        
        $subjects = Subject::factory()->count(3)->create();
        
        // Create marks: 80, 90, 70 = Total 240, Avg 80
        $scores = [80, 90, 70];
        foreach ($subjects as $index => $subject) {
            $template = AssessmentTemplate::factory()->termTotal()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
            ]);
            
            StudentMark::factory()->create([
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'subject_id' => $subject->id,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
                'score' => $scores[$index],
            ]);
        }

        // Act
        $result = $this->gradingService->calculateStudentTotals(
            $student, 
            $this->term, 
            $this->academicYear, 
            $subjects
        );

        // Assert
        $this->assertEquals(240, $result['total']);
        $this->assertEquals(80, $result['average']);
        $this->assertCount(3, $result['marks']);
    }

    /** @test */
    public function it_handles_empty_grades_gracefully(): void
    {
        // Arrange
        $student = Student::factory()->create();
        $this->enrollStudent($student);
        $subjects = Subject::factory()->count(2)->create();

        // Act - No marks created
        $result = $this->gradingService->calculateStudentTotals(
            $student, 
            $this->term, 
            $this->academicYear, 
            $subjects
        );

        // Assert
        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['average']);
        $this->assertCount(0, $result['marks']);
    }

    /** @test */
    public function it_handles_zero_scores_correctly(): void
    {
        // Arrange
        $student = Student::factory()->create();
        $this->enrollStudent($student);
        
        $subjects = Subject::factory()->count(2)->create();
        
        // Create marks with zero scores
        foreach ($subjects as $subject) {
            $template = AssessmentTemplate::factory()->termTotal()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
            ]);
            
            StudentMark::factory()->zero()->create([
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'subject_id' => $subject->id,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
            ]);
        }

        // Act
        $result = $this->gradingService->calculateStudentTotals(
            $student, 
            $this->term, 
            $this->academicYear, 
            $subjects
        );

        // Assert
        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['average']);
        $this->assertCount(2, $result['marks']);
    }

    /** @test */
    public function it_calculates_section_totals_for_multiple_students(): void
    {
        // Arrange
        $students = Student::factory()->count(3)->create();
        $subject = Subject::factory()->create();
        
        $scores = [85, 75, 90];
        
        foreach ($students as $index => $student) {
            $this->enrollStudent($student);
            
            $template = AssessmentTemplate::factory()->termTotal()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
            ]);
            
            StudentMark::factory()->create([
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'subject_id' => $subject->id,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
                'score' => $scores[$index],
            ]);
        }

        // Act
        $results = $this->gradingService->calculateSectionTotals(
            $students, 
            $this->term, 
            $this->academicYear, 
            collect([$subject])
        );

        // Assert
        $this->assertCount(3, $results);
        $this->assertEquals(85, $results[$students[0]->id]['total']);
        $this->assertEquals(75, $results[$students[1]->id]['total']);
        $this->assertEquals(90, $results[$students[2]->id]['total']);
    }

    /** @test */
    public function it_gets_student_report_data_with_complete_structure(): void
    {
        // Arrange
        $student = Student::factory()->create();
        $this->enrollStudent($student);
        
        $subject = Subject::factory()->create();
        
        $template = AssessmentTemplate::factory()->termTotal()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
        ]);
        
        StudentMark::factory()->create([
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'subject_id' => $subject->id,
            'assessment_template_id' => $template->id,
            'section_id' => $this->section->id,
            'score' => 85,
        ]);

        // Act
        $result = $this->gradingService->getStudentReportData(
            $student, 
            $this->term, 
            $this->academicYear
        );

        // Assert
        $this->assertArrayHasKey('marks', $result);
        $this->assertArrayHasKey('totalScore', $result);
        $this->assertArrayHasKey('average', $result);
    }

    /** @test */
    public function it_calculates_precise_decimal_averages(): void
    {
        // Arrange
        $student = Student::factory()->create();
        $this->enrollStudent($student);
        
        $subjects = Subject::factory()->count(3)->create();
        
        // Create marks: 85, 88, 92 = Total 265, Avg 88.33...
        $scores = [85, 88, 92];
        foreach ($subjects as $index => $subject) {
            $template = AssessmentTemplate::factory()->termTotal()->create([
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
            ]);
            
            StudentMark::factory()->create([
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYear->id,
                'term_id' => $this->term->id,
                'subject_id' => $subject->id,
                'assessment_template_id' => $template->id,
                'section_id' => $this->section->id,
                'score' => $scores[$index],
            ]);
        }

        // Act
        $result = $this->gradingService->calculateStudentTotals(
            $student, 
            $this->term, 
            $this->academicYear, 
            $subjects
        );

        // Assert - Check that average is calculated with precision
        $expectedAverage = round(265 / 3, 2);
        $this->assertEquals($expectedAverage, round($result['average'], 2));
    }

    /**
     * Helper to enroll a student in the test section
     */
    protected function enrollStudent(Student $student): void
    {
        $student->sections()->attach($this->section->id, [
            'academic_year_id' => $this->academicYear->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
    }
}
