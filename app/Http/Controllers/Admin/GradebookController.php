<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradebookController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::latest()->get();
        $gradeLevels = GradeLevel::all();
        
        return view('admin.gradebook.index', compact('academicYears', 'gradeLevels'));
    }

    public function entry(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $section = Section::with(['gradeLevel', 'students'])->findOrFail($request->section_id);
        $subject = Subject::findOrFail($request->subject_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $term = \App\Models\Term::findOrFail($request->term_id);
        
        
        // Fetch assessment templates for this context
        $gradeComponents = \App\Models\AssessmentTemplate::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->whereHas('assignments', function($query) use ($section, $subject) {
                $query->where('grade_level_id', $section->grade_level_id)
                      ->where('subject_id', $subject->id);
            })
            ->orderBy('order')
            ->orderBy('order')
            ->get();

        // VALIDATION: Check for Elective/Regular vs Term Type conflicts
        if ($subject->is_elective && $term->type === 'quarter') {
            return back()->with('error', 'Elective subjects are only assessed in Semester terms.');
        }

        if (!$subject->is_elective && $term->type === 'semester') {
            return back()->with('error', 'Regular subjects are assessed in Quarters. Semester grades are calculated automatically.');
        }

        // Fetch students enrolled in this section
        if ($subject->is_elective) {
            // For electives, only get students enrolled in this specific subject
            $students = $section->students()
                ->whereHas('electives', function($q) use ($subject, $academicYear) {
                    $q->where('subject_id', $subject->id)
                      ->where('student_electives.academic_year_id', $academicYear->id);
                })
                ->orderBy('first_name')
                ->get();
        } else {
            // For regular subjects, get all students in the section
            $students = $section->students()->orderBy('first_name')->get();
        }

        // Fetch existing marks
        $existingMarks = StudentMark::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('subject_id', $subject->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        return view('admin.gradebook.entry', compact('section', 'subject', 'academicYear', 'term', 'students', 'gradeComponents', 'existingMarks'));
    }

    public function getSections(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
        ]);

        $sections = Section::where('academic_year_id', $request->academic_year_id)
            ->where('grade_level_id', $request->grade_level_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($sections);
    }

    public function getSubjects(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
        ]);

        // Fetch subjects linked to this grade level for the given academic year
        $subjects = DB::table('grade_level_subjects')
            ->join('subjects', 'grade_level_subjects.subject_id', '=', 'subjects.id')
            ->where('grade_level_subjects.academic_year_id', $request->academic_year_id)
            ->where('grade_level_subjects.grade_level_id', $request->grade_level_id)
            ->select('subjects.id', 'subjects.name', 'subjects.code')
            ->orderBy('subjects.name')
            ->get();

        return response()->json($subjects);
    }

    public function getTerms(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $terms = \App\Models\Term::where('academic_year_id', $request->academic_year_id)
            ->orderBy('start_date')
            ->get(['id', 'name']);

        return response()->json($terms);
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|array',
            'marks.*' => 'array',
        ]);

        $term = Term::findOrFail($request->term_id);
        if (!$term->is_grading_open) {
            return back()->with('error', 'Grading for this term is currently closed.');
        }
        
        $subject = Subject::findOrFail($request->subject_id);
        
        // VALIDATION: Check for Elective/Regular vs Term Type conflicts
        if ($subject->is_elective && $term->type === 'quarter') {
            return back()->with('error', 'Elective subjects are only assessed in Semester terms.');
        }

        if (!$subject->is_elective && $term->type === 'semester') {
            return back()->with('error', 'Regular subjects are assessed in Quarters. Semester grades are calculated automatically.');
        }

        DB::beginTransaction();
        try {
            // Load all assessment templates for validation
            $templates = \App\Models\AssessmentTemplate::whereIn('id', 
                collect($request->marks)->flatMap(fn($components) => array_keys($components))->unique()
            )->get()->keyBy('id');

            $errors = [];
            
            foreach ($request->marks as $studentId => $components) {
                foreach ($components as $templateId => $data) {
                    // Skip if score is empty (not entered)
                    if (!isset($data['score']) || $data['score'] === '') {
                        continue;
                    }

                    // Validate score doesn't exceed max_score
                    $template = $templates->get($templateId);
                    if ($template && $data['score'] > $template->max_score) {
                        $student = \App\Models\Student::find($studentId);
                        $studentName = $student ? $student->first_name . ' ' . $student->last_name : "Student ID $studentId";
                        $errors[] = "$studentName: Score {$data['score']} exceeds maximum {$template->max_score} for {$template->name}";
                        continue; // Skip this entry
                    }

                    StudentMark::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'academic_year_id' => $request->academic_year_id,
                            'term_id' => $request->term_id,
                            'subject_id' => $request->subject_id,
                            'assessment_template_id' => $templateId,
                        ],
                        [
                            'section_id' => $request->section_id,
                            'teacher_id' => auth()->id(),
                            'score' => $data['score'],
                            'remarks' => $data['remarks'] ?? null,
                        ]
                    );
                }
            }
            
            DB::commit();
            
            if (!empty($errors)) {
                return back()->with('warning', 'Marks saved with warnings: ' . implode('; ', $errors));
            }
            
            return back()->with('success', 'Marks saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving marks: ' . $e->getMessage());
        }
    }

    public function exportTemplate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $section = Section::findOrFail($request->section_id);
        $subject = Subject::findOrFail($request->subject_id);
        $term = Term::findOrFail($request->term_id);
        
        // Fetch students
        $students = $section->students()->orderBy('first_name')->get();
        
        // Fetch assessment templates
        $templates = \App\Models\AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $term = Term::findOrFail($request->term_id);
        if (!$term->is_grading_open) {
            return back()->with('error', 'Grading for this term is currently closed.');
        }

        $subject = Subject::findOrFail($request->subject_id);

        // VALIDATION: Check for Elective/Regular vs Term Type conflicts
        if ($subject->is_elective && $term->type === 'quarter') {
            return back()->with('error', 'Elective subjects are only assessed in Semester terms.');
        }

        if (!$subject->is_elective && $term->type === 'semester') {
            return back()->with('error', 'Regular subjects are assessed in Quarters. Semester grades are calculated automatically.');
        }

        $section = Section::findOrFail($request->section_id);
        $subject = Subject::findOrFail($request->subject_id);
        
        // Fetch assessment templates
        $templates = \App\Models\AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('term_id', $request->term_id)
            ->where('is_active', true)
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

        DB::beginTransaction();
        try {
            $successCount = 0;
            $errors = [];
            
            while (($line = fgets($file)) !== false) {
                $row = str_getcsv($line, $delimiter);
                
                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) continue;
                
                $studentId = trim($row[0]);
                $student = \App\Models\Student::where('student_id', $studentId)->first();
                
                if (!$student) {
                    $errors[] = "Student ID $studentId not found";
                    continue;
                }

                foreach ($columnMap as $index => $template) {
                    $score = $row[$index] ?? null;
                    $score = trim($score);
                    
                    if ($score === null || $score === '') continue;
                    
                    if (!is_numeric($score)) {
                        $errors[] = "Invalid score for $studentId in {$template->name}";
                        continue;
                    }

                    if ($score > $template->max_score) {
                        $errors[] = "Score $score exceeds max {$template->max_score} for $studentId in {$template->name}";
                        continue;
                    }

                    StudentMark::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_year_id' => $request->academic_year_id,
                            'term_id' => $request->term_id,
                            'subject_id' => $request->subject_id,
                            'assessment_template_id' => $template->id,
                        ],
                        [
                            'section_id' => $request->section_id,
                            'teacher_id' => auth()->id(),
                            'score' => $score,
                        ]
                    );
                }
                $successCount++;
            }
            
            DB::commit();
            fclose($file);
            
            $message = "Imported grades for $successCount students.";
            if (!empty($errors)) {
                return back()->with('warning', $message . ' Errors: ' . implode('; ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '...' : ''));
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
