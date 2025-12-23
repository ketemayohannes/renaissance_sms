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
}
