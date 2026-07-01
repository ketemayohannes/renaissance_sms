<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SubjectAnalysisReport;
use App\Models\StudentMark;
use App\Models\AssessmentType;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultAnalysisController extends Controller
{
    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    public function index()
    {
        $activeYear = AcademicYear::active()->first();
        
        $allAssignments = TeacherAssignment::where('academic_year_id', $activeYear->id)
            ->with(['section.gradeLevel', 'subject', 'teacher'])
            ->get();

        // Group by Teacher, Grade Level, and Subject
        $assignments = $allAssignments->groupBy(function($a) {
            return $a->teacher_id . '-' . $a->section->grade_level_id . '-' . $a->subject_id;
        })->map(function($group) {
            // Get the first assignment as representative
            $first = $group->first();
            
            // Collect all section names
            $sections = $group->pluck('section.name')->unique()->sort()->values()->toArray();
            $sectionLabel = count($sections) > 1 ? implode(' & ', $sections) : $sections[0];
            
            // Attach formatted label
            $first->formatted_label = "{$first->teacher->name}: {$first->section->gradeLevel->name} - {$sectionLabel}: {$first->subject->name}";
            
            return $first;
        })->values();

        $terms = Term::where('academic_year_id', $activeYear->id)
            ->where('type', 'quarter')
            ->orderBy('start_date')
            ->get();

        return view('admin.reports.result_analysis.index', compact('assignments', 'terms'));
    }

    public function show(TeacherAssignment $assignment, Request $request)
    {
        $termId = $request->input('term_id');
        if (!$termId) {
            return redirect()->route('admin.result-analysis.index')->with('error', 'Please select a term.');
        }

        $term = Term::findOrFail($termId);
        $academicYear = AcademicYear::findOrFail($assignment->academic_year_id);
        $subject = $assignment->subject;
        $gradeLevelId = $assignment->section->grade_level_id;
        $teacherId = $assignment->teacher_id;

        // Resolve the TERM_TOTAL type ID so we can exclude it when summing components.
        // We always want to use the sum of individual component marks (quiz, tests, exams, etc.)
        // rather than a separately-saved TERM_TOTAL row, which can be stale.
        $termTotalTypeId = AssessmentType::where('code', 'TERM_TOTAL')->value('id');

        $relevantAssignments = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYear->id)
            ->where('subject_id', $subject->id)
            ->whereHas('section', function($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            })
            ->with(['section', 'subject'])
            ->get();

        $sectionData = [];
        $grandTotalAnalysis = [
            '0-49'           => ['male' => 0, 'female' => 0, 'total' => 0],
            '50-74'          => ['male' => 0, 'female' => 0, 'total' => 0],
            '75-100'         => ['male' => 0, 'female' => 0, 'total' => 0],
            'total_students' => ['male' => 0, 'female' => 0, 'total' => 0],
        ];

        // Calculate grouped section names for the title
        $sections = $relevantAssignments->pluck('section.name')->unique()->sort()->values()->toArray();
        $sectionLabel = count($sections) > 1 ? implode(' & ', $sections) : $sections[0];

        foreach ($relevantAssignments as $relAssignment) {
            $section = $relAssignment->section;

            if ($subject->is_elective) {
                $students = $section->students()
                    ->whereHas('electives', function($q) use ($subject, $academicYear) {
                        $q->where('subject_id', $subject->id)
                          ->where('student_electives.academic_year_id', $academicYear->id);
                    })
                    ->wherePivotIn('status', ['active', 'completed'])
                    ->where('students.is_active', true)
                    ->get();
            } else {
                $students = $section->students()
                    ->wherePivot('academic_year_id', $academicYear->id)
                    ->wherePivotIn('status', ['active', 'completed'])
                    ->where('students.is_active', true)
                    ->get();
            }

            $studentIds = $students->pluck('id');

            // Pre-fetch the TERM_TOTAL template IDs so we can exclude them cleanly.
            // Using whereNotIn on template IDs is the safest way to avoid the stale
            // master-sheet override — no subquery scoping issues.
            $termTotalTemplateIds = $termTotalTypeId
                ? \App\Models\AssessmentTemplate::where('assessment_type_id', $termTotalTypeId)
                    ->pluck('id')
                : collect();

            // Fetch component marks only (no TERM_TOTAL rows), sum them per student.
            $allComponentMarks = StudentMark::whereIn('student_id', $studentIds)
                ->where('term_id', $term->id)
                ->where('subject_id', $subject->id)
                ->when($termTotalTemplateIds->isNotEmpty(), function($q) use ($termTotalTemplateIds) {
                    $q->whereNotIn('assessment_template_id', $termTotalTemplateIds);
                })
                ->get()
                ->groupBy('student_id');

            $analysis = [
                '0-49'           => ['male' => 0, 'female' => 0, 'total' => 0],
                '50-74'          => ['male' => 0, 'female' => 0, 'total' => 0],
                '75-100'         => ['male' => 0, 'female' => 0, 'total' => 0],
                'total_students' => ['male' => 0, 'female' => 0, 'total' => 0],
            ];

            foreach ($students as $student) {
                // Sum all component marks for this student's subject
                $score = $allComponentMarks->get($student->id, collect())->sum('score');
                $gender = in_array(strtolower($student->gender), ['female', 'f']) ? 'female' : 'male';
                $range  = $score <= 49 ? '0-49' : ($score <= 74 ? '50-74' : '75-100');
                
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
                'analysis'   => $analysis,
                'report'     => $report,
            ];
        }

        $globalReport = SubjectAnalysisReport::where('teacher_assignment_id', $assignment->id)
            ->where('term_id', $term->id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        return view('admin.reports.result_analysis.show', compact(
            'assignment', 'term', 'academicYear', 'sectionData', 'grandTotalAnalysis', 'globalReport', 'sectionLabel'
        ));
    }
}
