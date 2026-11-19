<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Support\EthiopianCalendar;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for academic scheduling.
 *
 * Every module that needs "which year / semester / quarter / week is this date in",
 * "is school open", "when are the exams" or "how many school days" asks this service.
 * It reads the official calendar (academic_weeks, academic_events,
 * school_day_overrides) on top of the existing academic_years and terms tables.
 *
 * Results are computed in memory from a cached per-year snapshot; any change to a
 * calendar record calls flushCache(). Nothing here writes to grades, attendance or
 * other academic records.
 */
class AcademicCalendarService
{
    private const CACHE_VERSION_KEY = 'academic_calendar:version';

    /** @var array<string, mixed> in-process memo of cached values, keyed by versioned cache key */
    private array $memo = [];

    private ?int $version = null;

    private float $versionCheckedAt = 0;

    // ─── Cache ──────────────────────────────────────────────────────────────

    /** Invalidate every cached calendar computation (called on any calendar change). */
    public static function flushCache(): void
    {
        Cache::forever(self::CACHE_VERSION_KEY, (int) Cache::get(self::CACHE_VERSION_KEY, 0) + 1);

        if (app()->resolved(self::class)) {
            app(self::class)->resetMemo();
        }
    }

    public function resetMemo(): void
    {
        $this->memo = [];
        $this->version = null;
    }

    private function remember(string $key, \Closure $callback): mixed
    {
        // Re-read the shared version at most every few seconds, so long-running
        // processes (queue workers) still pick up edits made elsewhere.
        if ($this->version === null || microtime(true) - $this->versionCheckedAt > 5) {
            $version = (int) Cache::get(self::CACHE_VERSION_KEY, 0);
            if ($version !== $this->version) {
                $this->memo = [];
            }
            $this->version = $version;
            $this->versionCheckedAt = microtime(true);
        }

        $fullKey = "academic_calendar:v{$this->version}:{$key}";

        if (! array_key_exists($fullKey, $this->memo)) {
            $this->memo[$fullKey] = Cache::remember($fullKey, config('academic_calendar.cache_ttl', 3600), $callback);
        }

        return $this->memo[$fullKey];
    }

    // ─── Academic years ─────────────────────────────────────────────────────

    public function getActiveAcademicYear(): ?AcademicYear
    {
        return AcademicYear::active()->first();
    }

    /** The academic year a date belongs to, or null when the date is outside every year. */
    public function getAcademicYearForDate(CarbonInterface|string|null $date = null): ?AcademicYear
    {
        $id = $this->yearIdForDate($date);

        return $id ? AcademicYear::find($id) : null;
    }

    private function years(): array
    {
        return $this->remember('years', function () {
            $weekRanges = DB::table('academic_weeks')
                ->select('academic_year_id', DB::raw('MIN(start_date) as first_day'), DB::raw('MAX(end_date) as last_day'))
                ->groupBy('academic_year_id')->get()->keyBy('academic_year_id');

            return AcademicYear::orderBy('start_date')->get()->map(fn (AcademicYear $y) => [
                'id' => $y->id,
                'name' => $y->name,
                'start' => $y->start_date?->toDateString(),
                'end' => $y->end_date?->toDateString(),
                'active' => $y->is_active,
                'weeks_start' => isset($weekRanges[$y->id]) ? Carbon::parse($weekRanges[$y->id]->first_day)->toDateString() : null,
                'weeks_end' => isset($weekRanges[$y->id]) ? Carbon::parse($weekRanges[$y->id]->last_day)->toDateString() : null,
            ])->all();
        });
    }

    private function yearIdForDate(CarbonInterface|string|null $date): ?int
    {
        $d = EthiopianCalendar::date($date)->toDateString();

        $matches = array_filter($this->years(), fn ($y) => ($y['start'] && $y['start'] <= $d && $y['end'] >= $d)
            || ($y['weeks_start'] && $y['weeks_start'] <= $d && $y['weeks_end'] >= $d));

        if (! $matches) {
            return null;
        }

        // Overlapping year definitions: prefer the active one, then the latest start.
        usort($matches, fn ($a, $b) => [$b['active'], $b['start']] <=> [$a['active'], $a['start']]);

        return $matches[0]['id'];
    }

