<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Section;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradingService
{
    /**
     * Calculate Semester Grades for a Section.
     * Logic: Semester 1 = (Q1 + Q2) / 2
     * Logic: Semester 2 = (Q3 + Q4) / 2
     * Applies ONLY to Regular (Non-Elective) Subjects.
     */
    public function calculateSemesterGrades(Section $section, Term $semesterTerm, AcademicYear $academicYear)
    {
        if ($semesterTerm->type !== 'semester') {
            throw new \Exception("Target term must be a Semester.");
        }

        // 1. Identify Source Quarters
        $quarterNames = $this->getSourceQuarters($semesterTerm->name);
        
        $sourceTerms = Term::where('academic_year_id', $academicYear->id)
            ->whereIn('name', $quarterNames)
            ->get();

        if ($sourceTerms->isEmpty()) {
            throw new \Exception("Could not find source Quarters (" . implode(', ', $quarterNames) . ") for this Semester.");
        }

        $sourceTermIds = $sourceTerms->pluck('id');

        // 2. Identify Regular Subjects for this Grade Level
        // We get subjects attached to the grade level that are NOT elective
        $gradeLevel = $section->gradeLevel;
        $subjects = $gradeLevel->subjects()
            ->where('is_elective', false)
            ->get();

        if ($subjects->isEmpty()) {
            return 0;
        }

        // 3. Process Calculation
        $updatedCount = 0;
        
        DB::beginTransaction();
        try {
            // Get all students in the section
            // We use the same filtering as the SectionGradeController to be consistent
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivot('status', 'active') // Assuming we only calculate for active? Or all? Let's use active.
                ->get();
            
            // Per Subject
            foreach ($subjects as $subject) {
                // Ensure Destination Template Exists (Term Total for Semester)
                $destTemplate = $this->getOrCreateTermTotalTemplate($subject, $gradeLevel, $semesterTerm, $academicYear);
                
                // Get Source Templates (Term Total for Quarters)
                // We must search via the assignments relationship
                $sourceTemplateIds = AssessmentTemplate::whereHas('assignments', function($q) use ($subject, $gradeLevel) {
                        $q->where('subject_id', $subject->id)
                          ->where('grade_level_id', $gradeLevel->id);
                    })
                    ->where('assessment_type_id', $this->getTermTotalTypeId())
                    ->where('academic_year_id', $academicYear->id)
                    ->whereIn('term_id', $sourceTermIds)
                    ->pluck('id');

                if ($sourceTemplateIds->isEmpty()) {
                    continue; // Skip calculating if no quarter grades exist
                }

                // Per Student
                foreach ($students as $student) {
                    // Fetch sums
                    $marks = StudentMark::where('student_id', $student->id)
                        ->whereIn('assessment_template_id', $sourceTemplateIds)
                        ->whereNotNull('score')
                        ->get();

                    if ($marks->isEmpty()) {
                        continue;
                    }

                    // Logic: Semester Grade is strictly averaged over the number of Source Quarters (e.g., 2).
                    // If a Quarter grade is missing, it counts as 0.
                    // Q1=90, Q2=Missing => (90 + 0) / 2 = 45.
                    
                    $total = $marks->sum('score');
                    // We divide by the number of source quarters we EXPECTED to find, not the number of marks found.
                    // This creates the "current running average relative to full semester" effect.
                    $expectedCount = $sourceTermIds->count();
                    $average = $expectedCount > 0 ? ($total / $expectedCount) : 0;

                    // Save Result
                    StudentMark::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'assessment_template_id' => $destTemplate->id,
                        ],
                        [
                            'score' => round($average, 2),
                            'academic_year_id' => $academicYear->id,
                            'term_id' => $semesterTerm->id,
                            'subject_id' => $subject->id,
                            'section_id' => $section->id,
                            'teacher_id' => auth()->id(), // Who ran the calculation
                            'created_by' => auth()->id(),
                            'remarks' => 'Auto-calculated from Quarters'
                        ]
                    );

                    $updatedCount++;
                }
            }
            
            DB::commit();
            return $updatedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Grading Calculation Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function getSourceQuarters($semesterName)
    {
        // Simple string matching logic
        // TODO: Make this configurable or database driven relation
        if (stripos($semesterName, 'Semester 1') !== false) {
            return ['Quarter 1', 'Quarter 2'];
        }
        if (stripos($semesterName, 'Semester 2') !== false) {
            return ['Quarter 3', 'Quarter 4'];
        }
        
        throw new \Exception("Cannot determine source quarters for semester: $semesterName");
    }

    private function getOrCreateTermTotalTemplate($subject, $gradeLevel, $term, $academicYear)
    {
        // 1. Check if existing template is linked
        $template = AssessmentTemplate::whereHas('assignments', function($q) use ($subject, $gradeLevel) {
                $q->where('subject_id', $subject->id)
                  ->where('grade_level_id', $gradeLevel->id);
            })
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('assessment_type_id', $this->getTermTotalTypeId())
            ->first();

        if ($template) {
            return $template;
        }

        // 2. If not, check if a generic template exists for this term/year/type (maybe unlinked?)
        // Actually, we usually create a specific one or reuse?
        // Let's create a new one to be safe and ensure isolation, OR reuse if we want shared templates.
        // Given the unique nature of Term Totals, let's create unique or check narrowly.
        // Logic: Create Template -> Attach Assignment.
        
        $template = AssessmentTemplate::firstOrCreate(
            [
                'academic_year_id' => $academicYear->id,
                'term_id' => $term->id,
                'assessment_type_id' => $this->getTermTotalTypeId(), // Helper to get ID
                'name' => 'Term Total', 
                // We add a marker to know it's auto-generated?
                // 'type' column doesn't exist in schema shown in migration?
                // Wait, schema has 'assessment_type_id'. My code used 'type' => 'TERM_TOTAL' string.
                // Major Bug: 'type' column does not exist! usage of 'type' string in where clause is wrong.
                // It must use assessment_type_id!
            ],
            [
                'weight' => 100,
                'max_score' => 100,
                'is_active' => true,
            ]
        );

        // Attach to assignments
        $template->assignments()->firstOrCreate([
            'grade_level_id' => $gradeLevel->id,
            'subject_id' => $subject->id
        ]);

        return $template;
    }

    private function getTermTotalTypeId()
    {
        // Helper to find the AssessmentType ID for 'TERM_TOTAL'
        // Assuming we seeded an AssessmentType with code or name 'TERM_TOTAL'
        $type = \App\Models\AssessmentType::firstOrCreate(
            ['code' => 'TERM_TOTAL'],
            ['name' => 'Term Total', 'weight' => 100, 'max_score' => 100, 'is_active' => true]
        );
        return $type->id;
    }
}
