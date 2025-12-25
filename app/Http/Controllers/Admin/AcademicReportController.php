<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Term;
use App\Models\Subject;
use App\Services\AcademicReportService;
use Illuminate\Http\Request;

class AcademicReportController extends Controller
{
    protected $reportService;

    public function __construct(AcademicReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    public function index()
    {
        $academicYears = AcademicYear::latest()->get();
        $gradeLevels = GradeLevel::orderBy('sort_order')->get();
        $divisions = \App\Models\Division::orderBy('name')->get();
        return view('admin.academic-reports.index', compact('academicYears', 'gradeLevels', 'divisions'));
    }

    public function settings()
    {
        $settings = \App\Models\AcademicReportSetting::firstOrNew();
        $subjects = \App\Models\Subject::with('gradeLevels')->where('is_active', true)->orderBy('name')->get();
        $gradeLevels = \App\Models\GradeLevel::orderBy('sort_order')->get();
        return view('admin.academic-reports.settings', compact('settings', 'subjects', 'gradeLevels'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'school_name' => 'nullable|string|max:255',
            'roster_logo' => 'nullable|image|max:2048',
            'subject_order' => 'nullable|array',
        ]);

        $this->reportService->updateSettings($request->all(), $request->file('roster_logo'));

        return back()->with('success', 'Roster settings updated successfully.');
    }

    public function show(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'section_id' => 'required|exists:sections,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $termId = $request->term_id;
        
        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $academicYear->id]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        $section = Section::findOrFail($request->section_id);
        
        $reportType = $request->get('report_type', 'roster');

        if ($reportType === 'report_card') {
            return redirect()->route('admin.section-grades.bulk-print-report-cards', [
                'section' => $section->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $termId
            ]);
        }

        $params = $this->reportService->prepareRosterData($section, $term, $academicYear);

        if ($reportType === 'result_analysis') {
            // Internal analysis calculation - still simplified but kept in controller for now 
            // as it builds on the same data
            $subjects = $params['subjects'];
            $reports = $params['reports'];
            $subjectStats = [];
            $passMark = 50;

            foreach ($subjects as $subject) {
                $scores = [];
                foreach ($reports as $report) {
                    $score = $termId === 'yearly' ? ($report['rows']['avg']['marks'][$subject->id] ?? null) : ($report['marks'][$subject->id] ?? null);
                    if ($score !== null && $score !== '') $scores[] = (float)$score;
                }
                if (!empty($scores)) {
                    $passed = collect($scores)->filter(fn($s) => $s >= $passMark)->count();
                    $count = count($scores);
                    $subjectStats[$subject->id] = (object)[
                        'appeared' => $count,
                        'passed' => $passed,
                        'failed' => $count - $passed,
                        'pass_rate' => ($passed / $count) * 100,
                        'highest' => max($scores),
                        'lowest' => min($scores),
                        'average' => array_sum($scores) / $count,
                    ];
                }
            }

            $classAvgs = collect($reports)->map(fn($r) => $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0));
            $classStats = (object)[
                'total_students' => count($reports),
                'class_average' => $classAvgs->average(),
                'total_passed' => $classAvgs->filter(fn($avg) => $avg >= $passMark)->count(),
                'highest_avg' => $classAvgs->max(),
            ];

            $topPerformers = collect($reports)->sortByDesc(fn($r) => $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0))->take(5);

            return view('admin.academic-reports.analysis', array_merge($params, compact('subjectStats', 'classStats', 'topPerformers')));
        }

        return view('admin.academic-reports.show', $params);
    }

    public function subjectAnalysis(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $termId = $request->term_id;
        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $academicYear->id]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        $gradeLevel = GradeLevel::with('sections')->findOrFail($request->grade_level_id);
        $subject = Subject::findOrFail($request->subject_id);

        $params = $this->reportService->prepareSubjectAnalysisData($academicYear, $term, $gradeLevel, $subject);

        return view('admin.academic-reports.subject-analysis', $params);
    }

    public function gradeMatrix(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $termId = $request->term_id;
        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $academicYear->id]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        $query = GradeLevel::with('sections', 'subjects');
        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }
        $gradeLevels = $query->orderBy('sort_order', 'asc')->get();

        $params = $this->reportService->prepareGradeMatrixData($academicYear, $term, $gradeLevels);

        return view('admin.academic-reports.grade-matrix', $params);
    }

}
