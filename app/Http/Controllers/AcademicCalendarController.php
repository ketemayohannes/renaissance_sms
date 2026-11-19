<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Services\AcademicCalendarService;
use App\Support\EthiopianCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only academic calendar API for every signed-in user and for other modules'
 * front-ends. The data is school-wide (no student or staff records).
 */
class AcademicCalendarController extends Controller
{
    public function __construct(private AcademicCalendarService $calendar) {}

    /** GET /academic-calendar/context?date=Y-m-d */
    public function context(Request $request): JsonResponse
    {
        $request->validate(['date' => 'nullable|date']);

        return response()->json($this->calendar->getAcademicContext($request->input('date')));
    }

    /** GET /academic-calendar/summary — dashboard header data for today. */
    public function summary(): JsonResponse
    {
        return response()->json($this->calendar->getDashboardSummary());
    }

    /** GET /academic-calendar/month?system=gregorian|ethiopian&year=&month= */
    public function month(Request $request): JsonResponse
    {
        $data = $request->validate([
            'system' => 'nullable|in:gregorian,ethiopian',
            'year' => 'nullable|integer|between:1900,2200',
            'month' => 'nullable|integer|between:1,13',
        ]);

        $system = $data['system'] ?? 'gregorian';
        $today = EthiopianCalendar::today();

        if ($system === 'ethiopian') {
            $eth = EthiopianCalendar::fromGregorian($today);
            $year = (int) ($data['year'] ?? $eth['year']);
            $month = (int) ($data['month'] ?? $eth['month']);
        } else {
            $year = (int) ($data['year'] ?? $today->year);
            $month = (int) ($data['month'] ?? $today->month);
            if ($month > 12) {
                return response()->json(['message' => 'Gregorian months run 1–12.'], 422);
            }
        }

        return response()->json($this->calendar->getMonthGrid($system, $year, $month, $today));
    }

    /** GET /academic-calendar/day?date= — the event panel for one selected date. */
    public function day(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->input('date');

        return response()->json([
            'context' => $this->calendar->getAcademicContext($date),
            'events' => $this->calendar->getEventsByDate($date)->values(),
            'gregorian' => EthiopianCalendar::date($date)->format('l, F j, Y'),
            'ethiopian' => EthiopianCalendar::format($date),
        ]);
    }

    /** GET /academic-calendar/events?start=&end=&categories[]= */
    public function events(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'categories' => 'nullable|array',
            'categories.*' => 'string|in:'.implode(',', array_keys(config('academic_calendar.categories'))),
        ]);

        if (EthiopianCalendar::date($data['start'])->diffInDays(EthiopianCalendar::date($data['end'])) > 400) {
            return response()->json(['message' => 'Date range is limited to about one year.'], 422);
        }

        return response()->json($this->calendar->getEventsByDateRange($data['start'], $data['end'], $data['categories'] ?? null)->values());
    }

    /** GET /academic-calendar/upcoming?days=30 */
    public function upcoming(Request $request): JsonResponse
    {
        $request->validate(['days' => 'nullable|integer|between:1,366', 'all' => 'nullable|boolean']);

        return response()->json($this->calendar->getUpcomingEvents((int) $request->input('days', config('academic_calendar.upcoming_days')), null, $request->boolean('all'))->values());
    }

    /** GET /academic-calendar/working-days?start=&end= */
    public function workingDays(Request $request): JsonResponse
    {
        $data = $request->validate(['start' => 'required|date', 'end' => 'required|date|after_or_equal:start']);
        if (EthiopianCalendar::date($data['start'])->diffInDays(EthiopianCalendar::date($data['end'])) > 400) {
            return response()->json(['message' => 'Date range is limited to about one year.'], 422);
        }

        $days = $this->calendar->getSchoolDays($data['start'], $data['end']);

        return response()->json(['start' => $data['start'], 'end' => $data['end'], 'count' => $days->count(), 'dates' => $days]);
    }

    /** GET /academic-calendar/exam-periods?quarter_id= */
    public function examPeriods(Request $request): JsonResponse
    {
        $request->validate(['quarter_id' => 'nullable|integer|exists:terms,id', 'academic_year_id' => 'nullable|integer|exists:academic_years,id']);
        $quarter = $request->filled('quarter_id') ? Term::find($request->integer('quarter_id')) : null;

        return response()->json([
            'exams' => $this->calendar->getExamPeriods($quarter, $request->integer('academic_year_id') ?: null)->values(),
            'revision' => $this->calendar->getRevisionPeriods($quarter, $request->integer('academic_year_id') ?: null)->values(),
        ]);
    }
}