    // ─── Snapshot ───────────────────────────────────────────────────────────

    /**
     * Everything needed to answer date questions for one year, as plain arrays.
     */
    public function snapshot(int $academicYearId): ?array
    {
        return $this->remember("year:{$academicYearId}", function () use ($academicYearId) {
            $year = AcademicYear::find($academicYearId);
            if (! $year) {
                return null;
            }

            $terms = Term::where('academic_year_id', $academicYearId)->orderBy('start_date')->get()
                ->map(fn (Term $t) => [
                    'id' => $t->id, 'name' => $t->name, 'type' => $t->type, 'term_number' => (int) $t->term_number,
                    'parent_term_id' => $t->parent_term_id,
                    'start' => $t->start_date?->toDateString(), 'end' => $t->end_date?->toDateString(),
                ])->all();

            $weeks = DB::table('academic_weeks')->where('academic_year_id', $academicYearId)->orderBy('sequence')->get()
                ->map(fn ($w) => [
                    'id' => $w->id, 'term_id' => $w->term_id, 'week_number' => (int) $w->week_number, 'sequence' => (int) $w->sequence,
                    'start' => Carbon::parse($w->start_date)->toDateString(), 'end' => Carbon::parse($w->end_date)->toDateString(),
                    'working_days' => $w->working_days, 'week_type' => $w->week_type, 'description' => $w->description,
                    'source_label' => $w->source_label, 'needs_review' => (bool) $w->needs_review,
                ])->all();

            $events = DB::table('academic_events')->where('academic_year_id', $academicYearId)->orderBy('start_date')->orderByDesc('priority')->get()
                ->map(fn ($e) => [
                    'id' => $e->id, 'term_id' => $e->term_id, 'academic_week_id' => $e->academic_week_id,
                    'category' => $e->category, 'title' => $e->title, 'description' => $e->description,
                    'start' => Carbon::parse($e->start_date)->toDateString(), 'end' => Carbon::parse($e->end_date)->toDateString(),
                    'is_instructional' => (bool) $e->is_instructional, 'priority' => (int) $e->priority,
                    'status' => $e->status, 'source' => $e->source, 'needs_review' => (bool) $e->needs_review,
                ])->all();

            $overrides = DB::table('school_day_overrides')->where('academic_year_id', $academicYearId)->get()
                ->mapWithKeys(fn ($o) => [Carbon::parse($o->date)->toDateString() => ['is_school_day' => (bool) $o->is_school_day, 'reason' => $o->reason]])
                ->all();

            return [
                'year' => [
                    'id' => $year->id, 'name' => $year->name, 'ethiopian_year' => $year->ethiopian_year,
                    'start' => $year->start_date?->toDateString(), 'end' => $year->end_date?->toDateString(),
                    'is_active' => $year->is_active, 'working_weekdays' => $year->workingWeekdays(),
                    'label' => $year->calendar_label,
                ],
                'terms' => $terms,
                'weeks' => $weeks,
                'events' => $events,
                'overrides' => $overrides,
            ];
        });
    }

    private function snapshotForDate(CarbonInterface|string|null $date): ?array
    {
        $id = $this->yearIdForDate($date);

        return $id ? $this->snapshot($id) : null;
    }

    private function resolveYearId(?int $academicYearId, CarbonInterface|string|null $date = null): ?int
    {
        return $academicYearId ?? $this->yearIdForDate($date) ?? $this->getActiveAcademicYear()?->id;
    }

    // ─── Terms (semesters / quarters) ───────────────────────────────────────

    /** Quarter containing the date (by the quarter's own start/end dates), or null. */
    public function getCurrentQuarter(CarbonInterface|string|null $date = null, ?int $academicYearId = null): ?Term
    {
        $term = $this->termRowForDate('quarter', $date, $academicYearId);

        return $term ? Term::find($term['id']) : null;
    }

    /** Semester containing the date, or null (e.g. during the semester break). */
    public function getCurrentSemester(CarbonInterface|string|null $date = null, ?int $academicYearId = null): ?Term
    {
        $term = $this->termRowForDate('semester', $date, $academicYearId);

        return $term ? Term::find($term['id']) : null;
    }

