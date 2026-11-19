<?php

namespace App\Services;

use App\Events\AcademicCalendarChanged;
use App\Models\AcademicActivity;
use App\Models\AcademicEvent;
use App\Models\AcademicWeek;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\SchoolDayOverride;
use App\Models\StudentAttendance;
use App\Models\Term;
use App\Support\EthiopianCalendar;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Applies administrator changes to the calendar and reports what they affect.
 *
 * Calendar edits only ever change calendar records. Submitted attendance, grades,
 * assessments and other academic records are never modified; instead the change
 * returns "impacts" (e.g. attendance already taken on a day that is now a holiday)
 * for the administrator to review. Every change is written to the audit log
 * (via the Auditable trait) and announced with AcademicCalendarChanged.
 */
class AcademicCalendarChangeService
{
    public function __construct(private AcademicCalendarService $calendar) {}

    /** @return array{model: AcademicEvent, impacts: array} */
    public function saveEvent(?AcademicEvent $event, AcademicYear $year, array $data): array
    {
        $isNew = $event === null;
        $event ??= new AcademicEvent(['academic_year_id' => $year->id, 'source' => 'manual', 'created_by' => Auth::id()]);
        $before = $isNew ? [] : $event->only(array_keys($data));

        $ranges = array_filter([
            $isNew ? null : [$event->start_date, $event->end_date],
            [$data['start_date'], $data['end_date']],
        ]);

        $examActivitiesBefore = $this->examActivitiesOutsideWindows($year);

        return $this->apply($year, $ranges, function () use ($event, $data) {
            $event->fill($data);
            $event->updated_by = Auth::id();
            $event->save();

            return $event;
        }, function (array $impacts) use ($event, $year, $isNew, $before, $examActivitiesBefore) {
            $newlyOutside = $this->examActivitiesOutsideWindows($year) - $examActivitiesBefore;
            if ($event->isExam() && $newlyOutside > 0) {
                $impacts[] = [
                    'type' => 'exams',
                    'count' => $newlyOutside,
                    'message' => "{$newlyOutside} subject exam(s) scheduled by teachers now fall outside every school-wide examination window. They were not moved.",
                ];
            }
            AcademicCalendarChanged::dispatch($year, AcademicEvent::class, $event->id, $isNew ? 'created' : 'updated',
                $before, $event->only(array_keys($before ?: $event->getAttributes())), $impacts, Auth::id());

            return $impacts;
        });
    }

    /** @return array{model: AcademicEvent, impacts: array} */
    public function setEventStatus(AcademicEvent $event, string $status): array
    {
        $year = AcademicYear::findOrFail($event->academic_year_id);
        $before = ['status' => $event->status];

        return $this->apply($year, [[$event->start_date, $event->end_date]], function () use ($event, $status) {
            $event->update(['status' => $status, 'updated_by' => Auth::id()]);

            return $event;
        }, function (array $impacts) use ($event, $year, $before, $status) {
            AcademicCalendarChanged::dispatch($year, AcademicEvent::class, $event->id,
                $status === AcademicEvent::STATUS_CANCELLED ? 'cancelled' : 'updated', $before, ['status' => $status], $impacts, Auth::id());

            return $impacts;
        });
    }

    /** Only manually created events can be deleted; imported ones are cancelled to keep the source trail. */
    public function deleteEvent(AcademicEvent $event): array
    {
        if ($event->source !== 'manual') {
            throw new \DomainException('Imported events cannot be deleted. Cancel the event instead so the original record is kept.');
        }

        $year = AcademicYear::findOrFail($event->academic_year_id);
        $before = $event->attributesToArray();
        $id = $event->id;

        return $this->apply($year, [[$event->start_date, $event->end_date]], function () use ($event) {
            $event->delete();

            return $event;
        }, function (array $impacts) use ($year, $before, $id) {
            AcademicCalendarChanged::dispatch($year, AcademicEvent::class, $id, 'deleted', $before, [], $impacts, Auth::id());

            return $impacts;
        });
    }

