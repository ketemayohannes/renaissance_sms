<?php

namespace App\Services;

use App\Models\AcademicEvent;
use App\Models\AcademicWeek;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Support\EthiopianCalendar;

/**
 * Finds schedule problems for administrative review. It never fixes anything.
 *
 * Each issue: ['level' => error|warning|review|info, 'message' => string, 'subject' => ?string]
 */
class AcademicCalendarValidator
{
    public function __construct(private AcademicCalendarService $calendar) {}

    public function validate(AcademicYear $year): array
    {
        $issues = [];
        $add = function (string $level, string $message, ?string $subject = null) use (&$issues) {
            $issues[] = compact('level', 'message', 'subject');
        };

        $weeks = AcademicWeek::where('academic_year_id', $year->id)->orderBy('sequence')->get();
        $events = AcademicEvent::where('academic_year_id', $year->id)->active()->orderBy('start_date')->get();

        if ($weeks->isEmpty()) {
            $add('info', "No academic weeks are defined for {$year->name}. School days fall back to weekdays within the year dates.");

            return $issues;
        }

        // Flags carried from the source document or set by an administrator.
        foreach ($weeks->where('needs_review', true) as $w) {
            $add('review', "Week {$w->source_label}: {$w->review_note}", "week:{$w->id}");
        }
        foreach ($events->where('needs_review', true) as $e) {
            $add('review', "{$e->title} ({$e->ethiopian_range}): {$e->review_note}", "event:{$e->id}");
        }

        // Overlapping weeks.
        $prev = null;
        foreach ($weeks as $w) {
            if ($prev && $w->start_date->lte($prev->end_date)) {
                $add('error', "Week {$w->source_label} overlaps week {$prev->source_label}.", "week:{$w->id}");
            }
            $prev = $w;
        }

        // Published working-day counts vs the days the calendar actually treats as school days.
        foreach ($weeks as $w) {
            if ($w->isBreak() || $w->working_days === null) {
                continue;
            }
            $computed = $this->calendar->getWorkingDays($w->start_date, $w->end_date);
            if ($computed !== (int) $w->working_days) {
                $add('warning', sprintf(
                    'Week %s is published with %d working days, but the calendar has %d school days in it. %s',
                    $w->source_label ?: $w->ethiopian_range, $w->working_days, $computed,
                    $computed > $w->working_days ? 'A holiday or closure may be missing.' : 'A holiday or closure may be extra.'
                ), "week:{$w->id}");
            }
        }

        // Quarter dates must match their weeks.
        $quarters = Term::where('academic_year_id', $year->id)->where('type', 'quarter')->orderBy('term_number')->get();
        foreach ($quarters as $q) {
            $qWeeks = $weeks->where('term_id', $q->id)->where('week_type', '!=', 'break');
            if ($qWeeks->isEmpty()) {
                $add('warning', "{$q->name} has no academic weeks assigned.", "term:{$q->id}");

                continue;
            }
            $start = $qWeeks->min(fn ($w) => $w->start_date->toDateString());
            $end = $qWeeks->max(fn ($w) => $w->end_date->toDateString());
            if ($q->start_date?->toDateString() !== $start || $q->end_date?->toDateString() !== $end) {
                $add('warning', sprintf('%s is recorded as %s → %s but its calendar weeks run %s → %s.',
                    $q->name, $q->start_date?->toDateString(), $q->end_date?->toDateString(), $start, $end), "term:{$q->id}");
            }
        }

        // Events outside the year, and school-wide activities that fall on no school day.
        foreach ($events as $e) {
            if ($e->start_date->lt($year->start_date) || $e->end_date->gt($year->end_date)) {
                $add('warning', "{$e->title} ({$e->start_date->toDateString()}) is outside the academic year dates.", "event:{$e->id}");
            }
            $closes = $e->closesSchool();
            if (! $closes && $e->is_instructional && $this->calendar->getWorkingDays($e->start_date, $e->end_date) === 0) {
                $add('warning', "{$e->title} ({$e->ethiopian_range}) falls entirely on non-school days.", "event:{$e->id}");
            }
            if ($e->isExam()) {
                $clash = $events->first(fn ($o) => $o->id !== $e->id && $o->closesSchool()
                    && $o->start_date->lte($e->end_date) && $o->end_date->gte($e->start_date));
                if ($clash) {
                    $add('warning', "{$e->title} overlaps {$clash->title} ({$clash->ethiopian_range}). Check the exam timetable for that day.", "event:{$e->id}");
                }
            }
        }

        $order = ['error' => 0, 'warning' => 1, 'review' => 2, 'info' => 3];
        usort($issues, fn ($a, $b) => $order[$a['level']] <=> $order[$b['level']]);

        return $issues;
    }

    /** Short date label used in messages. */
    public static function label($date): string
    {
        return EthiopianCalendar::format($date).' ('.EthiopianCalendar::date($date)->format('D j M Y').')';
    }
}
