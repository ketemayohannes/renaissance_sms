<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CachedData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\Student\QuickUpdateStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\AssessmentType;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentTermRecord;
use App\Models\Term;
use App\Models\User;
use App\Services\GradingService;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    private function buildStudentQuery(Request $request, array $relations = [])
    {
        $query = Student::with($relations);

        // Search Filter - Using scope
        $query->search($request->search);

        // Gender Filter - Using scope
        $query->byGender($request->gender);

        // Division Filter
        $query->byDivision($request->division_id);

        // Status Filter
        if ($request->filled('status')) {
            if ($request->status == 'blocked') {
                $query->blockedStatus();
            } elseif ($request->status == 'withdrawn') {
                $query->withdrawn();
            } elseif ($request->status == 'transferred') {
                $query->transferred();
            } elseif ($request->status == 'dropped_out') {
                $query->droppedOut();
            } elseif ($request->status == 'inactive') {
                $query->inactiveOnly(); // excludes graduated students
            } elseif ($request->status == 'active') {
                $query->active();
            } elseif ($request->status == 'graduated') {
                $query->graduated();
            } elseif ($request->status == 'trashed') {
                $query->onlyTrashed();
            }
        } else {
            // By default, exclude graduated students from the main listing
            // unless a specific status filter is applied
            $query->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhereDoesntHave('statusHistory', function ($sq) {
                        $sq->where('new_status', 'graduated');
                    });
            });
        }

        // Grade & Section Filter - Using scopes
        if ($request->filled('grade_id') || $request->filled('section_name')) {
            if ($request->section_name === 'unassigned') {
                $query->unassigned();
            } else {
                if ($request->filled('grade_id')) {
                    $query->inGrade($request->grade_id);
                }
                if ($request->filled('section_name')) {
                    $activeYearId = CachedData::activeAcademicYear()?->id;

                    // PERFORMANCE: Direct join instead of nested whereHas
                    $query->whereHas('enrollments', function ($q) use ($request, $activeYearId) {
                        $q->whereNull('end_date')
                            ->when($activeYearId, fn ($sq) => $sq->where('academic_year_id', $activeYearId))
                            ->whereHas('section', fn ($sq) => $sq->where('name', $request->section_name));
                    });
                }
            }
        }

        // Advanced Filters
        // Age Range
        if ($request->filled('age_min')) {
            $query->whereDate('date_of_birth', '<=', now()->subYears($request->age_min));
        }
        if ($request->filled('age_max')) {
            $query->whereDate('date_of_birth', '>=', now()->subYears($request->age_max + 1));
        }

        // Enrollment Year
        if ($request->filled('enrollment_year')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('academic_year_id', $request->enrollment_year);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if ($sort === 'name') {
            $query->orderBy('first_name', $direction)
                ->orderBy('father_name', $direction)
                ->orderBy('grandfather_name', $direction);
        } elseif (in_array($sort, ['student_id', 'admission_number', 'gender', 'is_active'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->buildStudentQuery($request, [
            'currentEnrollment.section.gradeLevel',
            'latestPromotion',
            'latestStatusHistory',
        ]);

        $perPage = $request->input('per_page', 50);
        // Limit max per page to avoid performance issues
        if ($perPage > 100) {
            $perPage = 100;
        }

        $students = $query->paginate($perPage)->withQueryString();

        $gradeLevels = GradeLevel::orderBy('sort_order')->get();
        $allSections = Section::orderBy('name')->get();
        $divisions = Division::where('is_active', true)->orderBy('sort_order')->get();
        $graduatedCount = Student::graduated()->count();
        $activeAcademicYearId = CachedData::activeAcademicYear()?->id;

        return view('admin.students.index', compact('students', 'gradeLevels', 'allSections', 'divisions', 'graduatedCount', 'activeAcademicYearId'));

    }

    public function create()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $sections = Section::with('gradeLevel.division')
            ->withCount(['students as enrolled_count' => function ($q) {
                $q->whereNull('student_enrollments.end_date');
            }])
            ->where('is_active', true)
            ->when($activeYear, function ($q) use ($activeYear) {
                return $q->where('academic_year_id', $activeYear->id);
            })
            ->get();

        return view('admin.students.create', compact('sections', 'activeYear'));
    }

    public function store(StoreStudentRequest $request)
    {
        try {
            $student = $this->studentService->createStudent(
                $request->validated(),
                $request->file('student_photo'),
                $request->file('guardians') ?? [],
                $request->file('driver_photo')
            );

            return redirect()->route('admin.students.index')
                ->with('success', 'Student registered successfully. Student ID: '.$student->student_id);

        } catch (\Exception $e) {
            return back()->with('error', 'Error registering student: '.$e->getMessage())->withInput();
        }
    }

    public function show(Student $student)
    {
        $student->load([
            'user',
            'guardians',
            'medicalInfo',
            'transportation',
            'siblings.enrollments.section.gradeLevel',
            'documents',
            'latestStatusHistory',
        ]);

        $enrollments = $student->enrollments()
            ->with(['section.gradeLevel', 'academicYear'])
            ->orderBy('enrollment_date', 'desc')
            ->get();

        // Attendance Stats
        $academicYear = AcademicYear::where('is_active', true)->first();
        $attendanceStats = [
            'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => 0, 'percentage' => 0,
        ];

        if ($academicYear) {
            $stats = $student->attendance()
                ->whereBetween('attendance_date', [$academicYear->start_date, $academicYear->end_date])
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $attendanceStats['present'] = $stats['present'] ?? 0;
            $attendanceStats['late'] = $stats['late'] ?? 0;
            $attendanceStats['absent'] = $stats['absent'] ?? 0;
            $attendanceStats['excused'] = $stats['excused'] ?? 0;

            $attendanceStats['total'] = array_sum($attendanceStats);
            // Don't double count total if percentage is added key (it is not yet)

            if ($attendanceStats['total'] > 0) {
                $attendanceStats['percentage'] = round((($attendanceStats['present'] + $attendanceStats['late']) / $attendanceStats['total']) * 100, 1);
            }
        }

        // Recent Attendance Log (last 15 entries)
        $recentAttendance = $student->attendance()
            ->with(['section.gradeLevel'])
            ->latest('attendance_date')
            ->take(15)
            ->get();

        // Disciplinary
        $disciplinaryRecords = $student->disciplinaryRecords()->with(['reporter', 'academicYear'])->latest('incident_date')->take(10)->get();

        // Academic History (Grouped by Academic Year -> Term)
        //
        // Quarters are rendered from their real StudentMark rows, resolved components-first
        // per subject: a TERM_TOTAL mark is an auto-synced copy of a subject's component sum,
        // so keep the live components when present and fall back to a subject's TERM_TOTAL
        // only when it has none. This avoids the 73 + TERM_TOTAL 73 = 146 double count while
        // keeping a TERM_TOTAL-only term from vanishing — matching GradingService.
        //
        // Semesters and the yearly roll-up are NOT read from stored marks. They are computed
        // live via GradingService (the same engine report cards use), so they are always
        // fresh, components-first, and consistent with the report card — and so Semester 2 /
        // Yearly appear even when no semester marks were ever written for the student.
        $termTotalTypeId = AssessmentType::where('code', 'TERM_TOTAL')->value('id');
        $resolveTermMarks = function ($termMarks) use ($termTotalTypeId) {
            return $termMarks
                ->groupBy('subject_id')
                ->flatMap(function ($subjectMarks) use ($termTotalTypeId) {
                    $components = $subjectMarks->reject(function ($mark) use ($termTotalTypeId) {
                        return $termTotalTypeId
                            && $mark->assessmentTemplate
                            && $mark->assessmentTemplate->assessment_type_id == $termTotalTypeId;
                    });

                    return $components->isNotEmpty() ? $components : $subjectMarks;
                })
                ->values();
        };

        $studentMarks = $student->marks()
            ->with(['subject', 'assessmentTemplate', 'term', 'academicYear'])
            ->get();

        // Quarter rows straight from stored marks, ordered Quarter 1..4 within each year.
        $academicRecords = $studentMarks
            ->filter(fn ($mark) => optional($mark->term)->type === 'quarter')
            ->groupBy(fn ($mark) => $mark->academicYear->name)
            ->map(function ($yearMarks) use ($resolveTermMarks) {
                return $yearMarks
                    ->groupBy(fn ($mark) => $mark->term->name)
                    ->map($resolveTermMarks)
                    ->sortBy(fn ($marks) => optional($marks->first()->term)->term_number ?? 99);
            });

        // Stored term summaries (avg / rank) for the badge chips on each term.
        $termRecords = StudentTermRecord::where('student_id', $student->id)
            ->with(['term', 'academicYear'])
            ->get()
            ->groupBy(fn ($record) => $record->academicYear->name)
            ->map(fn ($yearRecords) => $yearRecords->keyBy(fn ($record) => $record->term->name));

        // Inject computed Semester + Yearly rows (cells and badge) for every academic year
        // the student has marks in — sourced from GradingService, never from stored marks.
        $gradingService = app(GradingService::class);

        $buildComputedRow = function (array $report, $term, AcademicYear $ay) {
            $subjectsById = collect($report['subjects'] ?? [])->keyBy('id');
            $marks = collect();
            foreach (($report['marks'] ?? []) as $subjectId => $score) {
                if ($score === null || $score === '') {
                    continue;
                }
                $subject = $subjectsById->get($subjectId);
                if (! $subject) {
                    continue;
                }
                // In-memory (never saved) mark so the existing per-subject sum renders the
                // computed value with no view changes.
                $synthetic = new StudentMark;
                $synthetic->subject_id = $subjectId;
                $synthetic->score = $score;
                $synthetic->setRelation('subject', $subject);
                $marks->push($synthetic);
            }

            $record = new StudentTermRecord;
            $record->term_id = $term->id;
            $record->academic_year_id = $ay->id;
            $record->average_score = $report['average'] ?? null;
            $record->rank = is_numeric($report['rank'] ?? null) ? $report['rank'] : null;
            $record->rank_out_of = $report['rank_out_of'] ?? null;

            return [$marks, $record];
        };

        foreach ($studentMarks->pluck('academic_year_id')->unique()->filter() as $yearId) {
            $ay = AcademicYear::find($yearId);
            if (! $ay) {
                continue;
            }

            $computedTerms = collect();
            $computedRecords = collect();

            $semesters = Term::where('academic_year_id', $yearId)
                ->where('type', 'semester')
                ->orderBy('term_number')
                ->get();

            foreach ($semesters as $sem) {
                $report = $gradingService->getStudentReportData($student, $sem, $ay);
                if (empty($report)) {
                    continue;
                }
                [$marks, $record] = $buildComputedRow($report, $sem, $ay);
                if ($marks->isNotEmpty()) {
                    $computedTerms->put($sem->name, $marks);
                    $computedRecords->put($sem->name, $record);
                }
            }

            $yearlyTerm = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $yearId]);
            $yearlyTerm->incrementing = false;
            $yearlyTerm->id = 'yearly';

            $yearlyReport = $gradingService->getStudentReportData($student, $yearlyTerm, $ay);
            if (! empty($yearlyReport)) {
                [$marks, $record] = $buildComputedRow($yearlyReport, $yearlyTerm, $ay);
                if ($marks->isNotEmpty()) {
                    $computedTerms->put('Yearly', $marks);
                    $computedRecords->put('Yearly', $record);
                }
            }

            if ($computedTerms->isNotEmpty()) {
                // Quarters first (already ordered), then Semester 1, Semester 2, Yearly.
                // Wrap in collect() to force a base-collection (array_merge) merge that keeps
                // the term-name keys — an Eloquent Collection::merge() would re-key by model
                // primary key and lose them. Computed semester/yearly rows override any stored
                // ones of the same name so the display stays consistent and fresh.
                $academicRecords->put($ay->name, collect($academicRecords->get($ay->name, collect()))->merge($computedTerms));
                $termRecords->put($ay->name, collect($termRecords->get($ay->name, collect()))->merge($computedRecords));
            }
        }

        // For Report Card Modal
        $availableYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $availableTerms = Term::with('academicYear')
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('term_number', 'asc')
            ->get();

        return view('admin.students.show', compact(
            'student',
            'enrollments',
            'attendanceStats',
            'recentAttendance',
            'disciplinaryRecords',
            'academicRecords',
            'termRecords',
            'availableYears',
            'availableTerms',
            'academicYear'
        ));
    }

    public function edit(Student $student)
    {
        $student->load(['guardians', 'medicalInfo', 'transportation']);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $sections = Section::with('gradeLevel.division')
            ->where('is_active', true)
            ->when($activeYear, function ($q) use ($activeYear) {
                return $q->where('academic_year_id', $activeYear->id);
            })
            ->get();

        return view('admin.students.edit', compact('student', 'sections', 'activeYear'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            $this->studentService->updateStudent(
                $student,
                $request->validated(),
                $request->file('photo'),
                $request->file('guardians') ?? [],
                $request->file('driver_photo')
            );

            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Student updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Update failed: '.$e->getMessage());
        }
    }

    public function quickUpdate(QuickUpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        try {
            $student->update($validated);

            return back()->with('success', 'Student details updated quickly.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: '.$e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $students = $this->buildStudentQuery($request, [
            'currentActiveEnrollment.section.gradeLevel',
            'latestPromotion',
            'latestStatusHistory',
            'guardians',
            'medicalInfo',
            'transportation',
            'user',
        ])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_export_'.date('Y-m-d').'.csv"',
        ];

        $columns = [
            'Student ID', 'First Name', 'Father Name', 'Grandfather Name', 'Gender', 'Date of Birth',
            'Birth Country', 'Birth City', 'Nationality', 'Language Spoken',
            'Admission Number', 'Admission Date', 'Current Grade', 'Current Section',
            'Subcity', 'Woreda', 'House Number', 'Phone', 'Email',
            'Primary Guardian Name', 'Primary Guardian Phone', 'Primary Guardian Email', 'Primary Guardian Relationship',
            'Secondary Guardian Name', 'Secondary Guardian Phone', 'Secondary Guardian Email', 'Secondary Guardian Relationship',
            'Blood Type', 'Allergies', 'Medical Issues',
            'Driver Name', 'Route', 'Status',
        ];

        $callback = function () use ($students, $columns) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Force Excel to use comma as separator
            fwrite($file, "sep=,\n");

            fputcsv($file, $columns);

            foreach ($students as $student) {
                $primaryGuardian = $student->guardians->where('guardian_type', 'primary')->first();
                $secondaryGuardian = $student->guardians->where('guardian_type', 'secondary')->first();
                $enrollment = $student->currentActiveEnrollment;

                $row = [
                    $student->student_id,
                    $student->first_name,
                    $student->father_name,
                    $student->grandfather_name,
                    $student->gender,
                    $student->date_of_birth,
                    $student->birth_country,
                    $student->birth_city,
                    $student->nationality,
                    $student->language_spoken,
                    $student->admission_number,
                    $student->admission_date,
                    $enrollment?->section?->gradeLevel?->name ?? '',
                    $enrollment?->section?->name ?? '',
                    $student->subcity,
                    $student->woreda,
                    $student->house_number,
                    $student->phone,
                    $student->user->email ?? '',
                    $primaryGuardian ? $primaryGuardian->first_name.' '.$primaryGuardian->father_name : '',
                    $primaryGuardian->phone ?? '',
                    $primaryGuardian->email ?? '',
                    $primaryGuardian->relationship ?? '',
                    $secondaryGuardian ? $secondaryGuardian->first_name.' '.$secondaryGuardian->father_name : '',
                    $secondaryGuardian->phone ?? '',
                    $secondaryGuardian->email ?? '',
                    $secondaryGuardian->relationship ?? '',
                    $student->medicalInfo->blood_group ?? '',
                    $student->medicalInfo->allergies ?? '',
                    $student->medicalInfo->medical_issues ?? '',
                    $student->transportation ? ($student->transportation->driver_first_name.' '.$student->transportation->driver_father_name) : '',
                    $student->transportation->route ?? '',
                    $student->is_active ? 'Active' : 'Inactive',
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function toggleBlock(Student $student)
    {
        $student->is_active = ! $student->is_active;
        $student->save();

        // Also toggle user account status
        if ($student->user) {
            // Note: Laravel doesn't have a built-in 'is_active' on users table
            // We'll just use the student's is_active status
            // If you want to prevent login, you could delete the user's sessions
        }

        $status = $student->is_active ? 'unblocked' : 'blocked';

        return redirect()->back()->with('success', "Student has been {$status} successfully.");
    }

    public function destroy(Student $student)
    {
        try {
            $this->studentService->deleteStudent($student);

            return redirect()->route('admin.students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting student: '.$e->getMessage());
        }
    }

    public function restore(string $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();

        // Optionally restore user account if needed, but keeping it simple for now

        return redirect()->back()->with('success', 'Student restored successfully.');
    }
}
