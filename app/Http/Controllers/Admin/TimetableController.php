<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\ClassPeriod;
use App\Models\TeacherAssignment;
use App\Models\Timetable;
use App\Helpers\CachedData;

class TimetableController extends Controller
{
    public function index()
    {
        $academicYear = CachedData::activeAcademicYear();
        $sections = Section::with('gradeLevel')
            ->where('is_active', true)
            ->get()
            ->groupBy(fn($s) => $s->gradeLevel->name);

        return view('admin.timetable.index', compact('sections', 'academicYear'));
    }

    public function builder(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::with('gradeLevel')->findOrFail($request->section_id);
        $academicYear = CachedData::activeAcademicYear();
        
        $periods = ClassPeriod::orderBy('sort_order')->get();
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        // Get all teacher assignments for this section to populate the "Subject" dropdowns in the grid
        $assignments = TeacherAssignment::with(['subject', 'teacher'])
            ->where('section_id', $section->id)
            ->where('academic_year_id', $academicYear->id)
            ->get();

        // Get existing timetable entries
        $existingEntries = Timetable::where('section_id', $section->id)
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->groupBy(fn($e) => $e->day_of_week . '-' . $e->class_period_id);

        return view('admin.timetable.builder', compact('section', 'academicYear', 'periods', 'days', 'assignments', 'existingEntries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'timetable' => 'required|array',
        ]);

        $academicYear = CachedData::activeAcademicYear();
        $sectionId = $request->section_id;
        $errors = [];

        // Pre-validation: Check for conflicts across all submitted slots
        foreach ($request->timetable as $day => $slots) {
            foreach ($slots as $periodId => $data) {
                if (empty($data['assignment_id'])) continue;

                $assignment = TeacherAssignment::with(['subject', 'teacher'])->find($data['assignment_id']);
                $period = ClassPeriod::find($periodId);
                $dayName = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'][$day];

                // 1. Teacher Conflict: Is this teacher already in another section at this time?
                $teacherConflict = Timetable::where('academic_year_id', $academicYear->id)
                    ->where('day_of_week', $day)
                    ->where('class_period_id', $periodId)
                    ->where('section_id', '!=', $sectionId)
                    ->whereHas('teacherAssignment', function($q) use ($assignment) {
                        $q->where('teacher_id', $assignment->teacher_id);
                    })
                    ->first();

                if ($teacherConflict) {
                    $errors[] = "Teacher {$assignment->teacher->full_name} is already assigned to Section {$teacherConflict->section->name} on {$dayName} during {$period->name}.";
                }

                // 2. Room Conflict (Optional, only if room is provided)
                if (!empty($data['room'])) {
                    $roomConflict = Timetable::where('academic_year_id', $academicYear->id)
                        ->where('day_of_week', $day)
                        ->where('class_period_id', $periodId)
                        ->where('section_id', '!=', $sectionId)
                        ->where('room_number', $data['room'])
                        ->first();

                    if ($roomConflict) {
                        $errors[] = "Room {$data['room']} is already occupied by Section {$roomConflict->section->name} on {$dayName} during {$period->name}.";
                    }
                }
            }
        }

        if (!empty($errors)) {
            return back()->withInput()->with('error_conflicts', $errors);
        }

        \DB::transaction(function() use ($request, $academicYear, $sectionId) {
            // Clear existing for this specific section and year
            Timetable::where('section_id', $sectionId)
                ->where('academic_year_id', $academicYear->id)
                ->delete();

            foreach ($request->timetable as $day => $slots) {
                foreach ($slots as $periodId => $data) {
                    if (!empty($data['assignment_id'])) {
                        Timetable::create([
                            'academic_year_id' => $academicYear->id,
                            'section_id' => $sectionId,
                            'class_period_id' => $periodId,
                            'day_of_week' => $day,
                            'teacher_assignment_id' => $data['assignment_id'],
                            'room_number' => $data['room'] ?? null,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Timetable updated successfully.');
    }
}
