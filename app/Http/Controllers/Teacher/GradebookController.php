<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\StudentMark;
use App\Models\Term;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ReportCardSetting;
use Barryvdh\DomPDF\Facade\Pdf;

class GradebookController extends Controller
{
    /**
     * Show the grade entry form for a specific assignment.
     */
    public function entry(Request $request, TeacherAssignment $assignment)
    {
        // Security check: ensure this assignment belongs to the authenticated teacher.
        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized access to this class.');
        }

        $activeYear = AcademicYear::active()->firstOrFail();
        $academicYearId = $request->input('academic_year_id', $activeYear->id);
        $academicYear = AcademicYear::findOrFail($academicYearId);

        // Determine active term or fallback
        // 1. Prioritize the quarter that is currently open for grading
        $activeTerm = Term::where('academic_year_id', $academicYearId)
            ->where('type', 'quarter')
            ->where('is_grading_open', true)
            ->first();
            
        // 2. Fallback to calendar date if no grading quarter is explicitly open
        if (!$activeTerm) {
            $activeTerm = Term::where('academic_year_id', $academicYearId)
                ->where('type', 'quarter')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
        }
            
        $termId = $request->input('term_id', $activeTerm ? $activeTerm->id : null);
        
        // If no term is selected/active, get the closest one
        if (!$termId) {
            $firstTerm = Term::where('academic_year_id', $academicYearId)
                ->where('type', 'quarter')
                ->orderBy('start_date')
                ->first();
            if (!$firstTerm) {
                return back()->with('error', 'No quarter terms defined for this year.');
            }
            $termId = $firstTerm->id;
        }

        $term = Term::findOrFail($termId);
        $section = $assignment->section;
        $subject = $assignment->subject;

        // Fetch all terms for this year for the dropdown
        $terms = Term::where('academic_year_id', $academicYearId)
            ->where('type', 'quarter')
            ->orderBy('start_date')
            ->get();

        // Fetch assessment templates
        $termTotalType = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->first();
        
        $gradeComponents = \App\Models\AssessmentTemplate::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->whereHas('assignments', function($query) use ($section, $subject) {
                $query->where('grade_level_id', $section->grade_level_id)
                      ->where('subject_id', $subject->id);
            })
            ->with('assessmentType')
            ->orderBy('order')
            ->get();

        // Fetch Term Total template separately (for display if exists)
        $termTotalTemplate = null;
        if ($termTotalType) {
            $termTotalTemplate = \App\Models\AssessmentTemplate::where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->where('assessment_type_id', $termTotalType->id)
                ->whereHas('assignments', function($query) use ($section, $subject) {
                    $query->where('grade_level_id', $section->grade_level_id)
                          ->where('subject_id', $subject->id);
                })
                ->first();
        }

        // Fetch students
        if ($subject->is_elective) {
            $students = $section->students()
                ->whereHas('electives', function($q) use ($subject, $academicYear) {
                    $q->where('subject_id', $subject->id)
                      ->where('student_electives.academic_year_id', $academicYear->id);
                })
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->orderBy('students.first_name')
                ->get();
        } else {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->orderBy('students.first_name')
                ->get()
;
        }

        // Fetch existing marks
        $existingMarks = StudentMark::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('subject_id', $subject->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        // Calculate Statistics
        $componentIds = $gradeComponents->pluck('id');
        $studentTotals = collect();
        foreach ($students as $student) {
            $studentMarks = $existingMarks->get($student->id);
            if ($studentMarks && $studentMarks->count() > 0) {
                // IMPORTANT: Only sum marks that belong to the components currently being viewed
                $totalScore = $studentMarks->whereIn('assessment_template_id', $componentIds)->sum('score');
                
                // Fallback to Master Sheet total if components sum to 0
                if ($totalScore == 0 && $termTotalTemplate) {
                    $termTotalMark = $studentMarks->firstWhere('assessment_template_id', $termTotalTemplate->id);
                    if ($termTotalMark) {
                        $totalScore = $termTotalMark->score;
                    }
                }
                
                if ($totalScore > 0) {
                    $studentTotals->push([
                        'student' => $student,
                        'total' => $totalScore
                    ]);
                }
            }
        }

        // 1. Graded Average
        $gradedAverage = $studentTotals->count() > 0 ? $studentTotals->avg('total') : 0;
        
        // 2. Section Average
        $totalScoreSum = $studentTotals->sum('total');
        $totalStudentCount = $students->count();
        $classAverage = $totalStudentCount > 0 ? $totalScoreSum / $totalStudentCount : 0;

        $top3Students = $studentTotals->sortByDesc('total')->take(3);
        $bottom3Students = $studentTotals->sortBy('total')->take(3);

        return view('teacher.gradebook.entry', compact(
            'assignment', 'section', 'subject', 'academicYear', 'term', 'terms', 'students', 'gradeComponents', 'existingMarks',
            'gradedAverage', 'classAverage', 'top3Students', 'bottom3Students', 'termTotalTemplate'
        ));
    }

