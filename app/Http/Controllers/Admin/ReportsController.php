<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Term;
use App\Models\StudentTermRecord;
use App\Services\AcademicReportService;
use App\Models\ReportCardSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    protected $reportService;

    public function __construct(AcademicReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $academicYears = AcademicYear::latest()->get();
        $divisions = Division::where('code', '!=', 'KG')->orderBy('sort_order')->get();
        $gradeLevels = GradeLevel::orderBy('sort_order')->get();
        
        return view('admin.reports.index', compact('academicYears', 'divisions', 'gradeLevels'));
    }

    public function top3PerSection(Request $request)
    {
        // Prevent timeout during large PDF compile runs
        set_time_limit(120);

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required',
            'division_id' => 'required|exists:divisions,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $division = Division::findOrFail($request->division_id);
        $termId = $request->term_id;

        // Fetch term or mock yearly
        if ($termId === 'yearly') {
            $term = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $academicYear->id]);
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        // Fetch grade levels in division
        $gradeLevels = GradeLevel::where('division_id', $division->id)
            ->orderBy('sort_order')
            ->get();

        $sectionsData = [];
        $gradeSummaryData = [];

        foreach ($gradeLevels as $gradeLevel) {
            $sections = Section::where('grade_level_id', $gradeLevel->id)
                ->where('academic_year_id', $academicYear->id)
                ->orderBy('name')
                ->get();

            $gradeReports = collect();

            foreach ($sections as $section) {
                // Get roster parameters (re-uses cache or calculates on the fly)
                $rosterParams = $this->reportService->prepareRosterData($section, $term, $academicYear);
                $reportsArray = $rosterParams['reports'] ?? [];

                // Add section name to each student report record (works on raw array by reference)
                foreach ($reportsArray as &$r) {
                    $r['section_name'] = $section->name;
                }
                unset($r);

                $reports = collect($reportsArray);
                $gradeReports = $gradeReports->concat($reports);

                // Sort by average descending (yearly or term average)
                $top3 = $reports->sortByDesc(function ($r) use ($termId) {
                    return $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0);
                })->take(3)->values();

                // Map to Student model structure with average_score attribute expected by the view
                $top3Mapped = $top3->map(function ($r) use ($termId) {
                    $student = $r['student'];
                    $student->setAttribute('average_score', $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0));
                    return $student;
                });

                if ($top3Mapped->isNotEmpty()) {
                    $sectionsData[] = [
                        'grade_level' => $gradeLevel,
                        'section' => $section,
                        'students' => $top3Mapped
                    ];
                }
            }

            // Top 3 for grade level across all sections
            $gradeTop3 = $gradeReports->sortByDesc(function ($r) use ($termId) {
                return $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0);
            })->take(3)->values();

            $gradeTop3Mapped = $gradeTop3->map(function ($r) use ($termId) {
                $student = $r['student'];
                $student->setAttribute('average_score', $termId === 'yearly' ? ($r['rows']['avg']['average'] ?? 0) : ($r['average'] ?? 0));
                $student->setAttribute('section_name', $r['section_name'] ?? '');
                return $student;
            });

            if ($gradeTop3Mapped->isNotEmpty()) {
                $gradeSummaryData[] = [
                    'grade_level' => $gradeLevel,
                    'students' => $gradeTop3Mapped
                ];
            }
        }

        // Fetch general school logo configuration
        $settings = ReportCardSetting::first();
        $logoBase64 = null;
        if ($settings && $settings->logo_path) {
            $logoPath = storage_path('app/public/' . $settings->logo_path);
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
            }
        }

        $pdf = Pdf::loadView('admin.reports.top3-per-section-pdf', [
            'academicYear' => $academicYear,
            'division' => $division,
            'term' => $term,
            'sectionsData' => $sectionsData,
            'gradeSummaryData' => $gradeSummaryData,
            'settings' => $settings,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $termSlug = strtolower(str_replace([' ', '/'], '_', $term->name ?? $term->id));
        $divisionSlug = strtolower(str_replace(' ', '_', $division->name));
        $filename = "top3_per_section_summary_{$divisionSlug}_{$termSlug}.pdf";
        return $pdf->download($filename);
    }
}
