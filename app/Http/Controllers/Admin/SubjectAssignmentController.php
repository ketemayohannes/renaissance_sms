<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        // Subject assignments apply to every academic year.
        $assignedSubjectIds = DB::table('grade_level_subjects')
            ->where('grade_level_id', $gradeLevel->id)
            ->pluck('subject_id')
            ->toArray();

        return view('admin.subject-assignments.edit', compact('gradeLevel', 'subjects', 'assignedSubjectIds'));
    }

    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $request->validate([
            'subjects' => 'array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $subjectIds = $request->input('subjects', []);

        DB::transaction(function () use ($gradeLevel, $subjectIds) {
            $this->syncGradeSubjects($gradeLevel->id, $subjectIds);
        });

        return redirect()->route('admin.subject-assignments.index')
            ->with('success', 'Subjects assigned successfully to '.$gradeLevel->name);
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

        $gradeLevelIds = $request->input('grade_levels');
        $subjectIds = $request->input('subjects', []);

        DB::transaction(function () use ($gradeLevelIds, $subjectIds) {
            foreach ($gradeLevelIds as $gradeLevelId) {
                $this->syncGradeSubjects((int) $gradeLevelId, $subjectIds);
            }
        });

        return redirect()->route('admin.subject-assignments.index')
            ->with('success', 'Subjects assigned successfully to '.count($gradeLevelIds).' grade levels.');
    }

    /**
     * Make a grade's subject list exactly $subjectIds. Subjects that stay keep their row
     * (and sort order); removed subjects are deleted and new ones are added.
     */
    private function syncGradeSubjects(int $gradeLevelId, array $subjectIds): void
    {
        $subjectIds = array_map('intval', $subjectIds);

        DB::table('grade_level_subjects')
            ->where('grade_level_id', $gradeLevelId)
            ->whereNotIn('subject_id', $subjectIds)
            ->delete();

        $existing = DB::table('grade_level_subjects')
            ->where('grade_level_id', $gradeLevelId)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data = [];
        foreach (array_diff($subjectIds, $existing) as $subjectId) {
            $data[] = [
                'grade_level_id' => $gradeLevelId,
                'subject_id' => $subjectId,
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($data)) {
            DB::table('grade_level_subjects')->insert($data);
        }
    }
}
