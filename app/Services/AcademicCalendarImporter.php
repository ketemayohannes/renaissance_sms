<?php

namespace App\Services;

use App\Models\AcademicEvent;
use App\Models\AcademicWeek;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Support\EthiopianCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Imports an official academic calendar (see database/data/academic_calendar_*.php).
 *
 * Idempotent and conservative:
 *  - records are matched by a stable source_key; existing ones are left untouched
 *    (an administrator may have edited them) unless $refresh is true;
 *  - an existing academic year's dates and existing quarter/semester dates are never
 *    changed unless $alignTerms is true — differences are reported instead;
 *  - grades, attendance and other academic records are never touched.
 */
class AcademicCalendarImporter
{
    private array $report;

    public function __construct(private AcademicCalendarValidator $validator) {}

    /**
     * @return array{year: ?AcademicYear, created: array, updated: array, unchanged: array, issues: array, dry_run: bool}
     */
    public function import(array $data, bool $dryRun = false, bool $alignTerms = false, bool $refresh = false): array
    {
        $this->report = [
            'year' => null,
            'created' => ['years' => 0, 'terms' => 0, 'weeks' => 0, 'events' => 0],
            'updated' => ['years' => 0, 'terms' => 0, 'weeks' => 0, 'events' => 0],
            'unchanged' => ['terms' => 0, 'weeks' => 0, 'events' => 0],
            'issues' => [],
            'dry_run' => $dryRun,
        ];

        $plan = $this->buildPlan($data);

        DB::beginTransaction();
        try {
            $year = $this->importYear($data, $plan);
            $terms = $this->importTerms($year, $plan, $alignTerms);
            $this->importWeeksAndEvents($year, $terms, $plan, $refresh);

            $this->report['year'] = $year;
            $this->report['issues'] = array_merge($this->report['issues'], $this->validator->validate($year));

            $dryRun ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        AcademicCalendarService::flushCache();

        return $this->report;
    }

    public static function loadFile(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Calendar file not found: {$path}");
        }

        $data = require $path;
        if (! is_array($data) || empty($data['ethiopian_year']) || empty($data['quarters'])) {
            throw new InvalidArgumentException("Calendar file {$path} is missing 'ethiopian_year' or 'quarters'.");
        }

        return $data;
    }

    // ─── Plan: convert and sanity-check the source before touching the DB ───

    private function buildPlan(array $data): array
    {
        $ey = (int) $data['ethiopian_year'];
        $toDate = function (array $md, string $where) use ($ey): Carbon {
            [$m, $d] = $md;
            // Weeks that start in Pagume/late months still belong to the same E.C. year
            // in our school calendar (Meskerem–Sene), so the year is constant.
            try {
                return EthiopianCalendar::toGregorian($ey, (int) $m, (int) $d);
            } catch (InvalidArgumentException $e) {
                throw new RuntimeException("{$where}: {$e->getMessage()}");
            }
        };

        $weeks = [];
        $sequence = 0;
        foreach ($data['quarters'] as $q => $quarter) {
            foreach ($quarter['weeks'] as $row) {
                $sequence++;
                $key = sprintf('q%d-w%02d', $q, $row['no']);
                $start = $toDate($row['start'], "Quarter {$q} week {$row['no']} start");
                $end = $toDate($row['end'], "Quarter {$q} week {$row['no']} end");

                if ($end->lt($start)) {
                    throw new RuntimeException("Quarter {$q} week {$row['no']} ends before it starts ({$row['label']}).");
                }
                if ($start->dayOfWeekIso !== 1 || $end->dayOfWeekIso !== 5) {
                    $this->issue('warning', "Quarter {$q} week {$row['no']} ({$row['label']}) runs {$start->format('l')}–{$end->format('l')}, not Monday–Friday.");
                }

                $weeks[] = [
                    'key' => $key, 'quarter' => (int) $q, 'semester' => (int) $quarter['semester'], 'no' => (int) $row['no'],
                    'sequence' => $sequence, 'start' => $start, 'end' => $end, 'days' => $row['days'] ?? null,
                    'label' => $row['label'], 'text' => $row['text'], 'kind' => $row['kind'],
                    'week_type' => $row['week_type'] ?? 'instructional', 'review' => $row['review'] ?? null,
                    'milestone' => isset($row['milestone']) ? [
                        'title' => $row['milestone']['title'],
                        'date' => $toDate($row['milestone']['date'], "Quarter {$q} milestone"),
                    ] : null,
                ];
            }
        }

        for ($i = 1; $i < count($weeks); $i++) {
            if ($weeks[$i]['start']->lte($weeks[$i - 1]['end'])) {
                $this->issue('error', "Week {$weeks[$i]['label']} overlaps the previous week {$weeks[$i - 1]['label']}.");
            }
        }

        $events = array_map(fn ($ev) => $ev + ['date_g' => $toDate($ev['date'], $ev['title'])], $data['events'] ?? []);
        $holidays = array_map(fn ($h) => $h + ['date_g' => $toDate($h['date'], $h['title'])], $data['inferred_holidays'] ?? []);

        return ['weeks' => $weeks, 'events' => $events, 'holidays' => $holidays, 'semesters' => $data['semesters'] ?? []];
    }

