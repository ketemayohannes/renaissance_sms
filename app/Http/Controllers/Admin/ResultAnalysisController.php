<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SubjectAnalysisReport;
use App\Services\GradingService;
use Illuminate\Http\Request;

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
            '0-49' => ['male' => 0, 'female' => 0, 'total' => 0],
            '50-74' => ['male' => 0, 'female' => 0, 'total' => 0],
            '75-100' => ['male' => 0, 'female' => 0, 'total' => 0],
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

        return view('admin.reports.result_analysis.show', compact(
            'assignment', 'term', 'academicYear', 'sectionData', 'grandTotalAnalysis', 'globalReport', 'sectionLabel'
        ));
    }
}
