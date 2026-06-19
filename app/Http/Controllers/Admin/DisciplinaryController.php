<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\DisciplinaryRecord;
use App\Models\InfractionDefinition;
use App\Models\Student;
use App\Services\DisciplineEscalationService;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller
{
    public function index(Request $request)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $query = DisciplinaryRecord::with(['student', 'reporter', 'handler', 'infractionDefinition'])
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('incident_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tier')) {
            $query->whereHas('infractionDefinition', function ($q) use ($request) {
                $q->where('tier', $request->tier);
            });
        }

        if ($request->filled('infraction_definition_id')) {
            $query->where('infraction_definition_id', $request->infraction_definition_id);
        }

        $records = $query->paginate(25);

        $infractionDefinitions = InfractionDefinition::active()->ordered()->get();

        return view('admin.disciplinary.index', compact('records', 'academicYear', 'infractionDefinitions'));
    }

    public function create(Student $student = null)
    {
        $students = Student::whereHas('currentEnrollment')->orderBy('first_name')->get();
        $infractionDefinitions = InfractionDefinition::active()->ordered()->get();

        return view('admin.disciplinary.create', compact('students', 'student', 'infractionDefinitions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'              => 'required|exists:students,id',
            'infraction_definition_id' => 'required|exists:infraction_definitions,id',
            'incident_date'           => 'required|date',
            'description'             => 'required|string',
            'action_taken'            => 'nullable|string',
            'notify_parent'           => 'boolean',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();
        $infraction = InfractionDefinition::findOrFail($request->infraction_definition_id);

        $record = DisciplinaryRecord::create([
            'student_id'              => $request->student_id,
            'academic_year_id'        => $academicYear->id,
            'infraction_definition_id' => $infraction->id,
            'incident_date'           => $request->incident_date,
            'description'             => $request->description,
            'action_taken'            => $request->action_taken,
            'reported_by'             => auth()->id(),
            'notify_parent'           => $request->boolean('notify_parent') || $infraction->requires_parent_notification,
        ]);

        // Evaluate escalation rules
        $escalationService = new DisciplineEscalationService();
        $matchedRule = $escalationService->evaluateEscalation($record);

        $message = 'Disciplinary record created successfully.';
        if ($matchedRule) {
            $message .= ' Escalation triggered: ' . $matchedRule->action_label . '.';
        }

        return redirect()->route('admin.disciplinary.index')
            ->with('success', $message);
    }

    public function show(DisciplinaryRecord $disciplinary)
    {
        $disciplinary->load(['student', 'reporter', 'handler', 'academicYear', 'infractionDefinition', 'escalationRule']);
        return view('admin.disciplinary.show', compact('disciplinary'));
    }

    public function update(Request $request, DisciplinaryRecord $disciplinary)
    {
        $request->validate([
            'status'           => 'required|in:reported,under_review,resolved,escalated',
            'resolution_notes' => 'nullable|string',
        ]);

        $data = [
            'status'           => $request->status,
            'resolution_notes' => $request->resolution_notes,
            'handled_by'       => auth()->id(),
        ];

        if ($request->status === 'resolved') {
            $data['resolution_date'] = now();
        }

        $disciplinary->update($data);

        return redirect()->route('admin.disciplinary.show', $disciplinary)
            ->with('success', 'Record updated successfully.');
    }

    public function studentRecords(Student $student)
    {
        $records = DisciplinaryRecord::with(['reporter', 'handler', 'academicYear', 'infractionDefinition'])
            ->where('student_id', $student->id)
            ->orderBy('incident_date', 'desc')
            ->get();

        return view('admin.disciplinary.student', compact('student', 'records'));
    }
}
