<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\StudentTermRecord;
use App\Models\Term;
use App\Services\GradingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * One-shot maintenance command to rebuild every cached StudentTermRecord (total/average/
 * rank) using the current, fixed GradingService logic — needed after the 2026-07
 * TERM_TOTAL-precedence bug fixes, since every StudentTermRecord written by the old code
 * may be stale.
 */
class RecalculateAllGrades extends Command
{
    protected $signature = 'grades:recalculate-all
        {--dry-run : Report what would change (old vs new total/rank) without saving anything}';

    protected $description = 'Rebuild cached StudentTermRecord totals/ranks for every section and term using the current GradingService logic';

    /** @var array<int, array{label: string, error: string}> */
    private array $failures = [];

    private int $processed = 0;

    /** Dry-run only: count of section+term combinations with at least one changed student. */
    private int $changedCombinations = 0;

    public function handle(GradingService $gradingService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be saved.');
        }

        $academicYears = AcademicYear::orderBy('start_date')->get();

        if ($academicYears->isEmpty()) {
            $this->warn('No academic years found. Nothing to do.');

            return self::SUCCESS;
        }

        foreach ($academicYears as $academicYear) {
            $this->processAcademicYear($academicYear, $gradingService, $dryRun);
        }

        return $this->printSummary($dryRun);
    }

    private function processAcademicYear(AcademicYear $academicYear, GradingService $gradingService, bool $dryRun): void
    {
        $sections = Section::where('academic_year_id', $academicYear->id)->orderBy('name')->get();

        if ($sections->isEmpty()) {
            return;
        }

        $terms = $this->termsForYear($academicYear);

        $this->info("== {$academicYear->name} ({$sections->count()} section(s), {$terms->count()} term(s)) ==");

        foreach ($sections as $section) {
            foreach ($terms as $term) {
                $this->processSectionTerm($section, $term, $academicYear, $gradingService, $dryRun);
            }
        }
    }

    /**
     * Every real quarter/semester Term for the year, plus a synthetic "Yearly" term —
     * matching the same virtual term used everywhere else in GradingService/report cards.
     */
    private function termsForYear(AcademicYear $academicYear): \Illuminate\Support\Collection
    {
        $realTerms = Term::where('academic_year_id', $academicYear->id)
            ->whereIn('type', ['quarter', 'semester'])
            ->orderBy('type')
            ->orderBy('term_number')
            ->get();

        $yearlyTerm = new Term([
            'type' => 'yearly',
            'name' => 'Yearly',
            'academic_year_id' => $academicYear->id,
        ]);
        $yearlyTerm->incrementing = false;
        $yearlyTerm->id = 'yearly';

        return $realTerms->push($yearlyTerm);
    }

    private function processSectionTerm(Section $section, Term $term, AcademicYear $academicYear, GradingService $gradingService, bool $dryRun): void
    {
        $label = "{$academicYear->name} / {$section->name} / {$term->name}";
        $this->processed++;

        try {
            if ($dryRun) {
                $this->reportDryRun($gradingService, $section, $term, $academicYear, $label);

                return;
            }

            $gradingService->recalculateSectionStatistics($section, $term, $academicYear);

            // Yearly is intentionally never persisted to StudentTermRecord (GradingService
            // treats it as a virtual term computed live on every read, exactly like the
            // report card and the parent/student portals) — recalculating it here only
            // cascades a fresh recompute of its semesters/quarters, it writes nothing itself.
            $note = $term->id === 'yearly' ? ' (virtual — cascade only, nothing persisted for Yearly itself)' : '';
            $this->line("  {$label} ... done{$note}");
            Log::info("grades:recalculate-all recalculated {$label}");
        } catch (Throwable $e) {
            $this->failures[] = ['label' => $label, 'error' => $e->getMessage()];
            $this->error("  {$label} ... FAILED: {$e->getMessage()}");
            Log::error("grades:recalculate-all failed for {$label}: {$e->getMessage()}", ['exception' => $e]);
        }
    }

    /**
     * Compute what recalculateSectionStatistics WOULD write, using the same pure
     * calculateSectionTotals() + ranking logic it uses internally, and diff against the
     * currently stored StudentTermRecord — without calling the write path at all.
     */
    private function reportDryRun(GradingService $gradingService, Section $section, Term $term, AcademicYear $academicYear, string $label): void
    {
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated'])
            ->get();

        if ($students->isEmpty()) {
            $this->line("  {$label} ... no active students, skipped");

            return;
        }

        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        $results = $gradingService->calculateSectionTotals($students, $term, $academicYear, $subjects);

        // Same ranking logic as GradingService::recalculateTermStats().
        $studentTotals = [];
        foreach ($results as $sid => $data) {
            $studentTotals[$sid] = $data['total'];
        }
        $sortedTotals = collect($studentTotals)->sortDesc();
        $totalStudents = $sortedTotals->count();

        // Yearly is never persisted (see processSectionTerm), so there is no stored record
        // to diff against — is_numeric() guards that lookup to real quarter/semester terms.
        $existingRecords = is_numeric($term->id)
            ? StudentTermRecord::where('term_id', $term->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id')
            : collect();

        $changedLines = [];
        foreach ($students as $student) {
            $newTotal = round($studentTotals[$student->id] ?? 0, 2);
            $newAverage = round($results[$student->id]['average'] ?? 0, 2);
            $newRank = $sortedTotals->filter(fn ($score) => $score > $newTotal)->count() + 1;

            $old = $existingRecords->get($student->id);
            $oldTotal = $old ? round((float) $old->total_score, 2) : null;
            $oldRank = $old->rank ?? null;

            if ($oldTotal === $newTotal && $oldRank === $newRank) {
                continue;
            }

            $changedLines[] = sprintf(
                '      %s: total %s -> %s, avg -> %s, rank %s -> #%d/%d',
                $student->full_name ?? "Student #{$student->id}",
                $oldTotal === null ? 'n/a' : $oldTotal,
                $newTotal,
                $newAverage,
                $oldRank === null ? 'n/a' : "#{$oldRank}",
                $newRank,
                $totalStudents
            );
        }

        if (empty($changedLines)) {
            $this->line("  {$label} ... no change ({$students->count()} students)");

            return;
        }

        $this->changedCombinations++;
        $this->line("  {$label} ... " . count($changedLines) . " of {$students->count()} student(s) would change:");
        foreach ($changedLines as $line) {
            $this->line($line);
        }
    }

    private function printSummary(bool $dryRun): int
    {
        $this->newLine();
        $this->info("Processed {$this->processed} section+term combination(s).");

        if ($dryRun) {
            $this->info("{$this->changedCombinations} combination(s) would change.");
        }

        if (! empty($this->failures)) {
            $this->error(count($this->failures) . ' failure(s):');
            foreach ($this->failures as $failure) {
                $this->line("  - {$failure['label']}: {$failure['error']}");
            }

            return self::FAILURE;
        }

        $this->info('Done, no failures.');

        return self::SUCCESS;
    }
}
