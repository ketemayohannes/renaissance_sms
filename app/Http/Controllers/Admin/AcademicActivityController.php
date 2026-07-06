<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicActivity;
use App\Models\TeacherAssignment;
use App\Models\AssessmentTemplate;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Services\AcademicActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicActivityController extends Controller
{
    protected $service;

    public function __construct(AcademicActivityService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $user = Auth::user();
        $activities = AcademicActivity::with(['teacherAssignment.section', 'teacherAssignment.subject', 'assessmentTemplate'])
            ->latest()
            ->paginate(15);

        return view('admin.activities.index', compact('activities'));
    }

    public function create()
    {
        $teacherAssignments = TeacherAssignment::with(['section.gradeLevel', 'subject'])->get();
        $academicYears = AcademicYear::where('is_active', true)->get();
        $gradeLevels = GradeLevel::with(['sections', 'subjects'])->get();
        $subjects = Subject::where('is_active', true)->get();
        
        
        $mappedAssignments = $teacherAssignments->map(fn($ta) => [
            'id' => $ta->id,
            'grade_id' => $ta->section->grade_level_id,
            'grade_name' => $ta->section->gradeLevel->name,
            'section_id' => $ta->section_id,
            'section_name' => $ta->section->name,
            'subject_id' => $ta->subject_id,
            'subject_name' => $ta->subject->name,
        ])->values()->toArray();

        $mappedGrades = $gradeLevels->map(fn($g) => [
            'id' => $g->id, 
            'name' => $g->name, 
            'sections' => $g->sections ? $g->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray() : [],
            'subjects' => $g->subjects ? $g->subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray() : []
        ])->values()->toArray();
        
        
        $mappedSubjects = $subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toArray();

        return view('admin.activities.create', compact('mappedAssignments', 'academicYears', 'mappedGrades', 'mappedSubjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_assignment_id' => 'required|exists:teacher_assignments,id',
            'assessment_template_id' => 'nullable|exists:assessment_templates,id',
            'type' => 'required|in:homework,assignment,exam',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'start_date' => 'nullable|date',
            'max_score' => 'required|numeric|min:0',
            'is_published' => 'boolean',
            'config' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $assignment = TeacherAssignment::findOrFail($data['teacher_assignment_id']);
        
        $data['created_by'] = Auth::id();
        $data['division_id'] = $assignment->section->gradeLevel->division_id ?? 1; // Fallback
        $data['academic_year_id'] = $assignment->academic_year_id;

        $activity = $this->service->createActivity($data);

        return redirect()->route('admin.activities.index')->with('success', 'Activity created successfully.');
    }

    public function show(AcademicActivity $activity)
    {
        $activity->load(['submissions.student', 'attachments', 'questions']);
        return view('admin.activities.show', compact('activity'));
    }

    public function evaluate(AcademicActivity $activity)
    {
        $activity->load(['submissions.student', 'questions']);
        return view('admin.activities.evaluate', compact('activity'));
    }

    public function storeEvaluation(Request $request, AcademicActivity $activity)
    {
        $data = $request->validate([
            'submissions' => 'required|array',
            'submissions.*.student_id' => 'required|exists:students,id',
            'submissions.*.score' => 'nullable|numeric|min:0|max:' . $activity->max_score,
            'submissions.*.feedback' => 'nullable|string',
        ]);

        foreach ($data['submissions'] as $submissionData) {
            if ($submissionData['score'] !== null) {
                $submission = ActivitySubmission::updateOrCreate(
                    ['academic_activity_id' => $activity->id, 'student_id' => $submissionData['student_id']],
                    ['status' => 'graded'] // Placeholder to find/create
                );
                
                $this->service->evaluateSubmission($submission, [
                    'score' => $submissionData['score'],
                    'feedback' => $submissionData['feedback'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.activities.show', $activity)->with('success', 'Evaluations synchronized successfully.');
    }

    public function getTemplates(Request $request)
    {
        $assignmentId = $request->get('teacher_assignment_id');
        $gradeId = $request->get('grade_id');
        $subjectId = $request->get('subject_id');

        if ($assignmentId) {
            $assignment = TeacherAssignment::find($assignmentId);
            if ($assignment) {
                $gradeId = $assignment->section->grade_level_id;
                $subjectId = $assignment->subject_id;
            }
        }

        if (!$gradeId || !$subjectId) {
            return response()->json([]);
        }

        $templates = AssessmentTemplate::forGradeSubject($gradeId, $subjectId)
            ->with('term')
            ->whereHas('assessmentType', function($q) {
                $q->whereNotIn('code', ['FINAL', 'TERM_TOTAL']);
            })
            ->where('is_active', true)
            ->get();

        return response()->json($templates);
    }
}
