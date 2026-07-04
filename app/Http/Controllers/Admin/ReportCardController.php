<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\StudentTermRecord;
use App\Models\ExportRequest;
use App\Services\ReportCardService;
use Illuminate\Support\Facades\Storage;

class ReportCardController extends Controller
{
    protected $reportCardService;

    public function __construct(ReportCardService $reportCardService)
    {
        $this->reportCardService = $reportCardService;
    }
    // Settings
    public function settings()
    {
        $settings = ReportCardSetting::firstOrNew();
        return view('admin.report-cards.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'po_box' => 'nullable|string|max:255',
        ]);

        $this->reportCardService->updateSettings($request->all(), $request->file('logo'));

        return back()->with('success', 'Report card settings updated successfully.');
    }

    public function yearlySettings()
    {
        $settings = ReportCardSetting::firstOrNew();
        return view('admin.report-cards.yearly-settings', compact('settings'));
    }

    public function updateYearlySettings(Request $request)
    {
        $request->validate([
            'evaluation_method' => 'nullable|string',
            'remark' => 'nullable|string',
            'principal_name' => 'nullable|string',
            'parent_instructions' => 'nullable|string',
        ]);

        $this->reportCardService->updateYearlySettings($request->all());

        return back()->with('success', 'Yearly report card settings updated successfully.');
    }

    public function gradeScales()
    {
        $settings = ReportCardSetting::firstOrNew();
        $gradeScales = $settings->grade_scales ?? [
            ['min' => 90, 'grade' => 'A', 'label' => 'Excellent'],
            ['min' => 80, 'grade' => 'B', 'label' => 'Very Good'],
            ['min' => 70, 'grade' => 'C', 'label' => 'Satisfactory'],
            ['min' => 60, 'grade' => 'D', 'label' => 'Fair'],
            ['min' => 0,  'grade' => 'F', 'label' => 'Poor'],
        ];
        return view('admin.report-cards.grade-scales', compact('settings', 'gradeScales'));
    }

    public function updateGradeScales(Request $request)
    {
        $request->validate([
            'scales' => 'required|array',
            'scales.*.min' => 'required|numeric|min:0|max:100',
            'scales.*.grade' => 'required|string|max:5',
            'scales.*.label' => 'nullable|string|max:50',
        ]);

        $settings = ReportCardSetting::firstOrNew();
        
        // Sort scales descending by min score
        $scales = collect($request->scales)
            ->map(function($scale) {
                return [
                    'min' => (float)$scale['min'],
                    'grade' => trim($scale['grade']),
                    'label' => trim($scale['label'] ?? ''),
                ];
            })
            ->sortByDesc('min')
            ->values()
            ->toArray();

        $settings->grade_scales = $scales;
        $settings->save();

        return back()->with('success', 'Grade scaling rules updated successfully.');
    }

    // Data Entry (Conduct, Attendance, Comments)
    public function entry(Request $request, Section $section)
    {
        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id', AcademicYear::where('is_active', true)->value('id')));
        $term = Term::findOrFail($request->get('term_id')); // Require Term ID
        
        // Ensure section belongs to correct year? (Optional check)

        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivotIn('status', ['active', 'completed'])
            ->where('students.is_active', true)
            ->orderBy('students.first_name')
            ->get();
        
        // Fetch existing records
        $records = StudentTermRecord::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $term->id)
            ->get()
            ->keyBy('student_id');

        return view('admin.report-cards.entry', compact('section', 'academicYear', 'term', 'students', 'records'));
    }

    public function storeEntry(Request $request, Section $section)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'records' => 'array',
            'records.*.conduct' => 'nullable|string|in:A,B,C',
            'records.*.absent' => 'nullable|integer|min:0',
            'records.*.comment' => 'nullable|string',
        ]);
        
        try {
            $this->reportCardService->storeDataEntry(
                (int)$request->term_id, 
                (int)$request->academic_year_id, 
                $request->records ?? [],
                $section
            );
            return back()->with('success', 'Report Card details saved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error saving details: ' . $e->getMessage());
        }
    }

    // Generate PDF
    public function generatePdf(Request $request, \App\Models\Student $student)
    {
        $termId = $request->term_id;
        if ($termId === 'yearly') {
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $term = new Term([
                'type' => 'yearly', 
                'name' => 'Yearly Report', 
                'academic_year_id' => $academicYear->id
            ]);
            $term->incrementing = false;
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId ?? Term::where('is_active', true)->value('id'));
            $academicYear = $term->academicYear;
        }

        $params = $this->reportCardService->getStudentReportParams($student, $term, $academicYear);
        
        $viewName = $params['isYearly'] ? 'admin.report-cards.yearly-pdf' : 'admin.report-cards.pdf';
        return view($viewName, $params);
    }
    // Bulk Print
    public function bulkPrint(Request $request, Section $section)
    {
        // Increase resources for large PDF generation
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $academicYear = AcademicYear::findOrFail($request->get('academic_year_id'));
            $termId = $request->get('term_id');
            
            \Illuminate\Support\Facades\Log::info("Starting bulk report card generation for section: {$section->id}, term: {$termId}");

            if ($termId === 'yearly') {
                $term = new Term([
                    'type' => 'yearly', 
                    'name' => 'Yearly Report', 
                    'academic_year_id' => $academicYear->id
                ]);
                $term->incrementing = false;
                $term->id = 'yearly';
            } else {
                $term = Term::findOrFail($termId);
            }

            $params = $this->reportCardService->getSectionReportParams($section, $term, $academicYear);
            
            $viewName = $params['isYearly'] ? 'admin.report-cards.bulk-yearly-pdf' : 'admin.report-cards.bulk-pdf';
            
            \Illuminate\Support\Facades\Log::info("Rendering report card view: {$viewName}");
            return view($viewName, $params);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Report Card Generation Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.academic-reports.index')
                ->with('error', 'Error generating report cards: ' . $e->getMessage());
        }
    }

    // Background Exports
    public function exports()
    {
        $exports = ExportRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.report-cards.exports', compact('exports'));
    }

    public function bulkExport(Request $request, Section $section)
    {
        $this->reportCardService->initiateBulkExport(
            (int)$section->id, 
            $request->get('term_id'), 
            (int)$request->get('academic_year_id'), 
            (int)auth()->id()
        );

        return redirect()->route('admin.report-cards.exports')
            ->with('success', 'Bulk export started in background. You will find it here when finished.');
    }

    public function downloadExport(ExportRequest $exportRequest)
    {
        if ($exportRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if ($exportRequest->status !== 'completed' || !$exportRequest->file_path) {
            return back()->with('error', 'Export is not ready for download.');
        }

        // Exports are written to the private disk (see GenerateSectionReportCards).
        // Fall back to the legacy public disk for exports created before this change.
        if (Storage::disk('local')->exists($exportRequest->file_path)) {
            return Storage::disk('local')->download($exportRequest->file_path);
        }

        return Storage::disk('public')->download($exportRequest->file_path);
    }

    public function exportLowPerformance(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::findOrFail($request->section_id);
        $term = Term::findOrFail($request->term_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->wherePivotIn('status', ['active', 'completed'])
            ->where('students.is_active', true)
            ->orderBy('students.first_name')
            ->get();
        
        $records = StudentTermRecord::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $term->id)
            ->get()
            ->keyBy('student_id');

        $lowPerformers = $students->filter(function($student) use ($records) {
            $record = $records[$student->id] ?? null;
            return $record && $record->average_score !== null && $record->average_score < 75;
        });

        $filename = "low_performance_report_cards_{$section->name}_{$term->name}.csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($lowPerformers, $records) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM
            fputs($file, "sep=,\n"); // Excel separator hint
            fputcsv($file, ['Student ID', 'Student Name', 'Average Score (%)']);

            foreach ($lowPerformers as $student) {
                fputcsv($file, [
                    $student->student_id,
                    $student->full_name,
                    number_format($records[$student->id]->average_score, 1) . '%'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroyExport(ExportRequest $exportRequest)
    {
        if ($exportRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if ($exportRequest->file_path) {
            Storage::disk('public')->delete($exportRequest->file_path);
        }

        $exportRequest->delete();

        return back()->with('success', 'Export request deleted successfully.');
    }
}