    private function termRowForDate(string $type, CarbonInterface|string|null $date, ?int $academicYearId): ?array
    {
        $d = EthiopianCalendar::date($date)->toDateString();
        $yearId = $this->resolveYearId($academicYearId, $date);
        $snap = $yearId ? $this->snapshot($yearId) : null;

        foreach ($snap['terms'] ?? [] as $t) {
            if ($t['type'] === $type && $t['start'] && $t['start'] <= $d && $t['end'] >= $d) {
                return $t;
            }
        }

        return null;
    }

    /**
     * Quarter to pre-select in grading screens: the quarter open for grading, else the
     * quarter containing today, else null. Replaces logic previously copied per controller.
     */
    public function getDefaultGradingQuarter(int $academicYearId): ?Term
    {
        return Term::where('academic_year_id', $academicYearId)->where('type', 'quarter')->where('is_grading_open', true)->first()
            ?? $this->getCurrentQuarter(null, $academicYearId);
    }

    // ─── Weeks ──────────────────────────────────────────────────────────────

    /**
     * Academic week containing the date. With $includeWeekend (default) a Saturday or
     * Sunday belongs to the week that just ended, so "current week" stays meaningful
     * over the weekend.
     */
    public function getAcademicWeek(CarbonInterface|string|null $date = null, bool $includeWeekend = true): ?array
    {
        $snap = $this->snapshotForDate($date);
        if (! $snap) {
            return null;
        }

        $d = EthiopianCalendar::date($date);
        $ds = $d->toDateString();

        foreach ($snap['weeks'] as $w) {
            $end = $includeWeekend ? Carbon::parse($w['end'])->endOfWeek(Carbon::SUNDAY)->toDateString() : $w['end'];
            if ($w['start'] <= $ds && $end >= $ds) {
                return $this->decorateWeek($w, $snap);
            }
        }

        return null;
    }

    public function getCurrentAcademicWeek(): ?array
    {
        return $this->getAcademicWeek(EthiopianCalendar::today());
    }

    /** All weeks of a year (optionally one quarter), in order. */
    public function getWeeks(?int $academicYearId = null, ?int $termId = null): Collection
    {
        $yearId = $this->resolveYearId($academicYearId);
        if (! $yearId) {
            return collect();
        }
        $snap = $this->snapshot($yearId);

        return collect($snap['weeks'])
            ->when($termId, fn ($c) => $c->where('term_id', $termId))
            ->map(fn ($w) => $this->decorateWeek($w, $snap))->values();
    }

    private function decorateWeek(array $w, array $snap): array
    {
        $quarter = collect($snap['terms'])->firstWhere('id', $w['term_id']);
        $quarterWeeks = collect($snap['weeks'])->where('term_id', $w['term_id'])->where('week_type', '!=', 'break');

        return $w + [
            'quarter' => $quarter['term_number'] ?? null,
            'quarter_name' => $quarter['name'] ?? null,
            'weeks_in_quarter' => $w['term_id'] ? $quarterWeeks->count() : null,
            'ethiopian_range' => EthiopianCalendar::formatRange($w['start'], $w['end']),
        ];
    }

    // ─── Events ─────────────────────────────────────────────────────────────

    public function getEventsByDate(CarbonInterface|string|null $date = null, bool $includeCancelled = false): Collection
    {
        return $this->getEventsByDateRange($date, $date, null, $includeCancelled);
    }

    /**
     * Events overlapping [start, end], across academic years if the range spans them.
     *
     * @param  array<string>|null  $categories
     */
    public function getEventsByDateRange(CarbonInterface|string $start, CarbonInterface|string|null $end = null, ?array $categories = null, bool $includeCancelled = false): Collection
    {
        $s = EthiopianCalendar::date($start)->toDateString();
        $e = EthiopianCalendar::date($end ?? $start)->toDateString();

        $yearIds = collect($this->years())
            ->filter(fn ($y) => ($y['start'] && $y['start'] <= $e && $y['end'] >= $s) || ($y['weeks_start'] && $y['weeks_start'] <= $e && $y['weeks_end'] >= $s))
            ->pluck('id');

        return $yearIds->flatMap(fn ($id) => $this->snapshot($id)['events'] ?? [])
            ->filter(fn ($ev) => $ev['start'] <= $e && $ev['end'] >= $s)
            ->filter(fn ($ev) => $includeCancelled || $ev['status'] === 'active')
            ->when($categories, fn ($c) => $c->whereIn('category', $categories))
            ->sortBy([['start', 'asc'], ['priority', 'desc']])
            ->map(fn ($ev) => $this->decorateEvent($ev))
            ->values();
    }

