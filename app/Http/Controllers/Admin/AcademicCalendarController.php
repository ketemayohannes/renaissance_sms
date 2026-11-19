<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use App\Models\AcademicWeek;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\SchoolDayOverride;
use App\Models\Term;
use App\Services\AcademicCalendarChangeService;
use App\Services\AcademicCalendarImporter;
use App\Services\AcademicCalendarService;
use App\Services\AcademicCalendarValidator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Calendar administration (permission: manage academic calendar).
 * Every change goes through AcademicCalendarChangeService, which audits it and
 * reports impacts on existing records without modifying them.
 */
class AcademicCalendarController extends Controller
{
    public function __construct(
        private AcademicCalendarService $calendar,
        private AcademicCalendarChangeService $changes,
        private AcademicCalendarValidator $validator,
    ) {}

    public function index(Request $request)
    {
        $years = AcademicYear::orderByDesc('start_date')->get();
        $year = $this->selectedYear($request, $years);

        $weeks = $year ? AcademicWeek::with('term')->where('academic_year_id', $year->id)->orderBy('sequence')->get() : collect();
        $events = $year
            ? AcademicEvent::with('term')->where('academic_year_id', $year->id)
                ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
                ->when($request->boolean('review'), fn ($q) => $q->where('needs_review', true))
                ->orderBy('start_date')->orderByDesc('priority')->get()
            : collect();
        $overrides = $year ? SchoolDayOverride::where('academic_year_id', $year->id)->orderBy('date')->get() : collect();
        $terms = $year ? Term::where('academic_year_id', $year->id)->orderBy('type')->orderBy('term_number')->get() : collect();
        $issues = $year ? $this->validator->validate($year) : [];
        $termMismatch = collect($issues)->contains(fn ($i) => str_starts_with((string) $i['subject'], 'term:'));
        $dataFiles = collect(glob(database_path('data/academic_calendar_*.php')))->map(fn ($p) => basename($p))->values();

        return view('admin.academic-calendar.index', [
            'years' => $years,
            'year' => $year,
            'weeks' => $weeks,
            'events' => $events,
            'overrides' => $overrides,
            'terms' => $terms,
            'issues' => $issues,
            'termMismatch' => $termMismatch,
            'categories' => config('academic_calendar.categories'),
            'dataFiles' => $dataFiles,
            'stats' => $year ? [
                'weeks' => $weeks->where('week_type', '!=', 'break')->count(),
                'events' => $events->where('status', 'active')->count(),
                'school_days' => $weeks->isNotEmpty() ? $this->calendar->getWorkingDays($weeks->first()->start_date, $weeks->last()->end_date) : null,
                'review' => $events->where('needs_review', true)->count() + $weeks->where('needs_review', true)->count(),
            ] : null,
        ]);
    }

    // ─── Events ─────────────────────────────────────────────────────────────

    public function createEvent(Request $request)
    {
        $yearId = $request->integer('academic_year_id') ?: AcademicYear::active()->value('id');
        $year = AcademicYear::findOrFail($yearId);
        $date = $request->input('date', now()->toDateString());

        return view('admin.academic-calendar.event-form', $this->eventFormData($year, new AcademicEvent([
            'category' => 'holiday', 'priority' => 1, 'is_instructional' => false,
            'start_date' => $date, 'end_date' => $date,
        ])));
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $year = AcademicYear::findOrFail($request->integer('academic_year_id'));
        $result = $this->changes->saveEvent(null, $year, $this->validatedEvent($request, $year));

        return $this->back($year, 'Event created.', $result['impacts']);
    }

    public function editEvent(AcademicEvent $event)
    {
        $history = AuditLog::with('user')->where('auditable_type', AcademicEvent::class)
            ->where('auditable_id', $event->id)->latest()->limit(20)->get();

        return view('admin.academic-calendar.event-form', array_merge(
            $this->eventFormData($event->academicYear, $event),
            ['history' => $history]
        ));
    }

