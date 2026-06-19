<?php

namespace App\Services;

use App\Models\Term;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GradeCacheService
 *
 * Centralises all grade-related caching: read-through helpers and targeted
 * invalidation. Both the `roster_data_*` keys used by AcademicReportService
 * and the `section_subjects_*` key used by GradingService are managed here
 * so nothing falls through the cracks.
 *
 * TTL strategy
 * ─────────────
 *  • Roster / section-report data  → 5 minutes  (frequent reads, stale risk low)
 *  • Subject lists                  → 60 minutes (almost never changes mid-day)
 *
 * Cache-busting entry points
 * ───────────────────────────
 *  • clearSectionCache()  — called whenever ANY mark or enrollment in a section changes
 *  • clearStudentCache()  — future-use; individual student invalidation
 */
class GradeCacheService
{
    /** TTL in seconds for roster / section-report data. */
    public const ROSTER_TTL = 300;        // 5 min

    /** TTL in seconds for subject-list caches. */
    public const SUBJECTS_TTL = 3600;     // 60 min

    // ──────────────────────────────────────────────────────────────────────────
    // Key builders
    // ──────────────────────────────────────────────────────────────────────────

    public static function rosterKey(int $sectionId, $termId, int $academicYearId): string
    {
        return "roster_data_{$sectionId}_{$termId}_{$academicYearId}";
    }

    public static function subjectsKey(int $gradeLevelId): string
    {
        return "section_subjects_{$gradeLevelId}";
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Read-through helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Cache or compute the grade-level subject list.
     */
    public function getSubjects(int $gradeLevelId, callable $callback)
    {
        return Cache::remember(
            self::subjectsKey($gradeLevelId),
            self::SUBJECTS_TTL,
            $callback
        );
    }

    /**
     * Cache or compute the full roster data for a section + term.
     */
    public function getRosterData(int $sectionId, $termId, int $academicYearId, callable $callback)
    {
        return Cache::remember(
            self::rosterKey($sectionId, $termId, $academicYearId),
            self::ROSTER_TTL,
            $callback
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cache invalidation
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Bust all cached data tied to a section for a given academic year.
     *
     * Clears:
     *  • roster_data for every real term + the virtual 'yearly' pseudo-term
     *  • section_subjects for the section's grade level
     *  • The parent-semester cache when a quarter changes (via AcademicReportService)
     */
    public function clearSectionCache(int $sectionId, int $academicYearId): void
    {
        try {
            // Fetch all real term IDs for this academic year (quarters + semesters)
            $termIds = Term::where('academic_year_id', $academicYearId)
                ->pluck('id')
                ->toArray();

            // Always include the virtual yearly key
            $termIds[] = 'yearly';

            foreach ($termIds as $termId) {
                Cache::forget(self::rosterKey($sectionId, $termId, $academicYearId));
            }

            // Clear the subject list for the section's grade level
            $gradeLevelId = DB::table('sections')
                ->where('id', $sectionId)
                ->value('grade_level_id');

            if ($gradeLevelId) {
                Cache::forget(self::subjectsKey($gradeLevelId));
            }
        } catch (\Throwable $e) {
            // Cache failures must never break the write path
            Log::warning("GradeCacheService: failed to clear section cache for section {$sectionId}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bust cached data for a single student across all terms.
     * Resolves the student's current section and delegates to clearSectionCache().
     */
    public function clearStudentCache(int $studentId, int $academicYearId): void
    {
        try {
            $sectionId = DB::table('student_enrollments')
                ->where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->value('section_id');

            if ($sectionId) {
                $this->clearSectionCache((int) $sectionId, $academicYearId);
            }
        } catch (\Throwable $e) {
            Log::warning("GradeCacheService: failed to clear student cache for student {$studentId}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
