<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Grades\ImportGradesFromCsv;
use App\Actions\Grades\StoreBulkGrades;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentType;
use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\StudentMark;
use App\Models\TeacherAssignment;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradebookController extends Controller
{
    /**
     * Show the grade entry form for a specific assignment.
     */
    public function entry(Request $request, TeacherAssignment $assignment)
    {
        // Security check: ensure this assignment belongs to the authenticated teacher.
        if ($assignment->teacher_id !== Auth::id() && ! Auth::user()->hasRole('Super Admin')) {
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
        if (! $activeTerm) {
            $activeTerm = Term::where('academic_year_id', $academicYearId)
                ->where('type', 'quarter')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
        }

        $termId = $request->input('term_id', $activeTerm ? $activeTerm->id : null);

        // If no term is selected/active, get the closest one
        if (! $termId) {
            $firstTerm = Term::where('academic_year_id', $academicYearId)
                ->where('type', 'quarter')
                ->orderBy('start_date')
                ->first();
            if (! $firstTerm) {
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

        // Fetch students
        if ($subject->is_elective) {
            $students = $section->students()
                ->whereHas('electives', function ($q) use ($subject, $academicYear) {
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
        if ($assignment->teacher_id !== Auth::id() && ! Auth::user()->hasRole('Super Admin')) {
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
                ->whereHas('electives', function ($q) use ($subject, $academicYearId) {
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
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->first();

        $templates = AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $academicYearId)
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

    /**
     * Generate a PDF marksheet for the class.
     */
    public function marksheet(Request $request, TeacherAssignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id() && ! Auth::user()->hasRole('Super Admin')) {
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
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->first();

        $gradeComponents = AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function ($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
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

        // Fetch students
        if ($subject->is_elective) {
            $students = $section->students()
                ->whereHas('electives', function ($q) use ($subject, $academicYear) {
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
                ->get();
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

        $viewName = $request->query('summary') ? 'teacher.gradebook.summary_marksheet_pdf' : 'teacher.gradebook.marksheet_pdf';
        $paperOrientation = $request->query('summary') ? 'portrait' : 'landscape';
        $filenamePrefix = $request->query('summary') ? 'summary_marksheet' : 'marksheet';

        $pdf = Pdf::loadView($viewName, $data);
        $pdf->setPaper('a4', $paperOrientation);

        $filename = "{$filenamePrefix}_{$section->name}_{$subject->code}_{$term->name}.pdf";

        return $pdf->download($filename);
    }

    public function import(Request $request, TeacherAssignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id() && ! Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'term_id' => 'required|exists:terms,id',
        ]);

        $result = ImportGradesFromCsv::run(
            $request->file('file')->getRealPath(),
            $assignment->section,
            $assignment->subject,
            Term::findOrFail($request->term_id),
            $assignment->academic_year_id,
            Auth::id(),
        );

        return back()->with($result['type'], $result['message']);
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

        if ($assignment->teacher_id !== Auth::id() && ! Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        try {
            $result = StoreBulkGrades::run([
                'academic_year_id' => $assignment->academic_year_id,
                'term_id' => $request->term_id,
                'subject_id' => $assignment->subject_id,
                'section_id' => $assignment->section_id,
                'marks' => $request->marks,
            ], Auth::id());

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