    // ─── Year ───────────────────────────────────────────────────────────────

    private function importYear(array $data, array $plan): AcademicYear
    {
        $ey = (int) $data['ethiopian_year'];
        $first = $plan['weeks'][0]['start'];
        $last = collect($plan['weeks'])->max('end');
        foreach ($plan['events'] as $ev) {
            $last = $ev['date_g']->gt($last) ? $ev['date_g'] : $last;
        }

        $year = AcademicYear::where('ethiopian_year', $ey)->first()
            ?? AcademicYear::whereDate('start_date', '<=', $first->toDateString())->whereDate('end_date', '>=', $first->toDateString())->orderByDesc('is_active')->first();

        if (! $year) {
            $year = AcademicYear::create([
                'name' => $data['name'] ?? "{$ey} E.C.",
                'ethiopian_year' => $ey,
                'start_date' => $first->toDateString(),
                'end_date' => $last->toDateString(),
                'is_active' => false,
            ]);
            $this->report['created']['years']++;
            $this->issue('info', "Created academic year \"{$year->name}\" (not activated). Activate it from Academic Years when ready.");

            return $year;
        }

        if ($year->ethiopian_year === null) {
            $year->update(['ethiopian_year' => $ey]);
            $this->report['updated']['years']++;
        } elseif ($year->ethiopian_year !== $ey) {
            throw new RuntimeException("Academic year \"{$year->name}\" covers these dates but is recorded as {$year->ethiopian_year} E.C., not {$ey} E.C.");
        }

        if ($year->start_date->gt($first) || $year->end_date->lt($last)) {
            $this->issue('warning', sprintf(
                'Academic year "%s" runs %s → %s but the official calendar runs %s → %s. The year dates were left unchanged; adjust them in Academic Years if needed.',
                $year->name, $year->start_date->toDateString(), $year->end_date->toDateString(), $first->toDateString(), $last->toDateString()
            ));
        }

        return $year;
    }

    // ─── Terms ──────────────────────────────────────────────────────────────

    /** @return array{quarters: array<int, Term>, semesters: array<int, Term>} */
    private function importTerms(AcademicYear $year, array $plan, bool $alignTerms): array
    {
        $quarters = [];
        foreach (collect($plan['weeks'])->groupBy('quarter') as $q => $weeks) {
            $teaching = $weeks->where('week_type', '!=', 'break');
            $quarters[$q] = $this->upsertTerm($year, 'quarter', (int) $q, "Quarter {$q}",
                $teaching->min('start'), $teaching->max('end'), $alignTerms);
        }

        $semesters = [];
        foreach ($plan['semesters'] as $n => $semester) {
            $members = collect($semester['quarters'])->map(fn ($q) => $quarters[$q] ?? null)->filter();
            if ($members->isEmpty()) {
                continue;
            }
            $sem = $this->upsertTerm($year, 'semester', (int) $n, $semester['name'],
                Carbon::parse($members->min(fn ($t) => $t->start_date->toDateString())),
                Carbon::parse($members->max(fn ($t) => $t->end_date->toDateString())), $alignTerms);

            foreach ($members as $quarter) {
                if ($quarter->parent_term_id === null) {
                    $quarter->update(['parent_term_id' => $sem->id]);
                } elseif ($quarter->parent_term_id !== $sem->id) {
                    $this->issue('warning', "{$quarter->name} is linked to a different semester than the official calendar ({$semester['name']}); left unchanged.");
                }
            }
            $semesters[$n] = $sem;
        }

        return ['quarters' => $quarters, 'semesters' => $semesters];
    }