    /** @return array{model: AcademicWeek, impacts: array} */
    public function saveWeek(AcademicWeek $week, array $data): array
    {
        $year = AcademicYear::findOrFail($week->academic_year_id);
        $before = $week->only(array_keys($data));
        $ranges = [[$week->start_date, $week->end_date], [$data['start_date'] ?? $week->start_date, $data['end_date'] ?? $week->end_date]];

        return $this->apply($year, $ranges, function () use ($week, $data) {
            $week->update($data);

            return $week;
        }, function (array $impacts) use ($week, $year, $before, $data) {
            AcademicCalendarChanged::dispatch($year, AcademicWeek::class, $week->id, 'updated', $before, $data, $impacts, Auth::id());

            return $impacts;
        });
    }

    /** @return array{model: SchoolDayOverride, impacts: array} */
    public function saveOverride(AcademicYear $year, string $date, bool $isSchoolDay, string $reason): array
    {
        $date = EthiopianCalendar::date($date)->toDateString();

        return $this->apply($year, [[$date, $date]], function () use ($year, $date, $isSchoolDay, $reason) {
            return SchoolDayOverride::updateOrCreate(
                ['academic_year_id' => $year->id, 'date' => $date],
                ['is_school_day' => $isSchoolDay, 'reason' => $reason, 'created_by' => Auth::id()]
            );
        }, function (array $impacts) use ($year, $date, $isSchoolDay, $reason) {
            $override = SchoolDayOverride::where('academic_year_id', $year->id)->whereDate('date', $date)->first();
            AcademicCalendarChanged::dispatch($year, SchoolDayOverride::class, $override->id, 'updated', [],
                ['date' => $date, 'is_school_day' => $isSchoolDay, 'reason' => $reason], $impacts, Auth::id());

            return $impacts;
        });
    }

    public function deleteOverride(SchoolDayOverride $override): array
    {
        $year = AcademicYear::findOrFail($override->academic_year_id);
        $date = $override->date->toDateString();
        $before = $override->attributesToArray();
        $id = $override->id;

        return $this->apply($year, [[$date, $date]], function () use ($override) {
            $override->delete();

            return $override;
        }, function (array $impacts) use ($year, $before, $id) {
            AcademicCalendarChanged::dispatch($year, SchoolDayOverride::class, $id, 'deleted', $before, [], $impacts, Auth::id());

            return $impacts;
        });
    }

