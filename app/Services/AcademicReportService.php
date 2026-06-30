<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Term;
use App\Models\Subject;
use App\Models\AcademicReportSetting;
use App\Models\StudentTermRecord;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class AcademicReportService
{
    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Bust cached roster data for a section+term combination.
     * Preserved for call-sites that use this static method directly;
     * internally delegates to GradeCacheService.
     */
    public static function clearRosterCache(int $sectionId, $termId, int $academicYearId): void
    {
        app(GradeCacheService::class)->clearSectionCache($sectionId, $academicYearId);
    }


    /**
     * Update academic report (roster) settings.
     */
    public function updateSettings(array $data, ?UploadedFile $logo = null): AcademicReportSetting
    {
        $settings = AcademicReportSetting::firstOrNew();
        $settings->school_name = $data['school_name'] ?? $settings->school_name;

        if ($logo) {
            if ($settings->roster_logo_path) {
                Storage::disk('public')->delete($settings->roster_logo_path);
            }
            $settings->roster_logo_path = $logo->store('academic-reports', 'public');
        }

        $displayOptions = $settings->display_options ?? [];
        if (isset($data['subject_order'])) {
            $displayOptions['subject_order'] = collect($data['subject_order'])
                ->filter(fn($val) => $val !== null && $val !== '')
                ->map(fn($val) => (int)$val)
                ->toArray();
        }
        $settings->display_options = $displayOptions;

        $settings->save();
        return $settings;
    }

    /**
     * Prepare data for section roster report.
     * NOTE: Recalculation removed for performance. Data is read-only here.
     * Grades should be recalculated when marks are saved, not on every view.
     */
    public function prepareRosterData(Section $section, $term, AcademicYear $academicYear): array
    {
        return app(GradeCacheService::class)->getRosterData(
            (int) $section->id,
            $term->id,
            (int) $academicYear->id,
            function () use ($section, $term, $academicYear) {
            $gradeLevel = $section->gradeLevel;
            $subjects = $gradeLevel->subjects()->orderByPivot('sort_order')->get();
            $settings = \App\Models\AcademicReportSetting::first();

            // Enforce Subject Ordering: Prioritize UI Settings, Fallback to Fixed List
            if ($settings && isset($settings->display_options['subject_order'])) {
                $orderMap = $settings->display_options['subject_order'];
                $subjects = $subjects->sort(function ($a, $b) use ($orderMap) {
                    $posA = $orderMap[$a->id] ?? 999;
                    $posB = $orderMap[$b->id] ?? 999;
                    return $posA <=> $posB;
                })->values();
            } else {
                // Hardcoded Fallback (User Requested Step 162)
                $fixedOrder = [
                    'Amharic',
                    'Mathematics',
                    'Oromo',
                    'English',
                    'Afan',
                    'Physics',
                    'Chemistry',
                    'Biology',
                    'History',
                    'Geography',
                    'Citizenship',
                    'Economics',
                    'Information Technology',
                    'Information Technolog',
                    'Physical Education',
                    'Physical Educathin'
                ];

                $subjects = $subjects->sort(function ($a, $b) use ($fixedOrder) {
                    $getPos = function($name) use ($fixedOrder) {
                        $pos = array_search($name, $fixedOrder);
                        if ($pos !== false) return $pos;
                        foreach ($fixedOrder as $index => $orderedName) {
                            if (stripos($name, $orderedName) !== false) return $index;
                        }
                        return 999;
                    };
                    return $getPos($a->name) <=> $getPos($b->name);
                })->values();
            }

            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get();
            $sectionReportData = $this->gradingService->getSectionReportData($students, $section, $term, $academicYear);

            $targetTermsForAttendance = collect([$term]);
            if ($term->isSemester()) {
                $targetTermsForAttendance = $targetTermsForAttendance->concat($term->quarters()->get());
            } elseif ($term->type === 'yearly') {
                $targetTermsForAttendance = $targetTermsForAttendance->concat(
                    Term::where('academic_year_id', $academicYear->id)->whereIn('type', ['quarter', 'semester'])->get()
                );
            }
            $batchAttendance = $this->getBatchAttendance($students, $targetTermsForAttendance, $academicYear);

            $reports = [];
            foreach ($students as $student) {
                $studentData = $sectionReportData[$student->id] ?? null;
                if ($studentData) {
                    $reports[] = $this->prepareStudentRosterRow($student, $term, $academicYear, $subjects, $studentData, $batchAttendance[$student->id] ?? []);
                } else {
                    // Include student with empty data if missing from grading service
                    $absRaw = $batchAttendance[$student->id][$term->id]['absent'] ?? 0;
                    $reports[] = [
                        'student' => $student,
                        'gender' => $student->gender,
                        'marks' => [],
                        'total' => 0,
                        'average' => 0,
                        'rank' => '-',
                        'conduct' => 'A',
                        'absence' => ($absRaw == 0) ? '_' : $absRaw,
                        'rows' => []
                    ];
                }
            }

            // Calculate yearly ranks if applicable
            if ($term->type === 'yearly') {
                $reports = $this->calculateYearlyRanks($reports);
            }

            // Filter out elective subjects with no data
            $subjects = $this->filterEmptySubjects($subjects, $reports);

            return [
                'academicYear' => $academicYear,
                'term' => $term,
                'section' => $section,
                'subjects' => $subjects,
                'reports' => $reports,
                'settings' => $settings,
                'generalSettings' => \App\Models\ReportCardSetting::first(),
                'isKindergarten' => \App\Helpers\KindergartenGradeHelper::isKindergarten($section),
            ];
        });
    }

    private function prepareStudentRosterRow($student, $term, $academicYear, $subjects, array $reportData, array $studentAttendance = []): array
    {
        $rows = [
            'q1' => null,
            'q2' => null,
            'avg' => null
        ];
        $record = $reportData['record'] ?? null;

        if ($term->isSemester()) {
            $qIdx = 1;
            $quarters = collect($reportData['quarters'])->sortBy(fn($q) => $q['term']->term_number ?? $q['term']->start_date);
            
            foreach ($quarters as $qData) {
                $label = 'Quarter ' . ($qIdx == 1 ? 'I' : ($qIdx == 2 ? 'II' : ($qIdx == 3 ? 'III' : 'IV')));
                $dynamicAbs = $studentAttendance[$qData['term']->id]['absent'] ?? null;
                $rows['q' . $qIdx] = $this->formatRosterRow($label, $qData['marks'], $qData['total'], $qData['average'], $qData['record'], $qData['rank'], $dynamicAbs);
                $qIdx++;
                if ($qIdx > 2) break;
            }
            $dynamicSemAbs = $studentAttendance[$term->id]['absent'] ?? null;
            $rows['avg'] = $this->formatRosterRow('Sem Avg', $reportData['marks'], $reportData['totalScore'], $reportData['average'], $record, $reportData['rank'], $dynamicSemAbs);
            
        } elseif ($term->type === 'yearly') {
            return $this->prepareYearlyRosterRow($student, $academicYear, $subjects, $reportData, $studentAttendance);
            
        } else {
            $dynamicAbs = $studentAttendance[$term->id]['absent'] ?? null;
            $rows['q1'] = $this->formatRosterRow($term->name, $reportData['marks'], $reportData['totalScore'], $reportData['average'], $record, $reportData['rank'], $dynamicAbs);
        }

        $absRaw = ($record && ($record->days_absent ?? 0) > 0) ? $record->days_absent : ($studentAttendance[$term->id]['absent'] ?? 0);
        $absence = ($absRaw == 0 || $absRaw === null) ? '_' : $absRaw;

        return [
            'student' => $student,
            'gender' => $student->gender,
            'marks' => $reportData['marks'],
            'total' => $reportData['totalScore'],
            'average' => $reportData['average'],
            'rank' => $reportData['rank'],
            'conduct' => ($record ? $record->conduct_grade : null) ?? 'A',
            'absence' => $absence,
            'rows' => $rows
        ];
    }

    private function prepareYearlyRosterRow($student, $academicYear, $subjects, array $reportData, array $studentAttendance = []): array
    {
        // Yearly data is already pre-aggregated in $reportData by GradingService
        $rows = [];
        $yearTotal = $reportData['totalScore'] ?? 0;

        // Distribute quarter data into rows
        foreach ($reportData['quarters'] as $qId => $qData) {
            $quarter = $qData['term'];
            $rows['q' . $quarter->term_number] = $this->formatRosterRow(
                'Quarter ' . $this->romanNumeral($quarter->term_number),
                $qData['marks'],
                $qData['total'],
                $qData['average'],
                $qData['record'],
                $qData['rank'],
                $studentAttendance[$qId]['absent'] ?? null
            );
        }

        // Distribute semester data into rows
        if (isset($reportData['semesters']) && is_array($reportData['semesters'])) {
            foreach ($reportData['semesters'] as $sId => $sData) {
                $semester = $sData['term'];
                $rows['s' . $semester->term_number] = $this->formatRosterRow(
                    'Sem Avg',
                    $sData['marks'],
                    $sData['total'],
                    $sData['average'],
                    $sData['record'],
                    $sData['rank'],
                    $studentAttendance[$sId]['absent'] ?? null
                );
            }
        }

        $yearRecord = $reportData['record'] ?? null;
        $dynamicYearlyAbs = $studentAttendance['yearly']['absent'] ?? ($studentAttendance['all_year']['absent'] ?? null);
        
        $rows['avg'] = $this->formatRosterRow(
            'Year Avg',
            $reportData['marks'],
            $yearTotal,
            $reportData['average'] ?? 0,
            $yearRecord,
            $reportData['rank'] ?? '-',
            $dynamicYearlyAbs
        );

        $absRaw = ($yearRecord && $yearRecord->days_absent !== null) ? $yearRecord->days_absent : ($studentAttendance['all_year']['absent'] ?? 0);
        $absence = ($absRaw == 0 || $absRaw === null) ? '_' : $absRaw;

        return [
            'student' => $student,
            'gender' => $student->gender,
            'rows' => $rows,
            'yearTotal' => $yearTotal,
            'absence' => $absence
        ];
    }

    private function formatRosterRow($label, $marks, $total, $average, $record, $rank, $dynamicAbsence = null): array
    {
        // Absence is only meaningful at the quarter level — hide it for semester/yearly summary rows
        $isSummaryRow = in_array($label, ['Sem Avg', 'Year Avg']);

        // Only use the stored record value when it is actually a positive number.
        // days_absent = 0 in the record is just a default and should not block the
        // live attendance count (which is what actually shows the real absences).
        $storedAbsence = ($record && isset($record->days_absent) && $record->days_absent > 0)
            ? $record->days_absent
            : null;
        $absenceVal = $isSummaryRow
            ? 0
            : ($storedAbsence ?? (is_numeric($dynamicAbsence) ? $dynamicAbsence : 0));

        return [
            'label' => $label,
            'marks' => $marks,
            'total' => $total,
            'average' => $average,
            'conduct' => ($record ? $record->conduct_grade : null) ?? ($label === 'Sem Avg' || $label === 'Year Avg' ? '-' : 'A'),
            'absence' => ($isSummaryRow || $absenceVal == 0) ? '_' : $absenceVal,
            'rank' => $rank
        ];
    }

    private function getBatchAttendance(Collection $students, Collection $terms, AcademicYear $academicYear): array
    {
        $studentIds = $students->pluck('id')->toArray();
        $termIds = $terms->pluck('id')->filter(fn($id) => is_numeric($id))->toArray();

        // PERFORMANCE FIX: Pre-fetch section IDs instead of using whereHas
        // whereHas creates a slow subquery per row; this uses a simple IN clause
        $validSectionIds = \App\Models\Section::where('academic_year_id', $academicYear->id)
            ->pluck('id')
            ->toArray();

        $allAttendance = StudentAttendance::whereIn('student_id', $studentIds)
            ->whereIn('section_id', $validSectionIds)
            ->get(['student_id', 'attendance_date', 'status']);

        $results = [];
        foreach ($students as $student) {
            $studentAttendance = $allAttendance->where('student_id', $student->id);
            
            foreach ($terms as $term) {
                $termAttendance = $studentAttendance;
                if ($term->type !== 'yearly' && is_numeric($term->id)) {
                    $termAttendance = $studentAttendance->whereBetween('attendance_date', [$term->start_date, $term->end_date]);
                }

                $absCount = $termAttendance->where('status', 'absent')->count();
                $results[$student->id][$term->id] = ['absent' => $absCount];
            }
            
            // All year total
            $results[$student->id]['all_year'] = ['absent' => $studentAttendance->where('status', 'absent')->count()];
        }

        return $results;
    }

    private function calculateYearlyRanks(array $reports): array
    {
        $sortedTotals = collect($reports)->pluck('yearTotal')->sortDesc();
        foreach ($reports as &$report) {
            $report['rows']['avg']['rank'] = $sortedTotals->filter(fn($score) => $score > ($report['yearTotal'] ?? 0))->count() + 1;
        }
        return $reports;
    }

    private function filterEmptySubjects($subjects, $reports): Collection
    {
        return $subjects->filter(function($subject) use ($reports) {
            // Never hide non-elective subjects
            if (!$subject->is_elective) return true;
            
            foreach ($reports as $r) {
                // Check top-level marks (current term/yearly total)
                if (isset($r['marks'][$subject->id]) && $r['marks'][$subject->id] !== null && $r['marks'][$subject->id] !== '') {
                    return true;
                }
                
                // Check rows (Quarters, Semesters in Semester/Yearly view)
                if (isset($r['rows']) && is_array($r['rows'])) {
                    foreach ($r['rows'] as $row) {
                        if (isset($row['marks'][$subject->id]) && $row['marks'][$subject->id] !== null && $row['marks'][$subject->id] !== '') {
                            return true;
                        }
                    }
                }
            }
            return false;
        });
    }

    /**
     * Prepare data for overall subject analysis report.
     */
    public function prepareSubjectAnalysisData(AcademicYear $academicYear, $term, GradeLevel $gradeLevel, Subject $subject): array
    {
        $passMark = 75;
        $sectionStats = [];
        $allStudentsData = [];

        // Resolve TERM_TOTAL template IDs once, to exclude them when summing component marks.
        // This prevents stale master-sheet totals from overriding the real component sum.
        $termTotalTypeId = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->value('id');
        $termTotalTemplateIds = $termTotalTypeId
            ? \App\Models\AssessmentTemplate::where('assessment_type_id', $termTotalTypeId)->pluck('id')
            : collect();

        // Determine which term IDs to query marks for
        $isQuarter = $term->type === 'quarter';
        $isSemester = method_exists($term, 'isSemester') && $term->isSemester();
        $isYearly   = $term->id === 'yearly' || $term->type === 'yearly';

        if ($isSemester) {
            $targetTermIds = $term->quarters()->pluck('id');
        } elseif ($isYearly) {
            $semesters = \App\Models\Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'semester')->with('quarters')->get();
            $targetTermIds = $semesters->flatMap->quarters->pluck('id');
        } else {
            // Quarter
            $targetTermIds = collect([$term->id]);
        }

        foreach ($gradeLevel->sections as $section) {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivot('status', 'active')
                ->get();
            if ($students->isEmpty()) continue;

            $studentIds = $students->pluck('id');

            // Fetch component marks only (no TERM_TOTAL rows) for all students in this section.
            $rawMarks = \App\Models\StudentMark::whereIn('student_id', $studentIds)
                ->whereIn('term_id', $targetTermIds)
                ->where('subject_id', $subject->id)
                ->when($termTotalTemplateIds->isNotEmpty(), function($q) use ($termTotalTemplateIds) {
                    $q->whereNotIn('assessment_template_id', $termTotalTemplateIds);
                })
                ->get()
                ->groupBy('student_id');

            $scores = [];
            $passed = 0;
            $appeared = 0;

            foreach ($students as $student) {
                $studentMarks = $rawMarks->get($student->id, collect());
                if ($studentMarks->isEmpty()) continue;

                if ($isSemester || $isYearly) {
                    // Average the quarter sums
                    $quarterSums = $studentMarks->groupBy('term_id')->map(fn($g) => $g->sum('score'));
                    $score = $quarterSums->isNotEmpty() ? $quarterSums->average() : null;
                } else {
                    // Quarter: simple sum of components
                    $score = $studentMarks->sum('score');
                }

                if ($score !== null && $score !== '') {
                    $score = (float)$score;
                    $appeared++;
                    $scores[] = $score;
                    if ($score >= $passMark) $passed++;
                    $allStudentsData[] = ['student' => $student, 'section' => $section, 'score' => $score];
                }
            }

            if ($appeared > 0) {
                $sectionStats[$section->id] = (object)[
                    'section'   => $section,
                    'appeared'  => $appeared,
                    'passed'    => $passed,
                    'failed'    => $appeared - $passed,
                    'pass_rate' => ($passed / $appeared) * 100,
                    'highest'   => max($scores),
                    'lowest'    => min($scores),
                    'average'   => array_sum($scores) / $appeared,
                ];
            }
        }

        $allScores = collect($allStudentsData)->pluck('score');
        return [
            'academicYear'  => $academicYear,
            'term'          => $term,
            'gradeLevel'    => $gradeLevel,
            'subject'       => $subject,
            'sectionStats'  => $sectionStats,
            'overallStats'  => (object)[
                'total_appeared' => $allScores->count(),
                'total_passed'   => collect($allStudentsData)->filter(fn($d) => $d['score'] >= $passMark)->count(),
                'highest'        => $allScores->isNotEmpty() ? $allScores->max() : 0,
                'lowest'         => $allScores->isNotEmpty() ? $allScores->min() : 0,
                'average'        => $allScores->isNotEmpty() ? $allScores->average() : 0,
            ],
            'topPerformers' => collect($allStudentsData)->sortByDesc('score')->take(10),
        ];
    }

    /**
     * Prepare data for grade matrix report.
     */
    public function prepareGradeMatrixData(AcademicYear $academicYear, $term, Collection $gradeLevels): array
    {
        $termsToProcess = collect([$term]);

        // Only include subjects that are actually assigned to these grade levels
        $allSubjects = $gradeLevels->flatMap->subjects->unique('id');
        
        // Use custom Matrix order if available, otherwise fallback
        $settings = \App\Models\AcademicReportSetting::first();
        if ($settings && isset($settings->display_options['matrix_subject_order'])) {
            $orderMap = $settings->display_options['matrix_subject_order'];
            $allSubjects = $allSubjects->sort(function ($a, $b) use ($orderMap) {
                $posA = $orderMap[$a->id] ?? 999;
                $posB = $orderMap[$b->id] ?? 999;
                return $posA <=> $posB;
            })->values();
        } else {
            $allSubjects = $allSubjects->sortBy('name')->values();
        }

        $termData = [];

        foreach ($termsToProcess as $t) {
            $matrix = [];
            $gradeAverages = [];
            $tempSubData = [];

            foreach ($gradeLevels as $grade) {
                $gradeSubjectAverages = []; // [subject_id => [section_avg1, section_avg2, ...]]
                $studentScores = collect(); // To calculate grade average across all subjects

                foreach ($grade->sections as $section) {
                    $students = $section->students()
                        ->wherePivot('academic_year_id', $academicYear->id)
                        ->where('is_active', true)
                        ->get();
                    
                    if ($students->isEmpty()) continue;

                    $batchResults = $this->gradingService->calculateSectionTotals($students, $t, $academicYear, $grade->subjects);

                    foreach ($grade->subjects as $subject) {
                        $scores = collect($students)
                            ->map(fn($s) => $batchResults[$s->id]['marks'][$subject->id] ?? null)
                            ->filter(fn($s) => $s !== null && $s !== '')
                            ->map(fn($s) => (float)$s);
                        
                        if ($scores->isNotEmpty()) {
                            $sectionAvg = $scores->average();
                            $gradeSubjectAverages[$subject->id][] = $sectionAvg;

                            // Still accumulate raw sums for school-wide subject averages (footer)
                            $tempSubData[$subject->id] = $tempSubData[$subject->id] ?? ['sum' => 0, 'count' => 0];
                            $tempSubData[$subject->id]['sum'] += $scores->sum();
                            $tempSubData[$subject->id]['count'] += $scores->count();
                        }
                    }
                    
                    foreach ($students as $student) {
                        if (isset($batchResults[$student->id]['average'])) {
                            $studentScores->push($batchResults[$student->id]['average']);
                        }
                    }
                }

                $finalGradeMatrix = [];
                foreach ($gradeSubjectAverages as $subId => $averages) {
                    $finalGradeMatrix[$subId] = count($averages) > 0 ? array_sum($averages) / count($averages) : 0;
                }

                $matrix[$grade->id] = $finalGradeMatrix;
                $gradeAverages[$grade->id] = count($finalGradeMatrix) > 0 ? array_sum($finalGradeMatrix) / count($finalGradeMatrix) : 0;
            }

            $subjectAverages = [];
            foreach ($allSubjects as $subject) {
                if (isset($tempSubData[$subject->id])) {
                    $subjectAverages[$subject->id] = $tempSubData[$subject->id]['sum'] / $tempSubData[$subject->id]['count'];
                } else {
                    $subjectAverages[$subject->id] = null;
                }
            }

            $termData[$t->id] = [
                'term' => $t,
                'matrix' => $matrix,
                'gradeAverages' => $gradeAverages,
                'subjectAverages' => $subjectAverages,
                'overallAverage' => collect($subjectAverages)->filter()->avg(),
            ];
        }

        return [
            'academicYear' => $academicYear,
            'term' => $term, // Selected term
            'gradeLevels' => $gradeLevels,
            'allSubjects' => $allSubjects,
            'termData' => $termData,
            'isMultiTerm' => $termsToProcess->count() > 1
        ];
    }

    private function romanNumeral($num)
    {
        $numerals = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        return $numerals[$num] ?? $num;
    }
}
