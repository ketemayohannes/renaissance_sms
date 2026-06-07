<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentAttendance;
use App\Models\StudentMark;
use App\Models\AcademicActivity;
use App\Models\Timetable;
use App\Models\Notice;
use App\Models\DisciplinaryRecord;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user is a student
        if (!$user->hasRole('Student')) {
            return redirect()->route('dashboard');
        }

        $student = $user->student;

        if (!$student) {
            return view('student.no-profile'); // Handle case where user has role but no linked student profile
        }

        // Eager load necessary relationships
        $student->load(['currentEnrollment.section.gradeLevel', 'currentEnrollment.academicYear']);

        $attendanceStats = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
            'rate' => 0,
        ];
        
        $currentEnrollment = $student->currentEnrollment;
        $timetable = collect();
        $recentGrades = collect();
        $recentActivities = collect();
        $activeNotices = collect();
        $disciplinaryCount = 0;
        $activeTerm = null;

        if ($currentEnrollment) {
            $sectionId = $currentEnrollment->section_id;
            $academicYearId = $currentEnrollment->academic_year_id;

            // 1. Calculate actual attendance stats from Attendance records
            $attendances = StudentAttendance::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->get();
                
            $totalAttendanceDays = $attendances->count();
            if ($totalAttendanceDays > 0) {
                $presentCount = $attendances->where('status', 'present')->count();
                $absentCount = $attendances->where('status', 'absent')->count();
                $lateCount = $attendances->where('status', 'late')->count();
                $excusedCount = $attendances->where('status', 'excused')->count();
                
                // Presence rate calculation
                $presenceRate = round((($presentCount + $lateCount + $excusedCount) / $totalAttendanceDays) * 100, 1);
                
                $attendanceStats = [
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'excused' => $excusedCount,
                    'rate' => $presenceRate,
                ];
            }

            // 2. Fetch the student's enrolled section timetable
            $timetable = Timetable::with(['classPeriod', 'teacherAssignment.teacher', 'teacherAssignment.subject'])
                ->where('section_id', $sectionId)
                ->where('academic_year_id', $academicYearId)
                ->get()
                ->sortBy(function($t) {
                    return $t->classPeriod->sort_order ?? 0;
                })
                ->groupBy('day_of_week');

            // Determine active term (moved before recent grades so we can filter by it)
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

            // 3. Fetch recent grades (StudentMark) — filtered to active term only
            $recentMarksQuery = StudentMark::with(['subject', 'term', 'assessmentTemplate'])
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId);

            if ($activeTerm) {
                $recentMarksQuery->where('term_id', $activeTerm->id);
            }

            $allRecentMarks = $recentMarksQuery->get();

            $filteredRecentMarks = collect();
            foreach ($allRecentMarks->groupBy('term_id') as $termId => $termMarks) {
                foreach ($termMarks->groupBy('subject_id') as $subjectId => $subjectMarks) {
                    $components = $subjectMarks->filter(function($m) {
                        return $m->assessmentTemplate && $m->assessmentTemplate->name !== 'Term Total';
                    });
                    
                    if ($components->isNotEmpty()) {
                        $filteredRecentMarks = $filteredRecentMarks->concat($components);
                    } else {
                        $termTotal = $subjectMarks->first(function($m) {
                            return $m->assessmentTemplate && $m->assessmentTemplate->name === 'Term Total';
                        });
                        if ($termTotal) {
                            $filteredRecentMarks->push($termTotal);
                        }
                    }
                }
            }

            $recentGrades = $filteredRecentMarks->sortByDesc('created_at')->take(5);

            // 4. Fetch upcoming academic activities & homework
            $recentActivities = AcademicActivity::with(['teacherAssignment.subject', 'teacherAssignment.teacher', 'assessmentTemplate'])
                ->whereHas('teacherAssignment', function($q) use ($sectionId) {
                    $q->where('section_id', $sectionId);
                })
                ->where('academic_year_id', $academicYearId)
                ->where('is_published', true)
                ->orderBy('due_date', 'asc')
                ->limit(5)
                ->get();

            // (active term already determined above, before recent grades)
        }

        // 5. Fetch active notices targeting all or students
        $activeNotices = Notice::where('is_active', true)
            ->whereIn('target_audience', ['All', 'Student'])
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now()->toDateString());
            })
            ->orderBy('publish_date', 'desc')
            ->get();

        // 6. Fetch disciplinary record count
        $disciplinaryCount = DisciplinaryRecord::where('student_id', $student->id)
            ->where('status', '!=', 'resolved')
            ->count();

        return view('student.dashboard', compact(
            'student',
            'attendanceStats',
            'timetable',
            'recentGrades',
            'recentActivities',
            'activeNotices',
            'disciplinaryCount',
            'activeTerm'
        ));
    }
}
