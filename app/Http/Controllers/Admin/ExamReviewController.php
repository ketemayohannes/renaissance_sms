<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamReviewController extends Controller
{
    public function index()
    {
        $exams = ExamPaper::with(['teacher', 'subject', 'gradeLevel', 'term'])
            ->whereIn('status', ['submitted', 'under_review', 'approved', 'rejected'])
            ->latest('submitted_at')
            ->paginate(15);

        return view('admin.exams.index', compact('exams'));
    }

    public function show(ExamPaper $exam)
    {
        if ($exam->status === 'submitted') {
            $exam->update(['status' => 'under_review']);
        }
        
        $exam->load(['teacher', 'subject', 'gradeLevel', 'term', 'reviewer']);
        return view('admin.exams.show', compact('exam'));
    }

    public function review(Request $request, ExamPaper $exam)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_comments' => 'required_if:status,rejected|nullable|string',
        ]);

        $exam->update([
            'status' => $request->status,
            'review_comments' => $request->review_comments,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        $message = $request->status === 'approved' ? 'Exam paper approved.' : 'Exam paper sent back for revisions.';
        return redirect()->route('admin.exams.index')->with('success', $message);
    }

    public function destroy(ExamPaper $exam)
    {
        // Delete file from storage if exists
        if ($exam->file_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($exam->file_path);
        }

        $exam->delete();

        return redirect()->route('admin.exams.index')->with('success', 'Exam paper deleted successfully.');
    }
}
