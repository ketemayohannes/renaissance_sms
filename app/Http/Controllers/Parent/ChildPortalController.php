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
        
        // Calculate average score
        $averageScore = $student->marks->avg('score');
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
            'recentMarks', 
            'recentAttendance', 
            'recentConduct'
        ));
    }

    public function grades(Student $student)
    {
        $student->load(['marks.subject', 'marks.assessmentTemplate', 'marks.term', 'currentEnrollment']);
        
        $allMarks = $student->marks;

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

        // Group marks by term for display
        $groupedMarks = $filteredMarks->groupBy(function($mark) {
            return $mark->term->name ?? 'Other';
        });

        $academicYearId = $student->currentEnrollment->academic_year_id ?? \App\Helpers\CachedData::activeAcademicYear()?->id;

        // Also fetch all available terms for report download selection
        $terms = \App\Models\Term::where('academic_year_id', $academicYearId)->get();

        // Fetch term records (rank, average) for this student
        $termRecords = \App\Models\StudentTermRecord::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->with('term')
            ->get()
            ->keyBy(function($record) {
                return $record->term->name ?? '';
            });
        
        return view('parent.student.grades', compact('student', 'groupedMarks', 'terms', 'termRecords'));
    }

    public function downloadReport(Request $request, Student $student)
    {
        $termId = $request->input('term_id') ?? \App\Models\Term::where('is_active', true)->value('id');
        $academicYearId = $request->input('academic_year_id') ?? \App\Helpers\CachedData::activeAcademicYear()?->id;

        // Create a new Request object for the admin controller
        $newRequest = new Request();
        $newRequest->replace([
            'term_id' => $termId,
            'academic_year_id' => $academicYearId,
        ]);

        return app('\App\Http\Controllers\Admin\ReportCardController')->generatePdf($newRequest, $student);
    }

    public function attendance(Student $student)
    {
        $student->load('attendance');
        
        // Calculate totals
        $attendanceCount = $student->attendance->count();
        $presentCount = $student->attendance->whereIn('status', ['present', 'Present'])->count();
        $lateCount = $student->attendance->whereIn('status', ['late', 'Late'])->count();
        $absentCount = $student->attendance->whereIn('status', ['absent', 'Absent'])->count();
        $excusedCount = $student->attendance->whereIn('status', ['excused', 'Excused'])->count();
        
        return view('parent.student.attendance', compact(
            'student', 
            'attendanceCount', 
            'presentCount', 
            'lateCount', 
            'absentCount', 
            'excusedCount'
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
