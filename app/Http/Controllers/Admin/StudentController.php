<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Students\ProcessStudentWithdrawal;
use App\Helpers\CachedData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\AssessmentType;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\IdCardSetting;
use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentMark;
use App\Models\StudentPromotion;
use App\Models\StudentStatusHistory;
use App\Models\StudentTermRecord;
use App\Models\Term;
use App\Models\User;
use App\Services\GradingService;
use App\Services\StudentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index(Request $request)
    {
        // PERFORMANCE: Simplified eager loading - only load current enrollment
        $query = Student::with([
            'enrollments' => function ($q) {
                $q->whereNull('end_date')->with('section.gradeLevel');
            },
            'latestPromotion',
            'latestStatusHistory',
        ]);

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
                    // PERFORMANCE: Direct join instead of nested whereHas
                    $query->whereHas('enrollments', function ($q) use ($request) {
                        $q->whereNull('end_date')
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

        return view('admin.students.index', compact('students', 'gradeLevels', 'allSections', 'divisions', 'graduatedCount'));

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

    public function linkSibling(Request $request, Student $student)
    {
        $request->validate([
            'sibling_id' => [
                'required',
                'exists:students,id',
                function ($attribute, $value, $fail) use ($student) {
                    if ($value == $student->id) {
                        $fail('A student cannot be their own sibling.');
                    }
                },
            ],
        ]);

        $sibling = Student::findOrFail($request->sibling_id);
        $student->addSibling($sibling);

        // Auto-fill/sync guardian info from the first student to the newly linked sibling
        if ($student->guardians()->count() > 0) {
            $sibling->guardians()->delete();

            foreach ($student->guardians as $guardian) {
                $newGuardian = $sibling->guardians()->create([
                    'guardian_type' => $guardian->guardian_type,
                    'photo' => $guardian->photo,
                    'first_name' => $guardian->first_name,
                    'father_name' => $guardian->father_name,
                    'grandfather_name' => $guardian->grandfather_name,
                    'phone' => $guardian->phone,
                    'email' => $guardian->email,
                    'relationship' => $guardian->relationship,
                    'communication_preferences' => $guardian->communication_preferences,
                    'is_emergency_contact' => $guardian->is_emergency_contact,
                    'user_id' => $guardian->user_id,
                ]);
                $this->studentService->syncGuardianUser($newGuardian);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Sibling linked successfully.']);
        }

        return back()->with('success', 'Sibling linked successfully.');
    }

    public function unlinkSibling(Student $student, Student $sibling)
    {
        $student->removeSibling($sibling);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Sibling unlinked successfully.']);
        }

        return back()->with('success', 'Sibling unlinked successfully.');
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

    public function quickUpdate(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
        ]);

        try {
            $student->update($validated);

            return back()->with('success', 'Student details updated quickly.');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: '.$e->getMessage());
        }
    }

    public function export()
    {
        $students = Student::with(['guardians', 'medicalInfo', 'transportation', 'enrollments.section.gradeLevel'])
            ->orderBy('created_at', 'desc')
            ->get();

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
                $enrollment = $student->enrollments->first();

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
                    $enrollment ? $enrollment->section->gradeLevel->name : '',
                    $enrollment ? $enrollment->section->name : '',
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

    public function transferForm(Student $student)
    {
        $student->load('enrollments.section.gradeLevel');
        $currentEnrollment = $student->enrollments()->whereNull('end_date')->first();

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

    public function transfer(Request $request, Student $student)
    {
        $request->validate([
            'new_section_id' => 'required|exists:sections,id',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // End current enrollment
            $currentEnrollment = $student->enrollments()->whereNull('end_date')->first();

            if (! $currentEnrollment) {
                return back()->with('error', 'Student has no active enrollment.');
            }

            if ($currentEnrollment->section_id == $request->new_section_id) {
                return back()->with('error', 'Student is already in this section.');
            }

            // Validate that the new section is in the same grade level
            $newSection = Section::findOrFail($request->new_section_id);
            if ($newSection->grade_level_id != $currentEnrollment->section->grade_level_id) {
                return back()->with('error', 'Transfers are only allowed within the same grade level.');
            }

            $currentEnrollment->update([
                'end_date' => $request->transfer_date,
                'status' => 'transferred',
            ]);

            // Create new enrollment
            $newSection = Section::findOrFail($request->new_section_id);
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
        $student->load(['enrollments' => function ($q) {
            $q->whereNull('end_date')->latest(); // Active enrollment
        }, 'enrollments.section.gradeLevel.subjects']);

        $currentEnrollment = $student->enrollments->first();

        if (! $currentEnrollment) {
            return back()->with('error', 'Student must have an active enrollment to assign electives.');
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

    public function storeElectives(Request $request, Student $student)
    {
        $request->validate([
            'electives' => 'array', // Can be empty if removing all
            'electives.*' => 'exists:subjects,id',
        ]);

        $currentEnrollment = $student->enrollments()->whereNull('end_date')->first();

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

    public function import()
    {
        return view('admin.students.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_import_template.csv"',
        ];

        $columns = [
            'first_name', 'father_name', 'grandfather_name', 'gender', 'date_of_birth',
            'birth_country', 'birth_city', 'nationality', 'language_spoken',
            'admission_number', 'admission_date', 'grade_level', 'section_name',
            'subcity', 'woreda', 'house_number', 'phone', 'email',
            'primary_guardian_first_name', 'primary_guardian_father_name', 'primary_guardian_grandfather_name',
            'primary_guardian_phone', 'primary_guardian_relationship', 'primary_guardian_email',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Force Excel to use comma as separator
            fwrite($file, "sep=,\n");

            fputcsv($file, $columns);

            // Add sample row
            fputcsv($file, [
                'Abebe', 'Kebede', 'Tesfaye', 'M', '2015-09-01',
                'Ethiopia', 'Addis Ababa', 'Ethiopian', 'Amharic',
                'STU001', '2023-09-01', 'Grade 1', 'A',
                'Bole', '03', '123', '0911234567', 'student@example.com',
                'Kebede', 'Tesfaye', 'Alemu',
                '0911987654', 'Father', 'parent@example.com',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadQuickTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_quick_import_template.csv"',
        ];

        $columns = ['first_name', 'father_name', 'grandfather_name', 'gender', 'grade_level', 'section_name'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fwrite($file, "sep=,\n");
            fputcsv($file, $columns);
            fputcsv($file, ['Abebe',  'Kebede', 'Tesfaye', 'M', 'Grade 1', 'A']);
            fputcsv($file, ['Tigist', 'Alemu',  'Bekele',  'F', 'Grade 2', 'B']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function quickUpload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'No active academic year found.');
        }

        ini_set('auto_detect_line_endings', true);
        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return back()->with('error', 'The file is empty.');
        }

        if (strpos($lines[0], 'sep=') === 0) {
            array_shift($lines);
        }
        $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
        if (strpos($lines[0], "\xEF\xBB\xBF") === 0) {
            $lines[0] = substr($lines[0], 3);
        }

        $data = array_map(fn ($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_map('trim', array_shift($data));
        $headerMap = array_flip($header);

        $today = now()->format('Y-m-d');

        $gradeLevels = GradeLevel::with('division')->get();
        $gradeLevelMap = [];
        foreach ($gradeLevels as $gl) {
            $gradeLevelMap[strtolower($gl->name)] = $gl;
        }

        $getCounter = function ($prefix, $default = 1) {
            $last = Student::where('admission_number', 'like', $prefix.'%')
                ->orderByRaw('LENGTH(admission_number) DESC, admission_number DESC')
                ->value('admission_number');
            if ($last) {
                preg_match('/'.preg_quote($prefix, '/').'(\d+)/', $last, $matches);
                if (! empty($matches[1])) {
                    return (int) $matches[1] + 1;
                }
            }

            return $default;
        };

        $kgCounter = $getCounter('RISKG');
        $esCounter = $getCounter('RISEL');
        $hsCounter = $getCounter('RISHS');

        $rows = array_map(function ($row) use ($headerMap, $today, $gradeLevelMap, &$kgCounter, &$esCounter, &$hsCounter) {
            $val = fn ($key) => isset($headerMap[$key]) && isset($row[$headerMap[$key]])
                ? trim($row[$headerMap[$key]])
                : null;

            $gradeLevelName = $val('grade_level');
            $divisionCode = 'ES'; // Default fallback division code
            if ($gradeLevelName) {
                $glKey = strtolower(trim($gradeLevelName));
                $gl = $gradeLevelMap[$glKey] ?? null;
                if (! $gl && ! str_contains($glKey, 'grade') && ! str_contains($glKey, 'kg')) {
                    $gl = $gradeLevelMap['grade '.$glKey] ?? null;
                }
                if ($gl && $gl->division) {
                    $divisionCode = $gl->division->code;
                }
            }

            if ($divisionCode === 'KG') {
                $admissionNumber = 'RISKG'.str_pad($kgCounter++, 3, '0', STR_PAD_LEFT);
                $dob = now()->subYears(5)->format('Y-m-d');
            } elseif ($divisionCode === 'HS') {
                $admissionNumber = 'RISHS'.str_pad($hsCounter++, 3, '0', STR_PAD_LEFT);
                $dob = now()->subYears(16)->format('Y-m-d');
            } else {
                // Default to ES (Elementary)
                $admissionNumber = 'RISEL'.str_pad($esCounter++, 4, '0', STR_PAD_LEFT);
                $dob = now()->subYears(10)->format('Y-m-d');
            }

            return [
                'first_name' => $val('first_name'),
                'father_name' => $val('father_name'),
                'grandfather_name' => $val('grandfather_name'),
                'gender' => $val('gender'),
                'date_of_birth' => $dob,
                'birth_country' => null,
                'birth_city' => null,
                'nationality' => 'Ethiopian',
                'language_spoken' => null,
                'admission_number' => $admissionNumber,
                'admission_date' => $today,
                'grade_level' => $gradeLevelName,
                'section_name' => $val('section_name'),
                'phone' => null,
                'subcity' => null,
                'woreda' => null,
                'house_number' => null,
                'email' => null,
                'student_id' => null,
                'primary_guardian_first_name' => null,
                'primary_guardian_father_name' => null,
                'primary_guardian_grandfather_name' => null,
                'primary_guardian_phone' => null,
                'primary_guardian_email' => null,
                'primary_guardian_relationship' => null,
            ];
        }, $data);

        $result = $this->studentService->bulkImport($rows, $activeYear);

        $message = "Quick Import completed. {$result['success']} student(s) added, {$result['skipped']} skipped.";
        $redirect = redirect()->route('admin.students.index')->with('success', $message);

        if (count($result['errors']) > 0) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'No active academic year found.');
        }

        ini_set('auto_detect_line_endings', true);
        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return back()->with('error', 'The file is empty.');
        }

        if (strpos($lines[0], 'sep=') === 0) {
            array_shift($lines);
        }
        $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
        if (strpos($lines[0], "\xEF\xBB\xBF") === 0) {
            $lines[0] = substr($lines[0], 3);
        }

        $data = array_map(fn ($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_map('trim', array_shift($data));
        $headerMap = array_flip($header);

        $rows = array_map(function ($row) use ($headerMap) {
            $val = fn ($key) => isset($headerMap[$key]) && isset($row[$headerMap[$key]]) ? trim($row[$headerMap[$key]]) : null;

            return [
                'first_name' => $val('first_name'),
                'father_name' => $val('father_name'),
                'grandfather_name' => $val('grandfather_name'),
                'gender' => $val('gender'),
                'date_of_birth' => $val('date_of_birth'),
                'birth_country' => $val('birth_country'),
                'birth_city' => $val('birth_city'),
                'nationality' => $val('nationality'),
                'language_spoken' => $val('language_spoken'),
                'admission_number' => $val('admission_number'),
                'admission_date' => $val('admission_date'),
                'grade_level' => $val('grade_level'),
                'section_name' => $val('section_name'),
                'phone' => $val('phone'),
                'subcity' => $val('subcity'),
                'woreda' => $val('woreda'),
                'house_number' => $val('house_number'),
                'email' => $val('email'),
                'student_id' => $val('student_id'),
                'primary_guardian_first_name' => $val('primary_guardian_first_name'),
                'primary_guardian_father_name' => $val('primary_guardian_father_name'),
                'primary_guardian_grandfather_name' => $val('primary_guardian_grandfather_name'),
                'primary_guardian_phone' => $val('primary_guardian_phone'),
                'primary_guardian_email' => $val('primary_guardian_email'),
                'primary_guardian_relationship' => $val('primary_guardian_relationship'),
            ];
        }, $data);

        $result = $this->studentService->bulkImport($rows, $activeYear);

        $message = "Import completed. Success: {$result['success']}, Skipped: {$result['skipped']}.";
        $redirect = redirect()->route('admin.students.index')->with('success', $message);

        if (count($result['errors']) > 0) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:students,id',
        ]);

        try {
            $count = $this->studentService->bulkDeleteStudents($request->ids);

            return redirect()->route('admin.students.index')->with('success', "$count students deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting students: '.$e->getMessage());
        }
    }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:students,id',
        ]);

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->ids as $id) {
                $student = Student::findOrFail($id);
                if ($student->is_active) {
                    $student->update(['is_active' => false]);

                    StudentStatusHistory::create([
                        'student_id' => $student->id,
                        'old_status' => 'active',
                        'new_status' => 'inactive',
                        'reason' => 'Bulk Deactivation',
                        'notes' => 'Deactivated via bulk action.',
                        'effective_date' => now(),
                        'changed_by' => auth()->id(),
                    ]);

                    // Close current active enrollment as well
                    $enrollment = $student->enrollments()->whereNull('end_date')->first();
                    if ($enrollment) {
                        $enrollment->update([
                            'status' => 'withdrawn',
                            'end_date' => now(),
                        ]);
                    }

                    $count++;
                }
            }
            DB::commit();

            return redirect()->route('admin.students.index')->with('success', "$count students deactivated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error deactivating students: '.$e->getMessage());
        }
    }

    public function bulkTransfer(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:students,id',
            'new_section_id' => 'required|exists:sections,id',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $newSection = Section::findOrFail($request->new_section_id);

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->ids as $id) {
                $student = Student::findOrFail($id);
                $currentEnrollment = $student->enrollments()->whereNull('end_date')->first();

                // If already in that section, skip
                if ($currentEnrollment && $currentEnrollment->section_id == $request->new_section_id) {
                    continue;
                }

                if ($currentEnrollment) {
                    // Close the current enrollment
                    $currentEnrollment->update([
                        'end_date' => $request->transfer_date,
                        'status' => 'transferred',
                    ]);
                }

                // Create new enrollment in the target section
                $student->enrollments()->create([
                    'section_id' => $request->new_section_id,
                    'academic_year_id' => $newSection->academic_year_id,
                    'enrollment_date' => $request->transfer_date,
                    'status' => 'active',
                ]);

                $count++;
            }

            DB::commit();

            return redirect()->route('admin.students.index')->with('success', "$count students transferred successfully to {$newSection->gradeLevel->name} - {$newSection->name}.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Bulk transfer failed: '.$e->getMessage());
        }
    }

    public function idCardsIndex()
    {
        $academicYear = CachedData::activeAcademicYear();
        $sections = Section::with('gradeLevel')
            ->where('academic_year_id', $academicYear->id)
            ->withCount('students')
            ->get();

        return view('admin.students.id-cards-index', compact('sections'));
    }

    public function generateIdCard(Student $student)
    {
        $student->load(['currentEnrollment.section.gradeLevel', 'primaryGuardian']);
        $academicYear = AcademicYear::where('is_active', true)->first();
        $settings = ReportCardSetting::first();
        $idSettings = IdCardSetting::first();

        $pdf = Pdf::loadView('admin.students.id-card-pdf', compact('student', 'academicYear', 'settings', 'idSettings'))
            ->setPaper([0, 0, 242.65, 153.07], 'landscape'); // 8.56cm x 5.40cm in points

        return $pdf->stream("id-card-{$student->student_id}.pdf");
    }

    public function bulkIdCards(Section $section)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $students = Student::whereHas('enrollments', function ($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'active');
        })->with(['currentEnrollment.section.gradeLevel', 'primaryGuardian'])->orderBy('first_name')->get();

        $settings = ReportCardSetting::first();
        $idSettings = IdCardSetting::first();

        $pdf = Pdf::loadView('admin.students.id-cards-bulk-pdf', compact('students', 'section', 'academicYear', 'settings', 'idSettings'))
            ->setPaper([0, 0, 242.65, 153.07], 'landscape');

        return $pdf->stream("id-cards-{$section->name}.pdf");
    }

    public function withdrawForm(Student $student)
    {
        $student->load('currentEnrollment.section.gradeLevel');
        $reasons = StudentStatusHistory::withdrawalReasons();
        $statuses = StudentStatusHistory::statusOptions();

        return view('admin.students.withdraw', compact('student', 'reasons', 'statuses'));
    }

    public function processWithdrawal(Request $request, Student $student)
    {
        $request->validate([
            'new_status' => 'required|in:withdrawn,graduated,transferred,dropped_out',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'effective_date' => 'required|date',
        ]);

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

    public function createStudentUser(Student $student)
    {
        // Check if student already has user
        if ($student->user_id) {
            return back()->with('error', 'Student already has a portal account.');
        }

        // Check for email
        if (empty($student->email)) {
            // Option: Generate a dummy email or fail?
            // For now, let's require an email or generate one based on ID.
            // Let's generate one if missing: ID@school.com (Example)
            // Better behavior: Require email update first or use generated.
            // Let's use generated if empty: studentID@domain
            $email = $student->admission_number.'@renaissance.edu';
            // return back()->with('error', 'Student must have an email address to create a portal account.');
        } else {
            $email = $student->email;
        }

        // Check if user exists with this email
        $existingUser = User::where('email', $email)->first();

        $password = null;

        DB::transaction(function () use ($student, $email, $existingUser, &$password) {
            if ($existingUser) {
                // Link to existing user
                $student->update(['user_id' => $existingUser->id]);

                // Ensure role
                if (! $existingUser->hasRole('Student')) {
                    $existingUser->assignRole('Student');
                }
            } else {
                // Create new user
                $password = Str::random(10);

                $user = User::create([
                    'name' => $student->full_name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'temp_password' => $password,
                ]);

                $user->assignRole('Student');
                $student->update(['user_id' => $user->id]);
            }
        });

        if ($password) {
            return back()->with('success', 'Portal account created. Password: '.$password);
        } else {
            return back()->with('success', 'Portal account linked successfully.');
        }
    }

    public function resetStudentPassword(Student $student)
    {
        if (! $student->user_id || ! $student->user) {
            return back()->with('error', 'Student does not have a portal account.');
        }

        $password = Str::random(10);
        $student->user->update([
            'password' => Hash::make($password),
            'temp_password' => $password,
        ]);

        return back()->with('success', 'Password reset successfully. New Password: '.$password);
    }

    public function restore(string $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();

        // Optionally restore user account if needed, but keeping it simple for now

        return redirect()->back()->with('success', 'Student restored successfully.');
    }

    public function storeDocument(Request $request, Student $student)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|string|in:standard,medical,legal,other',
            'file' => 'required|file|max:10240', // 10MB max
            'notes' => 'nullable|string',
        ]);

        // Private disk (storage/app/private): student documents contain PII and
        // must be served only through the gated download route, never a public URL.
        $path = $request->file('file')->store('students/documents', 'local');

        $student->documents()->create([
            'title' => $request->title,
            'document_type' => $request->document_type,
            'file_path' => $path,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function deleteDocument(Student $student, StudentDocument $document)
    {
        // Add auth check if needed
        if ($document->student_id !== $student->id) {
            abort(403);
        }

        // New documents are on the private disk; legacy ones may still be public.
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        } elseif (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    /**
     * Stream a student document through an authenticated, permission-gated
     * route so PII files are never exposed via a public storage URL.
     */
    public function downloadDocument(Student $student, StudentDocument $document)
    {
        if ($document->student_id !== $student->id) {
            abort(403);
        }

        if (Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->title);
        }

        // Fallback for documents uploaded before the private-disk migration.
        if (Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->title);
        }

        abort(404);
    }

    public function bulkIdCardsSelected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:students,id',
        ]);

        $students = Student::whereIn('id', $request->ids)
            ->with(['currentEnrollment.section.gradeLevel', 'primaryGuardian'])
            ->orderBy('first_name')
            ->get();

        $academicYear = AcademicYear::where('is_active', true)->first();
        $settings = ReportCardSetting::first();

        // We can reuse the bulk PDF view but pass students directly
        // The existing view expects $students and $section (for title)
        // We'll pass a dummy section or adjust the view to handle mixed sections

        $pdf = Pdf::loadView('admin.students.id-cards-bulk-pdf', [
            'students' => $students,
            'section' => null, // View should handle null section
            'academicYear' => $academicYear,
            'settings' => $settings,
        ])->setPaper([0, 0, 243.78, 153.07], 'landscape');

        return $pdf->stream('selected-students-id-cards.pdf');
    }
}