    /**
     * Upcoming events starting after $from (default today) within $days.
     * By default the weekly teaching backdrop (regular class / CA / project / worksheet)
     * is left out so lists show milestones, exams, revision, breaks and holidays.
     */
    public function getUpcomingEvents(?int $days = null, CarbonInterface|string|null $from = null, bool $includeBackground = false): Collection
    {
        $from = EthiopianCalendar::date($from);
        $days ??= (int) config('academic_calendar.upcoming_days', 30);
        $background = config('academic_calendar.background_categories', []);

        return $this->getEventsByDateRange($from->copy()->addDay(), $from->copy()->addDays($days))
            ->filter(fn ($ev) => $ev['start_date'] > $from->toDateString())
            ->filter(fn ($ev) => $includeBackground || ! in_array($ev['category'], $background, true))
            ->values();
    }

    /** Events in progress on the date (started before or on it, ending on or after it). */
    public function getOngoingEvents(CarbonInterface|string|null $date = null, bool $includeBackground = false): Collection
    {
        $background = config('academic_calendar.background_categories', []);

        return $this->getEventsByDate($date)
            ->filter(fn ($ev) => $includeBackground || ! in_array($ev['category'], $background, true))
            ->values();
    }

    /** School-wide examination windows (midterm + final), optionally for one quarter. */
    public function getExamPeriods(Term|int|null $quarter = null, ?int $academicYearId = null): Collection
    {
        return $this->categoryEvents($this->examCategories(), $quarter, $academicYearId);
    }

    public function getRevisionPeriods(Term|int|null $quarter = null, ?int $academicYearId = null): Collection
    {
        return $this->categoryEvents(['revision'], $quarter, $academicYearId);
    }

    public function getSemesterBreaks(?int $academicYearId = null): Collection
    {
        return $this->categoryEvents(['semester_break'], null, $academicYearId);
    }

    /** First exam window ending on/after the date. */
    public function getNextExamPeriod(CarbonInterface|string|null $from = null): ?array
    {
        $d = EthiopianCalendar::date($from)->toDateString();

        return collect($this->years())->pluck('id')
            ->flatMap(fn ($id) => $this->snapshot($id)['events'] ?? [])
            ->filter(fn ($ev) => $ev['status'] === 'active' && in_array($ev['category'], $this->examCategories(), true) && $ev['end'] >= $d)
            ->sortBy('start')
            ->map(fn ($ev) => $this->decorateEvent($ev, $from))
            ->first();
    }

    /** Next major milestone (priority 2) starting after the date, excluding background events. */
    public function getNextMajorEvent(CarbonInterface|string|null $from = null): ?array
    {
        $d = EthiopianCalendar::date($from)->toDateString();

        return collect($this->years())->pluck('id')
            ->flatMap(fn ($id) => $this->snapshot($id)['events'] ?? [])
            ->filter(fn ($ev) => $ev['status'] === 'active' && $ev['priority'] >= 2 && $ev['start'] > $d)
            ->sortBy('start')
            ->map(fn ($ev) => $this->decorateEvent($ev, $from))
            ->first();
    }

    private function categoryEvents(array $categories, Term|int|null $quarter, ?int $academicYearId): Collection
    {
        $termId = $quarter instanceof Term ? $quarter->id : $quarter;
        $yearId = $quarter instanceof Term ? $quarter->academic_year_id : $this->resolveYearId($academicYearId);
        if (! $yearId) {
            return collect();
        }

        return collect($this->snapshot($yearId)['events'])
            ->filter(fn ($ev) => $ev['status'] === 'active' && in_array($ev['category'], $categories, true))
            ->when($termId, fn ($c) => $c->where('term_id', $termId))
            ->map(fn ($ev) => $this->decorateEvent($ev))
            ->values();
    }

