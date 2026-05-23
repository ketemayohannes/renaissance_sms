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

            $allMarks = \App\Models\StudentMark::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->with(['subject', 'assessmentTemplate', 'term'])
                ->get();

            // Filter out Term Totals for subjects that have component marks in that term
            $filteredMarks = collect();
            foreach ($allMarks->groupBy('term_id') as $termId => $termMarks) {
                foreach ($termMarks->groupBy('subject_id') as $subjectId => $subjectMarks) {
                    $components = $subjectMarks->filter(function($m) {
                        return $m->assessmentTemplate && $m->assessmentTemplate->name !== 'Term Total';
                    });
                    
                    if ($components->isNotEmpty()) {
                        $filteredMarks = $filteredMarks->concat($components);
                    } else {
                        $termTotal = $subjectMarks->first(function($m) {
                            return $m->assessmentTemplate && $m->assessmentTemplate->name === 'Term Total';
                        });
                        if ($termTotal) {
                            $filteredMarks->push($termTotal);
                        }
                    }
                }
            }

            // Apply Filter
            if (str_starts_with($selectedPeriod, 'term_')) {
                $termId = (int) str_replace('term_', '', $selectedPeriod);
                $filteredMarks = $filteredMarks->where('term_id', $termId);
                $periodName = $quarters->find($termId)->name ?? 'Selected Term';
            } elseif (str_starts_with($selectedPeriod, 'semester_')) {
                $semesterId = (int) str_replace('semester_', '', $selectedPeriod);
                $childTermIds = $quarters->where('parent_term_id', $semesterId)->pluck('id')->toArray();
                $filteredMarks = $filteredMarks->whereIn('term_id', $childTermIds);
                $periodName = $semesters->find($semesterId)->name ?? 'Selected Semester';
            } elseif ($selectedPeriod === 'yearly') {
                $periodName = 'Yearly Report';
            }

            // Determine active term
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

            $activeTermName = $activeTerm ? $activeTerm->name : null;

            $grades = $filteredMarks->groupBy('term.name')
                ->sortBy(function ($termGrades, $termName) use ($activeTermName) {
                    $term = $termGrades->first()->term ?? null;
                    $termNumber = $term ? $term->term_number : 0;
                    
                    if ($activeTermName && $termName === $activeTermName) {
                        return -100;
                    }
                    
                    return -$termNumber;
                });

            // Fetch term records (rank, average) for this student
            $termRecords = \App\Models\StudentTermRecord::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->with('term')
                ->get()
                ->keyBy(function($record) {
                    return $record->term->name ?? '';
                });
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
        
        $viewName = $params['isYearly'] ? 'admin.report-cards.yearly-pdf' : 'admin.report-cards.pdf';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $params)->setPaper('a4', 'portrait');
        
        return $pdf->download('ReportCard_' . $student->admission_number . '_' . str_replace(' ', '_', $term->name) . '.pdf');
    }
}
