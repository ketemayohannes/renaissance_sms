<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\Section;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
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
                ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated'])
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
                $total = round($studentTotals[$student->id], 2);
                $avg = round($studentAverages[$student->id], 2);
                $rank = $sortedTotals->filter(fn($score) => $score > $total)->count() + 1;

                $sectionCache[$student->id] = [
                    'total' => $total,
                    'average' => $avg,
                    'rank' => $rank,
                    'rank_out_of' => $totalStudents,
                ];

                // Only persist to DB if it's a real term (not virtual 'yearly')
                if ($term->id !== 'yearly' && $term->type !== 'yearly' && is_numeric($term->id)) {
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
                    'section_id' => $section->id,
                    'new_values' => ['section_id' => $section->id, 'term_id' => $term->id, 'count' => count($upsertData)],
                    'url' => request()->fullUrl(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            if ($term->id === 'yearly') {
                $this->yearlyStatsCache["{$section->id}_{$academicYear->id}"] = $sectionCache;
            }

            // Bust roster cache after bulk recalculation
            app(GradeCacheService::class)->clearSectionCache((int) $section->id, (int) $academicYear->id);

            return $students->count();
        }
        
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated'])
            ->get();

        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        $isSemester = $term->isSemester();
        
        // If this is a semester, also recalculate each quarter's stats first
        if ($isSemester) {
            $quarters = $term->quarters()->get();
            foreach ($quarters as $quarter) {
                $this->recalculateTermStats($section, $students, $quarter, $academicYear, $subjects);
            }
        }
        
        // Recalculate stats for the main term
        $this->recalculateTermStats($section, $students, $term, $academicYear, $subjects);

        return $students->count();
    }

    private function recalculateTermStats($section, $students, $term, $academicYear, $subjects)
    {
        // Batch calculate all totals for the section
        $batchResults = $this->calculateSectionTotals($students, $term, $academicYear, $subjects);
        
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
            $total = round($studentTotals[$student->id] ?? 0, 2);
            $avg = round($batchResults[$student->id]['average'] ?? 0, 2);
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
                'section_id' => $section->id,
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

        $isGrade11Or12 = false;
        $firstStudent = $students->first();
        if ($firstStudent) {
            $enrollment = $firstStudent->enrollments()->where('academic_year_id', $academicYear->id)->first();
            if ($enrollment && $enrollment->section && $enrollment->section->gradeLevel) {
                if (preg_match('/\b(11|12)\b/', $enrollment->section->gradeLevel->name)) {
                    $isGrade11Or12 = true;
                }
            }
        }

        // Pre-fetch elective enrollments for accurate denominator calculation
        $electiveEnrollments = DB::table('student_electives')
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->groupBy('student_id')
            ->map(fn($group) => $group->pluck('subject_id')->toArray());

        $regularSubjectsCount = $subjects->where('is_elective', false)->count();

        if ($term->isSemester()) {
            $quarterIds = $term->quarters()->pluck('id');
            $termTotalTypeId = $this->getTermTotalTypeId();
            
            $allMarks = StudentMark::whereIn('student_id', $studentIds)
                ->whereIn('term_id', $quarterIds)
                ->whereIn('subject_id', $subjectIds)
                ->with('assessmentTemplate')
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $studentMarks = $allMarks->get($student->id, collect())->groupBy('subject_id');
                $total = 0;
                $subjectScores = [];
                
                foreach ($subjects as $subject) {
                    $subMarks = $studentMarks->get($subject->id);
                    if ($subMarks && $subMarks->isNotEmpty()) {
                        $quarterSums = [];
                        foreach ($subMarks->groupBy('term_id') as $termId => $termMarks) {
                            // Prefer live components; fall back to TERM_TOTAL only if no components exist
                            $components = $termMarks->filter(
                                fn($m) => !$m->assessmentTemplate
                                       || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                            );
                            if ($components->isNotEmpty()) {
                                $quarterSums[$termId] = $components->sum('score');
                            } else {
                                $termTotal = $termMarks->first(
                                    fn($m) => $m->assessmentTemplate
                                           && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId
                                );
                                if ($termTotal) $quarterSums[$termId] = $termTotal->score;
                            }
                        }
                        
                        $count = count($quarterSums);
                        $score = $count > 0 ? array_sum($quarterSums) / $count : 0;
                        $total += $score;
                        $subjectScores[$subject->id] = $score;
                    }
                }
                
                // Determine effective elective count (Enrolled OR has score)
                $enrolledElectiveIds = $electiveEnrollments->get($student->id) ?? [];
                $electiveIdsWithScores = collect($subjectScores)->keys()->filter(function($id) use ($subjects) {
                    $s = $subjects->firstWhere('id', $id);
                    return $s && $s->is_elective;
                })->toArray();
                
                if ($isGrade11Or12) {
                    $effectiveElectiveCount = count($electiveIdsWithScores);
                } else {
                    $effectiveElectiveCount = count(array_unique(array_merge($enrolledElectiveIds, $electiveIdsWithScores)));
                }
                $denominator = $regularSubjectsCount + $effectiveElectiveCount;
                
                $average = $denominator > 0 ? round($total / $denominator, 2) : 0;
                $results[$student->id] = ['total' => round($total, 2), 'average' => $average, 'marks' => $subjectScores];
            }
        } elseif ($term->type === 'yearly' || $term->id === 'yearly') {
            $semesters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'semester')
                ->with('quarters')
                ->get();
            
            $allChildTermIds = $semesters->pluck('id')->concat($semesters->flatMap->quarters->pluck('id'))->unique();
            $termTotalTypeId = $this->getTermTotalTypeId();
            
            $allMarks = StudentMark::whereIn('student_id', $studentIds)
                ->whereIn('term_id', $allChildTermIds)
                ->whereIn('subject_id', $subjectIds)
                ->with('assessmentTemplate')
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $studentMarks = $allMarks->get($student->id, collect());
                $subjectScores = [];
                $totalYearlyAvg = 0;

                foreach ($subjects as $subject) {
                    $semAverages = [];
                    foreach ($semesters as $sem) {
                        $qIds = $sem->quarters->pluck('id');
                        // Per-quarter score: prefer live components; fall back to TERM_TOTAL
                        // only when no components exist (e.g. Q1/Q2 entered as a single total mark).
                        $allQMarks = $studentMarks
                            ->where('subject_id', $subject->id)
                            ->whereIn('term_id', $qIds);

                        if ($allQMarks->isNotEmpty()) {
                            $quarterSums = [];
                            foreach ($allQMarks->groupBy('term_id') as $termId => $termMarks) {
                                $components = $termMarks->filter(
                                    fn($m) => !$m->assessmentTemplate
                                           || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                                );
                                if ($components->isNotEmpty()) {
                                    $quarterSums[$termId] = $components->sum('score');
                                } else {
                                    $termTotal = $termMarks->first(
                                        fn($m) => $m->assessmentTemplate
                                               && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId
                                    );
                                    if ($termTotal) $quarterSums[$termId] = $termTotal->score;
                                }
                            }
                            $count = count($quarterSums);
                            if ($count > 0) $semAverages[] = array_sum($quarterSums) / $count;
                        }
                    }
                    
                    if (!empty($semAverages)) {
                        $semCount = count($semAverages);
                        $yearlyAvg = $semCount > 0 ? array_sum($semAverages) / $semCount : 0;
                        $subjectScores[$subject->id] = $yearlyAvg;
                        $totalYearlyAvg += $yearlyAvg;
                    }
                }

                $enrolledElectiveIds = $electiveEnrollments->get($student->id) ?? [];
                $electiveIdsWithScores = collect($subjectScores)->keys()->filter(function($id) use ($subjects) {
                    $s = $subjects->firstWhere('id', $id);
                    return $s && $s->is_elective;
                })->toArray();
                
                if ($isGrade11Or12) {
                    $effectiveElectiveCount = count($electiveIdsWithScores);
                } else {
                    $effectiveElectiveCount = count(array_unique(array_merge($enrolledElectiveIds, $electiveIdsWithScores)));
                }
                $denominator = $regularSubjectsCount + $effectiveElectiveCount;

                $average = $denominator > 0 ? round($totalYearlyAvg / $denominator, 2) : 0;
                $results[$student->id] = ['total' => round($totalYearlyAvg, 2), 'average' => $average, 'marks' => $subjectScores];
            }
        } else {
            $allMarks = StudentMark::whereIn('student_id', $studentIds)
                ->where('term_id', $term->id)
                ->whereIn('subject_id', $subjectIds)
                ->with('assessmentTemplate')
                ->get()
                ->groupBy('student_id');

            $termTotalTypeId = $this->getTermTotalTypeId();
            foreach ($students as $student) {
                $studentMarks = $allMarks->get($student->id, collect())->groupBy('subject_id');
                $subjectScores = [];
                $total = 0;

                foreach ($subjects as $subject) {
                    $subMarks = $studentMarks->get($subject->id);
                    if ($subMarks && $subMarks->isNotEmpty()) {
                        // Prefer live components; fall back to TERM_TOTAL only if no components exist.
                        // A stale leftover TERM_TOTAL row must never outrank fresh component marks.
                        $components = $subMarks->filter(
                            fn($m) => !$m->assessmentTemplate
                                   || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                        );
                        if ($components->isNotEmpty()) {
                            $score = $components->sum('score');
                        } else {
                            $termTotal = $subMarks->first(fn($m) => $m->assessmentTemplate && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId);
                            $score = $termTotal ? $termTotal->score : 0;
                        }
                        $subjectScores[$subject->id] = $score;
                        $total += $score;
                    }
                }
                
                $enrolledElectiveIds = $electiveEnrollments->get($student->id) ?? [];
                $electiveIdsWithScores = collect($subjectScores)->keys()->filter(function($id) use ($subjects) {
                    $s = $subjects->firstWhere('id', $id);
                    return $s && $s->is_elective;
                })->toArray();
                
                if ($isGrade11Or12) {
                    $effectiveElectiveCount = count($electiveIdsWithScores);
                } else {
                    $effectiveElectiveCount = count(array_unique(array_merge($enrolledElectiveIds, $electiveIdsWithScores)));
                }
                $denominator = $regularSubjectsCount + $effectiveElectiveCount;
                
                $average = $denominator > 0 ? round($total / $denominator, 2) : 0;
                $results[$student->id] = ['total' => round($total, 2), 'average' => $average, 'marks' => $subjectScores];
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

        $isGrade11Or12 = false;
        $enrollmentForGrade = $student->enrollments()->where('academic_year_id', $academicYear->id)->first();
        if ($enrollmentForGrade && $enrollmentForGrade->section && $enrollmentForGrade->section->gradeLevel) {
            if (preg_match('/\b(11|12)\b/', $enrollmentForGrade->section->gradeLevel->name)) {
                $isGrade11Or12 = true;
            }
        }

        // Determine actual denominator (Regular + Enrolled Electives)
        $regularSubjectsCount = $subjects->where('is_elective', false)->count();
        $enrolledElectivesCount = DB::table('student_electives')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->count();
        $denominator = $regularSubjectsCount + $enrolledElectivesCount;

        if ($term->isSemester()) {
            $quarterIds = $term->quarters()->pluck('id');
            $targetTermIds = $quarterIds->concat([$term->id]);
            $termTotalTypeId = $this->getTermTotalTypeId();
            $marks = StudentMark::where('student_id', $student->id)
                ->whereIn('term_id', $targetTermIds)
                ->with('assessmentTemplate')
                ->get()
                ->groupBy('subject_id');

            $total = 0;
            $subjectScores = [];
            foreach ($subjects as $subject) {
                $subMarks = $marks->get($subject->id, collect());
                $score = 0;
                
                if ($subMarks->isNotEmpty()) {
                    // 1. Calculate from Quarters
                    $quarterMarks = $subMarks->whereIn('term_id', $quarterIds);
                    if ($quarterMarks->isNotEmpty()) {
                        $quarterSums = [];
                        foreach ($quarterMarks->groupBy('term_id') as $termId => $termMarks) {
                            // Prefer live components; fall back to TERM_TOTAL only if no components exist
                            $components = $termMarks->filter(
                                fn($m) => !$m->assessmentTemplate
                                       || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                            );
                            if ($components->isNotEmpty()) {
                                $quarterSums[$termId] = $components->sum('score');
                            } else {
                                $termTotal = $termMarks->first(
                                    fn($m) => $m->assessmentTemplate
                                           && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId
                                );
                                if ($termTotal) $quarterSums[$termId] = $termTotal->score;
                            }
                        }
                        $count = count($quarterSums);
                        $score = $count > 0 ? array_sum($quarterSums) / $count : 0;
                    }
                    
                    // 2. FALLBACK: only use a direct semester mark when the quarters produced no
                    // live score at all (e.g. master grading opened directly on the semester term
                    // with no quarter data behind it). Never let it override a real quarter-derived
                    // score — that previously let a stale semester mark (frozen the last time
                    // "Auto-Calculate Semester" ran) silently outlive later corrections to the
                    // underlying quarter marks, e.g. Chemistry Q3/Q4 edited but semester still 74.5.
                    if ($score == 0) {
                        $directSemMark = $subMarks->firstWhere('term_id', $term->id);
                        if ($directSemMark) {
                            $score = $directSemMark->score;
                        }
                    }

                    if ($score > 0 || $subMarks->isNotEmpty()) {
                        $total += $score;
                        $subjectScores[$subject->id] = $score;
                    }
                }
            }
            
            // Determine effective elective count for denominator
            $enrolledElectiveIds = DB::table('student_electives')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->pluck('subject_id')
                ->toArray();
            
            $electiveIdsWithScores = collect($subjectScores)->keys()->filter(function($id) use ($subjects) {
                $s = $subjects->firstWhere('id', $id);
                return $s && $s->is_elective;
            })->toArray();
            
            if ($isGrade11Or12) {
                $effectiveElectiveCount = count($electiveIdsWithScores);
            } else {
                $effectiveElectiveCount = count(array_unique(array_merge($enrolledElectiveIds, $electiveIdsWithScores)));
            }
            $denominator = $regularSubjectsCount + $effectiveElectiveCount;

            $average = $denominator > 0 ? round($total / $denominator, 2) : 0;
            return ['total' => round($total, 2), 'average' => $average, 'marks' => $subjectScores];

        } else {
            if ($term->type === 'yearly' || $term->id === 'yearly') {
                $semesters = Term::where('academic_year_id', $academicYear->id)
                    ->where('type', 'semester')
                    ->with('quarters')
                    ->get();
                
                $allChildTermIds = $semesters->pluck('id')->concat($semesters->flatMap->quarters->pluck('id'))->unique();
                $termTotalTypeId = $this->getTermTotalTypeId();
                $rawMarks = StudentMark::where('student_id', $student->id)
                    ->whereIn('term_id', $allChildTermIds)
                    ->with('assessmentTemplate')
                    ->get()
                    ->groupBy('subject_id');

                $total = 0;
                $subjectScores = [];
                foreach ($subjects as $subject) {
                    $semAverages = [];
                    foreach ($semesters as $sem) {
                        $qIds = $sem->quarters->pluck('id');
                        // Per-quarter score: prefer live components; fall back to TERM_TOTAL
                        // only when no components exist (e.g. Q1/Q2 entered as a single total mark).
                        $allQMarks = $rawMarks
                            ->get($subject->id, collect())
                            ->whereIn('term_id', $qIds);

                        if ($allQMarks->isNotEmpty()) {
                            $quarterSums = [];
                            foreach ($allQMarks->groupBy('term_id') as $tId => $termMarks) {
                                $components = $termMarks->filter(
                                    fn($m) => !$m->assessmentTemplate
                                           || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                                );
                                if ($components->isNotEmpty()) {
                                    $quarterSums[$tId] = $components->sum('score');
                                } else {
                                    $termTotal = $termMarks->first(
                                        fn($m) => $m->assessmentTemplate
                                               && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId
                                    );
                                    if ($termTotal) $quarterSums[$tId] = $termTotal->score;
                                }
                            }
                            $count = count($quarterSums);
                            if ($count > 0) $semAverages[] = array_sum($quarterSums) / $count;
                        }
                    }
                    
                    if (!empty($semAverages)) {
                        $semCount = count($semAverages);
                        $yearlyAvg = $semCount > 0 ? array_sum($semAverages) / $semCount : 0;
                        $subjectScores[$subject->id] = $yearlyAvg;
                        $total += $yearlyAvg;
                    }
                }
            } else {
                $termTotalTypeId = $this->getTermTotalTypeId();
                $marks = StudentMark::where('student_id', $student->id)
                    ->where('term_id', $term->id)
                    ->with('assessmentTemplate')
                    ->get()
                    ->groupBy('subject_id');

                $total = 0;
                $subjectScores = [];
                foreach ($marks as $subjectId => $subMarks) {
                    // Prefer live components; fall back to TERM_TOTAL only if no components exist.
                    // Summing every row regardless of type previously double-counted subjects that
                    // had both fresh component marks AND a stale leftover TERM_TOTAL row.
                    $components = $subMarks->filter(
                        fn($m) => !$m->assessmentTemplate
                               || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                    );
                    if ($components->isNotEmpty()) {
                        $score = $components->sum('score');
                    } else {
                        $termTotal = $subMarks->first(fn($m) => $m->assessmentTemplate && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId);
                        $score = $termTotal ? $termTotal->score : 0;
                    }
                    $subjectScores[$subjectId] = $score;
                    $total += $score;
                }
            }
            
            // Determine effective elective count
            $enrolledElectiveIds = DB::table('student_electives')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->pluck('subject_id')
                ->toArray();
            
            $electiveIdsWithScores = collect($subjectScores)->keys()->filter(function($id) use ($subjects) {
                $s = $subjects->firstWhere('id', $id);
                return $s && $s->is_elective;
            })->toArray();
            
            if ($isGrade11Or12) {
                $effectiveElectiveCount = count($electiveIdsWithScores);
            } else {
                $effectiveElectiveCount = count(array_unique(array_merge($enrolledElectiveIds, $electiveIdsWithScores)));
            }
            $denominator = $regularSubjectsCount + $effectiveElectiveCount;
            
            $average = $denominator > 0 ? round($total / $denominator, 2) : 0;
        }

        return ['total' => round($total, 2), 'average' => $average, 'marks' => $subjectScores];
    }

    /**
     * Get standardized report data for a student.
     */
    public function getStudentReportData(Student $student, Term $term, AcademicYear $academicYear)
    {
        $enrollment = $student->enrollments()->where('academic_year_id', $academicYear->id)->first();
        if (!$enrollment) return [];

        $section = $enrollment->section;
        $isYearly = $term->id === 'yearly' || $term->type === 'yearly';

        // We use the section bulk fetcher to ensure on-the-fly ranking is performed
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated'])
            ->get();
        
        $allReports = $this->getSectionReportData($students, $section, $term, $academicYear);
        return $allReports[(string)$student->id] ?? $allReports[(int)$student->id] ?? $this->prepareReportData($student, $section, $term, $academicYear, null);
    }

    /**
     * Get report data for all students in a section (Bulk Version).
     */
    public function getSectionReportData(Collection $students, Section $section, Term $term, AcademicYear $academicYear)
    {
        $subjects = app(GradeCacheService::class)->getSubjects(
            (int) $section->grade_level_id,
            fn() => $section->gradeLevel->subjects()->orderByPivot('sort_order')->get()
        );
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

        $allMarks = StudentMark::with('assessmentTemplate')->whereIn('student_id', $studentIds)
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

        // On-the-fly ranking if records are missing ranks in the database
        // This ensures the roster (Academic Report) shows ranks even before a manual sync
        $hasMissingRanks = false;
        foreach ($reports as $r) {
            if ($r['rank'] === '-' || $r['rank'] === null || $r['rank'] === 0) {
                $hasMissingRanks = true;
                break;
            }
        }

        if ($hasMissingRanks) {
            $sortedTotals = collect($reports)->pluck('totalScore')->sortDesc();
            $totalStudents = $sortedTotals->count();
            foreach ($reports as $sid => &$report) {
                if ($report['rank'] === '-' || $report['rank'] === null || $report['rank'] === 0) {
                    $rank = $sortedTotals->filter(fn($score) => $score > $report['totalScore'])->count() + 1;
                    $report['rank'] = $rank;
                    $report['rank_out_of'] = $totalStudents;
                }
            }
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
        
        $isGrade11Or12 = false;
        if ($section && $section->gradeLevel) {
            if (preg_match('/\b(11|12)\b/', $section->gradeLevel->name)) {
                $isGrade11Or12 = true;
            }
        }
        
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

            if (!$record || $record->total_score === null || $record->total_score == 0) {
                $stats = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
                $totalScore = $stats['total'];
                $average = $stats['average'];
                $rank = ($record && $record->rank) ? $record->rank : '-';
                $rankOutOf = ($record && $record->rank_out_of) ? $record->rank_out_of : '-';
            } else {
                // DEFENSIVE: If record has total > 0 but marks were pre-fetched and found 0, 
                // we should trust the marks more (or recalculate).
                // This fixed the "ghost 6.7%" stat when grades were deleted but records remained.
                if ($record->total_score > 0 && $preFetchedMarks && $preFetchedMarks->isEmpty()) {
                    $stats = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
                    $totalScore = $stats['total'];
                    $average = $stats['average'];
                    $rank = $record->rank;
                    $rankOutOf = $record->rank_out_of;
                } else {
                    $totalScore = $record->total_score;
                    $average = $record->average_score;
                    $rank = $record->rank;
                    $rankOutOf = $record->rank_out_of;
                }
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
            $termTotalTypeId = $this->getTermTotalTypeId();
        
            foreach ($targetSemesters as $sem) {
                $quarters = $sem->quarters()->orderBy('term_number')->get();
                foreach ($quarters as $q) {
                    $qRecord = $preFetchedRecords ? $preFetchedRecords->firstWhere('term_id', $q->id) : StudentTermRecord::where('student_id', $student->id)->where('term_id', $q->id)->first();
                    
                    $qMarksCollection = $preFetchedMarks ? $preFetchedMarks->where('term_id', $q->id) : StudentMark::where('student_id', $student->id)->where('term_id', $q->id)->get();
                    
                    // Per-subject score: prefer live component marks over TERM_TOTAL rows
                    // (TERM_TOTAL can be stale if components were edited after calculation).
                    // BUT if a quarter only has a TERM_TOTAL row and no components (e.g. Q1/Q2
                    // entered as a single total), fall back to that TERM_TOTAL so data is not lost.
                    $qMarks = $qMarksCollection->groupBy('subject_id')->map(
                        function($subjectMarks) use ($termTotalTypeId) {
                            $components = $subjectMarks->filter(
                                fn($m) => !$m->assessmentTemplate
                                       || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                            );
                            if ($components->isNotEmpty()) {
                                // Live components exist — sum them (ignores any stale TERM_TOTAL)
                                return $components->sum('score');
                            }
                            // No components — fall back to the TERM_TOTAL row if present
                            $termTotal = $subjectMarks->first(
                                fn($m) => $m->assessmentTemplate
                                       && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId
                            );
                            return $termTotal ? $termTotal->score : 0;
                        }
                    );

                    // Determine actual denominator for this student
                    $regularCount = $subjects->where('is_elective', false)->count();
                    
                    $qSubjectScores = $qMarks->toArray();
                    $electiveIdsWithScores = collect($qSubjectScores)->keys()->filter(function($id) use ($subjects) {
                        $s = $subjects->firstWhere('id', $id);
                        return $s && $s->is_elective;
                    })->toArray();

                    if ($isGrade11Or12) {
                        $effectiveElectiveCount = count($electiveIdsWithScores);
                    } else {
                        $enrolledElectiveIds = DB::table('student_electives')
                            ->where('student_id', $student->id)
                            ->where('academic_year_id', $academicYear->id)
                            ->pluck('subject_id')
                            ->toArray();
                        $effectiveElectiveCount = count(array_unique(array_merge($enrolledElectiveIds, $electiveIdsWithScores)));
                    }
                    
                    $denominator = $regularCount + $effectiveElectiveCount;

                    // Always compute total & average from the freshly-resolved $qMarks.
                    // $qRecord->total_score / average_score can be stale (written when marks
                    // were different, e.g. Chemistry 59 → stored total 1214 instead of 1252).
                    // Rank still comes from the DB record since it requires a section-wide sort.
                    $liveTotal   = round($qMarks->sum(), 2);
                    $liveAverage = $denominator > 0 ? round($liveTotal / $denominator, 2) : 0;

                    $quarterData[$q->id] = [
                        'term'    => $q,
                        'marks'   => $qMarks,
                        'record'  => $qRecord,
                        'total'   => $liveTotal,
                        'average' => $liveAverage,
                        'rank'    => ($qRecord && $qRecord->rank && $qRecord->rank != 0) ? $qRecord->rank : '-',
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
                    'total' => ($sRecord && $sRecord->total_score > 0) ? $sRecord->total_score : $sStats['total'],
                    'average' => ($sRecord && $sRecord->average_score > 0) ? $sRecord->average_score : $sStats['average'],
                    'rank' => ($sRecord && $sRecord->rank && $sRecord->rank != 0) ? $sRecord->rank : '-',
                ];

                foreach ($sStats['marks'] as $subId => $score) {
                    $marksBySemester[$subId][$sem->id] = $score;
                }
            }
            
            // Calculate averages per subject (for yearly average or final semester view)
            if ($isSemester && isset($sStats)) {
                $marks = collect($sStats['marks']);
            } elseif ($isYearly) {
                $marks = collect();
                foreach ($subjects as $subject) {
                    $semAverages = isset($marksBySemester[$subject->id]) ? array_values($marksBySemester[$subject->id]) : [];
                    if (!empty($semAverages)) {
                        $semCount = count($semAverages);
                        $marks[$subject->id] = $semCount > 0 ? round(array_sum($semAverages) / $semCount, 2) : null;
                    } else {
                        $marks[$subject->id] = null;
                    }
                }
            } else {
                foreach ($allQuarterMarks as $subId => $scores) {
                    $count = count($scores);
                    $marks[$subId] = $count > 0 ? round(array_sum($scores) / $count, 2) : null;
                }
            }
        } elseif ($term->type === 'yearly') {
            $stats = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
            $marks = collect($stats['marks']);
        } else {
            $rawMarks = $preFetchedMarks ? $preFetchedMarks->where('term_id', $term->id) : StudentMark::with('assessmentTemplate')->where('student_id', $student->id)->where('term_id', $term->id)->get();

            $termTotalTypeId = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->value('id');
            $marks = collect();

            // Prefer live components; fall back to TERM_TOTAL only if no components exist for
            // that subject. A stale leftover TERM_TOTAL row must never outrank fresh component
            // marks entered/edited afterwards.
            foreach ($rawMarks->groupBy('subject_id') as $subId => $subMarks) {
                $components = $subMarks->filter(
                    fn($m) => !$m->assessmentTemplate
                           || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId
                );
                if ($components->isNotEmpty()) {
                    $marks[$subId] = $components->sum('score');
                } else {
                    $termTotal = $subMarks->first(fn($m) => $m->assessmentTemplate && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId);
                    if ($termTotal) $marks[$subId] = $termTotal->score;
                }
            }
        }

        if ($isYearly) {
            $regularCount = $subjects->where('is_elective', false)->count();
            
            $electiveIdsWithScores = $marks->filter(fn($v) => $v !== null && $v !== '')->keys()->filter(function($id) use ($subjects) {
                $s = $subjects->firstWhere('id', $id);
                return $s && $s->is_elective;
            })->toArray();

            if ($isGrade11Or12) {
                $effectiveElectiveCount = count($electiveIdsWithScores);
            } else {
                $enrolledElectiveIds = DB::table('student_electives')
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->pluck('subject_id')
                    ->toArray();
                $effectiveElectiveCount = count(array_unique(array_merge($enrolledElectiveIds, $electiveIdsWithScores)));
            }
            
            $denominator = $regularCount + $effectiveElectiveCount;
            $marksSum = round($marks->filter(fn($v) => $v > 0)->sum(), 2);

            // Only override $totalScore/$average from marks when they actually have data.
            // If the semester-loop produced all-zero marks (e.g. no semester terms found,
            // or calculateStudentTotals returned empty), we keep the value already set
            // by calculateStudentTotals (line ~672) or yearlyStatsCache (line ~662).
            if ($marksSum > 0) {
                $totalScore = $marksSum;
                $average = $denominator > 0 ? round($totalScore / $denominator, 2) : 0;
            } elseif (empty($totalScore) || $totalScore == 0) {
                // Last-resort fallback: recalculate directly from raw marks
                $fallback = $this->calculateStudentTotals($student, $term, $academicYear, $subjects);
                $totalScore = $fallback['total'];
                $average   = $fallback['average'];
                // Sync marks collection from fallback so the roster table shows correct per-subject values
                if (!empty($fallback['marks'])) {
                    foreach ($fallback['marks'] as $subId => $score) {
                        $marks[$subId] = $score;
                    }
                }
            }
            // else: $totalScore > 0 was set earlier (cache / calculateStudentTotals), keep it.
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
            'semesters' => $this->getYearlyStats($student, $term, $academicYear, $subjects, $preFetchedRecords),
            'marks_by_quarter' => $marksByQuarter,
            'marks_by_semester' => $marksBySemester,
            'stats_by_quarter' => $statsByQuarter,
            'stats_by_semester' => $statsBySemester,
            'records_by_quarter' => $recordsByQuarter,
        ];
    }

    private function getYearlyStats($student, $term, $academicYear, $subjects, $preFetchedRecords = null)
    {
        if ($term->type !== 'yearly') return [];

        $semesterStats = [];
        $semesters = Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'semester')
            ->get();
        
        foreach ($semesters as $semester) {
            $sRecord = $preFetchedRecords ? $preFetchedRecords->firstWhere('term_id', $semester->id) : StudentTermRecord::where('student_id', $student->id)
                ->where('term_id', $semester->id)
                ->first();
            
            $sData = $this->calculateStudentTotals($student, $semester, $academicYear, $subjects);

            $semesterStats[$semester->id] = [
                'term' => $semester,
                'record' => $sRecord,
                'marks' => $sData['marks'] ?? [],
                'total' => ($sRecord && $sRecord->total_score > 0) ? $sRecord->total_score : $sData['total'],
                'average' => ($sRecord && $sRecord->average_score > 0) ? $sRecord->average_score : $sData['average'],
                'rank' => ($sRecord && $sRecord->rank && $sRecord->rank != 0) ? $sRecord->rank : '-'
            ];
        }
        return $semesterStats;
    }

    /**
     * Calculate Semester Grades for a Section.
     * Logic: Semester 1 = (Q1 + Q2) / 2
     * Logic: Semester 2 = (Q3 + Q4) / 2
     * Applies to all subjects assigned to the grade level.
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
        $subjects = $gradeLevel->subjects()->get();

        if ($subjects->isEmpty()) return 0;

        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated'])
            ->get();

        if ($students->isEmpty()) return 0;

        $termTotalTypeId = $this->getTermTotalTypeId();

        // 1. Pre-register/fetch all destination templates for all subjects
        $destTemplatesMap = []; // subject_id => template_id
        foreach ($subjects as $subject) {
            $template = $this->getOrCreateTermTotalTemplate($subject, $gradeLevel, $semesterTerm, $academicYear);
            $destTemplatesMap[$subject->id] = $template->id;
        }

        // 2. Fetch all TERM_TOTAL source templates (one per subject per quarter)
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

        // Build a map: subject_id => [term_id => template_id] for quick lookup
        $termTotalTemplateMap = []; // subject_id => [term_id => template_id]
        foreach ($allSourceTemplates as $subjectId => $templates) {
            foreach ($templates as $tpl) {
                $termTotalTemplateMap[$subjectId][$tpl->term_id] = $tpl->id;
            }
        }

        // 3a. Fetch TERM_TOTAL marks for subjects that have them
        $allSourceTemplateIds = $allSourceTemplates->flatten()->pluck('id');
        $termTotalMarks = StudentMark::whereIn('student_id', $students->pluck('id'))
            ->whereIn('assessment_template_id', $allSourceTemplateIds)
            ->whereNotNull('score')
            ->get()
            ->groupBy(function($m) { return $m->student_id . '_' . $m->subject_id . '_' . $m->term_id; });

        // 3b. Also fetch ALL component marks for Q3/Q4 (used as fallback when no TERM_TOTAL exists)
        $allComponentMarks = StudentMark::whereIn('student_id', $students->pluck('id'))
            ->whereIn('term_id', $sourceTermIds->toArray())
            ->whereNotNull('score')
            ->get()
            ->groupBy(function($m) { return $m->student_id . '_' . $m->subject_id . '_' . $m->term_id; });

        $upsertData = [];
        $now = now();

        // 4. Calculate averages in memory
        foreach ($subjects as $subject) {
            $destTemplateId = $destTemplatesMap[$subject->id];

            foreach ($students as $student) {
                $quarterScores = [];

                foreach ($sourceTermIds as $termId) {
                    $key = $student->id . '_' . $subject->id . '_' . $termId;

                    // Prefer TERM_TOTAL mark if it exists for this subject+quarter
                    if (!empty($termTotalTemplateMap[$subject->id][$termId]) && $termTotalMarks->has($key)) {
                        $quarterScores[] = $termTotalMarks[$key]->sum('score');
                    } elseif ($allComponentMarks->has($key)) {
                        // Fallback: sum component marks for this subject+quarter
                        $quarterScores[] = $allComponentMarks[$key]->sum('score');
                    }
                    // If neither exists, this quarter is simply omitted (student has no grade)
                }

                if (empty($quarterScores)) continue;

                // Average over the number of quarters that actually had data
                $total = array_sum($quarterScores);
                $average = round($total / count($quarterScores), 2);

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
