<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use App\Models\Term;

class DashboardController extends Controller
{
    protected $teacherService;

    public function __construct(\App\Services\TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    public function index()
    {
        $user = Auth::user();
        $metrics = $this->teacherService->getDashboardMetrics($user);
        
        $activeYear = AcademicYear::active()->first();
        
        $assignments = TeacherAssignment::where('teacher_id', $user->id)
            ->where('academic_year_id', $activeYear->id)
            ->with(['section.gradeLevel', 'subject'])
            ->get();

        $subjects = $assignments->pluck('subject')->unique('id')->values();

        $terms = Term::where('academic_year_id', $activeYear->id)
            ->where('type', 'quarter')
            ->orderBy('start_date')
            ->get();
        
        return view('teacher.dashboard', compact('metrics', 'assignments', 'terms', 'subjects'));
    }

    public function getChartData(Request $request)
    {
        $assignmentId = $request->input('assignment_id');
        $termId = $request->input('term_id');

        if (!$assignmentId || !$termId) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $distribution = $this->teacherService->getPerformanceDistribution(Auth::user(), $assignmentId, $termId);

        return response()->json($distribution);
    }

    public function getEfficiencyData(Request $request)
    {
        $termId = $request->input('term_id');
        $subjectId = $request->input('subject_id');

        if (!$termId) {
            return response()->json(['error' => 'Missing parameter: term_id'], 400);
        }

        $data = $this->teacherService->getOverallEfficiency(Auth::user(), $termId, $subjectId);

        return response()->json($data);
    }
}
