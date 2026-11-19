<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveAttendanceRequest;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\AcademicCalendarService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService, protected AcademicCalendarService $calendar)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sections = $this->attendanceService->getActiveSectionsSummary($academicYear);

        // Eager load active sections for grade levels (sections are shared across years)
        $gradeLevels = GradeLevel::with(['sections' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('name');
        }])->orderBy('sort_order')->get();

        $today = now()->format('Y-m-d');

        return view('admin.attendance.index', compact('gradeLevels', 'academicYear', 'sections', 'today'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
        ]);

        $section = Section::with('gradeLevel')->findOrFail($request->section_id);
        $date = Carbon::parse($request->date)->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $students = Student::whereHas('enrollments', function ($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'active');
        })->orderBy('first_name')->get();

        $existingAttendance = StudentAttendance::where('section_id', $section->id)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        // Which week/quarter this date belongs to, and whether school is in session.
        $academicContext = $this->calendar->getAcademicContext($date);

        return view('admin.attendance.register', compact('section', 'date', 'students', 'existingAttendance', 'academicContext'));
    }

    public function store(SaveAttendanceRequest $request)
    {
        $this->attendanceService->saveAttendance(
            $request->section_id,
            $request->date,
            $request->attendance,
            $request->remarks ?? []
        );

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Attendance marked successfully for '.$request->date);
    }

    public function report(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer',
        ]);

        $section = Section::with('gradeLevel')->findOrFail($request->section_id);
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $academicYear = AcademicYear::where('is_active', true)->first();

        $students = Student::whereHas('enrollments', function ($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'active');
        })->orderBy('first_name')->get();

        $attendanceData = StudentAttendance::where('section_id', $section->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->groupBy('student_id');

        // Scheduled school days for the month come from the academic calendar.
        $monthStart = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
        $schoolDaySet = $this->calendar->getSchoolDays($monthStart, $monthEnd)->flip()->all();
        $nonSchoolReasons = [];
        foreach (CarbonPeriod::create($monthStart, $monthEnd) as $day) {
            if (! isset($schoolDaySet[$day->toDateString()])) {
                $status = $this->calendar->getDayStatus($day);
                $nonSchoolReasons[$day->toDateString()] = $status['reason'] ?? $status['day_type'];
            }
        }

        return view('admin.attendance.report', compact('section', 'month', 'year', 'students', 'attendanceData', 'schoolDaySet', 'nonSchoolReasons'));
    }
}
