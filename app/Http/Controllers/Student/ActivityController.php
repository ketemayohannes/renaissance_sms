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
        $activities = AcademicActivity::whereHas('teacherAssignment', function ($q) use ($student) {
            $q->where('section_id', $student->currentEnrollment()->section_id);
        })
            ->with(['teacherAssignment.subject', 'submissions' => function ($q) use ($student) {
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

        if ($activity->type === 'exam') {
            return redirect()->route('student.activities.show', $activity)
                ->with('error', 'This activity is an online exam. Complete it from the exam page.');
        }

        if ($activity->recordFor($student->id)?->status === 'graded') {
            return redirect()->route('student.activities.show', $activity)
                ->with('error', 'This work has already been graded and can no longer be changed.');
        }

        // Allow-list of document/image types only: anything executable or renderable as a
        // web page (php, html, svg, js…) is refused.
        $data = $request->validate([
            'attachments.*' => 'required|file|max:10240|mimes:'.implode(',', AcademicActivityService::ALLOWED_UPLOAD_EXTENSIONS),
        ]);

        $this->service->submitWork($activity, $student->id, $data);

        return redirect()->back()->with('success', 'Work submitted successfully.');
    }

    public function takeExam(AcademicActivity $activity)
    {
        $this->authorizeActivity($activity);
        $student = Auth::user()->student;

        if ($redirect = $this->refuseExamAttempt($activity, $student->id)) {
            return $redirect;
        }

        $activity->load('questions');

        // Stamp the server-side start time once (on first open) so the time
        // limit can be enforced regardless of the client-side countdown.
        ActivitySubmission::firstOrCreate(
            ['academic_activity_id' => $activity->id, 'student_id' => $student->id],
            ['status' => 'pending', 'started_at' => now(), 'attempt_number' => 1]
        );

        return view('student.activities.exam', compact('activity'));
    }

    public function submitExam(Request $request, AcademicActivity $activity)
    {
        $this->authorizeActivity($activity);
        $student = Auth::user()->student;

        // One attempt only: a second submission would overwrite the recorded score and
        // wipe any manual grading the teacher has done.
        if ($redirect = $this->refuseExamAttempt($activity, $student->id)) {
            return $redirect;
        }

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

        // Load (or start) the submission so we can read the recorded start time.
        $submission = ActivitySubmission::firstOrNew(
            ['academic_activity_id' => $activity->id, 'student_id' => $student->id]
        );

        // Enforce the time limit server-side: lateness is decided from the
        // recorded start time, not the client countdown (which can be bypassed).
        $status = 'submitted';
        $timeLimit = (int) ($activity->config['time_limit'] ?? 0); // minutes
        if ($submission->started_at && $timeLimit > 0) {
            $deadline = $submission->started_at->copy()->addMinutes($timeLimit)->addSeconds(30); // 30s grace
            if (now()->greaterThan($deadline)) {
                $status = 'late';
            }
        }

        $submission->fill([
            'started_at' => $submission->started_at ?? now(),
            'submitted_at' => now(),
            'answers' => $answers, // keyed by question id, for teacher review
            'status' => $status,
            'score' => $totalScore, // Initial score (may change after manual grading)
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        $submission->save();

        // Sync to Gradebook if applicable (initial sync)
        if ($activity->assessment_template_id) {
            $this->service->syncMarkToGradebook($submission);
        }

        $message = $status === 'late'
            ? 'Examination submitted after the time limit and flagged as late. Your objective score has been recorded.'
            : 'Examination submitted successfully. Your objective score has been recorded.';

        return redirect()->route('student.activities.index')->with('success', $message);
    }

    private function authorizeActivity(AcademicActivity $activity)
    {
        $student = Auth::user()->student;
        if (! $student) {
            abort(403, 'Unauthorized action.');
        }

        $enrollment = $student->currentEnrollment;
        if (! $enrollment) {
            abort(403, 'Student is not enrolled in any section.');
        }

        if (! $activity->teacherAssignment || $activity->teacherAssignment->section_id !== $enrollment->section_id) {
            abort(403, 'Unauthorized action.');
        }

        // Drafts are invisible to students until the teacher publishes them.
        if (! $activity->is_published) {
            abort(404);
        }
    }

    /**
     * Returns a redirect when the student may not open or submit this exam: it is not an
     * exam, it has not started yet, or the student has already submitted it.
     */
    private function refuseExamAttempt(AcademicActivity $activity, int $studentId)
    {
        if ($activity->type !== 'exam') {
            abort(404);
        }

        if ($activity->start_date && now()->lessThan($activity->start_date)) {
            return redirect()->route('student.activities.show', $activity)
                ->with('error', 'This exam has not opened yet.');
        }

        if ($activity->recordFor($studentId)?->submitted_at) {
            return redirect()->route('student.activities.show', $activity)
                ->with('error', 'You have already submitted this exam.');
        }

        return null;
    }
}