    public function updateEvent(Request $request, AcademicEvent $event): RedirectResponse
    {
        $year = $event->academicYear;
        $result = $this->changes->saveEvent($event, $year, $this->validatedEvent($request, $year));

        return $this->back($year, 'Event updated.', $result['impacts']);
    }

    public function cancelEvent(AcademicEvent $event): RedirectResponse
    {
        $result = $this->changes->setEventStatus($event, AcademicEvent::STATUS_CANCELLED);

        return $this->back($event->academicYear, "\"{$event->title}\" cancelled. It stays in the history and can be restored.", $result['impacts']);
    }

    public function restoreEvent(AcademicEvent $event): RedirectResponse
    {
        $result = $this->changes->setEventStatus($event, AcademicEvent::STATUS_ACTIVE);

        return $this->back($event->academicYear, "\"{$event->title}\" restored.", $result['impacts']);
    }

    public function destroyEvent(AcademicEvent $event): RedirectResponse
    {
        $year = $event->academicYear;

        try {
            $result = $this->changes->deleteEvent($event);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return $this->back($year, 'Event deleted.', $result['impacts']);
    }

    /** Clear (or set) the "needs review" flag after an administrator has checked an item. */
    public function reviewEvent(Request $request, AcademicEvent $event): RedirectResponse
    {
        $event->update(['needs_review' => $request->boolean('needs_review'), 'updated_by' => auth()->id()]);

        return back()->with('success', $event->needs_review ? 'Flagged for review.' : 'Marked as reviewed.');
    }

    // ─── Weeks ──────────────────────────────────────────────────────────────

    public function editWeek(AcademicWeek $week)
    {
        $week->load('academicYear');
        $quarters = Term::where('academic_year_id', $week->academic_year_id)->where('type', 'quarter')->orderBy('term_number')->get();
        $history = AuditLog::with('user')->where('auditable_type', AcademicWeek::class)
            ->where('auditable_id', $week->id)->latest()->limit(20)->get();

        return view('admin.academic-calendar.week-form', compact('week', 'quarters', 'history'));
    }

    public function storeWeek(Request $request): RedirectResponse
    {
        $year = AcademicYear::findOrFail($request->integer('academic_year_id'));
        $data = $this->validatedWeek($request, $year);
        $data['sequence'] = (int) AcademicWeek::where('academic_year_id', $year->id)->max('sequence') + 1;
        $week = AcademicWeek::create($data + ['academic_year_id' => $year->id]);
        $this->resequence($year);

        return $this->back($year, "Week added ({$week->ethiopian_range}).", []);
    }

    public function updateWeek(Request $request, AcademicWeek $week): RedirectResponse
    {
        $year = $week->academicYear;
        $data = $this->validatedWeek($request, $year, $week);
        $data['needs_review'] = $request->boolean('needs_review');
        $result = $this->changes->saveWeek($week, $data);
        $this->resequence($year);

        return $this->back($year, 'Week updated.', $result['impacts']);
    }

    public function destroyWeek(AcademicWeek $week): RedirectResponse
    {
        $year = $week->academicYear;
        if ($week->source_key) {
            return back()->with('error', 'Weeks from the official calendar cannot be deleted; edit them instead.');
        }
        $week->delete();
        $this->resequence($year);

        return $this->back($year, 'Week removed.', []);
    }

    /**
     * Create Monday–Friday weeks covering a quarter's dates: the quick way to set up
     * a future year without a data file. Weeks already in that range are kept.
     */
    public function generateWeeks(Request $request): RedirectResponse
    {
        $data = $request->validate(['term_id' => 'required|exists:terms,id']);
        $quarter = Term::where('type', 'quarter')->findOrFail($data['term_id']);
        $year = $quarter->academicYear;

        $existing = AcademicWeek::where('academic_year_id', $year->id)->get();
        $quarterStart = Carbon::parse($quarter->start_date->toDateString());
        $quarterEnd = Carbon::parse($quarter->end_date->toDateString());
        $monday = $quarterStart->copy()->startOfWeek(Carbon::MONDAY);
        $number = 0;
        $created = 0;

        while ($monday->lte($quarterEnd)) {
            $start = $monday->copy()->max($quarterStart);
            $friday = $monday->copy()->addDays(4)->min($quarterEnd);
            if ($start->lte($friday)) {
                $number++;
                $overlaps = $existing->contains(fn ($w) => $w->start_date->lte($friday) && $w->end_date->gte($start));
                if (! $overlaps) {
                    AcademicWeek::create([
                        'academic_year_id' => $year->id, 'term_id' => $quarter->id, 'week_number' => $number,
                        'sequence' => 5000 + $created, 'start_date' => $start->toDateString(), 'end_date' => $friday->toDateString(),
                        'working_days' => null, 'week_type' => 'instructional', 'description' => 'Regular classes',
                    ]);
                    $created++;
                }
            }
            $monday->addWeek();
        }
        $this->resequence($year);

        return $this->back($year, "{$created} week(s) generated for {$quarter->name}. Add exam windows, revision weeks and holidays as events.", []);
    }

    // ─── School-day overrides ───────────────────────────────────────────────

    public function storeOverride(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'date' => 'required|date',
            'is_school_day' => 'required|boolean',
            'reason' => 'required|string|max:255',
        ]);
        $year = AcademicYear::findOrFail($data['academic_year_id']);
        $result = $this->changes->saveOverride($year, $data['date'], (bool) $data['is_school_day'], $data['reason']);

