<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Grades\ImportGradesFromCsv;
use App\Actions\Grades\StoreBulkGrades;
use App\Helpers\CachedData;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentType;
use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradebookController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view subject gradebook', only: ['index', 'entry', 'marksheet', 'exportTemplate', 'getSections', 'getSubjects', 'getTerms']),
            new Middleware('permission:edit subject gradebook', only: ['store', 'import']),
        ];
    }

    public function index()
    {
        // PERFORMANCE: Use cached data
        $academicYears = CachedData::academicYears();
        $gradeLevels = CachedData::gradeLevels();

        $currentYear = CachedData::activeAcademicYear() ?? $academicYears->first();
        $terms = $currentYear ? Term::where('academic_year_id', $currentYear->id)->get() : collect();

        // Initial state for dependent dropdowns
        $sections = collect();
        $subjects = collect();

        return view('admin.gradebook.index', compact('academicYears', 'gradeLevels', 'terms', 'sections', 'subjects'));
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
        $term = Term::findOrFail($request->term_id);

        // Fetch assessment templates for this context
        // IMPORTANT: Exclude TERM_TOTAL type - it's a calculated value, not an editable assessment
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->first();

        $gradeComponents = AssessmentTemplate::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function ($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->whereHas('assignments', function ($query) use ($section, $subject) {
                $query->where('grade_level_id', $section->grade_level_id)
                    ->where('subject_id', $subject->id);
            })
            ->orderBy('order')
            ->get();

        // Fetch Term Total template separately (for display if exists)
        $termTotalTemplate = null;
        if ($termTotalType) {
            $termTotalTemplate = AssessmentTemplate::where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->where('assessment_type_id', $termTotalType->id)
                ->whereHas('assignments', function ($query) use ($section, $subject) {
                    $query->where('grade_level_id', $section->grade_level_id)
                        ->where('subject_id', $subject->id);
                })
                ->first();
        }

        // VALIDATION: Check for Elective/Regular vs Term Type conflicts
        if ($subject->is_elective && $term->type === 'quarter') {
        }

        if (! $subject->is_elective && $term->type === 'semester') {
        }

        // Fetch students enrolled in this section
        if ($subject->is_elective) {
            // For electives, only get students enrolled in this specific subject
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivotIn('status', ['active', 'completed'])
                ->where('students.is_active', true)
                ->whereHas('electives', function ($q) use ($subject, $academicYear) {
                    $q->where('subject_id', $subject->id)
                        ->where('student_electives.academic_year_id', $academicYear->id);
                })
                ->orderBy('students.first_name')
                ->get();
        } else {
            // For regular subjects, get all students in the section
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivotIn('status', ['active', 'completed'])
                ->where('students.is_active', true)
                ->orderBy('students.first_name')
                ->get();
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
                // This prevents double counting if a 'TERM_TOTAL' mark exists from the master sheet
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
                        'total' => $totalScore,
                    ]);
                }
            }
        }

        // 1. Graded Average: (Sum of all scores) / (Number of Graded Students)
        $gradedAverage = $studentTotals->count() > 0 ? $studentTotals->avg('total') : 0;

        // 2. Section Average: (Sum of all scores) / (Total Students in Section)
        $totalScoreSum = $studentTotals->sum('total');
        $totalStudentCount = $students->count();
        $classAverage = $totalStudentCount > 0 ? $totalScoreSum / $totalStudentCount : 0;

        $top3Students = $studentTotals->sortByDesc('total')->take(3);
        $bottom3Students = $studentTotals->sortBy('total')->take(3);

        return view('admin.gradebook.entry', compact(
            'section', 'subject', 'academicYear', 'term', 'students', 'gradeComponents',
            'existingMarks', 'gradedAverage', 'classAverage', 'top3Students', 'bottom3Students',
            'termTotalTemplate'
        ));
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

        $terms = Term::where('academic_year_id', $request->academic_year_id)
            ->orderBy('start_date')
            ->get(['id', 'name', 'type'])
            ->toArray();

        // Check if there are semesters to allow yearly report
        $hasSemesters = false;
        foreach ($terms as $term) {
            if ($term['type'] === 'semester') {
                $hasSemesters = true;
                break;
            }
        }

        if ($hasSemesters) {
            $terms[] = [
                'id' => 'yearly',
                'name' => 'Yearly Report',
                'type' => 'yearly',
            ];
        }

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

        try {
            $result = StoreBulkGrades::run($request->only([
                'academic_year_id', 'term_id', 'subject_id', 'section_id', 'marks',
            ]), auth()->id());

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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
        $students = $section->students()
            ->wherePivot('academic_year_id', $request->academic_year_id)
            ->wherePivotIn('status', ['active', 'completed'])
            ->where('students.is_active', true)
            ->orderBy('students.first_name')
            ->get();

        // Fetch assessment templates (excluding TERM_TOTAL - it's a calculated value)
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->first();

        $templates = AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function ($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->orderBy('order')
            ->get();

        $filename = "grades_{$section->name}_{$subject->name}.csv";
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($students, $templates) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Add separator hint for Excel
            fwrite($file, "sep=,\n");

            // Header row
            $headerRow = ['Student ID', 'Student Name'];
            foreach ($templates as $template) {
                $headerRow[] = $template->name." (Max: {$template->max_score})";
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
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $result = ImportGradesFromCsv::run(
            $request->file('file')->getRealPath(),
            Section::findOrFail($request->section_id),
            Subject::findOrFail($request->subject_id),
            Term::findOrFail($request->term_id),
            (int) $request->academic_year_id,
            auth()->id(),
        );

        return back()->with($result['type'], $result['message']);
    }

    public function marksheet(Request $request)
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
        $term = Term::findOrFail($request->term_id);
        $settings = ReportCardSetting::firstOrNew();

        // Fetch assessment templates
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->first();

        $gradeComponents = AssessmentTemplate::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function ($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->whereHas('assignments', function ($query) use ($section, $subject) {
                $query->where('grade_level_id', $section->grade_level_id)
                    ->where('subject_id', $subject->id);
            })
            ->with('assessmentType')
            ->orderBy('order')
            ->get();

        // Fetch Term Total template separately (for display if exists)
        $termTotalTemplate = null;
        if ($termTotalType) {
            $termTotalTemplate = AssessmentTemplate::where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->where('assessment_type_id', $termTotalType->id)
                ->whereHas('assignments', function ($query) use ($section, $subject) {
                    $query->where('grade_level_id', $section->grade_level_id)
                        ->where('subject_id', $subject->id);
                })
                ->first();
        }

        // Fetch students enrolled in this section
        if ($subject->is_elective) {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivotIn('status', ['active', 'completed'])
                ->where('students.is_active', true)
                ->whereHas('electives', function ($q) use ($subject, $academicYear) {
                    $q->where('subject_id', $subject->id)
                        ->where('student_electives.academic_year_id', $academicYear->id);
                })
                ->orderBy('students.first_name')
                ->get();
        } else {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivotIn('status', ['active', 'completed'])
                ->where('students.is_active', true)
                ->orderBy('students.first_name')
                ->get();
        }

        // Fetch existing marks
        $existingMarks = StudentMark::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('subject_id', $subject->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        // Fetch the assigned teacher
        $assignment = TeacherAssignment::where('academic_year_id', $academicYear->id)
            ->where('section_id', $section->id)
            ->where('subject_id', $subject->id)
            ->first();

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
            'teacher' => $assignment ? $assignment->teacher : Auth::user(),
        ];

        $viewName = $request->query('summary') ? 'teacher.gradebook.summary_marksheet_pdf' : 'teacher.gradebook.marksheet_pdf';
        $paperOrientation = $request->query('summary') ? 'portrait' : 'landscape';
        $filenamePrefix = $request->query('summary') ? 'summary_marksheet' : 'marksheet';

        $pdf = Pdf::loadView($viewName, $data);
        $pdf->setPaper('a4', $paperOrientation);

        $filename = "{$filenamePrefix}_{$section->name}_{$subject->code}_{$term->name}.pdf";

        return $pdf->download($filename);
    }
}