    public function examCategories(): array
    {
        return collect(config('academic_calendar.categories'))->filter(fn ($c) => $c['exam'] ?? false)->keys()->all();
    }

    public function decorateEvent(array $ev, CarbonInterface|string|null $relativeTo = null): array
    {
        $cfg = config("academic_calendar.categories.{$ev['category']}") ?? config('academic_calendar.categories.other');
        $today = EthiopianCalendar::date($relativeTo);
        $start = Carbon::parse($ev['start']);
        $end = Carbon::parse($ev['end']);

        return [
            'id' => $ev['id'],
            'type' => strtoupper($ev['category']),
            'category' => $ev['category'],
            'category_label' => $cfg['label'],
            'color' => $cfg['color'],
            'title' => $ev['title'],
            'description' => $ev['description'],
            'start_date' => $ev['start'],
            'end_date' => $ev['end'],
            'ethiopian_range' => EthiopianCalendar::formatRange($ev['start'], $ev['end']),
            'days' => $start->diffInDays($end) + 1,
            'days_until' => (int) $today->diffInDays($start, false),
            'is_ongoing' => $ev['start'] <= $today->toDateString() && $ev['end'] >= $today->toDateString(),
            'is_instructional' => $ev['is_instructional'],
            'closes_school' => (bool) ($cfg['closes_school'] ?? false),
            'is_exam' => (bool) ($cfg['exam'] ?? false),
            'priority' => $ev['priority'],
            'status' => $ev['status'],
            'source' => $ev['source'],
            'needs_review' => $ev['needs_review'],
            'term_id' => $ev['term_id'],
        ];
    }

    // ─── School days ────────────────────────────────────────────────────────

    /**
     * Full classification of one date.
     *
     * day_type: instructional | examination | revision | event | holiday | closure |
     *           break | weekend | non_school_day | outside_year
     *
     * @return array{date:string, is_school_day:bool, is_instructional:bool, day_type:string, reason:?string, calendar_configured:bool}
     */
    public function getDayStatus(CarbonInterface|string|null $date = null): array
    {
        $d = EthiopianCalendar::date($date);
        $ds = $d->toDateString();
        $snap = $this->snapshotForDate($d);

        $result = fn (bool $school, string $type, ?string $reason = null, ?bool $instructional = null) => [
            'date' => $ds,
            'is_school_day' => $school,
            'is_instructional' => $instructional ?? $school,
            'day_type' => $type,
            'reason' => $reason,
            'calendar_configured' => (bool) ($snap && $snap['weeks']),
        ];

        if (! $snap) {
            return $result(false, 'outside_year', 'Outside every configured academic year');
        }

        $events = collect($snap['events'])->filter(fn ($ev) => $ev['status'] === 'active' && $ev['start'] <= $ds && $ev['end'] >= $ds);

        // 1. An explicit per-date decision wins.
        if (isset($snap['overrides'][$ds])) {
            $o = $snap['overrides'][$ds];

            return $o['is_school_day']
                ? $result(true, $this->schoolDayType($events), $o['reason'])
                : $result(false, 'closure', $o['reason']);
        }

        // 2. Breaks, holidays and closures close school.
        $closing = $events->first(fn ($ev) => config("academic_calendar.categories.{$ev['category']}.closes_school"));
        if ($closing) {
            $type = match ($closing['category']) {
                'semester_break' => 'break',
                'holiday' => 'holiday',
                default => 'closure',
            };

            return $result(false, $type, $closing['title']);
        }

        // 3. Weekly pattern.
        if (! in_array($d->dayOfWeekIso, $snap['year']['working_weekdays'], true)) {
            return $result(false, 'weekend', $d->format('l'));
        }

        // 4. With an official calendar, school runs only inside instructional weeks.
        if ($snap['weeks']) {
            $week = collect($snap['weeks'])->first(fn ($w) => $w['start'] <= $ds && $w['end'] >= $ds);
            if (! $week) {
                return $result(false, 'non_school_day', 'Not part of any academic week');
            }
            if ($week['week_type'] === 'break') {
                return $result(false, 'break', $week['description'] ?: 'Break week');
            }
        } elseif ($snap['year']['start'] > $ds || $snap['year']['end'] < $ds) {
            return $result(false, 'outside_year', 'Outside the academic year dates');
        }

        $type = $this->schoolDayType($events);
        $nonInstructional = $events->first(fn ($ev) => ! $ev['is_instructional']);

        return $result(true, $type, $nonInstructional['title'] ?? null, ! $nonInstructional);
    }

