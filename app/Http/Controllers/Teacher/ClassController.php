<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    /**
     * Display a listing of the teacher's assigned classes.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Fetch teacher assignments with related section, grade level, and subject
        $assignments = $user->teacherAssignments()
            ->with(['section.gradeLevel', 'subject'])
            ->get();

        return view('teacher.classes.index', compact('assignments'));
    }

    /**
     * Show details of a specific class (section + subject).
     */
    public function show($id)
    {
        $assignment = Auth::user()->teacherAssignments()
            ->with(['section.enrollments.student.user', 'subject', 'section.gradeLevel'])
            ->findOrFail($id);
            
        return view('teacher.classes.show', compact('assignment'));
    }
}
