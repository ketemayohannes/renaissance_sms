<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Students\ProcessStudentWithdrawal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentElectivesRequest;
use App\Http\Requests\Student\TransferStudentRequest;
use App\Http\Requests\Student\WithdrawStudentRequest;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentPromotion;
use App\Models\StudentStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
    public function enrollmentsIndex(Request $request)
    {
        $query = StudentEnrollment::with([
            'student',
            'section.gradeLevel',
            'academicYear',
        ]);

        // Search by student name or ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('grandfather_name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            });
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by grade level
        if ($request->filled('grade_level_id')) {
            $query->whereHas('section', fn ($q) => $q->where('grade_level_id', $request->grade_level_id));
        }

        // Filter by section
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query
            ->orderByDesc('enrollment_date')
            ->paginate(50, ['*'], 'page')
            ->withQueryString()
            ->appends('tab', 'enrollments');

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $gradeLevels = GradeLevel::orderBy('sort_order')->get();
        $sections = Section::with('gradeLevel')->orderBy('name')->get();

        // Summary counts
        $totalCount = StudentEnrollment::count();
        $activeCount = StudentEnrollment::where('status', 'active')->count();

        // "Pending Enrollment" queue: approved promotion decisions held until a registrar
        // enrols the student into next year. Graduated students never need enrolling, so
        // they are excluded (mirrors PromotionController::enrollStudent's guards).
        $pendingBase = StudentPromotion::where('is_enrolled', false)
            ->where('status', '!=', 'graduated');

        $pending = (clone $pendingBase)
            ->with(['student', 'fromGradeLevel', 'fromAcademicYear', 'toGradeLevel', 'toSection', 'toAcademicYear'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('student', fn ($s) => $s->search($request->search)))
            ->orderByDesc('created_at')
            ->paginate(50, ['*'], 'pendingPage')
            ->withQueryString()
            ->appends('tab', 'pending');

        $pendingCount = (clone $pendingBase)->count();

        // For the Enrollments tab: which enrollments resulted from a promotion that can be
        // reversed. Keyed by "studentId:academicYearId" -> promotion id, so a row can offer
        // an in-place Reverse without a second query per row.
        $reversibleMap = StudentPromotion::where('is_enrolled', true)
            ->whereIn('student_id', $enrollments->pluck('student_id')->filter()->unique()->all())
            ->get(['id', 'student_id', 'to_academic_year_id'])
            ->keyBy(fn ($p) => $p->student_id.':'.$p->to_academic_year_id)
            ->map->id;

        $activeTab = $request->input('tab', $pendingCount > 0 ? 'pending' : 'enrollments');

        return view('admin.students.enrollments', compact(
            'enrollments',
            'academicYears',
            'gradeLevels',
            'sections',
            'totalCount',
            'activeCount',
            'pending',
            'pendingCount',
            'reversibleMap',
            'activeTab'
        ));
    }

    public function transferForm(Student $student)
    {
        $student->load('currentActiveEnrollment.section.gradeLevel');
        $currentEnrollment = $student->currentActiveEnrollment;

        $activeYear = AcademicYear::where('is_active', true)->first();

        // Get current grade level ID if available
        $currentGradeLevelId = $currentEnrollment?->section?->grade_level_id;

        $sections = Section::with('gradeLevel.division')
            ->where('is_active', true)
            ->when($activeYear, function ($q) use ($activeYear) {
                return $q->where('academic_year_id', $activeYear->id);
            })
            ->when($currentGradeLevelId, function ($q) use ($currentGradeLevelId) {
                // Restrict to same grade level
                return $q->where('grade_level_id', $currentGradeLevelId);
            })
            ->get();

        return view('admin.students.transfer', compact('student', 'currentEnrollment', 'sections', 'activeYear'));
    }

    public function transfer(TransferStudentRequest $request, Student $student)
    {
        // Guard checks run before the transaction opens: returning early from inside an
        // open transaction would leave it uncommitted, and the session write (with this
        // error flash) would be discarded along with it.
        $currentEnrollment = $student->currentActiveEnrollment;

        if (! $currentEnrollment) {
            return back()->with('error', 'Student has no active enrollment.');
        }

        if ($currentEnrollment->section_id == $request->new_section_id) {
            return back()->with('error', 'Student is already in this section.');
        }

        // Validate that the new section is in the same grade level. A student with no
        // section yet (e.g. enrolled into a new year before sections were created) has
        // nothing to compare against, so any section they're assigned is accepted.
        $newSection = Section::findOrFail($request->new_section_id);
        if ($currentEnrollment->section && $newSection->grade_level_id != $currentEnrollment->section->grade_level_id) {
            return back()->with('error', 'Transfers are only allowed within the same grade level.');
        }

        DB::beginTransaction();
        try {
            // End current enrollment
            $currentEnrollment->update([
                'end_date' => $request->transfer_date,
                'status' => 'transferred',
            ]);

            // Create new enrollment
            $student->enrollments()->create([
                'section_id' => $request->new_section_id,
                'academic_year_id' => $newSection->academic_year_id,
                'enrollment_date' => $request->transfer_date,
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Student transferred successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Transfer failed: '.$e->getMessage());
        }
    }

    public function assignElectivesForm(Student $student)
    {
        $student->load('currentActiveEnrollment.section.gradeLevel.subjects');

        $currentEnrollment = $student->currentActiveEnrollment;

        if (! $currentEnrollment) {
            return back()->with('error', 'Student must have an active enrollment to assign electives.');
        }

        if (! $currentEnrollment->section) {
            return back()->with('error', 'Student has no section assigned yet — assign one before choosing electives.');
        }

        $academicYearId = $currentEnrollment->academic_year_id;

        // Get elective subjects available for this student's grade level
        // We filter the subjects relation on the grade level model manually or query it
        $gradeLevel = $currentEnrollment->section->gradeLevel;

        // Assuming GradeLevel has 'subjects' relationship, we filter for electives
        // If relationship returns all subjects (pivot), we check is_elective column on Subject
        $availableElectives = $gradeLevel->subjects()->where('is_elective', true)->get();

        // Get currently assigned electives for this academic year
        $assignedElectiveIds = $student->electives()
            ->wherePivot('academic_year_id', $academicYearId)
            ->pluck('subjects.id')
            ->toArray();

        return view('admin.students.assign-electives', compact('student', 'currentEnrollment', 'availableElectives', 'assignedElectiveIds'));
    }

    public function storeElectives(StoreStudentElectivesRequest $request, Student $student)
    {
        $currentEnrollment = $student->currentActiveEnrollment;

        if (! $currentEnrollment) {
            return back()->with('error', 'Student must have an active enrollment.');
        }

        $academicYearId = $currentEnrollment->academic_year_id;

        // Sync electives for this academic year
        // We need to attach with academic_year_id
        // sync() supports pivot data

        $syncData = [];
        if ($request->has('electives')) {
            foreach ($request->electives as $subjectId) {
                $syncData[$subjectId] = ['academic_year_id' => $academicYearId];
            }
        }

        // We only want to sync for the CURRENT academic year.
        // Sync replaces all records. If a student has records for PAST years, we shouldn't touch them.
        // sync() usually replaces all related records.
        // To scoped sync, we can use detach() then attach(), or manual logic.
        // Let's remove current year electives first, then add new ones.

        DB::transaction(function () use ($student, $academicYearId, $syncData) {
            // Detach all electives for this student for this academic year
            DB::table('student_electives')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->delete();

            // Attach new ones
            if (! empty($syncData)) {
                $student->electives()->attach($syncData);
            }
        });

        return redirect()->route('admin.students.show', $student)->with('success', 'Elective subjects updated successfully.');
    }

    public function withdrawForm(Student $student)
    {
        $student->load('currentEnrollment.section.gradeLevel');
        $reasons = StudentStatusHistory::withdrawalReasons();
        $statuses = StudentStatusHistory::statusOptions();

        return view('admin.students.withdraw', compact('student', 'reasons', 'statuses'));
    }

    public function processWithdrawal(WithdrawStudentRequest $request, Student $student)
    {
        ProcessStudentWithdrawal::run($student, $request->only(['new_status', 'reason', 'notes', 'effective_date']));

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student status updated to '.ucfirst(str_replace('_', ' ', $request->new_status)).' successfully!');
    }

    public function statusHistory(Student $student)
    {
        $history = StudentStatusHistory::where('student_id', $student->id)
            ->with('changer')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.students.status-history', compact('student', 'history'));
    }
}