    private function schoolDayType(Collection $events): string
    {
        $categories = $events->pluck('category')->all();

        return match (true) {
            (bool) array_intersect($categories, $this->examCategories()) => 'examination',
            in_array('revision', $categories, true) => 'revision',
            (bool) $events->first(fn ($ev) => ! $ev['is_instructional']) => 'event',
            default => 'instructional',
        };
    }

    public function isSchoolDay(CarbonInterface|string|null $date = null): bool
    {
        return $this->getDayStatus($date)['is_school_day'];
    }

    public function isExamPeriod(CarbonInterface|string|null $date = null): bool
    {
        return $this->getEventsByDate($date, false)->contains(fn ($ev) => $ev['is_exam']);
    }

    public function isRevisionWeek(CarbonInterface|string|null $date = null): bool
    {
        return $this->getEventsByDate($date)->contains(fn ($ev) => $ev['category'] === 'revision');
    }

    public function isSemesterBreak(CarbonInterface|string|null $date = null): bool
    {
        if ($this->getEventsByDate($date)->contains(fn ($ev) => $ev['category'] === 'semester_break')) {
            return true;
        }

        $week = $this->getAcademicWeek($date, false);

        return $week !== null && $week['week_type'] === 'break';
    }

    /** School days in [start, end] as Y-m-d strings. */
    public function getSchoolDays(CarbonInterface|string $start, CarbonInterface|string $end): Collection
    {
        $s = EthiopianCalendar::date($start);
        $e = EthiopianCalendar::date($end);
        if ($e->lt($s)) {
            return collect();
        }

        return collect(CarbonPeriod::create($s, $e))
            ->map(fn ($d) => $this->getDayStatus($d))
            ->filter(fn ($st) => $st['is_school_day'])
            ->pluck('date')
            ->values();
    }

    /** Number of school days in [start, end] inclusive. */
    public function getWorkingDays(CarbonInterface|string $start, CarbonInterface|string $end): int
    {
        return $this->getSchoolDays($start, $end)->count();
    }

    /**
     * Teaching days left in a quarter/semester from $from (inclusive): school days
     * that are not inside an examination window and not non-instructional events.
     * Defaults to the current quarter.
     */
    public function getTeachingDaysRemaining(Term|int|null $period = null, CarbonInterface|string|null $from = null): ?int
    {
        $from = EthiopianCalendar::date($from);
        $term = $period instanceof Term ? $period : ($period ? Term::find($period) : $this->getCurrentQuarter($from));
        if (! $term || ! $term->end_date) {
            return null;
        }

        $termStart = EthiopianCalendar::date($term->start_date);
        $start = $from->greaterThan($termStart) ? $from : $termStart;

        return collect(CarbonPeriod::create($start, EthiopianCalendar::date($term->end_date)))
            ->map(fn ($d) => $this->getDayStatus($d))
            ->filter(fn ($st) => $st['is_school_day'] && $st['is_instructional'] && $st['day_type'] !== 'examination')
            ->count();
    }

    /**
     * Warnings for scheduling something (a subject exam, an assignment due date) on a
     * date. Never blocks: callers show these to the user. Subject exams are expected
     * inside a school-wide examination window.
     *
     * @return array<string>
     */
    public function schedulingWarnings(CarbonInterface|string $date, bool $isExam = false): array
    {
        $d = EthiopianCalendar::date($date);
        $status = $this->getDayStatus($d);
        $label = EthiopianCalendar::format($d).' ('.$d->format('D, M j').')';
        $warnings = [];

        if ($status['day_type'] === 'outside_year') {
            return ["{$label} is outside the academic year."];
        }

        if (! $status['is_school_day']) {
            $warnings[] = "{$label} is not a school day ({$status['reason']}).";
        }

        if ($isExam && $status['calendar_configured'] && ! $this->isExamPeriod($d)) {
            $next = $this->getNextExamPeriod($d);
            $warnings[] = "{$label} is outside the school-wide examination periods"
                .($next ? "; the next one is {$next['title']} ({$next['ethiopian_range']})." : '.');
        }

        return $warnings;
    }