        return $this->back($year, 'School-day override saved.', $result['impacts']);
    }

    public function destroyOverride(SchoolDayOverride $override): RedirectResponse
    {
        $year = $override->academicYear;
        $result = $this->changes->deleteOverride($override);

        return $this->back($year, 'Override removed.', $result['impacts']);
    }

    // ─── Terms & import ─────────────────────────────────────────────────────

    /** Move quarter (and semester) dates to match their calendar weeks. Audited; marks stay linked. */
    public function alignTerms(Request $request): RedirectResponse
    {
        $year = AcademicYear::findOrFail($request->integer('academic_year_id'));
        $weeks = AcademicWeek::where('academic_year_id', $year->id)->where('week_type', '!=', 'break')->get()->groupBy('term_id');
        $changed = 0;

        foreach (Term::where('academic_year_id', $year->id)->where('type', 'quarter')->get() as $quarter) {
            $qWeeks = $weeks->get($quarter->id);
            if (! $qWeeks) {
                continue;
            }
            $start = $qWeeks->min(fn ($w) => $w->start_date->toDateString());
            $end = $qWeeks->max(fn ($w) => $w->end_date->toDateString());
            if ($quarter->start_date?->toDateString() !== $start || $quarter->end_date?->toDateString() !== $end) {
                $this->changes->alignTermDates($quarter, $start, $end, 'Aligned with academic calendar weeks');
                $changed++;
            }
        }

        foreach (Term::with('quarters')->where('academic_year_id', $year->id)->where('type', 'semester')->get() as $semester) {
            if ($semester->quarters->isEmpty()) {
                continue;
            }
            $start = $semester->quarters->min(fn ($q) => $q->start_date->toDateString());
            $end = $semester->quarters->max(fn ($q) => $q->end_date->toDateString());
            if ($semester->start_date?->toDateString() !== $start || $semester->end_date?->toDateString() !== $end) {
                $this->changes->alignTermDates($semester, $start, $end, 'Aligned with its quarters');
                $changed++;
            }
        }

        $message = $changed
            ? "{$changed} term(s) aligned with the calendar. Marks and records remain linked to the same terms."
            : 'Terms already match the calendar.';

        return $this->back($year, $message, []);
    }

    public function import(Request $request, AcademicCalendarImporter $importer): RedirectResponse
    {
        $files = collect(glob(database_path('data/academic_calendar_*.php')))->map(fn ($p) => basename($p))->all();
        $data = $request->validate(['file' => ['required', Rule::in($files)], 'dry_run' => 'nullable|boolean']);

        try {
            $report = $importer->import(AcademicCalendarImporter::loadFile(database_path('data/'.$data['file'])), $request->boolean('dry_run'));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Import failed: '.$e->getMessage());
        }

        $summary = sprintf('%sImported %s: %d week(s) and %d event(s) created; %d week(s) and %d event(s) were already present.',
            $report['dry_run'] ? '[Dry run, nothing saved] ' : '', $report['year']->name,
            $report['created']['weeks'], $report['created']['events'], $report['unchanged']['weeks'], $report['unchanged']['events']);

        return redirect()->route('admin.academic-calendar.index', $report['dry_run'] ? [] : ['academic_year_id' => $report['year']->id])
            ->with('success', $summary)
            ->with('import_issues', $report['issues']);
    }

    /** Change history of calendar records (from the audit log). */
    public function history()
    {
        $logs = AuditLog::with('user')
            ->whereIn('auditable_type', [AcademicEvent::class, AcademicWeek::class, SchoolDayOverride::class, Term::class])
            ->latest()->paginate(30);

        return view('admin.academic-calendar.history', compact('logs'));
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function selectedYear(Request $request, $years): ?AcademicYear
    {
        if ($request->filled('academic_year_id')) {
            return $years->firstWhere('id', $request->integer('academic_year_id'));
        }

        return $years->firstWhere('is_active', true) ?? $years->first();
    }

    private function eventFormData(AcademicYear $year, AcademicEvent $event): array
    {
        return [
            'year' => $year,
            'event' => $event,
            'categories' => config('academic_calendar.categories'),
            'quarters' => Term::where('academic_year_id', $year->id)->where('type', 'quarter')->orderBy('term_number')->get(),
            'history' => collect(),
        ];
    }

    private function validatedEvent(Request $request, AcademicYear $year): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => ['required', Rule::in(array_keys(config('academic_calendar.categories')))],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('academic_year_id', $year->id)],
            'priority' => 'required|integer|between:0,2',
            'is_instructional' => 'nullable|boolean',
            'needs_review' => 'nullable|boolean',
            'review_note' => 'nullable|string|max:2000',
        ]);

        if (Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) > 366) {
            throw ValidationException::withMessages(['end_date' => 'An event cannot be longer than a year.']);
        }

        $data['is_instructional'] = $request->boolean('is_instructional');
        $data['needs_review'] = $request->boolean('needs_review');

        return $data;
    }

    private function validatedWeek(Request $request, AcademicYear $year, ?AcademicWeek $week = null): array
    {
        $data = $request->validate([
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('academic_year_id', $year->id)->where('type', 'quarter')],
            'week_number' => 'required|integer|between:1,60',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'working_days' => 'nullable|integer|between:0,7',
            'week_type' => 'required|in:instructional,break',
            'description' => 'nullable|string|max:255',
            'review_note' => 'nullable|string|max:2000',
        ]);

        $overlap = AcademicWeek::where('academic_year_id', $year->id)
            ->when($week, fn ($q) => $q->where('id', '!=', $week->id))
            ->whereDate('start_date', '<=', $data['end_date'])
            ->whereDate('end_date', '>=', $data['start_date'])
            ->first();
        if ($overlap) {
            throw ValidationException::withMessages(['start_date' => "These dates overlap the week {$overlap->ethiopian_range}."]);
        }

        return $data;
    }

    /** Keep sequence in chronological order. Two passes avoid unique-index clashes. */
    private function resequence(AcademicYear $year): void
    {
        $ids = AcademicWeek::where('academic_year_id', $year->id)->orderBy('start_date')->pluck('id');
        foreach ($ids as $i => $id) {
            AcademicWeek::whereKey($id)->update(['sequence' => 10000 + $i]);
        }
        foreach ($ids as $i => $id) {
            AcademicWeek::whereKey($id)->update(['sequence' => $i + 1]);
        }
        AcademicCalendarService::flushCache();
    }

    private function back(AcademicYear $year, string $message, array $impacts): RedirectResponse
    {
        return redirect()->route('admin.academic-calendar.index', ['academic_year_id' => $year->id])
            ->with('success', $message)
            ->with('calendar_impacts', $impacts);
    }
}