    /**
     * Move a quarter/semester to the official calendar dates. Marks and other records
     * stay linked to the same term; only its start/end dates change. Audited.
     */
    public function alignTermDates(Term $term, Carbon|string $start, Carbon|string $end, string $reason): void
    {
        $old = ['start_date' => $term->start_date?->toDateString(), 'end_date' => $term->end_date?->toDateString()];
        $new = ['start_date' => EthiopianCalendar::date($start)->toDateString(), 'end_date' => EthiopianCalendar::date($end)->toDateString()];

        if ($old === $new) {
            return;
        }

        $term->update($new);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => 'updated',
            'auditable_type' => Term::class,
            'auditable_id' => $term->id,
            'old_values' => $old,
            'new_values' => $new + ['reason' => $reason],
            'url' => app()->runningInConsole() ? 'console' : Request::fullUrl(),
            'ip_address' => app()->runningInConsole() ? null : Request::ip(),
            'user_agent' => app()->runningInConsole() ? 'artisan' : Request::userAgent(),
        ]);

        $year = AcademicYear::find($term->academic_year_id);
        if ($year) {
            AcademicCalendarChanged::dispatch($year, Term::class, $term->id, 'aligned', $old, $new, [], Auth::id());
        }
    }

    // ─── Internals ──────────────────────────────────────────────────────────

    /**
     * Run a change inside a transaction, compare school-day status of the affected
     * dates before and after, and collect impacts on existing records.
     */
    private function apply(AcademicYear $year, array $ranges, \Closure $change, \Closure $after): array
    {
        $dates = $this->datesIn($ranges);
        $before = $this->statuses($dates);

        return DB::transaction(function () use ($year, $dates, $before, $change, $after) {
            $model = $change();
            AcademicCalendarService::flushCache();

            $afterStatus = $this->statuses($dates);
            $closed = array_keys(array_filter($afterStatus, fn ($s, $d) => ! $s && ($before[$d] ?? false), ARRAY_FILTER_USE_BOTH));
            $opened = array_keys(array_filter($afterStatus, fn ($s, $d) => $s && ! ($before[$d] ?? true), ARRAY_FILTER_USE_BOTH));

            $impacts = $after($this->impactsFor($year, $closed, $opened));

            return ['model' => $model, 'impacts' => $impacts];
        });
    }

    /**
     * Existing records on days whose school-day status just changed. Read-only.
     */
    public function impactsFor(AcademicYear $year, array $closedDates, array $openedDates): array
    {
        $impacts = [];

        if ($closedDates) {
            $attendance = StudentAttendance::withoutGlobalScopes()->whereIn(DB::raw('DATE(attendance_date)'), $closedDates)->count();
            if ($attendance) {
                $impacts[] = [
                    'type' => 'attendance',
                    'count' => $attendance,
                    'message' => "{$attendance} attendance record(s) were already taken on day(s) now marked closed (".implode(', ', $closedDates).'). They were kept unchanged; review and correct them explicitly if needed.',
                ];
            }

            $activities = AcademicActivity::withoutGlobalScopes()
                ->where(fn ($q) => $q->whereIn(DB::raw('DATE(due_date)'), $closedDates)->orWhereIn(DB::raw('DATE(start_date)'), $closedDates))
                ->count();
            if ($activities) {
                $impacts[] = [
                    'type' => 'activities',
                    'count' => $activities,
                    'message' => "{$activities} assignment(s)/assessment(s) start or are due on day(s) now marked closed. They were not moved.",
                ];
            }
        }

        if ($openedDates) {
            $impacts[] = [
                'type' => 'opened',
                'count' => count($openedDates),
                'message' => count($openedDates).' day(s) became school days ('.implode(', ', $openedDates).'). Attendance will be expected on them.',
            ];
        }

        return $impacts;
    }

    /** Number of subject-level exams (AcademicActivity type "exam") outside all exam windows of the year. */
    private function examActivitiesOutsideWindows(AcademicYear $year): int
    {
        $windows = $this->calendar->getExamPeriods(null, $year->id);
        if ($windows->isEmpty()) {
            return 0;
        }

        return AcademicActivity::withoutGlobalScopes()
            ->where('academic_year_id', $year->id)
            ->where('type', 'exam')
            ->get(['id', 'start_date', 'due_date'])
            ->filter(function ($a) use ($windows) {
                $d = ($a->start_date ?? $a->due_date)?->toDateString();

                return $d && ! $windows->contains(fn ($w) => $w['start_date'] <= $d && $w['end_date'] >= $d);
            })
            ->count();
    }

    private function datesIn(array $ranges): array
    {
        $dates = [];
        foreach ($ranges as [$s, $e]) {
            $start = EthiopianCalendar::date($s);
            $end = EthiopianCalendar::date($e);
            if ($end->lt($start) || $start->diffInDays($end) > 400) {
                continue;
            }
            foreach (CarbonPeriod::create($start, $end) as $d) {
                $dates[$d->toDateString()] = true;
            }
        }

        return array_keys($dates);
    }

    private function statuses(array $dates): array
    {
        $out = [];
        foreach ($dates as $d) {
            $out[$d] = $this->calendar->isSchoolDay($d);
        }

        return $out;
    }
}
