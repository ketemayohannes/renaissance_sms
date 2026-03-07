<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\MarkAttendanceRequest;
use App\Services\TeacherService;
use App\Services\AttendanceService;
use App\Services\AttendanceAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeroomController extends Controller
{
    protected $teacherService;
    protected $attendanceService;
    protected $alertService;

    public function __construct(
        TeacherService $teacherService, 
        AttendanceService $attendanceService,
        AttendanceAlertService $alertService
    ) {
        $this->teacherService = $teacherService;
        $this->attendanceService = $attendanceService;
        $this->alertService = $alertService;
    }

    /**
     * Display the homeroom roster.
     */
    public function index()
    {
        $user = Auth::user();
        $section = $this->teacherService->getHomeroomSection($user);

        if (!$section) {
            return redirect()->route('teacher.dashboard')->with('error', 'You are not assigned as a Homeroom Teacher for the current academic year.');
        }

        $students = $section->enrollments()->with('student.user')->where('status', 'active')->get();
        $atRiskStudents = $this->alertService->getAtRiskStudents($section);

        return view('teacher.homeroom.index', compact('section', 'students', 'atRiskStudents'));
    }

    /**
     * Show the attendance marking interface.
     */
    public function attendance(Request $request)
    {
        $user = Auth::user();
        $section = $this->teacherService->getHomeroomSection($user);

        if (!$section) {
            return redirect()->route('teacher.dashboard')->with('error', 'Unauthorized access.');
        }

        $date = $request->get('date', now()->format('Y-m-d'));
        $students = $section->enrollments()->with(['student.user', 'student.attendance' => function($query) use ($date, $section) {
            $query->where('attendance_date', $date)->where('section_id', $section->id);
        }])->where('status', 'active')->get();

        return view('teacher.homeroom.attendance', compact('section', 'students', 'date'));
    }

    /**
     * Store attendance records.
     */
    public function storeAttendance(MarkAttendanceRequest $request)
    {
        $this->attendanceService->saveAttendance(
            $request->section_id,
            $request->attendance_date,
            $request->attendance,
            $request->remarks ?? []
        );

        return redirect()->back()->with('success', 'Attendance marked successfully for ' . $request->attendance_date);
    }
}
