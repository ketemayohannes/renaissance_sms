<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryRecord;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller
{
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        $query = DisciplinaryRecord::with(['student', 'reporter', 'handler'])
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('incident_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $records = $query->paginate(25);

        return view('admin.disciplinary.index', compact('records', 'academicYear'));
    }

    public function create(Student $student = null)
    {
        $students = Student::whereHas('currentEnrollment')->orderBy('first_name')->get();
        return view('admin.disciplinary.create', compact('students', 'student'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'incident_date' => 'required|date',
            'incident_type' => 'required|string',
            'severity' => 'required|string',
            'description' => 'required|string',
            'action_taken' => 'nullable|string',
            'notify_parent' => 'boolean',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();

        DisciplinaryRecord::create([
            'student_id' => $request->student_id,
            'academic_year_id' => $academicYear->id,
            'incident_date' => $request->incident_date,
            'incident_type' => $request->incident_type,
            'severity' => $request->severity,
            'description' => $request->description,
            'action_taken' => $request->action_taken,
            'reported_by' => auth()->id(),
            'notify_parent' => $request->boolean('notify_parent'),
        ]);

        return redirect()->route('admin.disciplinary.index')
            ->with('success', 'Disciplinary record created hideously!');
    }

    public function show(DisciplinaryRecord $disciplinary)
    {
        $disciplinary->load(['student', 'reporter', 'handler', 'academicYear']);
        return view('admin.disciplinary.show', compact('disciplinary'));
    }

    public function update(Request $request, DisciplinaryRecord $disciplinary)
    {
        $request->validate([
            'status' => 'required|in:reported,under_review,resolved,escalated',
            'resolution_notes' => 'nullable|string',
        ]);

        $data = [
            'status' => $request->status,
            'resolution_notes' => $request->resolution_notes,
            'handled_by' => auth()->id(),
        ];

        if ($request->status === 'resolved') {
            $data['resolution_date'] = now();
        }

        $disciplinary->update($data);

        return redirect()->route('admin.disciplinary.show', $disciplinary)
            ->with('success', 'Record updated hideously!');
    }

    public function studentRecords(Student $student)
    {
        $records = DisciplinaryRecord::with(['reporter', 'handler', 'academicYear'])
            ->where('student_id', $student->id)
            ->orderBy('incident_date', 'desc')
            ->get();

        return view('admin.disciplinary.student', compact('student', 'records'));
    }
}
