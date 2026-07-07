<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentType;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentTermRecord;
use App\Models\Term;
use Illuminate\Support\Collection;

/**
 * Single source of truth for how a student's grade history is displayed across the
 * admin profile, the parent portal, and the student portal.
 *
 * It returns two aligned structures, both keyed by academic-year name then term name:
 *   - academicRecords: term => Collection<StudentMark>
 *       Quarters hold the student's real component marks, resolved components-first per
 *       subject (a TERM_TOTAL is an auto-synced copy of a subject's component sum, so keep
 *       live components when present and fall back to a subject's TERM_TOTAL only when it
 *       has none — this avoids the 73 + TERM_TOTAL 73 = 146 double count while keeping a
 *       TERM_TOTAL-only term from vanishing). Semesters and the yearly roll-up hold
 *       in-memory (never saved) marks carrying the per-subject score computed live by
 *       GradingService, so those terms are always fresh and appear even when no semester
 *       marks were ever written.
 *   - termRecords: term => StudentTermRecord (avg / rank / conduct / absence badge data).
 *       Quarters use the stored record; semesters and yearly use a synthetic record whose
 *       average/rank come from the same live computation as the cells.
 *
 * The Report view sums academicRecords per subject; the Assessment view renders the
 * quarter component matrix from the same marks. Both therefore agree by construction.
 */
class StudentAcademicHistoryService
{
    public function __construct(private GradingService $gradingService)
    {
    }

    /**
     * @return array{academicRecords: Collection, termRecords: Collection}
     */
    public function build(Student $student, ?int $academicYearId = null): array
    {
        $termTotalTypeId = AssessmentType::where('code', 'TERM_TOTAL')->value('id');

        $resolveTermMarks = function ($termMarks) use ($termTotalTypeId) {
            return $termMarks
                ->groupBy('subject_id')
                ->flatMap(function ($subjectMarks) use ($termTotalTypeId) {
                    $components = $subjectMarks->reject(function ($mark) use ($termTotalTypeId) {
                        return $termTotalTypeId
                            && $mark->assessmentTemplate
                            && $mark->assessmentTemplate->assessment_type_id == $termTotalTypeId;
                    });

                    return $components->isNotEmpty() ? $components : $subjectMarks;
                })
                ->values();
        };

        $studentMarks = $student->marks()
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->with(['subject', 'assessmentTemplate', 'term', 'academicYear'])
            ->get();

        // Quarter rows straight from stored marks, ordered Quarter 1..4 within each year.
        $academicRecords = $studentMarks
            ->filter(fn ($mark) => optional($mark->term)->type === 'quarter')
            ->groupBy(fn ($mark) => $mark->academicYear->name)
            ->map(function ($yearMarks) use ($resolveTermMarks) {
                return $yearMarks
                    ->groupBy(fn ($mark) => $mark->term->name)
                    ->map($resolveTermMarks)
                    ->sortBy(fn ($marks) => optional($marks->first()->term)->term_number ?? 99);
            });

        // Stored term summaries (avg / rank / conduct / absence) for the badge chips.
        $termRecords = StudentTermRecord::where('student_id', $student->id)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->with(['term', 'academicYear'])
            ->get()
            ->groupBy(fn ($record) => $record->academicYear->name)
            ->map(fn ($yearRecords) => $yearRecords->keyBy(fn ($record) => $record->term->name));

        // Inject computed Semester + Yearly rows (cells + badge) per academic year the
        // student has marks in — sourced from GradingService, never from stored marks.
        foreach ($studentMarks->pluck('academic_year_id')->unique()->filter() as $yearId) {
            $ay = AcademicYear::find($yearId);
            if (! $ay) {
                continue;
            }

            $computedTerms = collect();
            $computedRecords = collect();

            $semesters = Term::where('academic_year_id', $yearId)
                ->where('type', 'semester')
                ->orderBy('term_number')
                ->get();

            foreach ($semesters as $sem) {
                $report = $this->gradingService->getStudentReportData($student, $sem, $ay);
                if (empty($report)) {
                    continue;
                }
                [$marks, $record] = $this->buildComputedRow($report, $sem, $ay);
                if ($marks->isNotEmpty()) {
                    $computedTerms->put($sem->name, $marks);
                    $computedRecords->put($sem->name, $record);
                }
            }

            $yearlyTerm = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $yearId]);
            $yearlyTerm->incrementing = false;
            $yearlyTerm->id = 'yearly';

            $yearlyReport = $this->gradingService->getStudentReportData($student, $yearlyTerm, $ay);
            if (! empty($yearlyReport)) {
                [$marks, $record] = $this->buildComputedRow($yearlyReport, $yearlyTerm, $ay);
                if ($marks->isNotEmpty()) {
                    $computedTerms->put('Yearly', $marks);
                    $computedRecords->put('Yearly', $record);
                }
            }

            if ($computedTerms->isNotEmpty()) {
                // Quarters first (already ordered), then Semester 1, Semester 2, Yearly.
                // Wrap in collect() to force a base-collection (array_merge) merge that keeps
                // the term-name keys — an Eloquent Collection::merge() would re-key by model
                // primary key and lose them.
                $academicRecords->put($ay->name, collect($academicRecords->get($ay->name, collect()))->merge($computedTerms));
                $termRecords->put($ay->name, collect($termRecords->get($ay->name, collect()))->merge($computedRecords));
            }
        }

        return [
            'academicRecords' => $academicRecords,
            'termRecords' => $termRecords,
        ];
    }

    /**
     * Turn one GradingService report (semester or yearly) into an in-memory marks
     * collection + a synthetic summary record, so the same views render it unchanged.
     *
     * @return array{0: Collection, 1: StudentTermRecord}
     */
    private function buildComputedRow(array $report, Term $term, AcademicYear $ay): array
    {
        $subjectsById = collect($report['subjects'] ?? [])->keyBy('id');
        $marks = collect();

        foreach (($report['marks'] ?? []) as $subjectId => $score) {
            if ($score === null || $score === '') {
                continue;
            }
            $subject = $subjectsById->get($subjectId);
            if (! $subject) {
                continue;
            }
            $synthetic = new StudentMark();
            $synthetic->subject_id = $subjectId;
            $synthetic->score = $score;
            $synthetic->setRelation('subject', $subject);
            $marks->push($synthetic);
        }

        $record = new StudentTermRecord();
        $record->term_id = $term->id;
        $record->academic_year_id = $ay->id;
        $record->average_score = $report['average'] ?? null;
        $record->rank = is_numeric($report['rank'] ?? null) ? $report['rank'] : null;
        $record->rank_out_of = $report['rank_out_of'] ?? null;

        return [$marks, $record];
    }
}