    /**
     * Export a CSV template for grade entry.
     */
    public function export(Request $request, TeacherAssignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized access to this class.');
        }

        $request->validate([
            'term_id' => 'required|exists:terms,id',
        ]);

        $term = Term::findOrFail($request->term_id);
        $section = $assignment->section;
        $subject = $assignment->subject;
        $academicYearId = $assignment->academic_year_id;
        
        // Fetch students
        if ($subject->is_elective) {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYearId)
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->whereHas('electives', function($q) use ($subject, $academicYearId) {
                    $q->where('subject_id', $subject->id)
                      ->where('student_electives.academic_year_id', $academicYearId);
                })
                ->orderBy('students.first_name')
                ->get();
        } else {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYearId)
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->orderBy('students.first_name')
                ->get();
        }
        
        // Fetch assessment templates (excluding TERM_TOTAL - it's a calculated value)
        $termTotalType = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->first();
        
        $templates = \App\Models\AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->orderBy('order')
            ->get();

        $filename = "grades_{$section->name}_{$subject->name}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($students, $templates) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");
            
            // Add separator hint for Excel
            fputs($file, "sep=,\n");
            
            // Header row
            $headerRow = ['Student ID', 'Student Name'];
            foreach ($templates as $template) {
                $headerRow[] = $template->name . " (Max: {$template->max_score})";
            }
            fputcsv($file, $headerRow);

            // Student rows
            foreach ($students as $student) {
                $row = [$student->student_id, $student->full_name];
                // Empty columns for grades
                foreach ($templates as $template) {
                    $row[] = '';
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate a PDF marksheet for the class.
     */
    public function marksheet(Request $request, TeacherAssignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized access to this class.');
        }

        $request->validate([
            'term_id' => 'required|exists:terms,id',
        ]);

        $term = Term::findOrFail($request->term_id);
        $section = $assignment->section;
        $subject = $assignment->subject;
        $academicYear = AcademicYear::findOrFail($assignment->academic_year_id);
        $settings = ReportCardSetting::firstOrNew();
        
        // Fetch assessment templates
        $termTotalType = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->first();
        
        $gradeComponents = \App\Models\AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->with('assessmentType')
            ->orderBy('order')
            ->get();

        // Fetch Term Total template separately (for display if exists)
        $termTotalTemplate = null;
        if ($termTotalType) {
            $termTotalTemplate = \App\Models\AssessmentTemplate::where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->where('assessment_type_id', $termTotalType->id)
                ->whereHas('assignments', function($query) use ($section, $subject) {
                    $query->where('grade_level_id', $section->grade_level_id)
                          ->where('subject_id', $subject->id);
                })
                ->first();
        }

        // Fetch students
        if ($subject->is_elective) {
            $students = $section->students()
                ->whereHas('electives', function($q) use ($subject, $academicYear) {
                    $q->where('subject_id', $subject->id)
                      ->where('student_electives.academic_year_id', $academicYear->id);
                })
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->orderBy('students.first_name')
                ->get();
        } else {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->orderBy('students.first_name')
                ->get()
;
        }

        // Fetch existing marks
        $existingMarks = StudentMark::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('subject_id', $subject->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $data = [
            'assignment' => $assignment,
            'section' => $section,
            'subject' => $subject,
            'academicYear' => $academicYear,
            'term' => $term,
            'students' => $students,
            'gradeComponents' => $gradeComponents,
            'existingMarks' => $existingMarks,
            'termTotalTemplate' => $termTotalTemplate,
            'settings' => $settings,
            'teacher' => Auth::user(),
        ];

        $pdf = Pdf::loadView('teacher.gradebook.marksheet_pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        
        $filename = "marksheet_{$section->name}_{$subject->code}_{$term->name}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Import grades from a CSV file.
     */
    public function import(Request $request, TeacherAssignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'term_id' => 'required|exists:terms,id',
        ]);

        $term = Term::findOrFail($request->term_id);
        if (!$term->is_grading_open) {
            if (!Auth::user()->hasRole(['Super Admin', 'Principal'])) {
                return back()->with('error', 'Grading for this term is currently closed.');
            }
        }

        $section = $assignment->section;
        $subject = $assignment->subject;
        $academicYearId = $assignment->academic_year_id;
        
        // Fetch assessment templates (excluding TERM_TOTAL - it's a calculated value)
        $termTotalType = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->first();
        
        $templates = \App\Models\AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $request->term_id)
            ->where('is_active', true)
            ->when($termTotalType, function($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->get();

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');
        
        // Skip BOM if present
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }
        
        // Read first line to check for sep=,
        $line = fgets($file);
        if (str_starts_with($line, 'sep=')) {
            // It's the separator hint, ignore it and read next line as header
            $headerLine = fgets($file);
        } else {
            // It's the header
            $headerLine = $line;
        }

        if (!$headerLine) {
            fclose($file);
            return back()->with('error', 'The uploaded file is empty or invalid.');
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
        
        // Parse header
        $header = str_getcsv($headerLine, $delimiter);
        
        // Map CSV columns to template IDs
        $columnMap = [];
        foreach ($header as $index => $colName) {
            if ($index < 2) continue; // Skip ID and Name
            
            // Extract name from "Name (Max: 10)" format or just use name
            $name = preg_replace('/\s*\(Max:.*\)/', '', $colName);
            $name = trim($name);
            
            $template = $templates->first(function($t) use ($name) {
                return strtolower($t->name) === strtolower($name);
            });
            
            if ($template) {
                $columnMap[$index] = $template;
            }
        }

        $sectionStudents = $assignment->section->students()->get()->keyBy('student_id');

        DB::beginTransaction();
        try {
            $successCount = 0;
            $errors = [];
            $upsertData = [];
            $now = now();

            while (($line = fgets($file)) !== false) {
                $row = str_getcsv($line, $delimiter);
                
                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) continue;
                
                $studentIdStr = trim($row[0]);
                
                // Fetch from pre-loaded section students to ensure they belong to this section
                $student = $sectionStudents->get($studentIdStr);
                
                if (!$student) {
                    $errors[] = "Student ID $studentIdStr not found in this section";
                    continue;
                }

                foreach ($columnMap as $index => $template) {
                    $score = $row[$index] ?? null;
                    if ($score === null || $score === '') continue;
                    $score = trim($score);
                    
                    if (!is_numeric($score)) {
                        $errors[] = "Invalid score for $studentIdStr in {$template->name}";
                        continue;
                    }

                    if ($score > $template->max_score) {
                        $errors[] = "Score $score exceeds max {$template->max_score} for $studentIdStr in {$template->name}";
                        continue;
                    }

                    $upsertData[] = [
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYearId,
                        'term_id' => $request->term_id,
                        'subject_id' => $assignment->subject_id,
                        'assessment_template_id' => $template->id,
                        'section_id' => $assignment->section_id,
                        'teacher_id' => Auth::id(),
                        'score' => $score,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $successCount++;
            }

            if (!empty($upsertData)) {
                StudentMark::upsert(
                    $upsertData,
                    ['student_id', 'assessment_template_id'],
                    ['score', 'teacher_id', 'section_id', 'updated_at']
                );

                // Manual Audit Injection
                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'event' => 'bulk_grade_import',
                    'auditable_type' => StudentMark::class,
                    'auditable_id' => $assignment->subject_id,
                    'new_values' => ['subject_id' => $assignment->subject_id, 'term_id' => $request->term_id, 'section_id' => $assignment->section_id, 'count' => count($upsertData)],
                    'url' => request()->fullUrl(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
            
            DB::commit();
            fclose($file);
            
            // Recalculate Statistics
            $gradingService = app(\App\Services\GradingService::class);
            $gradingService->recalculateSectionStatistics($section, $term, \App\Models\AcademicYear::findOrFail($academicYearId));
            
            // Clear roster cache
            \Illuminate\Support\Facades\Cache::forget("roster_data_{$section->id}_{$term->id}_{$academicYearId}");

            if (empty($upsertData)) {
                return back()->with('warning', 'No grades were imported.');
            }
            
            $message = "Imported " . count($upsertData) . " grades for $successCount students.";
            if (!empty($errors)) {
                return back()->with('warning', $message . ' Errors: ' . implode('; ', array_slice($errors, 0, 5)));
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($file)) fclose($file);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Store grades for a specific assignment.
     */
    public function store(Request $request, TeacherAssignment $assignment)
    {
        $request->validate([
            'term_id' => 'required|exists:terms,id',
            'marks' => 'required|array',
            'marks.*' => 'array',
        ]);

        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        try {
            $result = \App\Actions\Grades\StoreBulkGrades::run([
                'academic_year_id' => $assignment->academic_year_id,
                'term_id' => $request->term_id,
                'subject_id' => $assignment->subject_id,
                'section_id' => $assignment->section_id,
                'marks' => $request->marks,
            ], Auth::id());
            
            // Recalculate Statistics
            $gradingService = app(\App\Services\GradingService::class);
            $gradingService->recalculateSectionStatistics($assignment->section, Term::findOrFail($request->term_id), \App\Models\AcademicYear::findOrFail($assignment->academic_year_id));
            
            // Clear roster cache
            \Illuminate\Support\Facades\Cache::forget("roster_data_{$assignment->section_id}_{$request->term_id}_{$assignment->academic_year_id}");

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
