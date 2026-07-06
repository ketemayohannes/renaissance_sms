<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveAttendanceRequest;
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

        $date = now()->format('Y-m-d');
        // Eager load everything needed for the roster
        $students = $section->enrollments()
            ->with([
                'student.user', 
                'student.primaryGuardian',
                'student.attendance' => function($query) use ($date, $section) {
                    $query->whereDate('attendance_date', $date)->where('section_id', $section->id);
                }
            ])
            ->where('status', 'active')
            ->get();
            
        $atRiskStudents = $this->alertService->getAtRiskStudents($section);

        // Pre-calculate stats to ensure accuracy in the view
        $stats = [
            'total' => $students->count(),
            'male' => $students->filter(function($e) {
                $g = strtoupper(optional($e->student)->gender);
                return $g === 'M' || $g === 'MALE';
            })->count(),
            'female' => $students->filter(function($e) {
                $g = strtoupper(optional($e->student)->gender);
                return $g === 'F' || $g === 'FEMALE';
            })->count(),
            'at_risk' => $atRiskStudents->count()
        ];

        return view('teacher.homeroom.index', compact('section', 'students', 'atRiskStudents', 'stats'));
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
            $query->whereDate('attendance_date', $date)->where('section_id', $section->id);
        }])->where('status', 'active')->get();

        return view('teacher.homeroom.attendance', compact('section', 'students', 'date'));
    }

    /**
     * Store attendance records.
     */
    public function storeAttendance(SaveAttendanceRequest $request)
    {
        $this->attendanceService->saveAttendance(
            $request->section_id,
            $request->attendance_date,
            $request->attendance,
            $request->remarks ?? []
        );

        return redirect()->back()->with('success', 'Attendance marked successfully for ' . $request->attendance_date);
    }

    /**
     * Display the behavior and protocol entry interface.
     */
    public function behavior(Request $request)
    {
        $user = Auth::user();
        $section = $this->teacherService->getHomeroomSection($user);

        if (!$section) {
            return redirect()->route('teacher.dashboard')->with('error', 'You are not assigned as a Homeroom Teacher.');
        }

        $academicYear = \App\Models\AcademicYear::active()->first();
        
        // Find current term based on date, or fallback to first quarter of the year
        $defaultTermId = \App\Models\Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'quarter')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->value('id') ?? \App\Models\Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'quarter')
            ->orderBy('start_date')
            ->first()?->id;

        $termId = $request->get('term_id', $defaultTermId);
        
        if (!$termId) {
            return back()->with('error', 'No quarterly terms defined for this year.');
        }

        $term = \App\Models\Term::findOrFail($termId);

        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivot('status', 'active')
            ->orderBy('students.first_name')
            ->get();

        $records = \App\Models\StudentTermRecord::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $term->id)
            ->get()
            ->keyBy('student_id');

        $terms = \App\Models\Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'quarter')
            ->orderBy('start_date')
            ->get();

        return view('teacher.homeroom.behavior', compact('section', 'students', 'records', 'academicYear', 'term', 'terms'));
    }

    /**
     * Store behavior and protocol records.
     */
    public function storeBehavior(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'records' => 'array',
            'records.*.conduct' => 'nullable|string|in:A,B,C',
            'records.*.absent' => 'nullable|integer|min:0',
            'records.*.comment' => 'nullable|string',
        ]);

        $term = \App\Models\Term::findOrFail($request->term_id);
        if (!$term->is_grading_open) {
            if (!Auth::user()->hasRole(['Super Admin', 'Principal'])) {
                return back()->with('error', 'Entry for this term is currently closed.');
            }
        }

        $section = $this->teacherService->getHomeroomSection(Auth::user());
        
        if (!$section) {
            abort(403, 'Unauthorized');
        }

        $reportCardService = app(\App\Services\ReportCardService::class);
        
        $reportCardService->storeDataEntry(
            (int)$request->term_id, 
            (int)$request->academic_year_id, 
            $request->records ?? [],
            $section
        );

        return back()->with('success', 'Behavior and attendance details saved successfully.');
    }
}
