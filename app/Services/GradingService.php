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
use Illuminate\Support\Facades\Cache;

use App\Models\Student;
use App\Models\StudentTermRecord;
use Illuminate\Support\Collection;

class GradingService
{
    private $yearlyStatsCache = [];

    /**
     * Recalculate and save statistics for all students in a section for a term.
     */
    public function recalculateSectionStatistics(Section $section, Term $term, AcademicYear $academicYear)
    {
        // Skip for virtual yearly term (no real DB record)
        if ($term->type === 'yearly' || $term->id === 'yearly') {
            // First ensure all semesters are calculated
            $semesters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'semester')
                ->get();
            
            foreach ($semesters as $semester) {
                $this->recalculateSectionStatistics($section, $semester, $academicYear);
            }

            // Now calculate yearly scores and ranks
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->get();
            $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();

            $batchResults = $this->calculateSectionTotals($students, $term, $academicYear, $subjects);
            $studentTotals = [];
            $studentAverages = [];

            foreach ($students as $student) {
                $data = $batchResults[$student->id] ?? ['total' => 0, 'average' => 0];
                $studentTotals[$student->id] = $data['total'];
                $studentAverages[$student->id] = $data['average'];
            }

            $sortedTotals = collect($studentTotals)->sortDesc();
            $totalStudents = $sortedTotals->count();

            $sectionCache = [];
            $upsertData = [];
            $now = now();

            foreach ($students as $student) {
                $total = $studentTotals[$student->id];
                $avg = $studentAverages[$student->id];
                $rank = $sortedTotals->filter(fn($score) => $score > $total)->count() + 1;

                $sectionCache[$student->id] = [
                    'total' => $total,
                    'average' => $avg,
                    'rank' => $rank,
                    'rank_out_of' => $totalStudents,
                ];

                // Only persist to DB if it's a real term (not virtual 'yearly')
                if ($term->id !== 'yearly' && is_numeric($term->id)) {
                    $upsertData[] = [
                        'student_id' => $student->id,
                        'term_id' => $term->id,
                        'academic_year_id' => $academicYear->id,
                        'total_score' => $total,
                        'average_score' => $avg,
                        'rank' => $rank,
                        'rank_out_of' => $totalStudents,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($upsertData)) {
                StudentTermRecord::upsert(
                    $upsertData,
                    ['student_id', 'term_id'],
                    ['total_score', 'average_score', 'rank', 'rank_out_of', 'updated_at']
                );

                // Manual Audit Injection
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'event' => 'bulk_recalculate',
                    'auditable_type' => StudentTermRecord::class,
                    'auditable_id' => $section->id,
                    'new_values' => ['section_id' => $section->id, 'term_id' => $term->id, 'count' => count($upsertData)],
                    'url' => request()->fullUrl(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            if ($term->id === 'yearly') {
                $this->yearlyStatsCache["{$section->id}_{$academicYear->id}"] = $sectionCache;
            }

            return $students->count();
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
        // Batch calculate all totals for the section
        $batchResults = $this->calculateSectionTotals($students, $term, $academicYear, $subjects);
        
        $studentTotals = collect($batchResults)->pluck('total', 'id')->toArray(); // This is wrong because batchResults is keyed by student_id
        $studentTotals = [];
        foreach($batchResults as $sid => $data) {
            $studentTotals[$sid] = $data['total'];
        }

        // Calculate Ranks
        $sortedTotals = collect($studentTotals)->sortDesc();
        $totalStudents = $sortedTotals->count();

        $upsertData = [];
        $now = now();

        foreach ($students as $student) {
            $total = $studentTotals[$student->id] ?? 0;
            $avg = $batchResults[$student->id]['average'] ?? 0;
            $rank = $sortedTotals->filter(fn($score) => $score > $total)->count() + 1;

            $upsertData[] = [
                'student_id' => $student->id,
                'term_id' => $term->id,
                'academic_year_id' => $academicYear->id,
                'total_score' => $total,
                'average_score' => $avg,
                'rank' => $rank,
                'rank_out_of' => $totalStudents,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($upsertData)) {
            StudentTermRecord::upsert(
                $upsertData,
                ['student_id', 'term_id'],
                ['total_score', 'average_score', 'rank', 'rank_out_of', 'updated_at']
            );

            // Manual Audit Injection
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'bulk_recalculate_term',
                'auditable_type' => StudentTermRecord::class,
                'auditable_id' => $term->id,
                'new_values' => ['term_id' => $term->id, 'count' => count($upsertData)],
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Batch calculate totals and averages for multiple students.
     */
    public function calculateSectionTotals($students, Term $term, AcademicYear $academicYear, $subjects)
    {
        $studentIds = $students->pluck('id');
        $subjectIds = $subjects->pluck('id');
        $results = [];

        if ($term->isSemester()) {
            $quarterIds = $term->quarters()->pluck('id');
            $allMarks = StudentMark::whereIn('student_id', $studentIds)
                ->whereIn('term_id', $quarterIds)
                ->whereIn('subject_id', $subjectIds)
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $studentMarks = $allMarks->get($student->id, collect())->groupBy('subject_id');
                $total = 0;
                $subjectScores = [];
                foreach ($subjects as $subject) {
                    $subMarks = $studentMarks->get($subject->id);
                    if ($subMarks) {
                        $score = $subMarks->avg('score');
                        $total += $score;
                        $subjectScores[$subject->id] = $score;
                    }
                }
                $average = $subjects->count() > 0 ? $total / $subjects->count() : 0;
                $results[$student->id] = ['total' => $total, 'average' => $average, 'marks' => $subjectScores];
            }
        } elseif ($term->type === 'yearly' || $term->id === 'yearly') {
            $semesters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'semester')
                ->get();
            
            $semesterResults = [];
            foreach ($semesters as $sem) {
                $semesterResults[$sem->id] = $this->calculateSectionTotals($students, $sem, $academicYear, $subjects);
            }

            foreach ($students as $student) {
                $total = 0;
                $subjectScores = [];
                foreach ($subjects as $subject) {
                    $subSemAverages = [];
                    foreach ($semesters as $sem) {
                        $stats = $semesterResults[$sem->id][$student->id] ?? null;
                        if ($stats && isset($stats['marks'][$subject->id]) && $stats['marks'][$subject->id] > 0) {
                            $subSemAverages[] = $stats['marks'][$subject->id];
                        }
                    }
                    if (!empty($subSemAverages)) {
                        $score = array_sum($subSemAverages) / count($subSemAverages);
                        $total += $score;
                        $subjectScores[$subject->id] = $score;
                    }
                }
                $average = $subjects->count() > 0 ? $total / $subjects->count() : 0;
                $results[$student->id] = ['total' => $total, 'average' => $average, 'marks' => $subjectScores];
            }
        } else {
            $allMarks = StudentMark::whereIn('student_id', $studentIds)
                ->where('term_id', $term->id)
                ->whereIn('subject_id', $subjectIds)
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $studentMarks = $allMarks->get($student->id, collect());
                $total = $studentMarks->sum('score');
                $subjectScores = $studentMarks->pluck('score', 'subject_id')->toArray();
                $average = $subjects->count() > 0 ? $total / $subjects->count() : 0;
                $results[$student->id] = ['total' => $total, 'average' => $average, 'marks' => $subjectScores];
            }
        }

        return $results;
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
        
        return $this->prepareReportData($student, $section, $term, $academicYear, $subjects);
    }

    /**
     * Get report data for all students in a section (Bulk Version).
     */
    public function getSectionReportData(Collection $students, Section $section, Term $term, AcademicYear $academicYear)
    {
        $cacheKey = "section_subjects_{$section->grade_level_id}";
        $subjects = Cache::remember($cacheKey, 3600, function() use ($section) {
            return $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        });
        $isYearly = $term->id === 'yearly' || $term->type === 'yearly';
        $isSemester = $term->isSemester();
        
        $studentIds = $students->pluck('id');
        $targetTermsIds = [];

        if ($isYearly) {
            $semesters = Term::where('academic_year_id', $academicYear->id)->where('type', 'semester')->get();
            $targetTermsIds = $semesters->pluck('id')->toArray();
            foreach ($semesters as $sem) {
                $targetTermsIds = array_merge($targetTermsIds, $sem->quarters()->pluck('id')->toArray());
            }
        } elseif ($isSemester) {
            $targetTermsIds = array_merge([$term->id], $term->quarters()->pluck('id')->toArray());
        } else {
            $targetTermsIds = [$term->id];
        }

        // Fetch all relevant records and marks in bulk
        $allRecords = StudentTermRecord::whereIn('student_id', $studentIds)
            ->whereIn('term_id', $targetTermsIds)
            ->get()
            ->groupBy('student_id');

        $allMarks = StudentMark::whereIn('student_id', $studentIds)
            ->whereIn('term_id', $targetTermsIds)
            ->get()
            ->groupBy('student_id');

        $reports = [];
        foreach ($students as $student) {
            $reports[$student->id] = $this->prepareReportData(
                $student, $section, $term, $academicYear, $subjects,
                $allRecords->get($student->id, collect()),
                $allMarks->get($student->id, collect())
            );
        }
        
        return $reports;
    }

    /**
     * Helper to prepare report data from provided or fetched records/marks.
     */
    private function prepareReportData(Student $student, Section $section, Term $term, AcademicYear $academicYear, $subjects, $preFetchedRecords = null, $preFetchedMarks = null)
    {
        $isSemester = $term->isSemester();
        $isYearly = $term->id === 'yearly' || $term->type === 'yearly';
        $cacheKey = "{$section->id}_{$academicYear->id}";
        
        // 1. Basic Stats Calculation/Retrieval
        if ($isYearly && isset($this->yearlyStatsCache[$cacheKey][$student->id])) {
            $stats = $this->yearlyStatsCache[$cacheKey][$student->id];
            $totalScore = $stats['total'];
            $average = $stats['average'];
            $rank = $stats['rank'];
            $rankOutOf = $stats['rank_out_of'];
            $record = null; 
        } else {
            $record = $preFetchedRecords ? $preFetchedRecords->firstWhere('term_id', $term->id) : StudentTermRecord::where('student_id', $student->id)->where('term_id', $term->id)->first();

            if (!$record || $record->total_score === null) {
                $stats = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
                $totalScore = $stats['total'];
                $average = $stats['average'];
                $rank = $record->rank ?? '-';
                $rankOutOf = $record->rank_out_of ?? '-';
            } else {
                $totalScore = $record->total_score;
                $average = $record->average_score;
                $rank = $record->rank;
                $rankOutOf = $record->rank_out_of;
            }
        }

        // 2. Term Breakdown Data
        $marks = collect();
        $quarterData = [];
        $marksByQuarter = [];
        $marksBySemester = [];
        $statsByQuarter = [];
        $statsBySemester = [];
        $recordsByQuarter = [];
        
        if ($isSemester || $isYearly) {
            $targetSemesters = $isSemester ? collect([$term]) : Term::where('academic_year_id', $academicYear->id)->where('type', 'semester')->orderBy('start_date')->get();
            $allQuarterMarks = [];
            
            foreach ($targetSemesters as $sem) {
                $quarters = $sem->quarters()->orderBy('term_number')->get();
                foreach ($quarters as $q) {
                    $qRecord = $preFetchedRecords ? $preFetchedRecords->firstWhere('term_id', $q->id) : StudentTermRecord::where('student_id', $student->id)->where('term_id', $q->id)->first();
                    
                    $qMarks = $preFetchedMarks ? $preFetchedMarks->where('term_id', $q->id)->pluck('score', 'subject_id') : StudentMark::where('student_id', $student->id)->where('term_id', $q->id)->get()->pluck('score', 'subject_id');

                    $quarterData[$q->id] = [
                        'term' => $q,
                        'marks' => $qMarks,
                        'record' => $qRecord,
                        'total' => $qRecord->total_score ?? $qMarks->sum(),
                        'average' => $qRecord->average_score ?? ($subjects->count() > 0 ? $qMarks->sum() / $subjects->count() : 0),
                        'rank' => $qRecord->rank ?? '-',
                    ];

                    $statsByQuarter[$q->id] = [
                        'total' => $quarterData[$q->id]['total'],
                        'average' => $quarterData[$q->id]['average'],
                        'rank' => $quarterData[$q->id]['rank'],
                    ];

                    $recordsByQuarter[$q->id] = $qRecord;
                    
                    foreach ($qMarks as $subId => $score) {
                        $marksByQuarter[$subId][$q->id] = $score;
                        if (!isset($allQuarterMarks[$subId])) {
                            $allQuarterMarks[$subId] = [];
                        }
                        $allQuarterMarks[$subId][] = $score;
                    }
                }

                // Building Semester Stats
                $sStats = $this->calculateStudentTotals($student, $sem, $academicYear, $subjects);
                $sRecord = $preFetchedRecords ? $preFetchedRecords->firstWhere('term_id', $sem->id) : StudentTermRecord::where('student_id', $student->id)->where('term_id', $sem->id)->first();
                
                $statsBySemester[$sem->id] = [
                    'total' => $sRecord->total_score ?? $sStats['total'],
                    'average' => $sRecord->average_score ?? $sStats['average'],
                    'rank' => $sRecord->rank ?? '-',
                ];

                foreach ($sStats['marks'] as $subId => $score) {
                    $marksBySemester[$subId][$sem->id] = $score;
                }
            }
            
            // Calculate averages per subject (for yearly average)
            foreach ($allQuarterMarks as $subId => $scores) {
                $marks[$subId] = round(array_sum($scores) / count($scores), 2);
            }
        } elseif ($term->type === 'yearly') {
            $stats = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
            $marks = collect($stats['marks']);
        } else {
            $marks = $preFetchedMarks ? $preFetchedMarks->where('term_id', $term->id)->pluck('score', 'subject_id') : StudentMark::where('student_id', $student->id)->where('term_id', $term->id)->get()->pluck('score', 'subject_id');
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
            'rank_out_of' => $rankOutOf,
            'isSemester' => $isSemester,
            'quarters' => $quarterData,
            'semesters' => $this->getYearlyStats($student, $term, $academicYear, $subjects),
            'marks_by_quarter' => $marksByQuarter,
            'marks_by_semester' => $marksBySemester,
            'stats_by_quarter' => $statsByQuarter,
            'stats_by_semester' => $statsBySemester,
            'records_by_quarter' => $recordsByQuarter,
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
