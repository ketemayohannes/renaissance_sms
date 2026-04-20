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
    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        
        $query = Employee::teachers()->active()->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('first_name')->paginate(20)->withQueryString();

        $assignments = TeacherAssignment::with(['teacher.employee', 'section.gradeLevel', 'subject'])
            ->where('academic_year_id', $activeYear->id)
            ->get()
            ->groupBy('teacher_id');

        return view('admin.teacher-assignments.index', compact('assignments', 'teachers', 'activeYear'));
    }

    public function create()
    {
        $teachers = Employee::teachers()->active()->with('user')->get();
        $divisions = \App\Models\Division::where('is_active', true)->get();
        $sections = Section::with('gradeLevel')->where('is_active', true)->get();
        $subjects = Subject::where('is_active', true)->get();
        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();

        $sectionsData = $sections->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'grade_level_name' => $section->gradeLevel->name,
                'division_id' => $section->gradeLevel->division_id,
            ];
        });

        $existingAssignments = [];
        if (request('teacher_user_id')) {
            $existingAssignments = TeacherAssignment::where('teacher_id', request('teacher_user_id'))
                ->where('academic_year_id', $activeYear->id)
                ->get()
                ->groupBy('subject_id')
                ->map(function ($group, $subjectId) {
                    return [
                        'subject_id' => $subjectId,
                        'section_ids' => $group->pluck('section_id')->toArray()
                    ];
                })->values()->toArray();
        }

        return view('admin.teacher-assignments.create', compact('teachers', 'divisions', 'sectionsData', 'subjects', 'activeYear', 'existingAssignments'));
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
