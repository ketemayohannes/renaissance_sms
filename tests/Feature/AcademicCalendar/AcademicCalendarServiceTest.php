<?php

namespace Tests\Feature\AcademicCalendar;

use App\Models\AcademicEvent;
use App\Models\AcademicWeek;
use App\Models\AcademicYear;
use App\Models\SchoolDayOverride;
use App\Models\Term;
use App\Services\AcademicCalendarImporter;
use App\Services\AcademicCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private AcademicCalendarService $calendar;

    private array $report;

    protected function setUp(): void
    {
        parent::setUp();
        $this->report = app(AcademicCalendarImporter::class)
            ->import(AcademicCalendarImporter::loadFile(database_path('data/academic_calendar_2019.php')));
        $this->calendar = app(AcademicCalendarService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function quarter(int $n): Term
    {
        return Term::where('type', 'quarter')->where('term_number', $n)->firstOrFail();
    }

    // ─── Import ─────────────────────────────────────────────────────────────

    /** @test */
    public function the_official_calendar_imports_all_four_quarters(): void
    {
        $this->assertSame(['years' => 1, 'terms' => 6, 'weeks' => 41, 'events' => 51], $this->report['created']);

        $year = AcademicYear::firstOrFail();
        $this->assertSame(2019, $year->ethiopian_year);
        $this->assertSame('2019 E.C. / 2026–2027 G.C.', $year->calendar_label);

        foreach ([1 => ['2026-09-14', '2026-11-20'], 2 => ['2026-11-23', '2027-01-29'], 3 => ['2027-02-08', '2027-04-16'], 4 => ['2027-04-19', '2027-06-25']] as $n => [$start, $end]) {
            $q = $this->quarter($n);
            $this->assertSame([$start, $end], [$q->start_date->toDateString(), $q->end_date->toDateString()], "Quarter {$n}");
            $this->assertSame(10, AcademicWeek::where('term_id', $q->id)->count(), "Quarter {$n} weeks");
            $this->assertSame(2, $this->calendar->getExamPeriods($q)->count(), "Quarter {$n} exam windows");
            $this->assertSame(1, $this->calendar->getRevisionPeriods($q)->count());
        }

        // Semesters span their quarters; the break sits between them.
        $s1 = Term::where('type', 'semester')->where('term_number', 1)->firstOrFail();
        $this->assertSame(['2026-09-14', '2027-01-29'], [$s1->start_date->toDateString(), $s1->end_date->toDateString()]);
        $this->assertSame($s1->id, $this->quarter(2)->parent_term_id);

        // The source wording and week labels are preserved.
        $week = AcademicWeek::where('source_key', 'q1-w09')->firstOrFail();
        $this->assertSame('Tik. 30 – Hid. 4', $week->source_label);
        $this->assertSame('Revision week', AcademicEvent::where('source_key', 'q1-w09-activity')->value('source_text'));
    }

    /** @test */
    public function import_is_idempotent_and_keeps_administrator_edits(): void
    {
        $event = AcademicEvent::where('source_key', 'q1-w07-activity')->firstOrFail();
        $event->update(['title' => 'Edited by admin']);

        $again = app(AcademicCalendarImporter::class)
            ->import(AcademicCalendarImporter::loadFile(database_path('data/academic_calendar_2019.php')));

        $this->assertSame(['years' => 0, 'terms' => 0, 'weeks' => 0, 'events' => 0], $again['created']);
        $this->assertSame(41, AcademicWeek::count());
        $this->assertSame(51, AcademicEvent::count());
        $this->assertSame('Edited by admin', $event->fresh()->title);
    }

    /** @test */
    public function import_does_not_move_existing_term_dates_unless_asked(): void
    {
        $q1 = $this->quarter(1);
        $q1->update(['start_date' => '2026-09-01', 'end_date' => '2026-11-15']);

        $report = app(AcademicCalendarImporter::class)
            ->import(AcademicCalendarImporter::loadFile(database_path('data/academic_calendar_2019.php')));

        $this->assertSame('2026-09-01', $q1->fresh()->start_date->toDateString());
        $this->assertTrue(collect($report['issues'])->contains(fn ($i) => str_contains($i['message'], 'Quarter 1 is recorded as 2026-09-01')));

        app(AcademicCalendarImporter::class)
            ->import(AcademicCalendarImporter::loadFile(database_path('data/academic_calendar_2019.php')), false, true);
        $this->assertSame(['2026-09-14', '2026-11-20'], [$q1->fresh()->start_date->toDateString(), $q1->fresh()->end_date->toDateString()]);
    }

    /** @test */
    public function dry_run_saves_nothing(): void
    {
        $this->refreshDatabaseOnly();
        $report = app(AcademicCalendarImporter::class)
            ->import(AcademicCalendarImporter::loadFile(database_path('data/academic_calendar_2019.php')), true);

        $this->assertTrue($report['dry_run']);
        $this->assertSame(41, $report['created']['weeks']);
        $this->assertSame(0, AcademicWeek::count());
        $this->assertSame(0, AcademicYear::count());
    }

    private function refreshDatabaseOnly(): void
    {
        AcademicEvent::query()->delete();
        AcademicWeek::query()->delete();
        Term::query()->delete();
        AcademicYear::query()->delete();
        AcademicCalendarService::flushCache();
    }

    /** @test */
    public function questionable_source_data_is_flagged_not_changed(): void
    {
        $messages = collect($this->report['issues'])->pluck('message')->implode("\n");

        $this->assertStringContainsString('Yek. 29 – Meg. 3 is published with 4 working days, but the calendar has 5', $messages);
        $this->assertStringContainsString('Derg Downfall Day', $messages);
        $this->assertStringContainsString('Sunday 4 July 2027', $messages);

        // The week keeps the published count; no holiday was invented for it.
        $this->assertSame(4, AcademicWeek::where('source_key', 'q3-w05')->value('working_days'));
        $this->assertTrue(AcademicWeek::where('source_key', 'q3-w05')->value('needs_review'));
        $this->assertSame(5, AcademicEvent::where('source', 'inferred')->where('needs_review', true)->count());
    }

    // ─── Academic periods ───────────────────────────────────────────────────

    /** @test */
    public function academic_context_matches_the_official_calendar(): void
    {
        $ctx = $this->calendar->getAcademicContext('2026-10-28'); // Tikimt 18 — Q1 week 7 midterm
        $this->assertSame('2019 E.C.', $ctx['academicYear']);
        $this->assertSame('2026/2027', $ctx['gregorianAcademicYear']);
        $this->assertSame([1, 1, 7, 10], [$ctx['semester'], $ctx['quarter'], $ctx['academicWeek'], $ctx['weeksInQuarter']]);
        $this->assertTrue($ctx['isSchoolDay']);
        $this->assertTrue($ctx['isExamPeriod']);
        $this->assertFalse($ctx['isRevisionWeek']);
        $this->assertSame('MIDTERM_EXAM', $ctx['activeEvents'][0]['type']);
        $this->assertSame('Tikimt 18, 2019', $ctx['ethiopianDate']['formatted']);
    }

    /** @test */
    public function quarter_and_semester_boundaries_are_exact(): void
    {
        $this->assertSame(1, $this->calendar->getAcademicContext('2026-11-20')['quarter']); // last day of Q1 (Friday)
        $this->assertSame(10, $this->calendar->getAcademicContext('2026-11-20')['academicWeek']);
        $this->assertSame([2, 1], [$this->calendar->getAcademicContext('2026-11-23')['quarter'], $this->calendar->getAcademicContext('2026-11-23')['academicWeek']]);

        // Weekend after Q1 still reports the week that just ended.
        $this->assertSame(10, $this->calendar->getAcademicContext('2026-11-22')['academicWeek']);
        $this->assertFalse($this->calendar->isSchoolDay('2026-11-22'));

        // Semester break: between semesters, not a school day, not a quarter.
        $break = $this->calendar->getAcademicContext('2027-02-02');
        $this->assertNull($break['semester']);
        $this->assertNull($break['quarter']);
        $this->assertNull($break['academicWeek']);
        $this->assertTrue($break['isSemesterBreak']);
        $this->assertFalse($break['isSchoolDay']);
        $this->assertSame(2, $this->calendar->getAcademicContext('2027-02-08')['semester']);
    }

    /** @test */
    public function dates_outside_the_academic_year_return_a_clear_result(): void
    {
        $ctx = $this->calendar->getAcademicContext('2027-08-01');
        $this->assertFalse($ctx['inAcademicYear']);
        $this->assertNull($ctx['quarter']);
        $this->assertNull($ctx['academicWeek']);
        $this->assertSame('outside_year', $ctx['dayType']);
        $this->assertFalse($ctx['isSchoolDay']);
        $this->assertNull($this->calendar->getCurrentQuarter('2027-08-01'));
    }

    /** @test */
    public function the_current_week_follows_addis_ababa_time(): void
    {
        // Sunday 22:30 UTC is already Monday 01:30 in Addis Ababa: week 2 has started.
        Carbon::setTestNow(Carbon::parse('2026-09-20 22:30:00', 'UTC'));

        $week = $this->calendar->getCurrentAcademicWeek();
        $this->assertSame(2, $week['week_number']);
        $this->assertSame(1, $this->calendar->getDashboardSummary()['context']['quarter']);
        $this->assertSame($this->quarter(1)->id, $this->calendar->getCurrentQuarter()->id);
    }

    // ─── School days ────────────────────────────────────────────────────────

    /** @test */
    public function working_days_follow_the_published_counts(): void
    {
        $this->assertSame(50, $this->calendar->getWorkingDays('2026-09-14', '2026-11-20')); // Q1
        $this->assertSame(48, $this->calendar->getWorkingDays('2026-11-23', '2027-01-29')); // Q2: Genna + Timket
        $this->assertSame(0, $this->calendar->getWorkingDays('2027-02-01', '2027-02-05'));  // semester break
        $this->assertSame(195, $this->calendar->getWorkingDays('2026-09-01', '2027-07-31'));

        foreach (AcademicWeek::where('week_type', 'instructional')->get() as $week) {
            if ($week->source_key === 'q3-w05') {
                continue; // flagged: closed day not identified in the source
            }
            $this->assertSame($week->working_days, $this->calendar->getWorkingDays($week->start_date, $week->end_date), $week->source_label);
        }
    }

    /** @test */
    public function weekdays_are_not_assumed_to_be_school_days(): void
    {
        $this->assertFalse($this->calendar->isSchoolDay('2027-01-07'));          // Genna, a Thursday
        $this->assertSame('holiday', $this->calendar->getDayStatus('2027-01-07')['day_type']);
        $this->assertFalse($this->calendar->isSchoolDay('2027-02-03'));          // semester break, a Wednesday
        $this->assertFalse($this->calendar->isSchoolDay('2026-09-10'));          // before school opens
        $this->assertFalse($this->calendar->isSchoolDay('2027-06-30'));          // after the last exam
        $this->assertTrue($this->calendar->isSchoolDay('2026-09-14'));
    }

    /** @test */
    public function exam_weeks_are_school_days_not_holidays(): void
    {
        $status = $this->calendar->getDayStatus('2026-11-18');
        $this->assertTrue($status['is_school_day']);
        $this->assertSame('examination', $status['day_type']);
        $this->assertTrue($this->calendar->isExamPeriod('2026-11-18'));
        $this->assertTrue($this->calendar->isRevisionWeek('2026-11-11'));
    }

    /** @test */
    public function overlapping_events_on_one_date_are_all_returned(): void
    {
        $titles = $this->calendar->getEventsByDate('2027-01-07')->pluck('title')->all();
        $this->assertContains('Ethiopian Christmas (Genna)', $titles);
        $this->assertContains('Continuous Assessment', $titles);

        $opening = $this->calendar->getEventsByDate('2026-09-14')->pluck('category')->all();
        $this->assertEqualsCanonicalizing(['milestone', 'regular_class'], $opening);
    }

    /** @test */
    public function teaching_days_remaining_exclude_exams_and_holidays(): void
    {
        // From Monday of Q1 week 8: weeks 8 and 9 remain for teaching; week 10 is the final exam.
        $this->assertSame(10, $this->calendar->getTeachingDaysRemaining($this->quarter(1), '2026-11-02'));
        // Q2 from Jan 4: week 7 (4 days, Genna), week 8 (5), week 9 (4, Timket); week 10 exams.
        $this->assertSame(13, $this->calendar->getTeachingDaysRemaining($this->quarter(2), '2027-01-04'));
    }

    /** @test */
    public function upcoming_events_show_milestones_and_exams_in_order(): void
    {
        $upcoming = $this->calendar->getUpcomingEvents(60, '2026-11-19');
        $this->assertSame(['2nd Quarter Begins', '2nd Quarter Midterm Examination', 'Ethiopian Christmas (Genna)', '2nd Quarter Revision Week'], $upcoming->pluck('title')->all());
        $this->assertSame(4, $upcoming->first()['days_until']);

        $next = $this->calendar->getNextExamPeriod('2026-11-21');
        $this->assertSame('2nd Quarter Midterm Examination', $next['title']);
    }

    // ─── Changes, cache and history ─────────────────────────────────────────

    /** @test */
    public function calendar_edits_invalidate_cached_results_immediately(): void
    {
        $this->assertTrue($this->calendar->isSchoolDay('2026-10-14'));

        AcademicEvent::create([
            'academic_year_id' => AcademicYear::value('id'), 'category' => 'closure', 'title' => 'Emergency closure',
            'start_date' => '2026-10-14', 'end_date' => '2026-10-14', 'is_instructional' => false, 'source' => 'manual',
        ]);
        $this->assertFalse($this->calendar->isSchoolDay('2026-10-14'));

        SchoolDayOverride::create(['academic_year_id' => AcademicYear::value('id'), 'date' => '2026-10-17', 'is_school_day' => true, 'reason' => 'Make-up Saturday']);
        $this->assertTrue($this->calendar->isSchoolDay('2026-10-17'));

        $this->quarter(1)->update(['end_date' => '2026-11-13']);
        $this->assertNull($this->calendar->getCurrentQuarter('2026-11-18'));
    }

    /** @test */
    public function a_week_ending_on_monday_does_not_swallow_the_next_weeks_days(): void
    {
        // Quarter 1 final exam runs into Monday 23 Nov; Quarter 2 starts Tuesday 24 Nov.
        AcademicWeek::whereDate('start_date', '2026-11-23')->firstOrFail()->update(['start_date' => '2026-11-24', 'working_days' => 4]);
        AcademicWeek::whereDate('start_date', '2026-11-16')->firstOrFail()->update(['end_date' => '2026-11-23', 'working_days' => 6]);
        AcademicCalendarService::flushCache();

        $this->assertSame([1, 10], [$this->calendar->getAcademicWeek('2026-11-23')['quarter'], $this->calendar->getAcademicWeek('2026-11-23')['week_number']]);
        $this->assertSame([2, 1], [$this->calendar->getAcademicWeek('2026-11-24')['quarter'], $this->calendar->getAcademicWeek('2026-11-24')['week_number']]);
        // Weekends still belong to the week before them.
        $this->assertSame(1, $this->calendar->getAcademicWeek('2026-11-28')['week_number']);
        $this->assertSame(9, $this->calendar->getAcademicWeek('2026-11-15')['week_number']);
    }

    /** @test */
    public function scheduling_warnings_flag_exams_outside_the_exam_window(): void
    {
        $this->assertSame([], $this->calendar->schedulingWarnings('2026-10-28', true));
        $warnings = $this->calendar->schedulingWarnings('2026-10-14', true);
        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('outside the school-wide examination periods', $warnings[0]);
        $this->assertStringContainsString('1st Quarter Midterm Examination', $warnings[0]);
        $this->assertStringContainsString('not a school day', implode(' ', $this->calendar->schedulingWarnings('2027-01-07')));
    }

    /** @test */
    public function month_grids_work_in_both_calendars(): void
    {
        $pagume = $this->calendar->getMonthGrid('ethiopian', 2019, 13, '2027-09-11');
        $inMonth = collect($pagume['days'])->where('in_month', true);
        $this->assertSame(6, $inMonth->count());
        $this->assertSame('Pagume 2019', $pagume['title']);
        $this->assertSame(['year' => 2020, 'month' => 1], $pagume['next']);
        $this->assertTrue(collect($pagume['days'])->firstWhere('date', '2027-09-11')['is_today']);

        $jan = $this->calendar->getMonthGrid('gregorian', 2027, 1, '2027-01-07');
        $genna = collect($jan['days'])->firstWhere('date', '2027-01-07');
        $this->assertTrue($genna['is_today']);
        $this->assertFalse($genna['is_school_day']);
        $this->assertSame('Tah 29', $genna['ethiopian_label']);
        $this->assertNotEmpty($genna['highlight_ids']);
        $this->assertSame(0, count($jan['days']) % 7);
    }

    /** @test */
    public function a_future_year_works_without_a_data_file(): void
    {
        $year = AcademicYear::create(['name' => '2020 E.C.', 'ethiopian_year' => 2020, 'start_date' => '2027-09-13', 'end_date' => '2028-07-10']);
        Term::create(['academic_year_id' => $year->id, 'name' => 'Quarter 1', 'type' => 'quarter', 'term_number' => 1, 'start_date' => '2027-09-13', 'end_date' => '2027-11-19']);

        // No weeks yet: weekdays inside the year count, and the context is still correct.
        $ctx = $this->calendar->getAcademicContext('2027-09-15');
        $this->assertSame('2020 E.C.', $ctx['academicYear']);
        $this->assertSame(1, $ctx['quarter']);
        $this->assertFalse($ctx['calendarConfigured']);
        $this->assertTrue($ctx['isSchoolDay']);

        // The 2019 calendar is untouched.
        $this->assertSame(1, $this->calendar->getAcademicContext('2026-09-14')['quarter']);
        $this->assertSame('2019 E.C.', $this->calendar->getAcademicContext('2026-09-14')['academicYear']);
    }
}
