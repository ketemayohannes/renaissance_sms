<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Term;
use App\Models\Subject;
use App\Services\AcademicReportService;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AcademicReportController extends Controller
{
    protected $reportService;
    protected $gradingService;

    public function __construct(AcademicReportService $reportService, GradingService $gradingService)
    {
        $this->reportService = $reportService;
        $this->gradingService = $gradingService;
    }
    public function index()
    {
        // PERFORMANCE: Use cached data
        $academicYears = \App\Models\AcademicYear::latest()->get(); // Usually few years, but could cache if needed
        $gradeLevels = \App\Helpers\CachedData::gradeLevels();
        $divisions = \App\Helpers\CachedData::divisions();
        return view('admin.academic-reports.index', compact('academicYears', 'gradeLevels', 'divisions'));
    }

    public function settings()
    {
        $settings = \App\Models\AcademicReportSetting::firstOrNew();
        // PERFORMANCE: Use cached data
        $subjects = \App\Helpers\CachedData::subjects();
        $gradeLevels = \App\Helpers\CachedData::gradeLevels();
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

        // Increase resources for report generation
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $termId = $request->term_id;
            
            \Illuminate\Support\Facades\Log::info("Starting academic report generation: term {$termId}, section {$request->section_id}");

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
                        $count = count($scores);
                        $passed = collect($scores)->filter(fn($s) => $s >= $passMark)->count();
                        $subjectStats[$subject->id] = (object)[
                            'appeared' => $count,
                            'passed' => $passed,
                            'failed' => $count - $passed,
                            'pass_rate' => $count > 0 ? ($passed / $count) * 100 : 0,
                            'highest' => max($scores),
                            'lowest' => min($scores),
                            'average' => $count > 0 ? array_sum($scores) / $count : 0,
                        ];
                    }
                }

                $classAvgs = collect($reports)->map(fn($r) => $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0));
                $classCount = $classAvgs->count();
                $classStats = (object)[
                    'total_students' => $classCount,
                    'class_average' => $classCount > 0 ? $classAvgs->average() : 0,
                    'total_passed' => $classAvgs->filter(fn($avg) => $avg >= $passMark)->count(),
                    'highest_avg' => $classAvgs->max(),
                ];

                $topPerformers = collect($reports)->sortByDesc(fn($r) => $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0))->take(5);

                return view('admin.academic-reports.analysis', array_merge($params, compact('subjectStats', 'classStats', 'topPerformers')));
            }

            return view('admin.academic-reports.show', $params);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Academic Report Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error generating report: ' . $e->getMessage());
        }
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

    public function recalculate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'section_id' => 'required|exists:sections,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $termId = $request->term_id;
        $section = Section::findOrFail($request->section_id);

        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'academic_year_id' => $academicYear->id]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        $this->gradingService->recalculateSectionStatistics($section, $term, $academicYear);
        
        // Clear roster cache since data changed
        \Illuminate\Support\Facades\Cache::forget("roster_data_{$section->id}_{$termId}_{$academicYear->id}");

        return back()->with('success', 'Statistics recalculated successfully.');
    }

    public function matrixReorder()
    {
        $settings = \App\Models\AcademicReportSetting::firstOrNew();
        $subjects = \App\Helpers\CachedData::subjects();
        $gradeLevels = \App\Helpers\CachedData::gradeLevels();
        return view('admin.academic-reports.matrix-reorder', compact('settings', 'subjects', 'gradeLevels'));
    }

    public function updateMatrixOrder(Request $request)
    {
        $request->validate([
            'subject_order' => 'nullable|array',
        ]);

        $settings = \App\Models\AcademicReportSetting::firstOrNew();
        $displayOptions = $settings->display_options ?? [];
        
        $displayOptions['matrix_subject_order'] = collect($request->subject_order)
            ->filter(fn($val) => $val !== null && $val !== '')
            ->map(fn($val) => (int)$val)
            ->toArray();
            
        $settings->display_options = $displayOptions;
        $settings->save();

        return back()->with('success', 'Matrix subject order updated successfully.');
    }

}
}
