<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectAssignmentController extends Controller
{
    public function index()
    {
        $gradeLevels = GradeLevel::with('division')->orderBy('sort_order')->get();
        return view('admin.subject-assignments.index', compact('gradeLevels'));
    }

    public function edit(GradeLevel $gradeLevel)
    {
        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        
        // Get currently assigned subjects for this grade and year
        $assignedSubjectIds = DB::table('grade_level_subjects')
            ->where('grade_level_id', $gradeLevel->id)
            ->where('academic_year_id', $activeYear->id)
            ->pluck('subject_id')
            ->toArray();

        return view('admin.subject-assignments.edit', compact('gradeLevel', 'subjects', 'assignedSubjectIds', 'activeYear'));
    }

    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $request->validate([
            'subjects' => 'array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        $subjectIds = $request->input('subjects', []);

        // Sync logic: Delete existing for this year/grade, then insert new
        DB::transaction(function () use ($gradeLevel, $activeYear, $subjectIds) {
            // Remove old assignments
            DB::table('grade_level_subjects')
                ->where('grade_level_id', $gradeLevel->id)
                ->where('academic_year_id', $activeYear->id)
                ->delete();

            // Insert new assignments
            $data = [];
            foreach ($subjectIds as $subjectId) {
                $data[] = [
                    'grade_level_id' => $gradeLevel->id,
                    'subject_id' => $subjectId,
                    'academic_year_id' => $activeYear->id,
                    'is_required' => true, // Defaulting to true for now
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($data)) {
                DB::table('grade_level_subjects')->insert($data);
            }
        });

        return redirect()->route('admin.subject-assignments.index')
            ->with('success', 'Subjects assigned successfully to ' . $gradeLevel->name);
    }
    public function bulkAssignForm()
    {
        $gradeLevels = GradeLevel::with('division')->orderBy('sort_order')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.subject-assignments.bulk-assign', compact('gradeLevels', 'subjects'));
    }

    public function storeBulkAssign(Request $request)
    {
        $request->validate([
            'grade_levels' => 'required|array|min:1',
            'grade_levels.*' => 'exists:grade_levels,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        $gradeLevelIds = $request->input('grade_levels');
        $subjectIds = $request->input('subjects', []);

        DB::transaction(function () use ($gradeLevelIds, $subjectIds, $activeYear) {
            foreach ($gradeLevelIds as $gradeLevelId) {
                // 1. Remove existing assignments for this year
                DB::table('grade_level_subjects')
                    ->where('grade_level_id', $gradeLevelId)
                    ->where('academic_year_id', $activeYear->id)
                    ->delete();

                // 2. Insert new
                $data = [];
                foreach ($subjectIds as $subjectId) {
                    $data[] = [
                        'grade_level_id' => $gradeLevelId,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $activeYear->id,
                        'is_required' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($data)) {
                    DB::table('grade_level_subjects')->insert($data);
                }
            }
        });

        return redirect()->route('admin.subject-assignments.index')
            ->with('success', 'Subjects assigned successfully to ' . count($gradeLevelIds) . ' grade levels.');
    }
}