    // ─── Academic context ───────────────────────────────────────────────────

    /**
     * Everything a module needs to know about a date, in one call.
     */
    public function getAcademicContext(CarbonInterface|string|null $date = null): array
    {
        $d = EthiopianCalendar::date($date);
        $eth = EthiopianCalendar::fromGregorian($d);
        $snap = $this->snapshotForDate($d);
        $status = $this->getDayStatus($d);

        $base = [
            'date' => $d->toDateString(),
            'ethiopianDate' => [
                'year' => $eth['year'], 'month' => $eth['month'], 'day' => $eth['day'],
                'monthName' => EthiopianCalendar::monthName($eth['month']),
                'formatted' => EthiopianCalendar::format($d),
            ],
            'inAcademicYear' => (bool) $snap,
            'calendarConfigured' => $status['calendar_configured'],
            'academicYearId' => null,
            'academicYear' => null,
            'gregorianAcademicYear' => null,
            'academicYearLabel' => null,
            'semester' => null, 'semesterId' => null,
            'quarter' => null, 'quarterId' => null, 'quarterName' => null,
            'academicWeek' => null, 'weekSequence' => null, 'weeksInQuarter' => null,
            'isSchoolDay' => $status['is_school_day'],
            'isInstructionalDay' => $status['is_instructional'],
            'dayType' => $status['day_type'],
            'dayTypeReason' => $status['reason'],
            'isExamPeriod' => false,
            'isRevisionWeek' => false,
            'isSemesterBreak' => false,
            'activeEvents' => [],
        ];

        if (! $snap) {
            return $base;
        }

        $y = $snap['year'];
        $semester = $this->termRowForDate('semester', $d, $y['id']);
        $quarter = $this->termRowForDate('quarter', $d, $y['id']);
        $week = $this->getAcademicWeek($d);
        $events = $this->getEventsByDate($d);

        return array_merge($base, [
            'academicYearId' => $y['id'],
            'academicYear' => $y['ethiopian_year'] ? $y['ethiopian_year'].' E.C.' : $y['name'],
            'gregorianAcademicYear' => $y['start'] && $y['end'] ? Carbon::parse($y['start'])->year.'/'.Carbon::parse($y['end'])->year : null,
            'academicYearLabel' => $y['label'],
            'semester' => $semester['term_number'] ?? null,
            'semesterId' => $semester['id'] ?? null,
            'quarter' => $quarter['term_number'] ?? $week['quarter'] ?? null,
            'quarterId' => $quarter['id'] ?? $week['term_id'] ?? null,
            'quarterName' => $quarter['name'] ?? $week['quarter_name'] ?? null,
            'academicWeek' => $week && $week['week_type'] !== 'break' ? $week['week_number'] : null,
            'weekSequence' => $week['sequence'] ?? null,
            'weeksInQuarter' => $week['weeks_in_quarter'] ?? null,
            'isExamPeriod' => $events->contains(fn ($ev) => $ev['is_exam']),
            'isRevisionWeek' => $events->contains(fn ($ev) => $ev['category'] === 'revision'),
            'isSemesterBreak' => $events->contains(fn ($ev) => $ev['category'] === 'semester_break') || ($week['week_type'] ?? null) === 'break',
            'activeEvents' => $events->map(fn ($ev) => ['id' => $ev['id'], 'type' => $ev['type'], 'title' => $ev['title']])->all(),
        ]);
    }

    // ─── Calendar grid for the dashboard ────────────────────────────────────

