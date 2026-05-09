<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\StudentMark;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentType;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SectionGradeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view master gradebook', only: ['index', 'entry', 'export', 'exportData', 'exportLowPerformance']),
            new Middleware('permission:edit master gradebook', only: ['store', 'import', 'calculateSemester']),
        ];
    }


    public function index()
    {
        // PERFORMANCE: Use cached data
        $academicYears = \App\Helpers\CachedData::academicYears();
        $gradeLevels = \App\Helpers\CachedData::gradeLevels();
        
        $currentYear = \App\Helpers\CachedData::activeAcademicYear() ?? $academicYears->first();
        $terms = $currentYear ? Term::where('academic_year_id', $currentYear->id)->get() : collect();
        
        // Initial state for dependent dropdowns
        $sections = collect();
        $subjects = collect();
        
        return view('admin.section-grades.index', compact('academicYears', 'gradeLevels', 'terms', 'sections', 'subjects'));
    }

    public function entry(Request $request, \App\Services\GradingService $gradingService)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $term = Term::findOrFail($request->term_id);
        $section = Section::findOrFail($request->section_id);
        
        // Fetch subjects for this grade level - ordered by the pivot sort_order
        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();


        // Fetch students
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivot('status', 'active')
            ->where('students.is_active', true)
            ->orderBy('students.first_name')
            ->get();

        // Ensure "Term Total" Assessment Template exists for EACH subject
        // We look for a template with a specific convention, e.g., type "TERM_GRADE" or name "Term Total"
        // For simplicity, we'll try to find or create one with assessment_type code 'FINAL' or 'TERM_GRADE' if exists.
        // Based on seeder, 'FINAL' exists. Let's use 'FINAL' or create a specific 'TERM_TOTAL' type.
        // User requested "out of 100". 'Final Exam' might be 40%. 
        // We should PROBABLY use a specific type "TERM_TOTAL" to avoid confusion with the "Final Exam" component.
        // Let's check/create 'TERM_TOTAL' type first.
        
        $termTotalType = AssessmentType::firstOrCreate(
            ['code' => 'TERM_TOTAL'],
            ['name' => 'Term Total', 'description' => 'Overall term grade out of 100', 'is_active' => true]
        );

        // Fetch Elective Enrollments for all students in this section
        // Map: [student_id] => [subject_id_1, subject_id_2]
        $studentElectives = DB::table('student_electives')
            ->whereIn('student_id', $students->pluck('id'))
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->groupBy('student_id')
            ->map(function ($items) {
                return $items->pluck('subject_id')->toArray();
            })
            ->toArray();


        
        // REVISED LOGIC for New Schema
        $subjectTemplates = [];
        
        foreach ($subjects as $subject) {
            // Find template assigned to this grade/subject for this year/term with type TERM_TOTAL
            $template = AssessmentTemplate::where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->where('assessment_type_id', $termTotalType->id)
                ->whereHas('assignments', function($q) use ($section, $subject) {
                    $q->where('grade_level_id', $section->grade_level_id)
                      ->where('subject_id', $subject->id);
                })
                ->first();

            if (!$template) {
                // Create Template
                $template = AssessmentTemplate::create([
                    'academic_year_id' => $academicYear->id,
                    'term_id' => $term->id,
                    'assessment_type_id' => $termTotalType->id,
                    'name' => 'Term Total',
                    'weight' => 100,
                    'max_score' => 100,
                    'is_active' => true,
                ]);

                // Assign to Grade/Subject
                $template->assignments()->create([
                    'grade_level_id' => $section->grade_level_id,
                    'subject_id' => $subject->id,
                ]);
            }
            $subjectTemplates[$subject->id] = $template;
        }

        // Fetch ALL existing marks for these students/subjects in this term
        // We do this to sum up component marks (like Quiz, Test) if the "Term Total" is not yet set.
        $allMarks = StudentMark::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get();
            
        // Organize marks: [student_id][subject_id] => score
        $marksMap = [];
        $componentSums = []; 

        foreach ($allMarks as $mark) {
            // Check if this mark belongs to the "Term Total" template for this subject
            $termTotalTemplate = $subjectTemplates[$mark->subject_id] ?? null;
            
            if ($termTotalTemplate && $mark->assessment_template_id == $termTotalTemplate->id) {
                // This is the explicit Term Total
                $marksMap[$mark->student_id][$mark->subject_id] = $mark->score;
            } else {
                // This is a component mark (Quiz, Test, etc.)
                if (!isset($componentSums[$mark->student_id][$mark->subject_id])) {
                    $componentSums[$mark->student_id][$mark->subject_id] = 0;
                }
                $componentSums[$mark->student_id][$mark->subject_id] += $mark->score;
            }
        }

        // Fill in Term Totals: Prioritize Component Sums if they exist to match Gradebook view
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                if (isset($componentSums[$student->id][$subject->id])) {
                    // If components exist in Gradebook, they override the explicit Term Total
                    $marksMap[$student->id][$subject->id] = $componentSums[$student->id][$subject->id];
                }
            }
        }

        if (!$term->is_master_grading_open) {
             // If checking via AJAX or just blocking save
             // Actually, for store/import we should block.
        }

        $isGrade11Or12 = false;
        if (preg_match('/\b(11|12)\b/', $section->gradeLevel->name)) {
            $isGrade11Or12 = true;
        }

        // Calculate real-time stats for Academic Alert
        // This ensures the alert is always accurate regardless of background calculation state
        $stats = $gradingService->calculateSectionTotals($students, $term, $academicYear, $subjects);

        return view('admin.section-grades.entry', compact('academicYear', 'term', 'section', 'students', 'subjects', 'marksMap', 'studentElectives', 'isGrade11Or12', 'stats'));
    }

    public function store(Request $request, \App\Services\GradingService $gradingService)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'term_id' => 'required',
            'section_id' => 'required',
            'marks' => 'array', // marks[student_id][subject_id] = score
        ]);
        
        $term = Term::findOrFail($request->term_id);
        if (!$term->is_master_grading_open) {
            return back()->with('error', 'Master Sheet grading is currently closed for this term.');
        }
        
        $section = Section::findOrFail($request->section_id);
        
        // We need to fetch the templates again to get their IDs
        // Optimization: We could pass template IDs in the form, but looking them up is safer
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->firstOrFail();
        
        DB::beginTransaction();
        try {
            foreach ($request->marks as $studentId => $subjectMarks) {
                foreach ($subjectMarks as $subjectId => $score) {
                    // Find the template
                    $template = AssessmentTemplate::where('academic_year_id', $request->academic_year_id)
                        ->where('term_id', $request->term_id)
                        ->where('assessment_type_id', $termTotalType->id)
                        ->whereHas('assignments', function($q) use ($section, $subjectId) {
                            $q->where('grade_level_id', $section->grade_level_id)
                              ->where('subject_id', $subjectId);
                        })
                        ->first();
                        
                    if (!$template) continue; 

                    if ($score === null || $score === '') {
                        // If score is empty, delete the existing record to clear it
                        StudentMark::where([
                            'student_id' => $studentId,
                            'academic_year_id' => $request->academic_year_id,
                            'term_id' => $request->term_id,
                            'subject_id' => $subjectId,
                            'assessment_template_id' => $template->id,
                        ])->delete();
                        continue;
                    }

                    // VALIDATION: Master Sheet scores are out of 100
                    if ($score > 100) {
                        $student = \App\Models\Student::find($studentId);
                        $studentName = $student ? $student->full_name : "Student #$studentId"; // Use full_name property
                        throw new \Exception("$studentName: Score $score exceeds maximum 100.");
                    }

                    StudentMark::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'academic_year_id' => $request->academic_year_id,
                            'term_id' => $request->term_id,
                            'subject_id' => $subjectId,
                            'assessment_template_id' => $template->id,
                        ],
                        [
                            'section_id' => $request->section_id,
                            'teacher_id' => auth()->id(),
                            'score' => $score,
                        ]
                    );
                }
            }
            DB::commit();
            
            // Recalculate Statistics
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $gradingService->recalculateSectionStatistics($section, $term, $academicYear);
            
            return back()->with('success', 'Grades saved and statistics recalculated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving grades: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::findOrFail($request->section_id);
        $term = Term::findOrFail($request->term_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivot('status', 'active')
            ->where('students.is_active', true)
            ->orderBy('students.first_name')
            ->get();

        $filename = "master_sheet_template_{$section->name}_{$term->name}.csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($students, $subjects) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM
            fputs($file, "sep=,\n"); // Force Excel to use comma

            // Header: Student ID, Student Name, [Subject Name]...
            $headerRow = ['Student ID', 'Student Name'];
            foreach ($subjects as $subject) {
                // Use format "SubjectName (ID:123)" to make import reliable
                $headerRow[] = $subject->name . " (ID:{$subject->id})";
            }
            fputcsv($file, $headerRow);

            foreach ($students as $student) {
                $row = [$student->student_id, $student->full_name];
                foreach ($subjects as $subject) {
                     $row[] = ''; // Empty grade
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportData(Request $request, \App\Services\GradingService $gradingService)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::with('gradeLevel')->findOrFail($request->section_id);
        $term = Term::findOrFail($request->term_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivot('status', 'active')
            ->where('students.is_active', true)
            ->orderBy('students.first_name')
            ->get();

        // Reuse logic from entry() to get current marks
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->first();
        $subjectTemplates = [];
        foreach ($subjects as $subject) {
            $template = AssessmentTemplate::where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->where('assessment_type_id', $termTotalType->id)
                ->whereHas('assignments', function($q) use ($section, $subject) {
                    $q->where('grade_level_id', $section->grade_level_id)
                      ->where('subject_id', $subject->id);
                })
                ->first();
            
            if ($template) {
                $subjectTemplates[$subject->id] = $template;
            }
        }

        $allMarks = StudentMark::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get();
            
        $marksMap = [];
        $componentSums = []; 

        foreach ($allMarks as $mark) {
            $termTotalTemplate = $subjectTemplates[$mark->subject_id] ?? null;
            if ($termTotalTemplate && $mark->assessment_template_id == $termTotalTemplate->id) {
                $marksMap[$mark->student_id][$mark->subject_id] = $mark->score;
            } else {
                if (!isset($componentSums[$mark->student_id][$mark->subject_id])) {
                    $componentSums[$mark->student_id][$mark->subject_id] = 0;
                }
                $componentSums[$mark->student_id][$mark->subject_id] += $mark->score;
            }
        }

        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                if (isset($componentSums[$student->id][$subject->id])) {
                    $marksMap[$student->id][$subject->id] = $componentSums[$student->id][$subject->id];
                }
            }
        }

        // Add stats for average column
        $stats = $gradingService->calculateSectionTotals($students, $term, $academicYear, $subjects);

        $filename = "master_sheet_data_{$section->name}_{$term->name}.csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($students, $subjects, $marksMap, $stats) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM
            fputs($file, "sep=,\n"); 

            $headerRow = ['Student ID', 'Student Name'];
            foreach ($subjects as $subject) {
                $headerRow[] = $subject->name;
            }
            $headerRow[] = 'Average';
            fputcsv($file, $headerRow);

            foreach ($students as $student) {
                $row = [$student->student_id, $student->full_name];
                foreach ($subjects as $subject) {
                     $row[] = $marksMap[$student->id][$subject->id] ?? '';
                }
                $row[] = number_format($stats[$student->id]['average'] ?? 0, 2);
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $term = Term::findOrFail($request->term_id);
        if (!$term->is_master_grading_open) {
            return back()->with('error', 'Master Sheet grading is currently closed for this term.');
        }

        $section = Section::findOrFail($request->section_id);
        
        // Ensure Term Total Type exists
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->firstOrCreate(
            ['code' => 'TERM_TOTAL'],
            ['name' => 'Term Total', 'description' => 'Overall term grade out of 100', 'is_active' => true]
        );

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');
        
        // Skip BOM
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") { rewind($file); }

        // Read first line (possibly sep=)
        $line = fgets($file);
        if (str_starts_with($line, 'sep=')) {
            // Read next line as header
            $headerLine = fgets($file);
        } else {
            $headerLine = $line;
        }

        // Auto-detect delimiter
        $delimiters = [',', ';', "\t"];
        $delimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $d) {
            $count = substr_count($headerLine, $d);
            if ($count > $maxCount) {
                $maxCount = $count;
                $delimiter = $d;
            }
        }
        
        $header = str_getcsv($headerLine, $delimiter);

        // Map columns to Subject IDs
        $subjectMap = []; // index => subject_id
        foreach ($header as $index => $colName) {
            if ($index < 2) continue;
            // Extract ID from "Name (ID:123)"
            if (preg_match('/\(ID:(\d+)\)/', $colName, $matches)) {
                $subjectMap[$index] = $matches[1];
            }
        }

        // Pre-fetch all students in this section mapped by their Student ID (admission number)
        $sectionStudents = $section->students()->get()->keyBy('student_id');

        DB::beginTransaction();
        try {
            $count = 0;
            $errors = [];
            while (($line = fgets($file)) !== false) {
                $row = str_getcsv($line, $delimiter);
                if (empty($row) || empty($row[0])) continue;

                $studentIdStr = trim($row[0]);
                
                // Fetch from the pre-loaded section students to ensure they belong here
                $student = $sectionStudents->get($studentIdStr);

                if (!$student) {
                    $errors[] = "Student ID $studentIdStr not found in this section";
                    continue;
                }

                foreach ($subjectMap as $index => $subjectId) {
                    $score = isset($row[$index]) ? trim($row[$index]) : '';
                    if ($score === '') continue;

                    if (!is_numeric($score) || $score < 0 || $score > 100) {
                         $errors[] = "Invalid score '$score' for student $studentIdStr";
                         continue;
                    }

                    // Find/Create Template
                     $template = AssessmentTemplate::where('academic_year_id', $request->academic_year_id)
                        ->where('term_id', $request->term_id)
                        ->where('assessment_type_id', $termTotalType->id)
                        ->whereHas('assignments', function($q) use ($section, $subjectId) {
                            $q->where('grade_level_id', $section->grade_level_id)
                              ->where('subject_id', $subjectId);
                        })
                        ->first();
                        
                    if (!$template) {
                        $template = AssessmentTemplate::create([
                            'academic_year_id' => $request->academic_year_id,
                            'term_id' => $request->term_id,
                            'assessment_type_id' => $termTotalType->id,
                            'name' => 'Term Total',
                            'weight' => 100,
                            'max_score' => 100,
                            'is_active' => true,
                        ]);
                        $template->assignments()->create([
                            'grade_level_id' => $section->grade_level_id,
                            'subject_id' => $subjectId,
                        ]);
                    }

                    StudentMark::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $request->academic_year_id,
                            'term_id' => $request->term_id,
                            'subject_id' => $subjectId,
                            'assessment_template_id' => $template->id,
                        ],
                        [
                            'section_id' => $request->section_id,
                            'teacher_id' => auth()->id(),
                            'score' => $score,
                        ]
                    );
                }
                $count++;
            }
            
            DB::commit();
            fclose($file);

            // Recalculate Statistics
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $gradingService = app(\App\Services\GradingService::class);
            $gradingService->recalculateSectionStatistics($section, $term, $academicYear);
            
            // Clear roster cache
            \Illuminate\Support\Facades\Cache::forget("roster_data_{$section->id}_{$term->id}_{$academicYear->id}");

            if (!empty($errors)) {
                 return back()->with('warning', "Imported $count rows. Errors: " . implode('; ', array_slice($errors, 0, 3)));
            }
            return back()->with('success', "Successfully imported process for $count students.");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function calculateSemester(Request $request, \App\Services\GradingService $gradingService)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'subject_id' => 'nullable', // We calculate for ALL regular subjects, so subject_id is not strictly needed per se, but let's see.
            // Actually, the plan says "Calculate for Regular Subjects". 
            // The master sheet is usually subject specific? 
            // No, the Master Sheet has ALL subjects.
            // So we can calculate ALL regular subjects for this section at once.
            'term_id' => 'required|exists:terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $section = Section::findOrFail($request->section_id);
        $term = Term::findOrFail($request->term_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);

        if ($term->type !== 'semester') {
            return back()->with('error', 'Auto-calculation is only available for Semester terms.');
        }

        if (!$term->is_grading_open && !$term->is_master_grading_open) {
             return back()->with('error', 'Grading is closed for this term.');
        }

        try {
            $count = $gradingService->calculateSemesterGrades($section, $term, $academicYear);
            return back()->with('success', "Calculation complete. Updated $count student marks.");
        } catch (\Exception $e) {
            return back()->with('error', 'Calculation failed: ' . $e->getMessage());
        }
    }
    public function exportLowPerformance(Request $request, \App\Services\GradingService $gradingService)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::findOrFail($request->section_id);
        $term = Term::findOrFail($request->term_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivot('status', 'active')
            ->where('students.is_active', true)
            ->orderBy('students.first_name')
            ->get();

        $stats = $gradingService->calculateSectionTotals($students, $term, $academicYear, $subjects);

        $lowPerformers = $students->filter(function($student) use ($stats) {
            $average = $stats[$student->id]['average'] ?? 0;
            return $average > 0 && $average < 75;
        });

        $filename = "low_performance_alert_{$section->name}_{$term->name}.csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($lowPerformers, $stats) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM
            fputs($file, "sep=,\n"); // Excel separator hint
            fputcsv($file, ['Student ID', 'Student Name', 'Average Score (%)']);

            foreach ($lowPerformers as $student) {
                fputcsv($file, [
                    $student->student_id,
                    $student->full_name,
                    number_format($stats[$student->id]['average'] ?? 0, 1) . '%'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
