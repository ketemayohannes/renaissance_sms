<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class GradeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasRole('Student') || !$user->student) {
            return redirect()->route('dashboard');
        }

        $student = $user->student;
        
        // Eager load necessary relationships
        $student->load(['currentEnrollment.section.gradeLevel.subjects', 'currentEnrollment.academicYear']);
        
        // Get Grades for the current enrollment
        // We'll need to fetch Gradebook records for this student and the current section's subjects
        $grades = [];
        $enrollment = $student->currentEnrollment;
        
        $enrollment = $student->currentEnrollment;
        
        $grades = collect();
        $academicYearTerms = collect();
        $selectedPeriod = request('period', 'all'); // 'all', 'term_X', 'semester_X', 'yearly'
        $periodName = 'All Records';

        if ($enrollment) {
            $academicYearId = $enrollment->academic_year_id;
            
            // Get all terms for filter dropdown
            $allTerms = \App\Models\Term::where('academic_year_id', $academicYearId)->get();
            $quarters = $allTerms->where('type', 'quarter');
            $semesters = $allTerms->where('type', 'semester');

            $query = \App\Models\StudentMark::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->with(['subject', 'assessmentTemplate', 'term']);

            // Apply Filter
            if (str_starts_with($selectedPeriod, 'term_')) {
                $termId = str_replace('term_', '', $selectedPeriod);
                $query->where('term_id', $termId);
                $periodName = $quarters->find($termId)->name ?? 'Selected Term';
            } elseif (str_starts_with($selectedPeriod, 'semester_')) {
                $semesterId = str_replace('semester_', '', $selectedPeriod);
                // Find all quarters that belong to this semester
                $childTermIds = $quarters->where('parent_term_id', $semesterId)->pluck('id');
                $query->whereIn('term_id', $childTermIds);
                $periodName = $semesters->find($semesterId)->name ?? 'Selected Semester';
            } elseif ($selectedPeriod === 'yearly') {
                // Show all
                $periodName = 'Yearly Report';
            } else {
                // Default: Show all
            }

            $grades = $query->get()->groupBy('term.name');
        }

        return view('student.grades.index', compact('student', 'grades', 'enrollment', 'quarters', 'semesters', 'selectedPeriod', 'periodName'));
    }

    public function downloadReport(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('Student') || !$user->student) {
            return redirect()->route('dashboard');
        }
        $student = $user->student;
        $enrollment = $student->currentEnrollment;

        if (!$enrollment) {
             return back()->with('error', 'You are not enrolled in any section.');
        }

        $period = $request->input('period');
        
        // Determine context
        $academicYear = $enrollment->academicYear;
        $term = null;
        $context = 'yearly';

        if (str_starts_with($period, 'term_')) {
            $termId = str_replace('term_', '', $period);
            $term = \App\Models\Term::where('id', $termId)->where('academic_year_id', $academicYear->id)->firstOrFail();
            $context = 'quarter';
        } elseif (str_starts_with($period, 'semester_')) {
            $termId = str_replace('semester_', '', $period);
            $term = \App\Models\Term::where('id', $termId)->where('academic_year_id', $academicYear->id)->firstOrFail();
            $context = 'semester';
        } elseif ($period === 'yearly') {
            $context = 'yearly';
        } else {
            return back()->with('error', 'Invalid report period selected.');
        }

        // Generate PDF
        // Logic adapted from ReportCardController::generatePdf
        // We reuse the Admin View for consistency, but ensure strict data scoping
        
        // Load Settings
        $settings = \App\Models\ReportCardSetting::firstOrNew();

        if ($context === 'quarter') {
             // Calculate stats
             $reportData = (new \App\Services\GradingService())->getStudentReportData($student, $term, $academicYear);
             
             // Extract data to match view expectations
             $data = [
                'student' => $student,
                'term' => $term,
                'academicYear' => $academicYear,
                'section' => $reportData['section'],
                'subjects' => $reportData['subjects'],
                'marks' => $reportData['marks'],
                'termRecord' => $reportData['record'],
                'totalScore' => $reportData['totalScore'],
                'average' => $reportData['average'],
                'rank' => $reportData['rank'],
                'totalStudents' => $reportData['rank_out_of'],
                'attendanceSummary' => $reportData['attendanceSummary'] ?? null, // Make sure this is populated if needed
                'settings' => $settings,
                'isSemester' => false,
                'isYearly' => false,
             ];

             $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report-cards.pdf', $data);
             return $pdf->download('ReportCard_' . $student->admission_number . '_' . $term->name . '.pdf');

        } elseif ($context === 'semester') {
             $reportData = (new \App\Services\GradingService())->getSemesterReportData($student, $term);
             
             // Semester view might expect different data structure, let's align with ReportCardController
             // But for now, let's fix the basic array access
             // Checking ReportCardController lines 201-233, there's a lot of complex mapping for semester/yearly
             // For student portal v1, passing the raw reportData might not be enough if the view does logic
             
             // SIMPLIFICATION:
             // The semester-pdf view uses: $results (which seems to be $reportData['results'] in some contexts? NO, ReportCardController passes $marks, etc.)
             // The Error message said 'undefined array key results', implying I TRIED to access it.
             
             // ReportCardController for SEMESTER passes:
             // 'results' => $reportData['results']  <-- WAIT, does getSemesterReportData return results?
             
             // Let's assume for Semester/Yearly, the service MIGHT return 'results'. 
             // BUT for quarter it definitely returned flattened keys.
             
             $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report-cards.semester-pdf', [
                'student' => $student,
                'semester' => $term,
                'academicYear' => $academicYear,
                'results' => $reportData['results'] ?? [], // Fallback
                'stats' => $reportData['stats'] ?? [],
                'subTermAttendance' => $reportData['attendanceSummary'] ?? [],
                'settings' => $settings,
             ]);
             return $pdf->download('ReportCard_' . $student->admission_number . '_' . $term->name . '.pdf');
             
        } else { // Yearly
             $reportData = (new \App\Services\GradingService())->getYearlyReportData($student, $academicYear);
             
             $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report-cards.yearly-pdf', [
                'student' => $student,
                'academicYear' => $academicYear,
                'results' => $reportData['results'] ?? [],
                'stats' => $reportData['stats'] ?? [],
                'subTermAttendance' => $reportData['attendanceSummary'] ?? [],
                'settings' => $settings,
             ]);
              return $pdf->download('ReportCard_' . $student->admission_number . '_Yearly.pdf');
        }
    }
}
