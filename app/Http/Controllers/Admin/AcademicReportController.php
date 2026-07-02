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
use Barryvdh\DomPDF\Facade\Pdf;

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
                $term->incrementing = false;
                $term->id = 'yearly';
            } else {
                $term = Term::findOrFail($termId);
            }

            $section = Section::findOrFail($request->section_id);
            
            $reportType = $request->get('report_type', 'roster');

            if ($reportType === 'report_card') {
                if (auth()->user()->hasRole('Principal')) {
                    abort(403, 'Unauthorized action.');
                }
                // Show action selection page — user must manually choose to view/print or export
                return view('admin.academic-reports.report-card-actions', [
                    'section'      => $section,
                    'academicYear' => $academicYear,
                    'termId'       => $termId,
                    'termLabel'    => $termId === 'yearly' ? 'Yearly' : ($term->name ?? $termId),
                ]);
            }

            if ($reportType === 'section_top_10') {
                $params = $this->reportService->prepareRosterData($section, $term, $academicYear);
                $reports = collect($params['reports']);
                
                // Sort by average descending (yearly or term average)
                $top10 = $reports->sortByDesc(function($r) use ($term) {
                    return $term->id === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0);
                })->take(10)->values();
                
                return view('admin.academic-reports.section-top10', array_merge($params, compact('top10')));
            }

            $params = $this->reportService->prepareRosterData($section, $term, $academicYear);

            if ($reportType === 'result_analysis') {
                // Internal analysis calculation - still simplified but kept in controller for now 
                // as it builds on the same data
                $subjects = $params['subjects'];
                $reports = $params['reports'];
                $subjectStats = [];
                $passMark = 75;

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

                $subjectAvgs = collect($subjectStats)->pluck('average');
                $classAvgs = collect($reports)->map(fn($r) => $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0));
                $classCount = $classAvgs->count();
                $classStats = (object)[
                    'total_students' => $classCount,
                    'class_average' => $subjectAvgs->isNotEmpty() ? $subjectAvgs->average() : ($classCount > 0 ? $classAvgs->average() : 0),
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
            $term->incrementing = false;
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
            $term->incrementing = false;
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

    public function gradeMatrixPdf(Request $request)
    {
        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id', AcademicYear::where('is_active', true)->value('id')));
        $term = Term::findOrFail($request->get('term_id'));
        $divisionId = $request->get('division_id');
        
        $query = GradeLevel::query();
        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }
        $gradeLevels = $query->orderBy('sort_order')->get();
        
        $data = $this->reportService->prepareGradeMatrixData($academicYear, $term, $gradeLevels);
        $settings = \App\Models\AcademicReportSetting::first();
        $data['settings'] = $settings;
        $data['academicYear'] = $academicYear;

        // Prepare Base64 Logo for DomPDF
        $logoBase64 = null;
        if ($settings && $settings->roster_logo_path) {
            $path = storage_path('app/public/' . $settings->roster_logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $imageData = file_get_contents($path);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($imageData);
            }
        }
        $data['logoBase64'] = $logoBase64;

        $pdf = Pdf::loadView('admin.academic-reports.grade-matrix-pdf', $data)
            ->setPaper('a3', 'landscape');

        $filename = str_replace(['/', '\\'], '-', "School_Matrix_{$term->name}_{$academicYear->name}.pdf");
        return $pdf->download($filename);
    }

    public function recalculate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'section_id' => 'required|exists:sections,id',
        ]);

        try {
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $termId = $request->term_id;
            $section = Section::findOrFail($request->section_id);

            if ($termId === 'yearly') {
                $term = new Term(['type' => 'yearly', 'academic_year_id' => $academicYear->id]);
                $term->incrementing = false;
                $term->id = 'yearly';
            } else {
                $term = Term::findOrFail($termId);
            }

            $this->gradingService->recalculateSectionStatistics($section, $term, $academicYear);
            
            // Clear roster cache since data changed
            \Illuminate\Support\Facades\Cache::forget("roster_data_{$section->id}_{$termId}_{$academicYear->id}");

            return back()->withFallback(route('admin.academic-reports.index'))->with('success', 'Statistics recalculated successfully.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Recalculate Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            return back()->withFallback(route('admin.academic-reports.index'))->with('error', 'Error recalculating statistics: ' . $e->getMessage());
        }
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

    public function categoryRanks(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'division_id' => 'nullable|exists:divisions,id',
        ]);
        
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $termId = $request->term_id;
        $divisionId = $request->division_id;
        
        // Resolve division to filter categories
        $divisionCode = null;
        if ($divisionId) {
            $division = \App\Models\Division::find($divisionId);
            $divisionCode = $division?->code;
        }
        
        // All category definitions
        $allCategories = [
            'grade_1_6' => [
                'name' => 'Grade 1 - 6',
                'codes' => ['G1', 'G2', 'G3', 'G4', 'G5', 'G6'],
                'divisions' => ['ES'], // Elementary only
            ],
            'grade_7_8' => [
                'name' => 'Grade 7 - 8',
                'codes' => ['G7', 'G8'],
                'divisions' => ['ES'], // Elementary only
            ],
            'grade_9_12' => [
                'name' => 'Grade 9 - 12',
                'codes' => ['G9', 'G10', 'G11(N)', 'G11(S)', 'G12(N)', 'G12(S)'],
                'divisions' => ['HS'], // High School only
            ],
        ];
        
        // Filter categories based on selected division
        $categories = collect($allCategories)->filter(function ($cat) use ($divisionCode) {
            if (!$divisionCode) return true; // no filter — show all
            return in_array($divisionCode, $cat['divisions']);
        })->all();
        
        $results = [];
        
        foreach ($categories as $key => $cat) {
            if ($termId === 'yearly') {
                // For virtual yearly term, average Semester 1 and Semester 2 records
                $semesters = Term::where('academic_year_id', $academicYear->id)
                    ->where('type', 'semester')
                    ->pluck('id')
                    ->toArray();
                    
                $records = \App\Models\StudentTermRecord::where('student_term_records.academic_year_id', $academicYear->id)
                    ->whereIn('student_term_records.term_id', $semesters)
                    ->join('student_enrollments', function($join) use ($academicYear) {
                        $join->on('student_term_records.student_id', '=', 'student_enrollments.student_id')
                             ->where('student_enrollments.academic_year_id', '=', $academicYear->id)
                             ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated']);
                    })
                    ->join('sections', 'student_enrollments.section_id', '=', 'sections.id')
                    ->join('grade_levels', 'sections.grade_level_id', '=', 'grade_levels.id')
                    ->join('students', 'student_term_records.student_id', '=', 'students.id')
                    ->whereIn('grade_levels.code', $cat['codes'])
                    ->select(
                        'student_term_records.student_id',
                        'students.first_name',
                        'students.father_name',
                        'students.grandfather_name',
                        'sections.name as section_name',
                        'grade_levels.name as grade_level_name',
                        \Illuminate\Support\Facades\DB::raw('AVG(student_term_records.average_score) as average_score')
                    )
                    ->groupBy(
                        'student_term_records.student_id',
                        'students.first_name',
                        'students.father_name',
                        'students.grandfather_name',
                        'sections.name',
                        'grade_levels.name'
                    )
                    ->orderByDesc('average_score')
                    ->take(10)
                    ->get();
            } else {
                // For standard terms, query directly
                $records = \App\Models\StudentTermRecord::where('student_term_records.academic_year_id', $academicYear->id)
                    ->where('student_term_records.term_id', $termId)
                    ->join('student_enrollments', function($join) use ($academicYear) {
                        $join->on('student_term_records.student_id', '=', 'student_enrollments.student_id')
                             ->where('student_enrollments.academic_year_id', '=', $academicYear->id)
                             ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated']);
                    })
                    ->join('sections', 'student_enrollments.section_id', '=', 'sections.id')
                    ->join('grade_levels', 'sections.grade_level_id', '=', 'grade_levels.id')
                    ->join('students', 'student_term_records.student_id', '=', 'students.id')
                    ->whereIn('grade_levels.code', $cat['codes'])
                    ->select(
                        'student_term_records.student_id',
                        'student_term_records.average_score',
                        'students.first_name',
                        'students.father_name',
                        'students.grandfather_name',
                        'sections.name as section_name',
                        'grade_levels.name as grade_level_name'
                    )
                    ->orderByDesc('student_term_records.average_score')
                    ->take(10)
                    ->get();
            }
            
            $results[$key] = [
                'name' => $cat['name'],
                'records' => $records,
            ];
        }
        
        // Fetch term object or mock one if yearly
        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $academicYear->id]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }
        
        return view('admin.academic-reports.category-ranks', compact('academicYear', 'term', 'results', 'divisionCode'));
    }

    public function academicExcellence(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);
        
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $termId = $request->term_id;
        
        $query = \App\Models\StudentTermRecord::where('student_term_records.academic_year_id', $academicYear->id)
            ->join('student_enrollments', function($join) use ($academicYear) {
                $join->on('student_term_records.student_id', '=', 'student_enrollments.student_id')
                     ->where('student_enrollments.academic_year_id', '=', $academicYear->id)
                     ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated']);
            })
            ->join('sections', 'student_enrollments.section_id', '=', 'sections.id')
            ->join('grade_levels', 'sections.grade_level_id', '=', 'grade_levels.id')
            ->join('students', 'student_term_records.student_id', '=', 'students.id');
            
        if ($request->section_id) {
            $query->where('student_enrollments.section_id', $request->section_id);
        } elseif ($request->grade_level_id) {
            $query->where('sections.grade_level_id', $request->grade_level_id);
        }
        
        if ($termId === 'yearly') {
            $semesters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'semester')
                ->pluck('id')
                ->toArray();
                
            $query->whereIn('student_term_records.term_id', $semesters)
                ->select(
                    'student_term_records.student_id',
                    'students.first_name',
                    'students.father_name',
                    'students.grandfather_name',
                    'sections.name as section_name',
                    'grade_levels.name as grade_level_name',
                    \Illuminate\Support\Facades\DB::raw('AVG(student_term_records.average_score) as average_score')
                )
                ->groupBy(
                    'student_term_records.student_id',
                    'students.first_name',
                    'students.father_name',
                    'students.grandfather_name',
                    'sections.name',
                    'grade_levels.name'
                )
                ->having(\Illuminate\Support\Facades\DB::raw('AVG(student_term_records.average_score)'), '>=', 90);
        } else {
            $query->where('student_term_records.term_id', $termId)
                ->where('student_term_records.average_score', '>=', 90)
                ->select(
                    'student_term_records.student_id',
                    'student_term_records.average_score',
                    'students.first_name',
                    'students.father_name',
                    'students.grandfather_name',
                    'sections.name as section_name',
                    'grade_levels.name as grade_level_name'
                );
        }
        
        $records = $query->orderByDesc('average_score')->get();
        
        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $academicYear->id]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }
        
        $selectedSection = $request->section_id ? Section::find($request->section_id) : null;
        $selectedGrade = $request->grade_level_id ? GradeLevel::find($request->grade_level_id) : null;
        
        return view('admin.academic-reports.academic-excellence', compact('academicYear', 'term', 'records', 'selectedSection', 'selectedGrade'));
    }

}

