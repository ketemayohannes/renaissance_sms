<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\AcademicYear;
use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Term;
use App\Services\GradingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateSectionReportCards implements ShouldQueue
{
    use Queueable;

    public $exportRequest;
    public $section;
    public $term;
    public $academicYear;

    /**
     * Create a new job instance.
     */
    public function __construct(ExportRequest $exportRequest, Section $section, Term $term, AcademicYear $academicYear)
    {
        $this->exportRequest = $exportRequest;
        $this->section = $section;
        $this->term = $term;
        $this->academicYear = $academicYear;
    }

    /**
     * Execute the job.
     */
    public function handle(GradingService $gradingService): void
    {
        $this->exportRequest->update(['status' => 'processing']);

        try {
            $students = $this->section->students()
                ->wherePivot('academic_year_id', $this->academicYear->id)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get();

            $sectionReportData = $gradingService->getSectionReportData($students, $this->section, $this->term, $this->academicYear);
            $settings = ReportCardSetting::first();

            $zipName = "report_cards_{$this->section->name}_{$this->term->name}_" . time() . ".zip";
            $zipPath = storage_path("app/public/exports/{$zipName}");
            
            // Create a temporary directory for PDFs
            $tempDirName = "temp_" . time() . "_" . uniqid();
            $tempDirPath = storage_path("app/public/exports/{$tempDirName}");
            
            if (!file_exists($tempDirPath)) {
                mkdir($tempDirPath, 0755, true);
            }

            $isSemester = $this->term->isSemester();
            $isYearly = $this->term->type === 'yearly';

            $reportCardService = app(\App\Services\ReportCardService::class);
            $targetTermsForAttendance = collect([$this->term])->concat($quarters)->concat($semesters)->unique('id');
            $batchAttendance = $reportCardService->getBatchAttendanceSummary($students, $targetTermsForAttendance, $this->academicYear);

            foreach ($students as $student) {
                $reportData = $sectionReportData[$student->id] ?? null;
                if (!$reportData) continue;

                $viewData = $this->prepareViewData($reportData, $isSemester, $isYearly, $batchAttendance);
                
                $view = $isYearly ? 'admin.report-cards.yearly-pdf' : 'admin.report-cards.pdf';
                $pdf = Pdf::loadView($view, $viewData)->setPaper('a4', 'portrait');
                
                $filename = "{$student->student_id}_{$student->first_name}_{$student->last_name}.pdf";
                // Clean filename for safety
                $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
                file_put_contents($tempDirPath . DIRECTORY_SEPARATOR . $filename, $pdf->output());
            }

            // Use PowerShell to zip the folder
            $fullTempPath = str_replace('/', DIRECTORY_SEPARATOR, $tempDirPath);
            $fullZipPath = str_replace('/', DIRECTORY_SEPARATOR, $zipPath);
            
            // Command to zip the contents of the temp folder
            $cmd = "powershell -Command \"Compress-Archive -Path '{$fullTempPath}\*' -DestinationPath '{$fullZipPath}' -Force\"";
            
            shell_exec($cmd);

            // Cleanup temp directory
            $files = glob($tempDirPath . DIRECTORY_SEPARATOR . '*');
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            rmdir($tempDirPath);

            if (!file_exists($zipPath)) {
                throw new \Exception("Failed to generate ZIP file via PowerShell. Please ensure 'zip' extension is enabled in PHP or contact support.");
            }

            $this->exportRequest->update([
                'status' => 'completed',
                'file_path' => "exports/{$zipName}",
                'completed_at' => now()
            ]);

        } catch (\Exception $e) {
            $this->exportRequest->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function prepareViewData($reportData, $isSemester, $isYearly, $batchAttendance = [])
    {
        $student = $reportData['student'];
        $section = $reportData['section'];
        $term = $reportData['term'];
        $academicYear = $reportData['academicYear'];
        $subjects = $reportData['subjects'];
        $marks = $reportData['marks'];
        $termRecord = $reportData['record'];
        $totalScore = $reportData['totalScore'];
        $average = $reportData['average'];
        $rank = $reportData['rank'];
        $totalStudents = $reportData['rank_out_of'];
        
        $quarters = collect();
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
        } elseif ($isYearly) {
            $quarters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number')
                ->get();
        }
        $semesters = $isYearly ? Term::where('academic_year_id', $academicYear->id)->where('type', 'semester')->orderBy('start_date')->get() : collect();

        // Prepare legacy variables for view compatibility
        $quarterMarks = [];
        $quarterTotals = [];
        $quarterAverages = [];
        $quarterRanks = [];
        $quarterRecords = [];
        
        if ($isSemester || $isYearly) {
            foreach ($reportData['quarters'] as $qId => $qData) {
                foreach ($qData['marks'] as $subId => $score) {
                    $quarterMarks[$subId][$qId] = $score;
                }
                $quarterTotals[$qId] = $qData['total'];
                $quarterAverages[$qId] = $qData['average'];
                $quarterRanks[$qId] = $qData['rank'] . " / " . $totalStudents;
                $quarterRecords[$qId] = $qData['record'];
            }
        }

        $semesterMarks = [];
        $semesterTotals = [];
        $semesterAverages = [];
        $semesterRanks = [];
        if ($isYearly) {
            foreach ($reportData['semesters'] as $sId => $sData) {
                foreach ($sData['marks'] as $subId => $score) {
                    $semesterMarks[$subId][$sId] = $score;
                }
                $semesterTotals[$sId] = $sData['total'];
                $semesterAverages[$sId] = $sData['average'];
                $semesterRanks[$sId] = $sData['rank'] . " / " . $totalStudents;
            }
        }

        // Filter Subjects (Remove those without marks in any pertinent term)
        $subjects = $subjects->filter(function($subject) use ($marks, $quarterMarks, $semesterMarks, $isSemester, $isYearly) {
            if ($isSemester) return isset($quarterMarks[$subject->id]);
            if ($isYearly) return isset($semesterMarks[$subject->id]);
            return isset($marks[$subject->id]);
        });

        $reportCardService = app(\App\Services\ReportCardService::class);
        $attendance = $batchAttendance[$student->id][$term->id] ?? $reportCardService->getAttendanceSummary($student, $term, $academicYear);
        $subTermAttendance = $reportCardService->calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear, $batchAttendance);

        return [
            'student' => $student,
            'term' => $term,
            'academicYear' => $academicYear,
            'subjects' => $subjects,
            'marks' => $marks,
            'termRecord' => $termRecord,
            'settings' => ReportCardSetting::first(),
            'totalScore' => $totalScore,
            'average' => $average,
            'section' => $section,
            'rank' => $rank,
            'totalStudents' => $totalStudents,
            'isSemester' => $isSemester,
            'isYearly' => $isYearly,
            'quarters' => $quarters,
            'semesters' => $semesters,
            'quarterMarks' => $quarterMarks,
            'quarterTotals' => $quarterTotals,
            'quarterAverages' => $quarterAverages,
            'quarterRanks' => $quarterRanks,
            'quarterRecords' => $quarterRecords,
            'semesterMarks' => $semesterMarks,
            'semesterTotals' => $semesterTotals,
            'semesterAverages' => $semesterAverages,
            'semesterRanks' => $semesterRanks,
            'attendance' => $attendance,
            'subTermAttendance' => $subTermAttendance,
        ];
    }
}
