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

use App\Models\Student;
use App\Models\StudentTermRecord;

class GradingService
{
    /**
     * Recalculate and save statistics for all students in a section for a term.
     */
    public function recalculateSectionStatistics(Section $section, Term $term, AcademicYear $academicYear)
    {
        // Skip for virtual yearly term (no real DB record)
        if ($term->type === 'yearly' || $term->id === 'yearly') {
            // For yearly, recalculate all semesters and their quarters
            $semesters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'semester')
                ->get();
            
            foreach ($semesters as $semester) {
                $this->recalculateSectionStatistics($section, $semester, $academicYear);
            }
            return 0;
        }
        
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivot('status', 'active')
            ->get();

        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        $isSemester = $term->isSemester();
        
        // If this is a semester, also recalculate each quarter's stats first
        if ($isSemester) {
            $quarters = $term->quarters()->get();
            foreach ($quarters as $quarter) {
                $this->recalculateTermStats($students, $quarter, $academicYear, $subjects);
            }
        }
        
        // Recalculate stats for the main term
        $this->recalculateTermStats($students, $term, $academicYear, $subjects);

        return $students->count();
    }

    private function recalculateTermStats($students, $term, $academicYear, $subjects)
    {
        $studentTotals = [];
        $studentAverages = [];

        foreach ($students as $student) {
            $data = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
            $studentTotals[$student->id] = $data['total'];
            $studentAverages[$student->id] = $data['average'];
        }

        // Calculate Ranks
        $sortedTotals = collect($studentTotals)->sortDesc();
        $totalStudents = $sortedTotals->count();

        foreach ($students as $student) {
            $total = $studentTotals[$student->id];
            $avg = $studentAverages[$student->id];
            $rank = $sortedTotals->filter(fn($score) => $score > $total)->count() + 1;

            StudentTermRecord::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'term_id' => $term->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'total_score' => $total,
                    'average_score' => $avg,
                    'rank' => $rank,
                    'rank_out_of' => $totalStudents,
                ]
            );
        }
    }

    /**
     * Calculate individual student totals and averages.
     */
    public function calculateStudentTotals(Student $student, Term $term, AcademicYear $academicYear, $subjects = null)
    {
        if (!$subjects) {
            $enrollment = $student->enrollments()->where('academic_year_id', $academicYear->id)->first();
            $subjects = $enrollment->section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        }

        if ($term->isSemester()) {
            $quarters = $term->quarters()->get();
            $quarterIds = $quarters->pluck('id');
            $marks = StudentMark::where('student_id', $student->id)
                ->whereIn('term_id', $quarterIds)
                ->get()
                ->groupBy('subject_id');

            $total = 0;
            $subjectScores = [];
            foreach ($subjects as $subject) {
                $subMarks = $marks->get($subject->id);
                if ($subMarks) {
                    $score = $subMarks->avg('score');
                    $total += $score;
                    $subjectScores[$subject->id] = $score;
                }
            }
            $average = count($subjects) > 0 ? $total / count($subjects) : 0;
        } elseif ($term->type === 'yearly') {
            $semesters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'semester')
                ->get();
            
            $total = 0;
            $subjectScores = [];
            foreach ($subjects as $subject) {
                $subSemAverages = [];
                foreach ($semesters as $sem) {
                    $stats = $this->calculateStudentTotals($student, $sem, $academicYear, collect([$subject]));
                    if ($stats['total'] > 0) {
                        $subSemAverages[] = $stats['total'];
                    }
                }
                
                if (!empty($subSemAverages)) {
                    $score = array_sum($subSemAverages) / count($subSemAverages);
                    $total += $score;
                    $subjectScores[$subject->id] = $score;
                }
            }
            $average = count($subjects) > 0 ? $total / count($subjects) : 0;

        } else {
            $marks = StudentMark::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->get();
            $total = $marks->sum('score');
            $subjectScores = $marks->pluck('score', 'subject_id')->toArray();
            $average = count($subjects) > 0 ? $total / count($subjects) : 0;
        }

        return ['total' => $total, 'average' => $average, 'marks' => $subjectScores];
    }

    /**
     * Get standardized report data for a student.
     */
    public function getStudentReportData(Student $student, Term $term, AcademicYear $academicYear)
    {
        $enrollment = $student->enrollments()->where('academic_year_id', $academicYear->id)->first();
        $section = $enrollment->section;
        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        $isSemester = $term->isSemester();

        $record = StudentTermRecord::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->first();

        // If record doesn't have calc fields, recalculate on the fly (but log it as a warning)
        if (!$record || $record->total_score === null) {
            $stats = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
            $totalScore = $stats['total'];
            $average = $stats['average'];
            $rank = $record->rank ?? '-';
        } else {
            $totalScore = $record->total_score;
            $average = $record->average_score;
            $rank = $record->rank;
        }

        $marks = collect();
        $quarterData = [];
        
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
            $allQuarterMarks = [];
            
            foreach ($quarters as $q) {
                $qRecord = StudentTermRecord::where('student_id', $student->id)
                    ->where('term_id', $q->id)
                    ->first();
                
                $qMarks = StudentMark::where('student_id', $student->id)
                    ->where('term_id', $q->id)
                    ->get()
                    ->pluck('score', 'subject_id');

                $quarterData[$q->id] = [
                    'term' => $q,
                    'marks' => $qMarks,
                    'record' => $qRecord,
                    'total' => $qRecord->total_score ?? $qMarks->sum(),
                    'average' => $qRecord->average_score ?? ($subjects->count() > 0 ? $qMarks->sum() / $subjects->count() : 0),
                    'rank' => $qRecord->rank ?? '-',
                ];
                
                // Collect marks for semester average calculation
                foreach ($qMarks as $subId => $score) {
                    if (!isset($allQuarterMarks[$subId])) {
                        $allQuarterMarks[$subId] = [];
                    }
                    $allQuarterMarks[$subId][] = $score;
                }
            }
            
            // Calculate semester averages per subject
            foreach ($allQuarterMarks as $subId => $scores) {
                $marks[$subId] = round(array_sum($scores) / count($scores));
            }
        } else {
            $marks = StudentMark::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->get()
                ->pluck('score', 'subject_id');
        }

        return [
            'student' => $student,
            'section' => $section,
            'term' => $term,
            'academicYear' => $academicYear,
            'subjects' => $subjects,
            'marks' => $marks,
            'record' => $record,
            'totalScore' => $totalScore,
            'average' => $average,
            'rank' => $rank,
            'rank_out_of' => $record->rank_out_of ?? '-',
            'isSemester' => $isSemester,
            'quarters' => $quarterData,
            'semesters' => $this->getYearlyStats($student, $term, $academicYear, $subjects),
        ];
    }

    private function getYearlyStats($student, $term, $academicYear, $subjects)
    {
        if ($term->type !== 'yearly') return [];

        $semesterStats = [];
        $semesters = Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'semester')
            ->get();
        
        foreach ($semesters as $semester) {
            $sRecord = StudentTermRecord::where('student_id', $student->id)
                ->where('term_id', $semester->id)
                ->first();
            
            $sData = $this->calculateStudentTotals($student, $semester, $academicYear, $subjects);

            $semesterStats[$semester->id] = [
                'term' => $semester,
                'record' => $sRecord,
                'marks' => $sData['marks'] ?? [],
                'total' => $sRecord->total_score ?? $sData['total'],
                'average' => $sRecord->average_score ?? $sData['average'],
                'rank' => $sRecord->rank ?? '-'
            ];
        }
        return $semesterStats;
    }

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

        $quarterNames = $this->getSourceQuarters($semesterTerm->name);
        $sourceTerms = Term::where('academic_year_id', $academicYear->id)
            ->whereIn('name', $quarterNames)
            ->get();

        if ($sourceTerms->isEmpty()) {
            throw new \Exception("Could not find source Quarters (" . implode(', ', $quarterNames) . ") for this Semester.");
        }

        $sourceTermIds = $sourceTerms->pluck('id');
        $gradeLevel = $section->gradeLevel;
        $subjects = $gradeLevel->subjects()->where('is_elective', false)->get();

        if ($subjects->isEmpty()) return 0;

        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivot('status', 'active')
            ->get();

        if ($students->isEmpty()) return 0;

        $termTotalTypeId = $this->getTermTotalTypeId();

        // 1. Pre-register/fetch all destination templates for all subjects
        $destTemplatesMap = []; // subject_id => template_id
        foreach ($subjects as $subject) {
            $template = $this->getOrCreateTermTotalTemplate($subject, $gradeLevel, $semesterTerm, $academicYear);
            $destTemplatesMap[$subject->id] = $template->id;
        }

        // 2. Fetch all source template IDs for all subjects in one query
        $allSourceTemplates = AssessmentTemplate::with(['assignments' => function($q) use ($gradeLevel) {
                $q->where('grade_level_id', $gradeLevel->id);
            }])
            ->whereHas('assignments', function($q) use ($subjects, $gradeLevel) {
                $q->whereIn('subject_id', $subjects->pluck('id'))
                  ->where('grade_level_id', $gradeLevel->id);
            })
            ->where('assessment_type_id', $termTotalTypeId)
            ->where('academic_year_id', $academicYear->id)
            ->whereIn('term_id', $sourceTermIds)
            ->get()
            ->groupBy(function($item) {
                return $item->assignments->first()->subject_id;
            });

        // 3. Fetch ALL marks for these students and source templates in one query
        $allSourceTemplateIds = $allSourceTemplates->flatten()->pluck('id');
        $allMarks = StudentMark::whereIn('student_id', $students->pluck('id'))
            ->whereIn('assessment_template_id', $allSourceTemplateIds)
            ->whereNotNull('score')
            ->get()
            ->groupBy(['student_id', 'subject_id']);

        $upsertData = [];
        $expectedCount = $sourceTermIds->count();
        $now = now();

        // 4. Calculate averages in memory
        foreach ($subjects as $subject) {
            $destTemplateId = $destTemplatesMap[$subject->id];
            
            foreach ($students as $student) {
                $studentSubjectMarks = $allMarks->get($student->id)?->get($subject->id) ?? collect();
                
                if ($studentSubjectMarks->isEmpty()) continue;

                $total = $studentSubjectMarks->sum('score');
                $average = $expectedCount > 0 ? ($total / $expectedCount) : 0;

                $upsertData[] = [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'term_id' => $semesterTerm->id,
                    'subject_id' => $subject->id,
                    'section_id' => $section->id,
                    'teacher_id' => auth()->id(),
                    'assessment_template_id' => $destTemplateId,
                    'score' => round($average, 2),
                    'remarks' => 'Auto-calculated from Quarters',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // 5. Bulk Upsert
        if (!empty($upsertData)) {
            DB::transaction(function () use ($upsertData) {
                StudentMark::upsert(
                    $upsertData,
                    ['student_id', 'assessment_template_id'], // Unique keys
                    ['score', 'section_id', 'teacher_id', 'remarks', 'updated_at'] // Fields to update
                );
            });
        }

        return count($upsertData);
    }

    private function getSourceQuarters($semesterName)
    {
        if (stripos($semesterName, 'Semester 1') !== false) return ['Quarter 1', 'Quarter 2'];
        if (stripos($semesterName, 'Semester 2') !== false) return ['Quarter 3', 'Quarter 4'];
        throw new \Exception("Cannot determine source quarters for semester: $semesterName");
    }

    private function getOrCreateTermTotalTemplate($subject, $gradeLevel, $term, $academicYear)
    {
        $typeId = $this->getTermTotalTypeId();
        
        $template = AssessmentTemplate::whereHas('assignments', function($q) use ($subject, $gradeLevel) {
                $q->where('subject_id', $subject->id)
                  ->where('grade_level_id', $gradeLevel->id);
            })
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('assessment_type_id', $typeId)
            ->first();

        if ($template) return $template;

        $template = AssessmentTemplate::create([
            'academic_year_id' => $academicYear->id,
            'term_id' => $term->id,
            'assessment_type_id' => $typeId,
            'name' => 'Term Total', 
            'weight' => 100,
            'max_score' => 100,
            'is_active' => true,
        ]);

        $template->assignments()->create([
            'grade_level_id' => $gradeLevel->id,
            'subject_id' => $subject->id
        ]);

        return $template;
    }

    private function getTermTotalTypeId()
    {
        return \App\Models\AssessmentType::firstOrCreate(
            ['code' => 'TERM_TOTAL'],
            ['name' => 'Term Total', 'weight' => 100, 'max_score' => 100, 'is_active' => true]
        )->id;
    }
}
