<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }
    public function index(Request $request)
    {
        $query = Student::with(['user', 'enrollments.section.gradeLevel', 'enrollments.academicYear']);

        // Search Filter - Using scope
        $query->search($request->search);

        // Gender Filter - Using scope
        $query->byGender($request->gender);

        // Status Filter
        if ($request->filled('status')) {
            if ($request->status == 'blocked') {
                $query->inactive();
            } elseif ($request->status == 'active') {
                $query->active();
            } elseif ($request->status == 'trashed') {
                $query->onlyTrashed();
            }
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
                    $query->whereHas('enrollments', function($q) use ($request) {
                        $q->whereNull('end_date')
                          ->whereHas('section', fn($sq) => $sq->where('name', $request->section_name));
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
            $query->whereHas('enrollments', function($q) use ($request) {
                 $q->where('academic_year_id', $request->enrollment_year);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if ($sort === 'name') {
            $query->orderBy('first_name', $direction)
                  ->orderBy('father_name', $direction)
                  ->orderBy('grandfather_name', $direction);
        } elseif (in_array($sort, ['student_id', 'admission_number', 'gender', 'is_active'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('per_page', 15);
        // Limit max per page to avoid performance issues
        if ($perPage > 100) $perPage = 100;
        
        $students = $query->paginate($perPage)->withQueryString();
        
        // Data for filters (Cached)
        $gradeLevels = Cache::remember('grade_levels_all', 3600, function () {
            return \App\Models\GradeLevel::all();
        });

        $sections = Cache::remember('sections_grouped_by_grade', 3600, function () {
            return Section::with('gradeLevel')->get()->groupBy(function($section) {
                return $section->gradeLevel ? $section->gradeLevel->name : 'Unassigned';
            });
        });

        return view('admin.students.index', compact('students', 'gradeLevels', 'sections'));
    }

    public function create()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $sections = Section::with('gradeLevel.division')
            ->withCount(['students as enrolled_count' => function($q) {
                 $q->whereNull('student_enrollments.end_date');
            }])
            ->where('is_active', true)
            ->when($activeYear, function($q) use ($activeYear) {
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
                ->with('success', 'Student registered successfully. Student ID: ' . $student->student_id);

        } catch (\Exception $e) {
            return back()->with('error', 'Error registering student: ' . $e->getMessage())->withInput();
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
            'documents'
        ]);
        
        $enrollments = $student->enrollments()
            ->with(['section.gradeLevel', 'academicYear'])
            ->orderBy('enrollment_date', 'desc')
            ->get();

        // Attendance Stats
        $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $attendanceStats = [
            'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => 0, 'percentage' => 0
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
        $academicRecords = $student->marks()
            ->with(['subject', 'assessmentTemplate', 'term', 'academicYear'])
            ->get()
            ->groupBy(function($mark) {
                return $mark->academicYear->name;
            })
            ->map(function($yearMarks) {
                return $yearMarks->groupBy(function($mark) {
                    return $mark->term->name;
                });
            });

        // Also fetch Term Summaries (Report Card Records) to display Avg/Rank alongside the marks
        $termRecords = \App\Models\StudentTermRecord::where('student_id', $student->id)
            ->with(['term', 'academicYear'])
            ->get()
            ->groupBy(function($record) {
                return $record->academicYear->name;
            })
            ->map(function($yearRecords) {
                return $yearRecords->keyBy(function($record) {
                    return $record->term->name;
                });
            });

        return view('admin.students.show', compact('student', 'enrollments', 'attendanceStats', 'recentAttendance', 'disciplinaryRecords', 'academicRecords', 'termRecords'));
    }

    public function linkSibling(Request $request, Student $student)
    {
        $request->validate([
            'sibling_id' => 'required|exists:students,id|different:student_id',
        ]);

        $sibling = Student::findOrFail($request->sibling_id);
        $student->addSibling($sibling);

        return back()->with('success', 'Sibling linked successfully.');
    }

    public function unlinkSibling(Student $student, Student $sibling)
    {
        $student->removeSibling($sibling);
        return back()->with('success', 'Sibling unlinked successfully.');
    }

    public function edit(Student $student)
    {
        $student->load(['guardians', 'medicalInfo', 'transportation']);
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $sections = Section::with('gradeLevel.division')
            ->where('is_active', true)
            ->when($activeYear, function($q) use ($activeYear) {
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
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $students = Student::with(['guardians', 'medicalInfo', 'transportation', 'enrollments.section.gradeLevel'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_export_' . date('Y-m-d') . '.csv"',
        ];

        $columns = [
            'Student ID', 'First Name', 'Father Name', 'Grandfather Name', 'Gender', 'Date of Birth',
            'Birth Country', 'Birth City', 'Nationality', 'Language Spoken',
            'Admission Number', 'Admission Date', 'Current Grade', 'Current Section',
            'Subcity', 'Woreda', 'House Number', 'Phone', 'Email',
            'Primary Guardian Name', 'Primary Guardian Phone', 'Primary Guardian Email', 'Primary Guardian Relationship',
            'Secondary Guardian Name', 'Secondary Guardian Phone', 'Secondary Guardian Email', 'Secondary Guardian Relationship',
            'Blood Type', 'Allergies', 'Medical Issues',
            'Driver Name', 'Route', 'Status'
        ];

        $callback = function() use ($students, $columns) {
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
                    $primaryGuardian ? $primaryGuardian->first_name . ' ' . $primaryGuardian->father_name : '',
                    $primaryGuardian->phone ?? '',
                    $primaryGuardian->email ?? '',
                    $primaryGuardian->relationship ?? '',
                    $secondaryGuardian ? $secondaryGuardian->first_name . ' ' . $secondaryGuardian->father_name : '',
                    $secondaryGuardian->phone ?? '',
                    $secondaryGuardian->email ?? '',
                    $secondaryGuardian->relationship ?? '',
                    $student->medicalInfo->blood_group ?? '',
                    $student->medicalInfo->allergies ?? '',
                    $student->medicalInfo->medical_issues ?? '',
                    $student->transportation ? ($student->transportation->driver_first_name . ' ' . $student->transportation->driver_father_name) : '',
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
        $student->is_active = !$student->is_active;
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
        $sections = Section::with('gradeLevel.division')
            ->where('is_active', true)
            ->when($activeYear, function($q) use ($activeYear) {
                return $q->where('academic_year_id', $activeYear->id);
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
            
            if (!$currentEnrollment) {
                return back()->with('error', 'Student has no active enrollment.');
            }

            if ($currentEnrollment->section_id == $request->new_section_id) {
                return back()->with('error', 'Student is already in this section.');
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
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function assignElectivesForm(Student $student)
    {
        $student->load(['enrollments' => function($q) {
            $q->whereNull('end_date')->latest(); // Active enrollment
        }, 'enrollments.section.gradeLevel.subjects']);

        $currentEnrollment = $student->enrollments->first();

        if (!$currentEnrollment) {
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

        if (!$currentEnrollment) {
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
            if (!empty($syncData)) {
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
            return back()->with('error', 'Error deleting student: ' . $e->getMessage());
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
            'primary_guardian_phone', 'primary_guardian_relationship', 'primary_guardian_email'
        ];

        $callback = function() use ($columns) {
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
                '0911987654', 'Father', 'parent@example.com'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return back()->with('error', 'No active academic year found.');

        ini_set('auto_detect_line_endings', true);
        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) return back()->with('error', 'The file is empty.');

        if (strpos($lines[0], 'sep=') === 0) array_shift($lines);
        $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
        if (strpos($lines[0], "\xEF\xBB\xBF") === 0) $lines[0] = substr($lines[0], 3);

        $data = array_map(fn($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_map('trim', array_shift($data));
        $headerMap = array_flip($header);

        $rows = array_map(function($row) use ($headerMap) {
            $val = fn($key) => isset($headerMap[$key]) && isset($row[$headerMap[$key]]) ? trim($row[$headerMap[$key]]) : null;
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
            return back()->with('error', 'Error deleting students: ' . $e->getMessage());
        }
    }

    public function idCardsIndex()
    {
        $gradeLevels = \App\Models\GradeLevel::with('sections')->orderBy('order')->get();
        return view('admin.students.id-cards-index', compact('gradeLevels'));
    }

    public function generateIdCard(Student $student)
    {
        $student->load(['currentEnrollment.section.gradeLevel', 'primaryGuardian']);
        $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $settings = \App\Models\ReportCardSetting::first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.students.id-card-pdf', compact('student', 'academicYear', 'settings'))
            ->setPaper([0, 0, 243.78, 153.07], 'landscape'); // 86mm x 54mm in points

        return $pdf->stream("id-card-{$student->student_id}.pdf");
    }

    public function bulkIdCards(\App\Models\Section $section)
    {
        $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $students = Student::whereHas('enrollments', function($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
              ->where('academic_year_id', $academicYear->id)
              ->where('status', 'active');
        })->with(['currentEnrollment.section.gradeLevel', 'primaryGuardian'])->orderBy('first_name')->get();

        $settings = \App\Models\ReportCardSetting::first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.students.id-cards-bulk-pdf', compact('students', 'section', 'academicYear', 'settings'))
            ->setPaper([0, 0, 243.78, 153.07], 'landscape');

        return $pdf->stream("id-cards-{$section->name}.pdf");
    }

    public function withdrawForm(Student $student)
    {
        $student->load('currentEnrollment.section.gradeLevel');
        $reasons = \App\Models\StudentStatusHistory::withdrawalReasons();
        $statuses = \App\Models\StudentStatusHistory::statusOptions();
        
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

        DB::transaction(function() use ($request, $student) {
            $oldStatus = $student->is_active ? 'active' : 'inactive';
            
            // Create status history record
            \App\Models\StudentStatusHistory::create([
                'student_id' => $student->id,
                'old_status' => $oldStatus,
                'new_status' => $request->new_status,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'effective_date' => $request->effective_date,
                'changed_by' => auth()->id(),
            ]);

            // Update student status
            $student->update(['is_active' => false]);

            // Close current enrollment
            $enrollment = $student->currentEnrollment;
            if ($enrollment) {
                $enrollment->update([
                    'status' => 'completed',
                    'end_date' => $request->effective_date,
                ]);
            }
        });

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student status updated to ' . ucfirst(str_replace('_', ' ', $request->new_status)) . ' hideously!');
    }

    public function statusHistory(Student $student)
    {
        $history = \App\Models\StudentStatusHistory::where('student_id', $student->id)
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
            $email = $student->admission_number . '@renaissance.edu';
             // return back()->with('error', 'Student must have an email address to create a portal account.');
        } else {
            $email = $student->email;
        }

        // Check if user exists with this email
        $existingUser = User::where('email', $email)->first();
        
        $password = null;

        DB::transaction(function() use ($student, $email, $existingUser, &$password) {
            if ($existingUser) {
                // Link to existing user
                $student->update(['user_id' => $existingUser->id]);
                
                // Ensure role
                if (!$existingUser->hasRole('Student')) {
                    $existingUser->assignRole('Student');
                }
            } else {
                // Create new user
                $password = \Illuminate\Support\Str::random(10);
                
                $user = User::create([
                    'name' => $student->full_name,
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);

                $user->assignRole('Student');
                $student->update(['user_id' => $user->id]);
            }
        });

        if ($password) {
            return back()->with('success', 'Portal account created. Password: ' . $password);
        } else {
            return back()->with('success', 'Portal account linked successfully.');
        }
    }
    public function resetStudentPassword(Student $student)
    {
        if (!$student->user_id || !$student->user) {
            return back()->with('error', 'Student does not have a portal account.');
        }

        $password = \Illuminate\Support\Str::random(10);
        $student->user->update([
            'password' => Hash::make($password),
        ]);

        return back()->with('success', 'Password reset successfully. New Password: ' . $password);
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

        $path = $request->file('file')->store('students/documents', 'public');

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

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
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

        $academicYear = \App\Models\AcademicYear::where('is_active', true)->first();
        $settings = \App\Models\ReportCardSetting::first();
        
        // We can reuse the bulk PDF view but pass students directly
        // The existing view expects $students and $section (for title)
        // We'll pass a dummy section or adjust the view to handle mixed sections
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.students.id-cards-bulk-pdf', [
            'students' => $students,
            'section' => null, // View should handle null section
            'academicYear' => $academicYear,
            'settings' => $settings
        ])->setPaper([0, 0, 243.78, 153.07], 'landscape');

        return $pdf->stream('selected-students-id-cards.pdf');
    }
}