    private function upsertTerm(AcademicYear $year, string $type, int $number, string $name, Carbon $start, Carbon $end, bool $align): Term
    {
        $term = Term::where('academic_year_id', $year->id)->where('type', $type)->where('term_number', $number)->first();

        if (! $term) {
            $this->report['created']['terms']++;

            return Term::create([
                'academic_year_id' => $year->id, 'name' => $name, 'type' => $type, 'term_number' => $number,
                'start_date' => $start->toDateString(), 'end_date' => $end->toDateString(),
                'is_grading_open' => false, 'is_master_grading_open' => false,
            ]);
        }

        $differs = $term->start_date?->toDateString() !== $start->toDateString() || $term->end_date?->toDateString() !== $end->toDateString();
        if (! $differs) {
            $this->report['unchanged']['terms']++;

            return $term;
        }

        if ($align) {
            app(AcademicCalendarChangeService::class)->alignTermDates($term, $start, $end, 'Aligned with the official calendar during import');
            $this->report['updated']['terms']++;
        } else {
            $this->issue('warning', sprintf(
                '%s is recorded as %s → %s but the official calendar says %s → %s. Left unchanged — use "Align term dates" on the calendar admin page to apply.',
                $term->name, $term->start_date?->toDateString(), $term->end_date?->toDateString(), $start->toDateString(), $end->toDateString()
            ));
        }

        return $term;
    }

    // ─── Weeks and events ───────────────────────────────────────────────────

