<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use App\Models\GradeLevel;
use App\Models\StudentAttendance;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(\App\Services\AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $sections = $this->attendanceService->getActiveSectionsSummary($academicYear);
        $gradeLevels = GradeLevel::orderBy('sort_order')->get();
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
        $date = \Carbon\Carbon::parse($request->date)->toDateString();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $students = Student::whereHas('enrollments', function($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
              ->where('academic_year_id', $academicYear->id)
              ->where('status', 'active');
        })->orderBy('first_name')->get();

        $existingAttendance = StudentAttendance::where('section_id', $section->id)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        return view('admin.attendance.register', compact('section', 'date', 'students', 'existingAttendance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'remarks' => 'nullable|array',
        ]);

        $sectionId = $request->section_id;
        $date = \Carbon\Carbon::parse($request->date)->toDateString();
        $userId = auth()->id();

        DB::transaction(function() use ($request, $sectionId, $date, $userId) {
            foreach ($request->attendance as $studentId => $status) {
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'attendance_date' => $date,
                    ],
                    [
                        'section_id' => $sectionId,
                        'status' => $status,
                        'remarks' => $request->remarks[$studentId] ?? null,
                        'marked_by' => $userId,
                    ]
                );
            }
        });

        return redirect()->route('admin.attendance.index')
            ->with('success', 'Attendance marked successfully for ' . $date);
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

        $students = Student::whereHas('enrollments', function($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
              ->where('academic_year_id', $academicYear->id)
              ->where('status', 'active');
        })->orderBy('first_name')->get();

        $attendanceData = StudentAttendance::where('section_id', $section->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->groupBy('student_id');

        return view('admin.attendance.report', compact('section', 'month', 'year', 'students', 'attendanceData'));
    }
}
