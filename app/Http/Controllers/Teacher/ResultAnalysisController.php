<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\SubjectAnalysisReport;
use App\Models\ReportCardSetting;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ResultAnalysisController extends Controller
{
    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    public function index(Request $request)
    {
        $teacher = Auth::user();
        $activeYear = AcademicYear::active()->first();
        
        $assignments = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $activeYear->id)
            ->with(['section.gradeLevel', 'subject'])
            ->get();

        $uniqueAssignments = $assignments->groupBy(function($a) {
            return $a->section->grade_level_id . '-' . $a->subject_id;
        })->map(function($group) {
            $first = $group->first();
            $sections = $group->pluck('section.name')->sort()->values();
            $gradeName = $first->section->gradeLevel->name;
            $subjectName = $first->subject->name;
            
            if ($sections->count() > 1) {
                $first->formatted_label = "{$gradeName} - " . $sections->implode(' & ') . ": {$subjectName}";
            } else {
                $first->formatted_label = "{$gradeName}: {$subjectName}";
            }
            
            return $first;
        });

        $terms = Term::where('academic_year_id', $activeYear->id)
            ->where('type', 'quarter')
            ->orderBy('start_date')
            ->get();

        return view('teacher.reports.result_analysis.index', [
            'assignments' => $uniqueAssignments,
            'terms' => $terms,
            'activeYear' => $activeYear
        ]);
    }

    public function show(TeacherAssignment $assignment, Request $request)
    {
        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        $termId = $request->input('term_id');
        if (!$termId) {
            return redirect()->route('teacher.reports.result-analysis')->with('error', 'Please select a term.');
        }

        $term = Term::findOrFail($termId);
        $academicYear = AcademicYear::findOrFail($assignment->academic_year_id);
        $subject = $assignment->subject;
        $gradeLevelId = $assignment->section->grade_level_id;

        $relevantAssignments = TeacherAssignment::where('teacher_id', Auth::id())
            ->where('academic_year_id', $academicYear->id)
            ->where('subject_id', $subject->id)
            ->whereHas('section', function($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            })
            ->with(['section', 'subject'])
            ->get();

        $sectionData = [];
        $grandTotalAnalysis = [
            '0-49' => ['male' => 0, 'female' => 0, 'total' => 0],
            '50-74' => ['male' => 0, 'female' => 0, 'total' => 0],
            '75-100' => ['male' => 0, 'female' => 0, 'total' => 0],
            'total_students' => ['male' => 0, 'female' => 0, 'total' => 0],
        ];

        foreach ($relevantAssignments as $relAssignment) {
            $section = $relAssignment->section;
            if ($subject->is_elective) {
                $students = $section->students()
                    ->whereHas('electives', function($q) use ($subject, $academicYear) {
                        $q->where('subject_id', $subject->id)
                          ->where('student_electives.academic_year_id', $academicYear->id);
                    })
                    ->wherePivot('status', 'active')
                    ->where('students.is_active', true)
                    ->get();
            } else {
                $students = $section->students()
                    ->wherePivot('academic_year_id', $academicYear->id)
                    ->wherePivot('status', 'active')
                    ->where('students.is_active', true)
                    ->get();
            }

            $batchResults = $this->gradingService->calculateSectionTotals($students, $term, $academicYear, collect([$subject]));
            
            $analysis = [
                '0-49' => ['male' => 0, 'female' => 0, 'total' => 0],
                '50-74' => ['male' => 0, 'female' => 0, 'total' => 0],
                '75-100' => ['male' => 0, 'female' => 0, 'total' => 0],
                'total_students' => ['male' => 0, 'female' => 0, 'total' => 0],
            ];

            foreach ($students as $student) {
                $score = $batchResults[$student->id]['marks'][$subject->id] ?? 0;
                $gender = strtolower($student->gender) === 'female' || strtolower($student->gender) === 'f' ? 'female' : 'male';
                $range = $score <= 49 ? '0-49' : ($score <= 74 ? '50-74' : '75-100');
                
                $analysis[$range][$gender]++;
                $analysis[$range]['total']++;
                $analysis['total_students'][$gender]++;
                $analysis['total_students']['total']++;

                $grandTotalAnalysis[$range][$gender]++;
                $grandTotalAnalysis[$range]['total']++;
                $grandTotalAnalysis['total_students'][$gender]++;
                $grandTotalAnalysis['total_students']['total']++;
            }

            $report = SubjectAnalysisReport::where('teacher_assignment_id', $relAssignment->id)
                ->where('term_id', $term->id)
                ->where('academic_year_id', $academicYear->id)
                ->first();

            $sectionData[] = [
                'assignment' => $relAssignment,
                'analysis' => $analysis,
                'report' => $report
            ];
        }

        $globalReport = SubjectAnalysisReport::where('teacher_assignment_id', $assignment->id)
            ->where('term_id', $term->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        return view('teacher.reports.result_analysis.show', compact(
            'assignment', 'term', 'academicYear', 'sectionData', 'grandTotalAnalysis', 'globalReport'
        ));
    }

    public function store(TeacherAssignment $assignment, Request $request)
    {
        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        $request->validate([
            'term_id' => 'required|exists:terms,id',
            'remarks' => 'required|array',
            'comparison_comment' => 'nullable|string',
            'problems_encountered' => 'nullable|string',
            'solutions_implemented' => 'nullable|string',
            'additional_comment' => 'nullable|string',
        ]);

        foreach ($request->remarks as $assignmentId => $remark) {
            SubjectAnalysisReport::updateOrCreate(
                [
                    'teacher_assignment_id' => $assignmentId,
                    'term_id' => $request->term_id,
                    'academic_year_id' => $assignment->academic_year_id,
                ],
                [
                    'section_remark' => $remark,
                    'comparison_comment' => $request->comparison_comment,
                    'problems_encountered' => $request->problems_encountered,
                    'solutions_implemented' => $request->solutions_implemented,
                    'additional_comment' => $request->additional_comment,
                ]
            );
        }

        return back()->with('success', 'Analysis report saved successfully.');
    }

    public function print(TeacherAssignment $assignment, Request $request)
    {
        if ($assignment->teacher_id !== Auth::id() && !Auth::user()->hasRole('Super Admin')) {
            abort(403);
        }

        $termId = $request->input('term_id');
        $term = Term::findOrFail($termId);
        $semester = Term::find($term->parent_term_id); // Fetch parent semester if exists
        $academicYear = AcademicYear::findOrFail($assignment->academic_year_id);
        $subject = $assignment->subject;
        $gradeLevelId = $assignment->section->grade_level_id;

        $relevantAssignments = TeacherAssignment::where('teacher_id', Auth::id())
            ->where('academic_year_id', $academicYear->id)
            ->where('subject_id', $subject->id)
            ->whereHas('section', function($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            })
            ->with(['section', 'subject'])
            ->get();

        $sectionData = [];
        $grandTotalAnalysis = [
            '0-49' => ['male' => 0, 'female' => 0, 'total' => 0],
            '50-74' => ['male' => 0, 'female' => 0, 'total' => 0],
            '75-100' => ['male' => 0, 'female' => 0, 'total' => 0],
            'total_students' => ['male' => 0, 'female' => 0, 'total' => 0],
        ];

        foreach ($relevantAssignments as $relAssignment) {
            $section = $relAssignment->section;
            if ($subject->is_elective) {
                $students = $section->students()
                    ->whereHas('electives', function($q) use ($subject, $academicYear) {
                        $q->where('subject_id', $subject->id)
                          ->where('student_electives.academic_year_id', $academicYear->id);
                    })
                    ->wherePivot('status', 'active')
                    ->where('students.is_active', true)
                    ->get();
            } else {
                $students = $section->students()
                    ->wherePivot('academic_year_id', $academicYear->id)
                    ->wherePivot('status', 'active')
                    ->where('students.is_active', true)
                    ->get();
            }

            $batchResults = $this->gradingService->calculateSectionTotals($students, $term, $academicYear, collect([$subject]));
            
            $analysis = [
                '0-49' => ['male' => 0, 'female' => 0, 'total' => 0],
                '50-74' => ['male' => 0, 'female' => 0, 'total' => 0],
                '75-100' => ['male' => 0, 'female' => 0, 'total' => 0],
                'total_students' => ['male' => 0, 'female' => 0, 'total' => 0],
            ];

            foreach ($students as $student) {
                $score = $batchResults[$student->id]['marks'][$subject->id] ?? 0;
                $gender = strtolower($student->gender) === 'female' || strtolower($student->gender) === 'f' ? 'female' : 'male';
                $range = $score <= 49 ? '0-49' : ($score <= 74 ? '50-74' : '75-100');
                $analysis[$range][$gender]++;
                $analysis[$range]['total']++;
                $analysis['total_students'][$gender]++;
                $analysis['total_students']['total']++;

                $grandTotalAnalysis[$range][$gender]++;
                $grandTotalAnalysis[$range]['total']++;
                $grandTotalAnalysis['total_students'][$gender]++;
                $grandTotalAnalysis['total_students']['total']++;
            }

            $report = SubjectAnalysisReport::where('teacher_assignment_id', $relAssignment->id)
                ->where('term_id', $term->id)
                ->where('academic_year_id', $academicYear->id)
                ->first();

            $sectionData[] = [
                'assignment' => $relAssignment,
                'analysis' => $analysis,
                'report' => $report
            ];
        }

        $globalReport = SubjectAnalysisReport::where('teacher_assignment_id', $assignment->id)
            ->where('term_id', $term->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $settings = ReportCardSetting::first();

        $data = [
            'assignment' => $assignment,
            'term' => $term,
            'semester' => $semester,
            'academicYear' => $academicYear,
            'sectionData' => $sectionData,
            'grandTotalAnalysis' => $grandTotalAnalysis,
            'globalReport' => $globalReport,
            'teacher' => Auth::user(),
            'settings' => $settings,
        ];

        $pdf = Pdf::loadView('teacher.reports.result_analysis.print', $data)
            ->setPaper('a4', 'landscape');
        return $pdf->stream("Result_Analysis_{$assignment->section->gradeLevel->name}_{$subject->name}_{$term->name}.pdf");
    }
}
