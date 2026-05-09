<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamPaper;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamPaperController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found.');
        }

        $exams = ExamPaper::with(['subject', 'gradeLevel', 'term'])
            ->where('teacher_id', $employee->id)
            ->latest()
            ->paginate(15);

        return view('teacher.exams.index', compact('exams'));
    }

    public function create()
    {
        $assignments = TeacherAssignment::with(['section.gradeLevel', 'subject'])
            ->where('teacher_id', Auth::id())
            ->get()
            ->unique(function ($assignment) {
                return $assignment->subject_id . '-' . $assignment->section->grade_level_id;
            });

        $activeYear = \App\Helpers\CachedData::activeAcademicYear();
        $academicYears = AcademicYear::where('is_active', true)->get();
        $terms = Term::where('academic_year_id', $activeYear?->id)->get();

        return view('teacher.exams.create', compact('assignments', 'academicYears', 'terms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'term_id' => 'required|exists:terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'type' => 'required|in:text,upload',
            'content' => 'required_if:type,text',
            'file' => 'required_if:type,upload|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $employee = Auth::user()->employee;
        
        $data = $request->only(['title', 'subject_id', 'grade_level_id', 'term_id', 'academic_year_id', 'type', 'content']);
        $data['teacher_id'] = $employee->id;
        $data['status'] = $request->has('submit') ? 'submitted' : 'draft';
        
        if ($request->has('submit')) {
            $data['submitted_at'] = now();
        }

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('exams', 'local');
            $data['file_path'] = $path;
        }

        ExamPaper::create($data);

        return redirect()->route('teacher.exams.index')->with('success', 'Exam paper saved successfully.');
    }

    public function show(ExamPaper $exam)
    {
        if ($exam->teacher_id !== Auth::user()->employee->id) {
            abort(403);
        }

        return view('teacher.exams.show', compact('exam'));
    }

    public function edit(ExamPaper $exam)
    {
        if ($exam->teacher_id !== Auth::user()->employee->id) {
            abort(403);
        }

        if (!in_array($exam->status, ['draft', 'rejected'])) {
            return redirect()->route('teacher.exams.index')->with('error', 'Only drafts or rejected papers can be edited.');
        }

        $assignments = TeacherAssignment::with(['section.gradeLevel', 'subject'])
            ->where('teacher_id', Auth::id())
            ->get()
            ->unique(function ($assignment) {
                return $assignment->subject_id . '-' . $assignment->section->grade_level_id;
            });

        $activeYear = \App\Helpers\CachedData::activeAcademicYear();
        $academicYears = AcademicYear::where('is_active', true)->get();
        $terms = Term::where('academic_year_id', $activeYear?->id)->get();

        return view('teacher.exams.edit', compact('exam', 'assignments', 'academicYears', 'terms'));
    }

    public function update(Request $request, ExamPaper $exam)
    {
        if ($exam->teacher_id !== Auth::user()->employee->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'term_id' => 'required|exists:terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'type' => 'required|in:text,upload',
            'content' => 'required_if:type,text',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $data = $request->only(['title', 'subject_id', 'grade_level_id', 'term_id', 'academic_year_id', 'type', 'content']);
        
        if ($request->has('submit')) {
            $data['status'] = 'submitted';
            $data['submitted_at'] = now();
        }

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($exam->file_path) {
                Storage::disk('local')->delete($exam->file_path);
            }
            $path = $request->file('file')->store('exams', 'local');
            $data['file_path'] = $path;
        }

        $exam->update($data);

        return redirect()->route('teacher.exams.index')->with('success', 'Exam paper updated successfully.');
    }

    public function download(ExamPaper $exam)
    {
        if ($exam->teacher_id !== Auth::user()->employee?->id && !Auth::user()->hasAnyRole(['Super Admin', 'Vice Principal'])) {
            abort(403);
        }

        if (!$exam->file_path) {
            abort(404);
        }

        return Storage::disk('local')->download($exam->file_path, $exam->title . '.' . pathinfo($exam->file_path, PATHINFO_EXTENSION));
    }

    public function destroy(ExamPaper $exam)
    {
        if ($exam->teacher_id !== Auth::user()->employee->id) {
            abort(403);
        }

        if (!in_array($exam->status, ['draft', 'rejected'])) {
            return redirect()->route('teacher.exams.index')->with('error', 'Only drafts or rejected papers can be deleted.');
        }

        if ($exam->file_path) {
            Storage::disk('local')->delete($exam->file_path);
        }

        $exam->delete();

        return redirect()->route('teacher.exams.index')->with('success', 'Exam paper deleted successfully.');
    }
}
