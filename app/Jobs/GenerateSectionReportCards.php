<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\AcademicYear;
use App\Models\ExportRequest;
use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\StudentPromotion;
use App\Models\Term;
use App\Models\User;
use App\Notifications\ReportCardExportReady;
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
                ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated'])
                ->orderBy('first_name')
                ->get();

            $sectionReportData = $gradingService->getSectionReportData($students, $this->section, $this->term, $this->academicYear);
            $settings = ReportCardSetting::first();

            $zipName = "report_cards_{$this->section->name}_{$this->term->name}_" . time() . ".zip";
            // Store on the PRIVATE disk (storage/app/private) — report cards contain
            // student PII/grades and must not be web-accessible via public/storage.
            $zipPath = storage_path("app/private/exports/{$zipName}");

            // Ensure the private exports directory exists
            $exportsDir = storage_path("app/private/exports");
            if (!file_exists($exportsDir)) {
                mkdir($exportsDir, 0755, true);
            }

            // Create a temporary directory for PDFs
            $tempDirName = "temp_" . time() . "_" . uniqid();
            $tempDirPath = storage_path("app/private/exports/{$tempDirName}");
            
            if (!file_exists($tempDirPath)) {
                mkdir($tempDirPath, 0755, true);
            }

            $isSemester = $this->term->isSemester();
            $isYearly = $this->term->type === 'yearly';

            $quarters = collect();
            if ($isSemester) {
                $quarters = $this->term->quarters()->orderBy('term_number')->get();
            } elseif ($isYearly) {
                $quarters = Term::where('academic_year_id', $this->academicYear->id)
                    ->where('type', 'quarter')
                    ->orderBy('term_number')
                    ->get();
            }
            $semesters = $isYearly ? Term::where('academic_year_id', $this->academicYear->id)->where('type', 'semester')->orderBy('start_date')->get() : collect();

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

            // Use PHP native ZipArchive to zip the folder
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $files = glob($tempDirPath . DIRECTORY_SEPARATOR . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $zip->addFile($file, basename($file));
                    }
                }
                $zip->close();
            } else {
                throw new \Exception("Failed to generate ZIP file via PHP ZipArchive. Please ensure 'zip' extension is enabled in PHP.");
            }
            
            // Cleanup temp directory
            $files = glob($tempDirPath . DIRECTORY_SEPARATOR . '*');
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            rmdir($tempDirPath);

            if (!file_exists($zipPath)) {
                throw new \Exception("Failed to generate ZIP file. Please contact support.");
            }

            $this->exportRequest->update([
                'status'       => 'completed',
                'file_path'    => "exports/{$zipName}",
                'completed_at' => now(),
            ]);

            // Notify the user who requested the export
            if ($this->exportRequest->requested_by) {
                $requester = User::find($this->exportRequest->requested_by);
                $requester?->notify(new ReportCardExportReady($this->exportRequest));
            }

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
        if (!$totalStudents || $totalStudents === '-' || $totalStudents === 0) {
            $totalStudents = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->whereIn('student_enrollments.status', ['active', 'completed', 'graduated'])
                ->count();
        }
        
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
                $quarterRanks[$qId] = ($qData['rank'] && $qData['rank'] !== '-') ? ($qData['rank'] . " / " . $totalStudents) : '-';
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
                $semesterRanks[$sId] = ($sData['rank'] && $sData['rank'] !== '-') ? ($sData['rank'] . " / " . $totalStudents) : '-';
            }
        }

        $isGrade12 = false;
        if ($section && $section->gradeLevel && preg_match('/\b12\b/', $section->gradeLevel->name)) {
            $isGrade12 = true;
        }

        $enrolledElectiveIds = [];
        if ($isGrade12 && $isYearly) {
            $enrolledElectiveIds = \Illuminate\Support\Facades\DB::table('student_electives')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->pluck('subject_id')
                ->toArray();
        }

        // Filter Subjects (Remove those without marks in any pertinent term)
        $subjects = $subjects->filter(function($subject) use ($marks, $quarterMarks, $semesterMarks, $isSemester, $isYearly, $isGrade12, $enrolledElectiveIds) {
            if ($isGrade12 && $isYearly && $subject->is_elective) {
                return in_array($subject->id, $enrolledElectiveIds);
            }
            if ($isSemester) return isset($quarterMarks[$subject->id]);
            if ($isYearly) return isset($semesterMarks[$subject->id]);
            return isset($marks[$subject->id]);
        });

        $reportCardService = app(\App\Services\ReportCardService::class);
        $attendance = $batchAttendance[$student->id][$term->id] ?? $reportCardService->getAttendanceSummary($student, $term, $academicYear);
        $subTermAttendance = $reportCardService->calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear, $batchAttendance);

        // Promotion info — Elementary & High School only (division_id != 1)
        $promotedToGrade = null;
        $promotionStatus = null;
        if ($isYearly && $section && $section->gradeLevel && $section->gradeLevel->division_id != 1) {
            $promotion = StudentPromotion::with('toGradeLevel')
                ->where('student_id', $student->id)
                ->where('from_academic_year_id', $academicYear->id)
                ->first();
            if ($promotion && $promotion->toGradeLevel) {
                $promotedToGrade = trim(preg_replace('/\s*\(.*?\)/', '', $promotion->toGradeLevel->name));
                $promotionStatus = $promotion->status;
            }
        }

        return [
            'is_pdf' => true,
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
            'promotedToGrade' => $promotedToGrade,
            'promotionStatus' => $promotionStatus,
        ];
    }
}
