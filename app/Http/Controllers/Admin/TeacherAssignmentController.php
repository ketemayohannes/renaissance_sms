<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Actions\Teachers\AssignTeacherAssignments;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentController extends Controller
{
    public function index()
    {
        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        $assignments = TeacherAssignment::with(['teacher.employee', 'section.gradeLevel', 'subject'])
            ->where('academic_year_id', $activeYear->id)
            ->get()
            ->groupBy('teacher_id');

        $teachers = Employee::teachers()->active()->get();

        return view('admin.teacher-assignments.index', compact('assignments', 'teachers', 'activeYear'));
    }

    public function create()
    {
        $teachers = Employee::teachers()->active()->with('user')->get();
        $sections = Section::with('gradeLevel')->where('is_active', true)->get();
        $subjects = Subject::where('is_active', true)->get();
        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();

        return view('admin.teacher-assignments.create', compact('teachers', 'sections', 'subjects', 'activeYear'));
    }

    public function store(Request $request, AssignTeacherAssignments $assignAction)
    {
        $request->validate([
            'teacher_user_id' => 'required|exists:users,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.section_ids' => 'required|array|min:1',
            'assignments.*.section_ids.*' => 'required|exists:sections,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();

        try {
            $assignAction->execute(
                $request->teacher_user_id,
                $request->assignments,
                $activeYear
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('admin.teacher-assignments.index')
            ->with('success', 'Assignments updated successfully. Roles synced dynamically.');
    }

    public function destroy(TeacherAssignment $teacherAssignment)
    {
        $teacherAssignment->delete();
        return back()->with('success', 'Assignment removed.');
    }
}
