<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;

class CommunicationController extends Controller
{
    public function notices()
    {
        $notices = Notice::active()
            ->whereIn('target_audience', ['Parent', 'All'])
            ->orderByDesc('publish_date')
            ->get();
        return view('parent.notices.index', compact('notices'));
    }

    public function showNotice($id)
    {
        $notice = Notice::find($id);
        if (!$notice || !$notice->is_active || !in_array($notice->target_audience, ['Parent', 'All'])) {
            return redirect()->route('parent.notices.index')
                ->with('error', 'This announcement is no longer available.');
        }
        return view('parent.notices.show', compact('notice'));
    }

    public function contactForm()
    {
        // List of linked students for the parent to choose which teacher to contact
        $user = auth()->user();
        $children = $user->linked_students->load(['currentEnrollment.section.homeroomTeacher', 'currentEnrollment.section.gradeLevel']);
        return view('parent.contact', compact('children'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        $user = auth()->user();
        // Guard against IDOR: Verify parent actually owns/links this child
        $isLinked = $user->guardianProfiles()
            ->where('student_id', $validated['student_id'])
            ->exists();
        if (!$isLinked) {
            abort(403, 'Unauthorized access to student.');
        }

        // Find the homeroom teacher for the selected student
        $student = Student::findOrFail($validated['student_id']);
        $teacher = $student->currentEnrollment?->section?->homeroomTeacher ?? null;
        if ($teacher) {
            // Simple email dispatch (placeholder). In real app use Mailable.
            Mail::raw($validated['message'], function ($mail) use ($teacher, $validated) {
                $mail->to($teacher->email)
                     ->subject($validated['subject']);
            });
        }
        return back()->with('success', 'Message sent to the teacher.');
    }
}
