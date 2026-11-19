<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Services\GradeCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Grade ↔ subject assignments are kept per academic year, so a year's list can change
 * without touching earlier years' report cards and averages. Every screen works on one
 * year at a time (the active year unless another is picked).
 */
class SubjectAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $academicYear = $this->selectedYear($request);
        $gradeLevels = GradeLevel::with('division')->orderBy('sort_order')->get();

        $subjectCounts = DB::table('grade_level_subjects')
            ->where('academic_year_id', $academicYear?->id)
            ->selectRaw('grade_level_id, count(*) as c')
            ->groupBy('grade_level_id')
            ->pluck('c', 'grade_level_id');

        return view('admin.subject-assignments.index', compact('gradeLevels', 'academicYears', 'academicYear', 'subjectCounts'));
    }

    public function edit(Request $request, GradeLevel $gradeLevel)
    {
        $academicYear = $this->selectedYear($request);
        abort_unless($academicYear, 404, 'No academic year found.');

        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        $assignedSubjectIds = DB::table('grade_level_subjects')
            ->where('grade_level_id', $gradeLevel->id)
            ->where('academic_year_id', $academicYear->id)
            ->pluck('subject_id')
            ->toArray();

        return view('admin.subject-assignments.edit', compact('gradeLevel', 'subjects', 'assignedSubjectIds', 'academicYear'));
    }

    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'subjects' => 'array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $yearId = (int) $request->academic_year_id;
        $subjectIds = $request->input('subjects', []);

        DB::transaction(function () use ($gradeLevel, $subjectIds, $yearId) {
            $this->syncGradeSubjects($gradeLevel->id, $yearId, $subjectIds);
        });

        $year = AcademicYear::find($yearId);

        return redirect()->route('admin.subject-assignments.index', ['academic_year_id' => $yearId])
            ->with('success', "Subjects for {$gradeLevel->name} in {$year->name} saved. Other years are unchanged.");
    }

    public function bulkAssignForm(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $academicYear = $this->selectedYear($request);
        $gradeLevels = GradeLevel::with('division')->orderBy('sort_order')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('admin.subject-assignments.bulk-assign', compact('gradeLevels', 'subjects', 'academicYears', 'academicYear'));
    }

    public function storeBulkAssign(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_levels' => 'required|array|min:1',
            'grade_levels.*' => 'exists:grade_levels,id',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $yearId = (int) $request->academic_year_id;
        $gradeLevelIds = $request->input('grade_levels');
        $subjectIds = $request->input('subjects', []);

        DB::transaction(function () use ($gradeLevelIds, $subjectIds, $yearId) {
            foreach ($gradeLevelIds as $gradeLevelId) {
                $this->syncGradeSubjects((int) $gradeLevelId, $yearId, $subjectIds);
            }
        });

        return redirect()->route('admin.subject-assignments.index', ['academic_year_id' => $yearId])
            ->with('success', 'Subjects assigned successfully to '.count($gradeLevelIds).' grade levels.');
    }

    /** The year picked in the request, else the active year, else the latest one. */
    private function selectedYear(Request $request): ?AcademicYear
    {
        return ($request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id) : null)
            ?? AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderByDesc('start_date')->first();
    }

    /**
     * Make a grade's subject list for one year exactly $subjectIds. Subjects that stay
     * keep their row (and sort order); removed subjects are deleted and new ones added.
     */
    private function syncGradeSubjects(int $gradeLevelId, int $yearId, array $subjectIds): void
    {
        $subjectIds = array_map('intval', $subjectIds);
        $rows = fn () => DB::table('grade_level_subjects')
            ->where('grade_level_id', $gradeLevelId)
            ->where('academic_year_id', $yearId);

        $rows()->whereNotIn('subject_id', $subjectIds)->delete();

        $existing = $rows()->pluck('subject_id')->map(fn ($id) => (int) $id)->all();

        $data = [];
        foreach (array_diff($subjectIds, $existing) as $subjectId) {
            $data[] = [
                'grade_level_id' => $gradeLevelId,
                'subject_id' => $subjectId,
                'academic_year_id' => $yearId,
                'is_required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($data)) {
            DB::table('grade_level_subjects')->insert($data);
        }

        // Report and gradebook code caches each grade's subject list per year.
        Cache::forget(GradeCacheService::subjectsKey($gradeLevelId, $yearId));
    }
}
