<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ElectiveController extends Controller
{
    public function bulkAssignForm()
    {
        // PERFORMANCE: Use cached data
        $academicYears = \App\Helpers\CachedData::academicYears();
        $gradeLevels = \App\Helpers\CachedData::gradeLevels();
        
        return view('admin.electives.bulk-assign', compact('academicYears', 'gradeLevels'));
    }

    public function getSubjects(Request $request)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
        ]);

        // Get only ELECTIVE subjects for this grade level
        // Assuming subjects are linked to grade level via pivot or direct relation
        $subjects = DB::table('grade_level_subjects')
            ->join('subjects', 'grade_level_subjects.subject_id', '=', 'subjects.id')
            ->where('grade_level_subjects.grade_level_id', $request->grade_level_id)
            ->where('subjects.is_elective', true)
            ->select('subjects.id', 'subjects.name', 'subjects.code')
            ->orderBy('subjects.name')
            ->get();

        return response()->json($subjects);
    }

    public function getSections(Request $request)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $sections = Section::where('grade_level_id', $request->grade_level_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return response()->json($sections);
    }

    public function getStudents(Request $request)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // PERFORMANCE: Simplified query - only joined to enrollment to check grade level
        $students = Student::where('is_active', true)
        ->whereHas('enrollments', function($q) use ($request) {
            $q->where('academic_year_id', $request->academic_year_id)
              ->whereHas('section', function($sq) use ($request) {
                  $sq->where('grade_level_id', $request->grade_level_id);
              })
              ->whereNull('end_date');
        })
        ->orderBy('first_name')
        ->get(['id', 'first_name', 'father_name', 'grandfather_name', 'student_id']);
        
        $data = $students->map(function($student) {
            return [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->father_name} {$student->grandfather_name} ({$student->student_id})",
            ];
        });

        return response()->json($data);
    }

    public function storeBulkAssign(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'subject_id' => 'required|exists:subjects,id',
            'target_type' => 'required|in:section,student',
            'section_ids' => 'required_if:target_type,section|array',
            'student_ids' => 'required_if:target_type,student|array',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        if (!$subject->is_elective) {
            return back()->with('error', 'Selected subject is not an elective.');
        }

        $studentIds = [];

        if ($request->target_type === 'section') {
            // Get all students in selected sections
            $studentIds = Student::whereHas('enrollments', function($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id)
                  ->whereIn('section_id', $request->section_ids)
                  ->whereNull('end_date');
            })->pluck('id')->toArray();
        } else {
            $studentIds = $request->student_ids;
        }

        if (empty($studentIds)) {
            return back()->with('error', 'No students found for assignment.');
        }

        // Prepare sync data
        $syncData = [];
        foreach ($studentIds as $id) {
            $syncData[$id] = ['academic_year_id' => $request->academic_year_id];
        }

        DB::beginTransaction();
        try {
            // We want to ADD this elective to these students.
            // Using syncWithoutDetaching on the pivot table.
            
            // NOTE: Laravel's belongsToMany->attach() or syncWithoutDetaching() works on a single model.
            // Since we are doing bulk, we can iterate or use DB insert/upsert.
            // Using DB insert ignore or similar logic might be more efficient, 
            // but loop with attach/sync is safer for events (if any) and existing checks.
            
            // Efficient approach:
            // 1. Get existing assignments for this subject/year for these students to avoid primary key error
            $existing = DB::table('student_electives')
                ->where('subject_id', $request->subject_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->whereIn('student_id', $studentIds)
                ->pluck('student_id')
                ->toArray();

            $newIds = array_diff($studentIds, $existing);
            
            $insertData = [];
            $now = now();
            foreach ($newIds as $sid) {
                $insertData[] = [
                    'student_id' => $sid,
                    'subject_id' => $request->subject_id,
                    'academic_year_id' => $request->academic_year_id,
                    // 'created_at' => $now,
                    // 'updated_at' => $now, 
                ];
            }

            if (!empty($insertData)) {
                DB::table('student_electives')->insert($insertData);
            }

            DB::commit();
            
            $count = count($insertData);
            return back()->with('success', "Elective assigned to $count new student(s). " . (count($existing) ? count($existing) . " were already assigned." : ""));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error assigning electives: ' . $e->getMessage());
        }
    }
}