    /**
     * Month grid (Monday-first) in either the Gregorian or the Ethiopian calendar,
     * with both dates and each day's status and event ids.
     */
    public function getMonthGrid(string $system, int $year, int $month, CarbonInterface|string|null $today = null): array
    {
        $today = EthiopianCalendar::date($today);

        if ($system === 'ethiopian') {
            [$first, $last] = EthiopianCalendar::monthBounds($year, $month);
            $title = EthiopianCalendar::monthName($month).' '.$year;
            $subtitle = $first->format('M j, Y').' – '.$last->format('M j, Y');
            $prev = $month === 1 ? [$year - 1, 13] : [$year, $month - 1];
            $next = $month === 13 ? [$year + 1, 1] : [$year, $month + 1];
        } else {
            $first = Carbon::createFromDate($year, $month, 1, EthiopianCalendar::timezone())->startOfDay();
            $last = $first->copy()->endOfMonth()->startOfDay();
            $title = $first->format('F Y');
            $subtitle = EthiopianCalendar::formatRange($first, $last);
            $prevDate = $first->copy()->subMonth();
            $nextDate = $first->copy()->addMonth();
            $prev = [$prevDate->year, $prevDate->month];
            $next = [$nextDate->year, $nextDate->month];
        }

        $gridStart = $first->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $last->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay();
        $events = $this->getEventsByDateRange($gridStart, $gridEnd, null, false);
        $background = config('academic_calendar.background_categories', []);

        $days = [];
        foreach (CarbonPeriod::create($gridStart, $gridEnd) as $day) {
            $ds = $day->toDateString();
            $eth = EthiopianCalendar::fromGregorian($day);
            $status = $this->getDayStatus($day);
            $dayEvents = $events->filter(fn ($ev) => $ev['start_date'] <= $ds && $ev['end_date'] >= $ds);
            $week = $this->getAcademicWeek($day, false);

            $days[] = [
                'date' => $ds,
                'gregorian_day' => $day->day,
                'gregorian_label' => $day->format('M j'),
                'ethiopian_day' => $eth['day'],
                'ethiopian_label' => EthiopianCalendar::MONTHS_SHORT[$eth['month']].' '.$eth['day'],
                'in_month' => $ds >= $first->toDateString() && $ds <= $last->toDateString(),
                'is_today' => $ds === $today->toDateString(),
                'is_weekend' => $day->isWeekend(),
                'is_school_day' => $status['is_school_day'],
                'day_type' => $status['day_type'],
                'week_label' => $week && $week['week_type'] !== 'break' && $day->dayOfWeekIso === 1 ? 'Q'.$week['quarter'].' W'.$week['week_number'] : null,
                'event_ids' => $dayEvents->pluck('id')->values()->all(),
                'highlight_ids' => $dayEvents->reject(fn ($ev) => in_array($ev['category'], $background, true))->pluck('id')->values()->all(),
                'band_color' => optional($dayEvents->first(fn ($ev) => in_array($ev['category'], $background, true)))['color'],
            ];
        }

        return [
            'system' => $system,
            'year' => $year,
            'month' => $month,
            'title' => $title,
            'subtitle' => $subtitle,
            'prev' => ['year' => $prev[0], 'month' => $prev[1]],
            'next' => ['year' => $next[0], 'month' => $next[1]],
            'days' => $days,
            'events' => $events->keyBy('id')->all(),
        ];
    }

    // ─── Dashboard summary ──────────────────────────────────────────────────

    public function getDashboardSummary(CarbonInterface|string|null $date = null): array
    {
        $today = EthiopianCalendar::date($date);
        $context = $this->getAcademicContext($today);
        $quarter = $context['quarterId'] ? Term::find($context['quarterId']) : null;

        return [
            'context' => $context,
            'today' => [
                'date' => $today->toDateString(),
                'gregorian' => $today->format('l, F j, Y'),
                'ethiopian' => EthiopianCalendar::format($today),
            ],
            'nextMajorEvent' => $this->getNextMajorEvent($today),
            'nextExam' => $this->getNextExamPeriod($today),
            'progress' => [
                'week' => $context['academicWeek'],
                'weeksInQuarter' => $context['weeksInQuarter'],
                'percent' => $context['academicWeek'] && $context['weeksInQuarter']
                    ? (int) round($context['academicWeek'] / $context['weeksInQuarter'] * 100) : null,
                'teachingDaysRemaining' => $quarter ? $this->getTeachingDaysRemaining($quarter, $today) : null,
            ],
        ];
    }
}
