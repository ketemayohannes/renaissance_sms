<?php

namespace Tests\Feature\AcademicCalendar;

use App\Events\AcademicCalendarChanged;
use App\Models\AcademicEvent;
use App\Models\AcademicWeek;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Term;
use App\Models\User;
use App\Services\AcademicCalendarImporter;
use App\Services\AcademicCalendarService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AcademicCalendarAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private AcademicYear $year;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        app(AcademicCalendarImporter::class)->import(AcademicCalendarImporter::loadFile(database_path('data/academic_calendar_2019.php')));
        $this->year = AcademicYear::firstOrFail();
        $this->year->update(['is_active' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function attendanceOn(string $date): StudentAttendance
    {
        $division = Division::factory()->create();
        $grade = GradeLevel::factory()->create(['division_id' => $division->id]);
        $section = Section::factory()->create(['grade_level_id' => $grade->id]);
        $student = Student::factory()->create();

        return StudentAttendance::withoutGlobalScopes()->create([
            'student_id' => $student->id, 'section_id' => $section->id, 'academic_year_id' => $this->year->id,
            'attendance_date' => $date, 'status' => 'present', 'marked_by' => $this->admin->id,
        ]);
    }

    // ─── Permissions ────────────────────────────────────────────────────────

    /** @test */
    public function only_calendar_managers_can_open_the_admin_pages(): void
    {
        $this->actingAs($this->admin)->get(route('admin.academic-calendar.index'))->assertOk()->assertSee('Academic Calendar');

        $principal = User::factory()->create();
        $principal->assignRole('Principal');
        $this->actingAs($principal)->get(route('admin.academic-calendar.index'))->assertOk();

        $supervisor = User::factory()->create();
        $supervisor->assignRole('Supervisor'); // admin portal, but no calendar permission
        $this->actingAs($supervisor)->get(route('admin.academic-calendar.index'))->assertForbidden();
        $this->actingAs($supervisor)->post(route('admin.academic-calendar.events.store'), [])->assertForbidden();

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        $this->actingAs($teacher)->get(route('admin.academic-calendar.index'))->assertForbidden();
    }

    /** @test */
    public function the_read_api_needs_a_signed_in_user_but_no_admin_role(): void
    {
        $this->getJson(route('academic-calendar.context', ['date' => '2026-10-28']))->assertUnauthorized();

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        $this->actingAs($teacher)->getJson(route('academic-calendar.context', ['date' => '2026-10-28']))
            ->assertOk()
            ->assertJson(['academicYear' => '2019 E.C.', 'semester' => 1, 'quarter' => 1, 'academicWeek' => 7, 'isExamPeriod' => true]);
    }

    // ─── API ────────────────────────────────────────────────────────────────

    /** @test */
    public function api_endpoints_return_calendar_data_and_validate_input(): void
    {
        $this->actingAs($this->admin);

        $this->getJson(route('academic-calendar.month', ['system' => 'ethiopian', 'year' => 2019, 'month' => 13]))
            ->assertOk()->assertJsonPath('title', 'Pagume 2019');
        $this->getJson(route('academic-calendar.month', ['system' => 'gregorian', 'year' => 2027, 'month' => 13]))->assertStatus(422);

        $this->getJson(route('academic-calendar.day', ['date' => '2027-01-07']))
            ->assertOk()->assertJsonPath('context.dayType', 'holiday')->assertJsonPath('ethiopian', 'Tahsas 29, 2019');

        $this->getJson(route('academic-calendar.events', ['start' => '2026-10-26', 'end' => '2026-10-30', 'categories' => ['midterm_exam']]))
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.title', '1st Quarter Midterm Examination');
        $this->getJson(route('academic-calendar.events', ['start' => '2026-10-30', 'end' => '2026-10-01']))->assertStatus(422);
        $this->getJson(route('academic-calendar.events', ['start' => '2026-01-01', 'end' => '2028-01-01']))->assertStatus(422);

        $this->getJson(route('academic-calendar.working-days', ['start' => '2027-01-04', 'end' => '2027-01-08']))
            ->assertOk()->assertJsonPath('count', 4);

        $q1 = Term::where('type', 'quarter')->where('term_number', 1)->first();
        $this->getJson(route('academic-calendar.exam-periods', ['quarter_id' => $q1->id]))
            ->assertOk()->assertJsonCount(2, 'exams')->assertJsonCount(1, 'revision');
    }

    // ─── Event management ───────────────────────────────────────────────────

    /** @test */
    public function adding_a_holiday_is_audited_and_leaves_existing_attendance_untouched(): void
    {
        Event::fake([AcademicCalendarChanged::class]);
        $attendance = $this->attendanceOn('2026-10-14');

        $this->actingAs($this->admin)->post(route('admin.academic-calendar.events.store'), [
            'academic_year_id' => $this->year->id, 'title' => 'Emergency closure', 'category' => 'closure',
            'start_date' => '2026-10-14', 'end_date' => '2026-10-14', 'priority' => 1,
        ])->assertRedirect()->assertSessionHas('calendar_impacts', fn ($impacts) => collect($impacts)->contains(fn ($i) => $i['type'] === 'attendance' && $i['count'] === 1));

        $event = AcademicEvent::where('title', 'Emergency closure')->firstOrFail();
        $this->assertSame('manual', $event->source);
        $this->assertSame($this->admin->id, $event->created_by);
        $this->assertFalse(app(AcademicCalendarService::class)->isSchoolDay('2026-10-14'));

        // Historical attendance is preserved exactly.
        $this->assertSame('present', $attendance->fresh()->status);
        $this->assertSame('2026-10-14', $attendance->fresh()->attendance_date->toDateString());

        $this->assertTrue(AuditLog::where('auditable_type', AcademicEvent::class)->where('auditable_id', $event->id)->where('event', 'created')->where('user_id', $this->admin->id)->exists());
        Event::assertDispatched(AcademicCalendarChanged::class, fn ($e) => $e->action === 'created' && $e->subjectId === $event->id);
    }

    /** @test */
    public function rescheduling_an_event_records_old_and_new_values(): void
    {
        $exam = AcademicEvent::where('source_key', 'q1-w07-activity')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.academic-calendar.events.update', $exam), [
            'title' => $exam->title, 'category' => 'midterm_exam', 'term_id' => $exam->term_id,
            'start_date' => '2026-10-27', 'end_date' => '2026-10-30', 'priority' => 2, 'is_instructional' => 1,
        ])->assertRedirect();

        $log = AuditLog::where('auditable_type', AcademicEvent::class)->where('auditable_id', $exam->id)->where('event', 'updated')->latest('id')->firstOrFail();
        $this->assertStringStartsWith('2026-10-26', (string) $log->old_values['start_date']);
        $this->assertStringStartsWith('2026-10-27', (string) $log->new_values['start_date']);
        $this->assertSame($this->admin->id, $log->user_id);

        $this->assertFalse(app(AcademicCalendarService::class)->isExamPeriod('2026-10-26'));
        $this->assertTrue(app(AcademicCalendarService::class)->isExamPeriod('2026-10-27'));
    }

    /** @test */
    public function imported_events_can_be_cancelled_and_restored_but_not_deleted(): void
    {
        $holiday = AcademicEvent::where('source_key', 'holiday-genna')->firstOrFail();
        $this->actingAs($this->admin);

        $this->delete(route('admin.academic-calendar.events.destroy', $holiday))->assertSessionHas('error');
        $this->assertModelExists($holiday);

        $this->post(route('admin.academic-calendar.events.cancel', $holiday))->assertRedirect();
        $this->assertSame('cancelled', $holiday->fresh()->status);
        $this->assertTrue(app(AcademicCalendarService::class)->isSchoolDay('2027-01-07'));

        $this->post(route('admin.academic-calendar.events.restore', $holiday))->assertRedirect();
        $this->assertFalse(app(AcademicCalendarService::class)->isSchoolDay('2027-01-07'));

        $this->post(route('admin.academic-calendar.events.review', $holiday), ['needs_review' => 0]);
        $this->assertFalse($holiday->fresh()->needs_review);
    }

    /** @test */
    public function invalid_event_input_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.academic-calendar.events.store'), [
            'academic_year_id' => $this->year->id, 'title' => '', 'category' => 'party',
            'start_date' => '2026-10-20', 'end_date' => '2026-10-10', 'priority' => 5,
        ])->assertSessionHasErrors(['title', 'category', 'end_date', 'priority']);

        $this->assertSame(0, AcademicEvent::where('source', 'manual')->count());
    }

    // ─── Weeks, overrides, terms ────────────────────────────────────────────

    /** @test */
    public function weeks_can_be_edited_with_overlap_checks_and_history(): void
    {
        $week = AcademicWeek::where('source_key', 'q3-w05')->firstOrFail();
        $this->actingAs($this->admin);

        $this->put(route('admin.academic-calendar.weeks.update', $week), [
            'term_id' => $week->term_id, 'week_number' => 5, 'week_type' => 'instructional',
            'start_date' => '2027-03-05', 'end_date' => '2027-03-12', 'working_days' => 4,
        ])->assertSessionHasErrors('start_date'); // overlaps week 4

        $this->put(route('admin.academic-calendar.weeks.update', $week), [
            'term_id' => $week->term_id, 'week_number' => 5, 'week_type' => 'instructional',
            'start_date' => '2027-03-08', 'end_date' => '2027-03-12', 'working_days' => 4,
            'description' => 'Regular class and continuous Assessment', 'review_note' => 'Eid al-Fitr confirmed for 10 March',
        ])->assertRedirect();

        $this->assertFalse($week->fresh()->needs_review);
        $this->assertTrue(AuditLog::where('auditable_type', AcademicWeek::class)->where('auditable_id', $week->id)->where('event', 'updated')->exists());
        $this->get(route('admin.academic-calendar.weeks.edit', $week))->assertOk()->assertSee('Yek. 29 – Meg. 3');
    }

    /** @test */
    public function school_day_overrides_open_or_close_a_single_day(): void
    {
        $this->actingAs($this->admin)->post(route('admin.academic-calendar.overrides.store'), [
            'academic_year_id' => $this->year->id, 'date' => '2026-10-17', 'is_school_day' => 1, 'reason' => 'Make-up Saturday',
        ])->assertSessionHas('calendar_impacts', fn ($i) => collect($i)->contains('type', 'opened'));

        $this->assertTrue(app(AcademicCalendarService::class)->isSchoolDay('2026-10-17'));
        $this->assertSame(51, app(AcademicCalendarService::class)->getWorkingDays('2026-09-14', '2026-11-20'));
    }

    /** @test */
    public function aligning_terms_moves_dates_only_and_keeps_records_linked(): void
    {
        $q2 = Term::where('type', 'quarter')->where('term_number', 2)->firstOrFail();
        $q2->update(['start_date' => '2026-11-20', 'end_date' => '2027-02-05']);
        $attendance = $this->attendanceOn('2027-01-12');

        $this->actingAs($this->admin)->get(route('admin.academic-calendar.index'))->assertSee('Align term dates with calendar');
        $this->post(route('admin.academic-calendar.align-terms'), ['academic_year_id' => $this->year->id])->assertRedirect();

        $this->assertSame(['2026-11-23', '2027-01-29'], [$q2->fresh()->start_date->toDateString(), $q2->fresh()->end_date->toDateString()]);
        $this->assertTrue(AuditLog::where('auditable_type', Term::class)->where('auditable_id', $q2->id)->exists());
        $fresh = $attendance->fresh();
        $this->assertSame('present', $fresh->status);
        $this->assertSame('2027-01-12', $fresh->attendance_date->toDateString());
        $this->assertSame($this->year->id, $fresh->academic_year_id);
        $this->assertTrue($attendance->updated_at->equalTo($fresh->updated_at), 'attendance row must not be touched');
    }

    /** @test */
    public function future_years_can_be_set_up_by_generating_weeks(): void
    {
        $year = AcademicYear::create(['name' => '2020 E.C.', 'ethiopian_year' => 2020, 'start_date' => '2027-09-13', 'end_date' => '2028-07-10']);
        $q1 = Term::create(['academic_year_id' => $year->id, 'name' => 'Quarter 1', 'type' => 'quarter', 'term_number' => 1, 'start_date' => '2027-09-15', 'end_date' => '2027-11-19']);

        $this->actingAs($this->admin)->post(route('admin.academic-calendar.weeks.generate'), ['term_id' => $q1->id])->assertRedirect();

        $weeks = AcademicWeek::where('academic_year_id', $year->id)->orderBy('sequence')->get();
        $this->assertCount(10, $weeks);
        $this->assertSame('2027-09-15', $weeks->first()->start_date->toDateString()); // starts mid-week
        $this->assertSame('2027-11-19', $weeks->last()->end_date->toDateString());
        $this->assertSame(range(1, 10), $weeks->pluck('sequence')->all());
        $this->assertSame(48, app(AcademicCalendarService::class)->getWorkingDays('2027-09-13', '2027-11-19'));

        // Re-running does not duplicate.
        $this->post(route('admin.academic-calendar.weeks.generate'), ['term_id' => $q1->id]);
        $this->assertSame(10, AcademicWeek::where('academic_year_id', $year->id)->count());
    }

    /** @test */
    public function the_import_page_supports_a_dry_run_and_history_lists_changes(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.academic-calendar.import'), ['file' => 'academic_calendar_2019.php', 'dry_run' => 1])
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'Dry run'));
        $this->post(route('admin.academic-calendar.import'), ['file' => '../../.env'])->assertSessionHasErrors('file');

        $this->get(route('admin.academic-calendar.history'))->assertOk()->assertSee('Change History');
    }

    // ─── Dashboards & module integration ────────────────────────────────────

    /** @test */
    public function the_dashboard_component_highlights_today_and_upcoming_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 09:00', 'Africa/Addis_Ababa'));
        AcademicCalendarService::flushCache();

        $html = (string) $this->actingAs($this->admin)->blade('<x-academic-calendar />');

        $this->assertStringContainsString('2019 E.C. / 2026–2027 G.C.', $html);
        $this->assertStringContainsString('Week 10', $html);
        $this->assertStringContainsString('Examination in progress', $html);
        $this->assertStringContainsString('1st Quarter Final Examination', $html);
        $this->assertStringContainsString('2nd Quarter Begins', $html); // next major event
        $this->assertStringContainsString('In 5 days', $html);
    }

    /** @test */
    public function the_monthly_attendance_report_uses_calendar_school_days(): void
    {
        $attendance = $this->attendanceOn('2027-01-07'); // taken on Genna, before it was a holiday

        $response = $this->actingAs($this->admin)->get(route('admin.attendance.report', ['section_id' => $attendance->section_id, 'month' => 1, 'year' => 2027]));
        $response->assertOk();

        $schoolDays = $response->viewData('schoolDaySet');
        $this->assertCount(19, $schoolDays); // 21 weekdays in Jan 2027 − Genna − Timket
        $this->assertArrayNotHasKey('2027-01-07', $schoolDays);
        $this->assertArrayNotHasKey('2027-01-19', $schoolDays);
    }

    /** @test */
    public function attendance_register_shows_the_academic_context(): void
    {
        $division = Division::factory()->create();
        $grade = GradeLevel::factory()->create(['division_id' => $division->id]);
        $section = Section::factory()->create(['grade_level_id' => $grade->id]);

        $this->actingAs($this->admin)->get(route('admin.attendance.register', ['section_id' => $section->id, 'date' => '2027-01-07']))
            ->assertOk()
            ->assertSee('Tahsas 29, 2019 E.C.')
            ->assertSee('Ethiopian Christmas (Genna)')
            ->assertSee('does not list this as a school day');
    }

    /** @test */
    public function grading_screens_default_to_the_calendar_quarter(): void
    {
        Carbon::setTestNow(Carbon::parse('2027-03-10 10:00', 'Africa/Addis_Ababa'));
        AcademicCalendarService::flushCache();

        $q3 = Term::where('type', 'quarter')->where('term_number', 3)->firstOrFail();
        $this->assertSame($q3->id, app(AcademicCalendarService::class)->getDefaultGradingQuarter($this->year->id)->id);

        // An explicitly opened grading quarter still wins.
        $q2 = Term::where('type', 'quarter')->where('term_number', 2)->firstOrFail();
        $q2->update(['is_grading_open' => true]);
        $this->assertSame($q2->id, app(AcademicCalendarService::class)->getDefaultGradingQuarter($this->year->id)->id);
    }

    /** @test */
    public function switching_the_active_year_does_not_change_calendar_data(): void
    {
        $other = AcademicYear::create(['name' => 'Next', 'start_date' => '2027-09-13', 'end_date' => '2028-07-10', 'is_active' => false]);
        $before = [AcademicWeek::count(), AcademicEvent::count(), Term::count()];

        $this->actingAs($this->admin)->put(route('admin.academic-years.update', $other), [
            'name' => 'Next', 'ethiopian_year' => 2020, 'start_date' => '2027-09-13', 'end_date' => '2028-07-10', 'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame($before, [AcademicWeek::count(), AcademicEvent::count(), Term::count()]);
        $this->assertSame(2020, $other->fresh()->ethiopian_year);
        // Dates still resolve to the year they belong to, whichever is active.
        $this->assertSame('2019 E.C.', app(AcademicCalendarService::class)->getAcademicContext('2026-10-28')['academicYear']);
    }
}