    private function importWeeksAndEvents(AcademicYear $year, array $terms, array $plan, bool $refresh): void
    {
        $categories = config('academic_calendar.categories');

        foreach ($plan['weeks'] as $w) {
            $quarter = $terms['quarters'][$w['quarter']] ?? null;
            $isBreak = $w['week_type'] === 'break';

            $week = $this->upsert(AcademicWeek::class, 'weeks', $year, $w['key'], [
                'term_id' => $isBreak ? null : $quarter?->id,
                'week_number' => $w['no'],
                'sequence' => $w['sequence'],
                'start_date' => $w['start']->toDateString(),
                'end_date' => $w['end']->toDateString(),
                'working_days' => $w['days'],
                'week_type' => $w['week_type'],
                'description' => $this->weekDescription($w),
                'source_label' => $w['label'],
                'needs_review' => (bool) $w['review'],
                'review_note' => $w['review'],
            ], $refresh);

            $this->upsert(AcademicEvent::class, 'events', $year, "{$w['key']}-activity", [
                'term_id' => $quarter?->id,
                'academic_week_id' => $week->id,
                'category' => $w['kind'],
                'title' => $this->activityTitle($w),
                'description' => "Quarter {$w['quarter']}, week {$w['no']} ({$w['label']}).",
                'start_date' => $w['start']->toDateString(),
                'end_date' => $w['end']->toDateString(),
                'is_instructional' => $categories[$w['kind']]['instructional'] ?? true,
                'priority' => in_array($w['kind'], ['midterm_exam', 'final_exam'], true) ? 2 : (in_array($w['kind'], ['revision', 'semester_break'], true) ? 1 : 0),
                'source' => 'import',
                'source_text' => $w['text'],
                'needs_review' => false,
            ], $refresh);

            if ($w['milestone']) {
                $this->upsert(AcademicEvent::class, 'events', $year, "q{$w['quarter']}-milestone", [
                    'term_id' => $quarter?->id,
                    'academic_week_id' => $week->id,
                    'category' => 'milestone',
                    'title' => $w['milestone']['title'],
                    'description' => $w['text'],
                    'start_date' => $w['milestone']['date']->toDateString(),
                    'end_date' => $w['milestone']['date']->toDateString(),
                    'is_instructional' => true,
                    'priority' => 2,
                    'source' => 'import',
                    'source_text' => $w['text'],
                ], $refresh);
            }
        }

        foreach ($plan['events'] as $ev) {
            $this->upsert(AcademicEvent::class, 'events', $year, $ev['key'], [
                'term_id' => null,
                'category' => $ev['category'],
                'title' => $ev['title'],
                'description' => $ev['description'] ?? null,
                'start_date' => $ev['date_g']->toDateString(),
                'end_date' => $ev['date_g']->toDateString(),
                'is_instructional' => $ev['instructional'] ?? false,
                'priority' => $ev['priority'] ?? 1,
                'source' => 'import',
                'source_text' => $ev['text'] ?? $ev['title'],
                'needs_review' => ! empty($ev['review']),
                'review_note' => $ev['review'] ?? null,
            ], $refresh);
        }

        foreach ($plan['holidays'] as $h) {
            $date = $h['date_g'];
            $week = collect($plan['weeks'])->first(fn ($w) => $w['start']->lte($date) && $w['end']->gte($date));
            $this->upsert(AcademicEvent::class, 'events', $year, "holiday-{$h['key']}", [
                'term_id' => $week ? ($terms['quarters'][$week['quarter']] ?? null)?->id : null,
                'category' => 'holiday',
                'title' => $h['title'],
                'description' => 'Public holiday.',
                'start_date' => $date->toDateString(),
                'end_date' => $date->toDateString(),
                'is_instructional' => false,
                'priority' => 1,
                'source' => 'inferred',
                'source_text' => null,
                'needs_review' => true,
                'review_note' => $week
                    ? "Not named in the source document. Inferred because \"{$week['label']}\" is listed with {$week['days']} working days and this fixed-date national holiday falls in that week. Confirm and clear this flag."
                    : 'Not named in the source document. Confirm and clear this flag.',
            ], $refresh);
        }
    }

    /**
     * Create the record if its source_key is new; otherwise leave it alone unless
     * $refresh, in which case source fields are re-applied.
     */
    private function upsert(string $model, string $bucket, AcademicYear $year, string $key, array $attributes, bool $refresh)
    {
        $existing = $model::where('academic_year_id', $year->id)->where('source_key', $key)->first();

        if (! $existing) {
            $this->report['created'][$bucket]++;

            return $model::create($attributes + ['academic_year_id' => $year->id, 'source_key' => $key]);
        }

        if ($refresh) {
            $existing->fill($attributes);
            if ($existing->isDirty()) {
                $existing->save();
                $this->report['updated'][$bucket]++;

                return $existing;
            }
        }

        $this->report['unchanged'][$bucket]++;

        return $existing;
    }

    private function activityTitle(array $w): string
    {
        $q = $this->ordinal($w['quarter']);

        return match ($w['kind']) {
            'midterm_exam' => "{$q} Quarter Midterm Examination",
            'final_exam' => "{$q} Quarter Final Examination",
            'revision' => "{$q} Quarter Revision Week",
            'semester_break' => 'First Semester Break',
            'project_work' => 'Project Work',
            'worksheet' => 'Worksheet',
            'continuous_assessment' => 'Continuous Assessment',
            default => 'Regular Classes',
        };
    }

    private function weekDescription(array $w): string
    {
        return $w['week_type'] === 'break' ? 'Semester break' : $w['text'];
    }

    private function ordinal(int $n): string
    {
        return $n.(['th', 'st', 'nd', 'rd'][$n] ?? 'th');
    }

    private function issue(string $level, string $message): void
    {
        $this->report['issues'][] = ['level' => $level, 'message' => $message];
    }
}
