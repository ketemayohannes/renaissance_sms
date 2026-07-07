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
        $quarters = collect();
        $semesters = collect();
        $termRecords = collect();
        $activeTerm = null;

        if ($enrollment) {
            $academicYearId = $enrollment->academic_year_id;

            // Get all terms for filter dropdown
            $allTerms = \App\Models\Term::where('academic_year_id', $academicYearId)->get();
            $quarters = $allTerms->where('type', 'quarter');
            $semesters = $allTerms->where('type', 'semester');

            // Single source of truth (shared with the parent portal): quarters resolved
            // components-first, semesters + yearly computed live via GradingService — so the
            // numbers match the report card and the admin profile, and Semester 2 / Yearly show.
            $history = app(\App\Services\StudentAcademicHistoryService::class)->build($student, (int) $academicYearId);
            $grades = collect($history['academicRecords']->first() ?? collect());
            $termRecords = collect($history['termRecords']->first() ?? collect());

            // Determine active term (for highlighting).
            $activeTerm = \App\Models\Term::where('academic_year_id', $academicYearId)
                ->where('type', 'quarter')
                ->where('is_grading_open', true)
                ->first();

            if (!$activeTerm) {
                $activeTerm = \App\Models\Term::where('academic_year_id', $academicYearId)
                    ->where('type', 'quarter')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
            }

            // Apply period filter over the resolved terms.
            if (str_starts_with($selectedPeriod, 'term_')) {
                $periodName = optional($quarters->find((int) str_replace('term_', '', $selectedPeriod)))->name ?? 'Selected Term';
                $grades = $grades->only([$periodName]);
            } elseif (str_starts_with($selectedPeriod, 'semester_')) {
                $periodName = optional($semesters->find((int) str_replace('semester_', '', $selectedPeriod)))->name ?? 'Selected Semester';
                $grades = $grades->only([$periodName]);
            } elseif ($selectedPeriod === 'yearly') {
                $periodName = 'Yearly Report';
                $grades = $grades->only(['Yearly']);
            }
        }

        return view('student.grades.index', compact('student', 'grades', 'enrollment', 'quarters', 'semesters', 'selectedPeriod', 'periodName', 'termRecords', 'activeTerm'));
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
        $academicYear = $enrollment->academicYear;
        $term = null;

        if (str_starts_with($period, 'term_')) {
            $termId = str_replace('term_', '', $period);
            $term = \App\Models\Term::where('id', $termId)->where('academic_year_id', $academicYear->id)->firstOrFail();
        } elseif (str_starts_with($period, 'semester_')) {
            $termId = str_replace('semester_', '', $period);
            $term = \App\Models\Term::where('id', $termId)->where('academic_year_id', $academicYear->id)->firstOrFail();
        } elseif ($period === 'yearly') {
            $term = new \App\Models\Term([
                'type' => 'yearly',
                'name' => 'Yearly Report',
                'academic_year_id' => $academicYear->id
            ]);
            $term->incrementing = false;
            $term->id = 'yearly';
        } else {
            return back()->with('error', 'Invalid report period selected.');
        }

        $reportCardService = app(\App\Services\ReportCardService::class);
        $params = $reportCardService->getStudentReportParams($student, $term, $academicYear);
        $params['is_pdf'] = true;
        
        $viewName = $params['isYearly'] ? 'admin.report-cards.yearly-pdf' : 'admin.report-cards.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $params)->setPaper('a4', 'portrait');
        
        return $pdf->download('ReportCard_' . $student->admission_number . '_' . str_replace(' ', '_', $term->name) . '.pdf');
    }
}
