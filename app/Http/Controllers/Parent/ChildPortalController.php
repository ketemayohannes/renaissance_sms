<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class ChildPortalController extends Controller
{
    public function dashboard(Student $student)
    {
        $student->load(['marks.subject', 'marks.assessmentTemplate', 'marks.term', 'attendance', 'disciplinaryRecords', 'medicalInfo', 'transportation', 'currentEnrollment.section.gradeLevel']);
        
        // Calculate attendance summary
        $attendanceCount = $student->attendance->count();
        $presentCount = $student->attendance->whereIn('status', ['present', 'late', 'Present', 'Late'])->count();
        $attendanceRate = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100, 1) : 100;
        
        // Determine nearly closed term average
        $academicYear = \App\Helpers\CachedData::activeAcademicYear();
        $averageScore = null;
        $nearlyClosedTermName = '';
        
        if ($academicYear) {
            $quarters = \App\Models\Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number', 'asc')
                ->get();
            
            if ($quarters->isNotEmpty()) {
                $today = now();
                // Find the last quarter that has started
                $currentQuarter = $quarters->filter(fn($q) => $q->start_date <= $today)->last();
                
                if (!$currentQuarter) {
                    $currentQuarter = $quarters->first();
                }
                
                $currentIndex = $quarters->search(fn($q) => $q->id === $currentQuarter->id);
                if ($currentIndex > 0) {
                    // Previous term is the nearly closed term
                    $nearlyClosedTerm = $quarters->get($currentIndex - 1);
                } else {
                    // If we are in Quarter 1, fallback to Quarter 1
                    $nearlyClosedTerm = $currentQuarter;
                }
                
                if ($nearlyClosedTerm) {
                    $nearlyClosedTermName = $nearlyClosedTerm->name;
                    // Try to get the calculated average and rank from StudentTermRecord first
                    $termRecord = \App\Models\StudentTermRecord::where('student_id', $student->id)
                        ->where('term_id', $nearlyClosedTerm->id)
                        ->first();

                    $nearlyClosedTermRank = null;
                    if ($termRecord) {
                        if ($termRecord->average_score !== null) {
                            $averageScore = $termRecord->average_score;
                        }
                        if ($termRecord->rank !== null) {
                            $nearlyClosedTermRank = $termRecord->rank;
                        }
                    } else {
                        // Calculate dynamically via GradingService
                        $gradingService = app(\App\Services\GradingService::class);
                        $stats = $gradingService->calculateStudentTotals($student, $nearlyClosedTerm, $academicYear);
                        $averageScore = $stats['average'] ?? null;
                    }
                }
            }
        }
        
        $averageScore = $averageScore !== null ? round($averageScore, 1) : null;
        
        // Recent grades
        $recentMarks = $student->marks()->with(['subject', 'assessmentTemplate', 'term'])->orderBy('created_at', 'desc')->take(5)->get();
        
        // Recent attendance
        $recentAttendance = $student->attendance()->orderBy('attendance_date', 'desc')->take(5)->get();
        
        // Recent conduct
        $recentConduct = $student->disciplinaryRecords()->orderBy('incident_date', 'desc')->take(5)->get();
        
        return view('parent.student.dashboard', compact(
            'student', 
            'attendanceRate', 
            'averageScore', 
            'nearlyClosedTermName',
            'nearlyClosedTermRank',
            'recentMarks', 
            'recentAttendance', 
            'recentConduct'
        ));
    }

    public function grades(Student $student)
    {
        $student->load(['marks.subject', 'marks.assessmentTemplate', 'marks.term', 'currentEnrollment']);
        
        $enrollment = $student->currentEnrollment;
        $academicYearId = $enrollment->academic_year_id ?? \App\Helpers\CachedData::activeAcademicYear()?->id;

        // Get all terms for filter dropdown
        $allTerms = \App\Models\Term::where('academic_year_id', $academicYearId)->get();
        $quarters = $allTerms->where('type', 'quarter');
        $semesters = $allTerms->where('type', 'semester');

        $selectedPeriod = request('period', 'all'); // 'all', 'term_X', 'semester_X', 'yearly'
        $periodName = 'All Records';

        $allMarks = $student->marks()->where('academic_year_id', $academicYearId)->get();

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
            $filteredMarks = $filteredMarks->where('term_id', $semesterId);
            $periodName = $semesters->find($semesterId)->name ?? 'Selected Semester';
        } elseif ($selectedPeriod === 'yearly') {
            $periodName = 'Yearly Report';
        }

        // Group marks by term for display
        $groupedMarks = $filteredMarks->groupBy(function($mark) {
            return $mark->term->name ?? 'Other';
        });

        // Fetch term records (rank, average) for this student
        $termRecords = \App\Models\StudentTermRecord::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->with('term')
            ->get()
            ->keyBy(function($record) {
                return $record->term->name ?? '';
            });
        
        return view('parent.student.grades', compact(
            'student', 
            'groupedMarks', 
            'quarters', 
            'semesters', 
            'selectedPeriod', 
            'periodName', 
            'termRecords'
        ));
    }

    public function downloadReport(Request $request, Student $student)
    {
        $period = $request->input('period');
        $academicYearId = $student->currentEnrollment->academic_year_id ?? \App\Helpers\CachedData::activeAcademicYear()?->id;

        $termId = null;
        if ($period) {
            if (str_starts_with($period, 'term_')) {
                $termId = (int) str_replace('term_', '', $period);
            } elseif (str_starts_with($period, 'semester_')) {
                $termId = (int) str_replace('semester_', '', $period);
            } elseif ($period === 'yearly') {
                $termId = 'yearly';
            }
        }

        if (!$termId) {
            $termId = \App\Models\Term::where('academic_year_id', $academicYearId)->where('is_active', true)->value('id')
                ?? \App\Models\Term::where('academic_year_id', $academicYearId)->value('id');
        }

        // Create a new Request object for the admin controller
        $newRequest = new Request();
        $newRequest->replace([
            'term_id' => $termId,
            'academic_year_id' => $academicYearId,
        ]);

        return app('\App\Http\Controllers\Admin\ReportCardController')->generatePdf($newRequest, $student);
    }

    public function attendance(Request $request, Student $student)
    {
        $student->load('attendance');

        // Load quarters for filter
        $academicYear = \App\Helpers\CachedData::activeAcademicYear();
        $quarters = collect();
        if ($academicYear) {
            $quarters = \App\Models\Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number', 'asc')
                ->get();
        }

        // Get filter params
        $selectedQuarter = $request->input('quarter', 'all');
        $selectedMonth = $request->input('month', 'all');

        // Start with all attendance records
        $filteredAttendance = $student->attendance;

        // Filter by quarter (date range)
        if ($selectedQuarter !== 'all') {
            $quarter = $quarters->find((int) $selectedQuarter);
            if ($quarter && $quarter->start_date && $quarter->end_date) {
                $filteredAttendance = $filteredAttendance->filter(function ($record) use ($quarter) {
                    return $record->attendance_date >= $quarter->start_date 
                        && $record->attendance_date <= $quarter->end_date;
                });
            }
        }

        // Build available months list
        $availableMonths = collect();
        if ($selectedQuarter !== 'all') {
            $quarter = $quarters->find((int) $selectedQuarter);
            if ($quarter && $quarter->start_date && $quarter->end_date) {
                $current = $quarter->start_date->copy()->startOfMonth();
                $end = $quarter->end_date->copy()->startOfMonth();
                while ($current <= $end) {
                    $availableMonths->push($current->format('Y-m'));
                    $current->addMonth();
                }
            }
        } else {
            // Fallback: show months that actually have logs across all quarters
            $availableMonths = $student->attendance->pluck('attendance_date')
                ->filter()
                ->map(fn($d) => $d->format('Y-m'))
                ->unique()
                ->sort()
                ->values();
        }

        // Filter by month
        if ($selectedMonth !== 'all') {
            $filteredAttendance = $filteredAttendance->filter(function ($record) use ($selectedMonth) {
                return $record->attendance_date->format('Y-m') === $selectedMonth;
            });
        }

        // Calculate totals from filtered records
        $attendanceCount = $filteredAttendance->count();
        $presentCount = $filteredAttendance->whereIn('status', ['present', 'Present'])->count();
        $lateCount = $filteredAttendance->whereIn('status', ['late', 'Late'])->count();
        $absentCount = $filteredAttendance->whereIn('status', ['absent', 'Absent'])->count();
        $excusedCount = $filteredAttendance->whereIn('status', ['excused', 'Excused'])->count();
        
        return view('parent.student.attendance', compact(
            'student', 
            'attendanceCount', 
            'presentCount', 
            'lateCount', 
            'absentCount', 
            'excusedCount',
            'quarters',
            'selectedQuarter',
            'selectedMonth',
            'availableMonths',
            'filteredAttendance'
        ));
    }

    public function conduct(Student $student)
    {
        $student->load(['disciplinaryRecords' => function($query) {
            $query->orderBy('incident_date', 'desc');
        }]);
        return view('parent.student.conduct', compact('student'));
    }

    public function info(Student $student)
    {
        $student->load([
            'medicalInfo', 
            'transportation', 
            'guardians.user', 
            'enrollments.academicYear', 
            'enrollments.section.gradeLevel.division',
            'currentEnrollment.section.gradeLevel.division'
        ]);
        return view('parent.student.info', compact('student'));
    }
}
