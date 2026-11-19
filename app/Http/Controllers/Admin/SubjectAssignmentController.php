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

        $assigned = DB::table('grade_level_subjects')
            ->where('grade_level_id', $gradeLevel->id)
            ->where('academic_year_id', $academicYear->id)
            ->pluck('is_elective', 'subject_id');
        $assignedSubjectIds = $assigned->keys()->map(fn ($id) => (int) $id)->all();

        // Elective in this grade and year: the assignment's own setting, else the subject's.
        $electiveSubjectIds = $subjects
            ->filter(fn ($s) => $assigned->has($s->id) && $assigned[$s->id] !== null ? (bool) $assigned[$s->id] : $s->defaultIsElective())
            ->pluck('id')->all();

        return view('admin.subject-assignments.edit', compact('gradeLevel', 'subjects', 'assignedSubjectIds', 'electiveSubjectIds', 'academicYear'));
    }

    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'subjects' => 'array',
            'subjects.*' => 'exists:subjects,id',
            'electives' => 'array',
            'electives.*' => 'exists:subjects,id',
        ]);

        $yearId = (int) $request->academic_year_id;
        $subjectIds = $request->input('subjects', []);
        $electiveIds = array_map('intval', $request->input('electives', []));

        DB::transaction(function () use ($gradeLevel, $subjectIds, $yearId, $electiveIds) {
            $this->syncGradeSubjects($gradeLevel->id, $yearId, $subjectIds);
            $this->saveElectiveSettings($gradeLevel->id, $yearId, $electiveIds);
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

    /**
     * Record which assigned subjects are electives in this grade and year. Only a value
     * that differs from the subject's own setting is stored; otherwise it is left NULL so
     * the subject's setting applies.
     */
    private function saveElectiveSettings(int $gradeLevelId, int $yearId, array $electiveIds): void
    {
        $rows = DB::table('grade_level_subjects')
            ->join('subjects', 'subjects.id', '=', 'grade_level_subjects.subject_id')
            ->where('grade_level_subjects.grade_level_id', $gradeLevelId)
            ->where('grade_level_subjects.academic_year_id', $yearId)
            ->get(['grade_level_subjects.id', 'grade_level_subjects.subject_id', 'subjects.is_elective as default_elective']);

        foreach ($rows as $row) {
            $elective = in_array((int) $row->subject_id, $electiveIds, true);
            DB::table('grade_level_subjects')->where('id', $row->id)
                ->update(['is_elective' => $elective === (bool) $row->default_elective ? null : $elective]);
        }

        Cache::forget(GradeCacheService::subjectsKey($gradeLevelId, $yearId));
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
