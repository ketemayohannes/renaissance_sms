<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicActivity;
use App\Models\ActivitySubmission;
use App\Services\AcademicActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    protected $service;

    public function __construct(AcademicActivityService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $student = Auth::user()->student;
        
        // Fetch activities for the student's section
        $activities = AcademicActivity::whereHas('teacherAssignment', function($q) use ($student) {
            $q->where('section_id', $student->currentEnrollment()->section_id);
        })
        ->with(['teacherAssignment.subject', 'submissions' => function($q) use ($student) {
            $q->where('student_id', $student->id);
        }])
        ->where('is_published', true)
        ->latest()
        ->get();

        return view('student.activities.index', compact('activities'));
    }

    public function show(AcademicActivity $activity)
    {
        $this->authorizeActivity($activity);
        $student = Auth::user()->student;
        $submission = $activity->recordFor($student->id); // Custom helper or query
        
        $activity->load(['attachments', 'teacherAssignment.subject']);
        
        return view('student.activities.show', compact('activity', 'submission'));
    }

    public function submit(Request $request, AcademicActivity $activity)
    {
        $this->authorizeActivity($activity);
        $student = Auth::user()->student;
        
        $data = $request->validate([
            'attachments.*' => 'required|file|max:10240',
        ]);

        $this->service->submitWork($activity, $student->id, $data);

        return redirect()->back()->with('success', 'Work submitted successfully.');
    }

    public function takeExam(AcademicActivity $activity)
    {
        $this->authorizeActivity($activity);
        $student = Auth::user()->student;
        $activity->load('questions');
        
        return view('student.activities.exam', compact('activity'));
    }

    public function submitExam(Request $request, AcademicActivity $activity)
    {
        $this->authorizeActivity($activity);
        $student = Auth::user()->student;
        $answers = $request->input('answers', []);
        
        $totalScore = 0;
        $activity->load('questions');

        foreach ($activity->questions as $question) {
            $studentAnswer = $answers[$question->id] ?? null;
            
            // Auto-grading for objective types
            if (in_array($question->type, ['mcq', 'tf'])) {
                if ($studentAnswer == $question->correct_answer) {
                    $totalScore += $question->points;
                }
            }
            // Subjective questions start with 0 and remain for teacher review
        }

        // Create/Update Submission
        $submission = ActivitySubmission::updateOrCreate(
            ['academic_activity_id' => $activity->id, 'student_id' => $student->id],
            [
                'submitted_at' => now(),
                'status' => 'submitted',
                'score' => $totalScore, // Initial score (may change after manual grading)
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'config' => ['answers' => $answers] // Store answers in config
            ]
        );

        // Sync to Gradebook if applicable (initial sync)
        if ($activity->assessment_template_id) {
            $this->service->syncMarkToGradebook($submission);
        }

        return redirect()->route('student.activities.index')->with('success', 'Examination submitted successfully. Your objective score has been recorded.');
    }

    private function authorizeActivity(AcademicActivity $activity)
    {
        $student = Auth::user()->student;
        if (!$student) {
            abort(403, 'Unauthorized action.');
        }

        $enrollment = $student->currentEnrollment;
        if (!$enrollment) {
            abort(403, 'Student is not enrolled in any section.');
        }

        if (!$activity->teacherAssignment || $activity->teacherAssignment->section_id !== $enrollment->section_id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
