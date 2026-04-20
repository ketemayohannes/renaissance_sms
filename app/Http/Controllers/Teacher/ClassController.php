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
            ->with(['section' => function($query) {
                $query->withCount(['enrollments' => function($q) {
                    $q->where('status', 'active');
                }]);
            }, 'section.gradeLevel', 'subject'])
            ->get();

        $academicYear = \App\Helpers\CachedData::activeAcademicYear();
        $openQuarter = $academicYear ? \App\Models\Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'quarter')
            ->where('is_grading_open', true)
            ->orderBy('start_date')
            ->first() : null;
            
        $openSemester = $academicYear ? \App\Models\Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'semester')
            ->where('is_grading_open', true)
            ->orderBy('start_date')
            ->first() : null;

        foreach ($assignments as $assignment) {
            $isElective = $assignment->subject->is_elective ?? false;
            $activeTerm = $isElective ? $openSemester : $openQuarter;
            $assignment->grading_status = $activeTerm ? 'Grading Active: ' . $activeTerm->name : 'Grading Closed';
        }

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
