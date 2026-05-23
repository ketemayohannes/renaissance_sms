<?php

namespace App\Services;

use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentTermRecord;
use App\Models\ExportRequest;
use App\Jobs\GenerateSectionReportCards;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ReportCardService
{
    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Update report card settings.
     */
    public function updateSettings(array $data, ?UploadedFile $logo = null): ReportCardSetting
    {
        return DB::transaction(function () use ($data, $logo) {
            $settings = ReportCardSetting::firstOrNew();
            
            if ($logo) {
                if ($settings->logo_path) {
                    Storage::disk('public')->delete($settings->logo_path);
                }
                $settings->logo_path = $logo->store('report-cards', 'public');
            }

            $settings->fill([
                'school_name' => $data['school_name'],
                'email' => $data['email'] ?? $settings->email,
                'website' => $data['website'] ?? $settings->website,
                'telephone' => $data['telephone'] ?? $settings->telephone,
                'po_box' => $data['po_box'] ?? $settings->po_box,
            ]);

            $settings->template_config = [
                'show_rank' => isset($data['show_rank']),
                'show_conduct' => isset($data['show_conduct']),
                'show_attendance' => isset($data['show_attendance']),
                'traits' => $data['traits'] ?? [],
            ];

            $settings->save();
            return $settings;
        });
    }

    /**
     * Update yearly report card settings.
     */
    public function updateYearlySettings(array $data): ReportCardSetting
    {
        $settings = ReportCardSetting::firstOrNew();
        $config = $settings->yearly_config ?? [];
        
        $config['evaluation_method'] = $data['evaluation_method'] ?? null;
        $config['remark'] = $data['remark'] ?? null;
        $config['principal_name'] = $data['principal_name'] ?? null;
        $config['parent_instructions'] = $data['parent_instructions'] ?? null;
        
        $settings->yearly_config = $config;
        $settings->save();
        
        return $settings;
    }

    /**
     * Store bulk data entry for students.
     */
    public function storeDataEntry(int $termId, int $academicYearId, array $records, Section $section = null): void
    {
        DB::transaction(function () use ($termId, $academicYearId, $records) {
            foreach ($records as $studentId => $data) {
                StudentTermRecord::updateOrCreate(
                    [
                        'student_id' => $studentId, 
                        'term_id' => $termId,
                    ],
                    [
                        'academic_year_id' => $academicYearId,
                        'conduct_grade' => $data['conduct'] ?? null,
                        'days_absent' => $data['absent'] ?? 0,
                        'homeroom_teacher_comment' => $data['comment'] ?? null,
                    ]
                );
            }
        });

        // Clear roster cache for this section and term
        if ($section) {
            \Illuminate\Support\Facades\Cache::forget("roster_data_{$section->id}_{$termId}_{$academicYearId}");
        }
    }

    /**
     * Prepare full report card data for a single student.
     */
    public function getStudentReportParams(Student $student, $term, AcademicYear $academicYear): array
    {
        $reportData = $this->gradingService->getStudentReportData($student, $term, $academicYear);
        $settings = ReportCardSetting::first() ?? new ReportCardSetting();

        $isSemester = $reportData['isSemester'];
        $isYearly = $term->type === 'yearly';

        $quarters = collect();
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
        } elseif ($isYearly) {
            $quarters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number')
                ->get();
        }
        $semesters = $isYearly ? Term::where('academic_year_id', $academicYear->id)->where('type', 'semester')->orderBy('start_date')->get() : collect();

        $totalStudents = $reportData['rank_out_of'];
        if (!$totalStudents || $totalStudents === '-' || $totalStudents === 0) {
            $totalStudents = $reportData['section'] ? $reportData['section']->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->whereIn('student_enrollments.status', ['active', 'completed'])
                ->count() : 0;
        }

        $preparedQuarters = $this->prepareSubTermData($reportData['quarters'] ?? [], $totalStudents);
        $preparedSemesters = $this->prepareSubTermData($reportData['semesters'] ?? [], $totalStudents);

        // Filter and prepare subjects
        $subjects = $reportData['subjects']->filter(function($subject) use ($reportData, $preparedQuarters, $preparedSemesters, $isSemester, $isYearly) {
            if ($isSemester) return isset($preparedQuarters['marks'][$subject->id]);
            if ($isYearly) return isset($preparedSemesters['marks'][$subject->id]);
            return isset($reportData['marks'][$subject->id]);
        });

        $attendance = $this->getAttendanceSummary($student, $term, $academicYear);
        $subTermAttendance = $this->calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear);

        return [
            'student' => $student,
            'term' => $term,
            'academicYear' => $academicYear,
            'subjects' => $subjects,
            'marks' => $reportData['marks'],
            'termRecord' => $reportData['record'],
            'settings' => $settings,
            'totalScore' => $reportData['totalScore'],
            'average' => $reportData['average'],
            'section' => $reportData['section'],
            'rank' => $reportData['rank'],
            'totalStudents' => $totalStudents,
            'isSemester' => $isSemester,
            'isYearly' => $isYearly,
            'quarters' => $quarters,
            'semesters' => $semesters,
            'quarterMarks' => $preparedQuarters['marks'],
            'quarterTotals' => $preparedQuarters['totals'],
            'quarterAverages' => $preparedQuarters['averages'],
            'quarterRanks' => $preparedQuarters['ranks'],
            'quarterRecords' => $preparedQuarters['records'],
            'semesterMarks' => $preparedSemesters['marks'],
            'semesterTotals' => $preparedSemesters['totals'],
            'semesterAverages' => $preparedSemesters['averages'],
            'semesterRanks' => $preparedSemesters['ranks'],
            'attendance' => $attendance,
            'subTermAttendance' => $subTermAttendance,
        ];
    }

    /**
     * Prepare data for bulk printing.
     */
    public function getSectionReportParams(Section $section, $term, AcademicYear $academicYear): array
    {
        $this->gradingService->recalculateSectionStatistics($section, $term, $academicYear);
        
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->whereIn('student_enrollments.status', ['active', 'completed'])
            ->orderBy('first_name')
            ->get();

        $sectionReportData = $this->gradingService->getSectionReportData($students, $section, $term, $academicYear);
        $settings = ReportCardSetting::first() ?? new ReportCardSetting();

        $isSemester = $term->isSemester();
        $isYearly = $term->type === 'yearly';

        $quarters = collect();
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
        } elseif ($isYearly) {
            $quarters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number')
                ->get();
        }
        $semesters = $isYearly ? Term::where('academic_year_id', $academicYear->id)->where('type', 'semester')->orderBy('start_date')->get() : collect();

        // Batch fetch attendance for all students and relevant terms (current term + sub-terms)
        $targetTermsForAttendance = collect([$term])->concat($quarters)->concat($semesters)->unique('id');
        $batchAttendance = $this->getBatchAttendanceSummary($students, $targetTermsForAttendance, $academicYear);

        $reportCards = [];
        foreach ($students as $student) {
            $reportData = $sectionReportData[$student->id] ?? null;
            if (!$reportData) continue;

            $totalStudents = $reportData['rank_out_of'];
            if (!$totalStudents || $totalStudents === '-' || $totalStudents === 0) {
                $totalStudents = $students->count();
            }

            $preparedQuarters = $this->prepareSubTermData($reportData['quarters'] ?? [], $totalStudents);
            $preparedSemesters = $this->prepareSubTermData($reportData['semesters'] ?? [], $totalStudents);

            $studentSubjects = $reportData['subjects']->filter(function($subject) use ($reportData, $preparedQuarters, $preparedSemesters, $isSemester, $isYearly) {
                if (!$subject->is_elective) return true;
                if (isset($reportData['marks'][$subject->id])) return true;
                if ($isSemester && isset($preparedQuarters['marks'][$subject->id])) return !empty($preparedQuarters['marks'][$subject->id]);
                if ($isYearly && isset($preparedSemesters['marks'][$subject->id])) return !empty($preparedSemesters['marks'][$subject->id]);
                return false;
            });

            $reportCards[] = [
                'student' => $student,
                'marks' => $reportData['marks'],
                'termRecord' => $reportData['record'],
                'totalScore' => $reportData['totalScore'],
                'average' => $reportData['average'],
                'rank' => $reportData['rank'],
                'studentSubjects' => $studentSubjects,
                'studentQuarterMarks' => $preparedQuarters['marks'],
                'studentQuarterStats' => collect($preparedQuarters['totals'])->map(fn($t, $id) => [
                    'total' => $t,
                    'avg' => $preparedQuarters['averages'][$id],
                    'rank' => $preparedQuarters['ranks'][$id]
                ]),
                'studentQuarterRecords' => collect($preparedQuarters['records']),
                'studentSemesterMarks' => $preparedSemesters['marks'],
                'studentSemesterStats' => collect($preparedSemesters['totals'])->map(fn($t, $id) => [
                    'total' => $t,
                    'avg' => $preparedSemesters['averages'][$id],
                    'rank' => $preparedSemesters['ranks'][$id]
                ]),
                'attendance' => $batchAttendance[$student->id][$term->id] ?? $this->getAttendanceSummary($student, $term, $academicYear),
                'subTermAttendance' => $this->calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear, $batchAttendance),
            ];
        }

        return [
            'section' => $section,
            'term' => $term,
            'academicYear' => $academicYear,
            'subjects' => $section->gradeLevel->subjects()->orderByPivot('sort_order')->get(),
            'reportCards' => $reportCards,
            'settings' => $settings,
            'totalStudents' => $students->count(),
            'isSemester' => $isSemester,
            'isYearly' => $isYearly,
            'quarters' => $quarters,
            'semesters' => $semesters,
        ];
    }

    /**
     * Initiate bulk export.
     */
    public function initiateBulkExport(int $sectionId, string $termId, int $academicYearId, int $userId): ExportRequest
    {
        $section = Section::findOrFail($sectionId);
        $academicYear = AcademicYear::findOrFail($academicYearId);
        
        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'name' => 'Yearly Report', 'academic_year_id' => $academicYearId]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        $exportRequest = ExportRequest::create([
            'user_id' => $userId,
            'type' => 'section_report_cards_zip',
            'status' => 'pending',
            'params' => [
                'section_id' => $sectionId,
                'term_id' => $termId,
                'academic_year_id' => $academicYearId,
                'section_name' => $section->name,
                'term_name' => $term->name,
            ],
        ]);

        GenerateSectionReportCards::dispatch($exportRequest, $section, $term, $academicYear);

        return $exportRequest;
    }

    private function prepareSubTermData(array $subTerms, $totalStudents): array
    {
        $data = ['marks' => [], 'totals' => [], 'averages' => [], 'ranks' => [], 'records' => []];

        foreach ($subTerms as $id => $subTermData) {
            foreach ($subTermData['marks'] as $subId => $score) {
                $data['marks'][$subId][$id] = $score;
            }
            $data['totals'][$id] = $subTermData['total'];
            $data['averages'][$id] = $subTermData['average'];
            $data['ranks'][$id] = ($subTermData['rank'] && $subTermData['rank'] !== '-') ? ($subTermData['rank'] . " / " . $totalStudents) : '-';
            $data['records'][$id] = $subTermData['record'] ?? null;
        }

        return $data;
    }

    public function getBatchAttendanceSummary(iterable $students, iterable $terms, AcademicYear $academicYear): array
    {
        $studentIds = $students->pluck('id')->toArray();
        $termIds = collect($terms)->pluck('id')->filter(fn($id) => $id !== 'yearly')->toArray();
        $termObjects = collect($terms)->keyBy('id');

        $query = \App\Models\StudentAttendance::whereIn('student_id', $studentIds)
            ->whereHas('section', fn($q) => $q->where('academic_year_id', $academicYear->id));

        // If we only have specific terms, filter by their dates
        // This is complex because each term has its own dates. 
        // We'll fetch all attendance for the year and filter in PHP for simplicity and correctness 
        // unless the dataset is huge. Usually, attendance for one year is manageable.
        
        $allAttendance = $query->get(['student_id', 'attendance_date', 'status']);
        
        // Fetch all relevant StudentTermRecords for manual overrides
        $allRecords = StudentTermRecord::whereIn('student_id', $studentIds)
            ->whereIn('term_id', collect($terms)->pluck('id'))
            ->get(['student_id', 'term_id', 'days_absent'])
            ->groupBy('student_id');

        $results = [];
        foreach ($students as $student) {
            $studentAttendance = $allAttendance->where('student_id', $student->id);
            $studentRecords = $allRecords->get($student->id, collect())->keyBy('term_id');
            
            foreach ($terms as $term) {
                $termAttendance = $studentAttendance;
                if ($term->type !== 'yearly' && $term->id !== 'yearly') {
                    $termAttendance = $studentAttendance->whereBetween('attendance_date', [$term->start_date, $term->end_date]);
                }

                $stats = $termAttendance->groupBy('status')->map->count();
                
                $present = $stats['present'] ?? 0;
                $automatedAbsent = $stats['absent'] ?? 0;
                $late = $stats['late'] ?? 0;
                $excused = $stats['excused'] ?? 0;

                // Manual Override
                $record = $studentRecords->get($term->id);
                $absent = ($record && $record->days_absent !== null) ? $record->days_absent : $automatedAbsent;

                $results[$student->id][$term->id] = [
                    'total_days' => $present + $absent + $late + $excused,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused
                ];
            }
        }

        return $results;
    }

    public function getAttendanceSummary(Student $student, $term, AcademicYear $academicYear): array
    {
        $query = \App\Models\StudentAttendance::where('student_id', $student->id)
            ->whereHas('section', fn($q) => $q->where('academic_year_id', $academicYear->id));

        if ($term->type !== 'yearly' && $term->id !== 'yearly') {
            $query->whereBetween('attendance_date', [$term->start_date, $term->end_date]);
        }

        $stats = $query->selectRaw('count(*) as total, status')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $present = $stats['present'] ?? 0;
        $automatedAbsent = $stats['absent'] ?? 0;
        $late = $stats['late'] ?? 0;
        $excused = $stats['excused'] ?? 0;

        // Manual Override from StudentTermRecord
        $record = StudentTermRecord::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->first();
            
        $absent = ($record && $record->days_absent !== null) ? $record->days_absent : $automatedAbsent;

        return [
            'total_days' => $present + $absent + $late + $excused,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused
        ];
    }

    public function calculateSubTermAttendance(Student $student, $quarters, $semesters, bool $isSemester, bool $isYearly, AcademicYear $academicYear, array $batchAttendance = []): array
    {
        $subTermAttendance = [];
        
        if (($isSemester || $isYearly) && $quarters) {
            foreach ($quarters as $q) {
                $subTermAttendance[$q->id] = $batchAttendance[$student->id][$q->id] ?? $this->getAttendanceSummary($student, $q, $academicYear);
            }
        }
        
        if ($isYearly && $semesters) {
             foreach ($semesters as $s) {
                 $subTermAttendance[$s->id] = $batchAttendance[$student->id][$s->id] ?? $this->getAttendanceSummary($student, $s, $academicYear);
             }
        }
        
        return $subTermAttendance;
    }
}
