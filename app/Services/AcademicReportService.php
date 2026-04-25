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
        // PERFORMANCE FIX: Cache roster data for 5 minutes
        $cacheKey = "roster_data_{$section->id}_{$term->id}_{$academicYear->id}";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($section, $term, $academicYear) {
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

            return [
                'academicYear' => $academicYear,
                'term' => $term,
                'section' => $section,
                'subjects' => $subjects,
                'reports' => $reports,
                'settings' => $settings,
                'generalSettings' => \App\Models\ReportCardSetting::first(),
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
        $yearlyTotals = [];
        $yearlyCounts = [];

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

        $yearTotal = $reportData['totalScore'] ?? 0;
        $rows['avg'] = $this->formatRosterRow(
            'Year Avg',
            $reportData['marks'],
            $yearTotal,
            $reportData['average'] ?? 0,
            $reportData['record'],
            $reportData['rank'] ?? '-',
            $studentAttendance['yearly']['absent'] ?? ($studentAttendance['all_year']['absent'] ?? null)
        );

        return [
            'student' => $student,
            'gender' => $student->gender,
            'rows' => $rows,
            'yearTotal' => $yearTotal,
            'absence' => $studentAttendance['all_year']['absent'] ?? '_'
        ];
    }

    private function formatRosterRow($label, $marks, $total, $average, $record, $rank, $dynamicAbsence = null): array
    {
        $absenceVal = ($record && $record->days_absent) ? $record->days_absent : (is_numeric($dynamicAbsence) ? $dynamicAbsence : 0);

        return [
            'label' => $label,
            'marks' => $marks,
            'total' => $total,
            'average' => $average,
            'conduct' => ($record ? $record->conduct_grade : null) ?? ($label === 'Sem Avg' || $label === 'Year Avg' ? '-' : 'A'),
            'absence' => ($absenceVal == 0) ? '_' : $absenceVal,
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
            if (!$subject->is_elective) return true;
            foreach ($reports as $r) {
                foreach ($r['rows'] as $row) {
                    if (isset($row['marks'][$subject->id])) return true;
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
        $passMark = 50;
        $sectionStats = [];
        $allStudentsData = [];

        foreach ($gradeLevel->sections as $section) {
            $students = $section->students()->wherePivot('academic_year_id', $academicYear->id)->wherePivot('status', 'active')->get();
            if ($students->isEmpty()) continue;

            $batchResults = $this->gradingService->calculateSectionTotals($students, $term, $academicYear, collect([$subject]));
            
            $scores = [];
            $passed = 0;
            $appeared = 0;

            foreach ($students as $student) {
                $score = $batchResults[$student->id]['marks'][$subject->id] ?? null;
                if ($score !== null && $score !== '') {
                    $appeared++;
                    $scores[] = (float)$score;
                    if ($score >= $passMark) $passed++;
                    $allStudentsData[] = ['student' => $student, 'section' => $section, 'score' => $score];
                }
            }

            if ($appeared > 0) {
                $sectionStats[$section->id] = (object)[
                    'section' => $section,
                    'appeared' => $appeared,
                    'passed' => $passed,
                    'failed' => $appeared - $passed,
                    'pass_rate' => $appeared > 0 ? ($passed / $appeared) * 100 : 0,
                    'highest' => !empty($scores) ? max($scores) : 0,
                    'lowest' => !empty($scores) ? min($scores) : 0,
                    'average' => $appeared > 0 ? array_sum($scores) / $appeared : 0,
                ];
            }
        }

        $allScores = collect($allStudentsData)->pluck('score');
        return [
            'academicYear' => $academicYear,
            'term' => $term,
            'gradeLevel' => $gradeLevel,
            'subject' => $subject,
            'sectionStats' => $sectionStats,
            'overallStats' => (object)[
                'total_appeared' => $allScores->count(),
                'total_passed' => collect($allStudentsData)->filter(fn($d) => $d['score'] >= $passMark)->count(),
                'highest' => $allScores->isNotEmpty() ? $allScores->max() : 0,
                'lowest' => $allScores->isNotEmpty() ? $allScores->min() : 0,
                'average' => $allScores->isNotEmpty() ? $allScores->average() : 0,
            ],
            'topPerformers' => collect($allStudentsData)->sortByDesc('score')->take(10),
        ];
    }

    /**
     * Prepare data for grade matrix report.
     */
    public function prepareGradeMatrixData(AcademicYear $academicYear, $term, Collection $gradeLevels): array
    {
        $allSubjects = $gradeLevels->flatMap->subjects->unique('id')->sortBy('name');
        $matrix = [];
        $gradeAverages = [];

        foreach ($gradeLevels as $grade) {
            $gradeMatrix = [];
            $tempSubData = [];

            foreach ($grade->sections as $section) {
                $students = $section->students()->wherePivot('academic_year_id', $academicYear->id)->wherePivot('status', 'active')->get();
                if ($students->isEmpty()) continue;

                $batchResults = $this->gradingService->calculateSectionTotals($students, $term, $academicYear, $grade->subjects);

                foreach ($grade->subjects as $subject) {
                    $scores = collect($students)->map(fn($s) => $batchResults[$s->id]['marks'][$subject->id] ?? null)->filter(fn($s) => $s !== null && $s !== '')->map(fn($s) => (float)$s);
                    if ($scores->isNotEmpty()) {
                        $tempSubData[$subject->id] = $tempSubData[$subject->id] ?? ['sum' => 0, 'count' => 0];
                        $tempSubData[$subject->id]['sum'] += $scores->sum();
                        $tempSubData[$subject->id]['count'] += $scores->count();
                    }
                }
            }

            $totalGradeSum = 0;
            $totalSubCount = 0;
            foreach ($tempSubData as $subId => $data) {
                $avg = $data['count'] > 0 ? $data['sum'] / $data['count'] : 0;
                $gradeMatrix[$subId] = $avg;
                $totalGradeSum += $avg;
                $totalSubCount++;
            }
            $matrix[$grade->id] = $gradeMatrix;
            $gradeAverages[$grade->id] = $totalSubCount > 0 ? $totalGradeSum / $totalSubCount : 0;
        }

        $subjectAverages = [];
        foreach ($allSubjects as $subject) {
            $vals = collect($gradeLevels)->map(fn($g) => $matrix[$g->id][$subject->id] ?? null)->filter();
            $subjectAverages[$subject->id] = $vals->isNotEmpty() ? $vals->average() : null;
        }

        return [
            'academicYear' => $academicYear,
            'term' => $term,
            'gradeLevels' => $gradeLevels,
            'allSubjects' => $allSubjects,
            'matrix' => $matrix,
            'gradeAverages' => $gradeAverages,
            'subjectAverages' => $subjectAverages,
            'overallAverage' => collect($gradeAverages)->filter()->avg(),
        ];
    }

    private function romanNumeral($num)
    {
        $numerals = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        return $numerals[$num] ?? $num;
    }
}
