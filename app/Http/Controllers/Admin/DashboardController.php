<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\StudentAttendance;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $start = microtime(true);
        $academicYear = \App\Helpers\CachedData::activeAcademicYear();
        $divisionId = $request->get('division_id');
        $cacheTtl = 300; // Reduced to 5 minutes for more responsive dashboard stats
        
        $selectedTermId = $request->get('term_id');
        $selectedGradeLevelId = $request->get('grade_level_id');
        
        $academicYear = \App\Helpers\CachedData::activeAcademicYear();
        $terms = \App\Models\Term::where('academic_year_id', $academicYear?->id)->orderBy('start_date')->get();
        
        // Find default term if none selected
        if (!$selectedTermId) {
            $selectedTermId = $terms->where('is_grading_open', true)->first()?->id ?? $terms->last()?->id;
        }

        // Get all grade levels for the dropdown
        $gradeLevelsQuery = \App\Models\GradeLevel::where('is_active', true)->orderBy('sort_order');
        if ($divisionId) {
            $gradeLevelsQuery->where('division_id', $divisionId);
        }
        $gradeLevels = $gradeLevelsQuery->get();
        
        // Default to first grade level if none selected
        if (!$selectedGradeLevelId) {
            $selectedGradeLevelId = $gradeLevels->first()?->id;
        }

        $cacheSuffix = ($divisionId ? "_div_{$divisionId}" : "_all") 
            . ($selectedTermId ? "_term_{$selectedTermId}" : "") 
            . ($selectedGradeLevelId ? "_gl_{$selectedGradeLevelId}" : "");

        // Subject Averages for selected grade level (Cached)
        $subjectAverages = \Illuminate\Support\Facades\Cache::remember("admin_dashboard_subject_averages{$cacheSuffix}", $cacheTtl, function() use ($selectedTermId, $selectedGradeLevelId, $academicYear) {
            if (!$academicYear || !$selectedGradeLevelId) return collect();

            $gradeLevel = \App\Models\GradeLevel::find($selectedGradeLevelId);
            if (!$gradeLevel) return collect();

            // Get subjects for this grade level
            $subjects = $gradeLevel->subjects()->orderByPivot('sort_order')->get();
            if ($subjects->isEmpty()) return collect();

            // Get the TERM_TOTAL assessment type ID
            $termTotalTypeId = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->value('id');

            // Determine which quarter term IDs to query marks from
            $quarterIds = [];
            if ($selectedTermId === 'yearly') {
                $quarterIds = \App\Models\Term::where('academic_year_id', $academicYear->id)
                    ->where('type', 'quarter')->pluck('id')->toArray();
            } else {
                $term = \App\Models\Term::find($selectedTermId);
                if ($term && $term->isSemester()) {
                    $quarterIds = $term->quarters()->pluck('id')->toArray();
                } elseif ($term && $term->isQuarter()) {
                    $quarterIds = [$term->id];
                }
            }

            if (empty($quarterIds)) return collect();

            // Get all active students in this grade level's sections
            $sectionIds = \App\Models\Section::where('grade_level_id', $selectedGradeLevelId)
                ->where('is_active', true)->pluck('id');
            
            $studentIds = \App\Models\StudentEnrollment::whereIn('section_id', $sectionIds)
                ->where('academic_year_id', $academicYear->id)
                ->whereNull('end_date')
                ->whereIn('status', ['active', 'completed'])
                ->pluck('student_id');

            // Fetch marks for these students, subjects, and quarters
            // We fetch ALL marks and then prioritize TERM_TOTAL to handle live data (where total hasn't been saved yet)
            $marks = \App\Models\StudentMark::whereIn('student_id', $studentIds)
                ->whereIn('term_id', $quarterIds)
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->with('assessmentTemplate:id,assessment_type_id')
                ->get();

            // Calculate per-subject averages
            return $subjects->map(function($subject) use ($marks, $termTotalTypeId) {
                $subjectMarks = $marks->where('subject_id', $subject->id);
                
                if ($subjectMarks->isEmpty()) {
                    return (object)[
                        'subject_name' => $subject->name,
                        'average' => 0,
                    ];
                }

                // Group by student, then average each student's term scores
                $studentGroups = $subjectMarks->groupBy('student_id');
                $studentAverages = $studentGroups->map(function($studentRecs) use ($termTotalTypeId) {
                    // For each student, find the total for each quarter in the selection
                    $termScores = $studentRecs->groupBy('term_id')->map(function($termMarks) use ($termTotalTypeId) {
                        // Priority 1: Use TERM_TOTAL mark if it exists
                        $termTotalMark = $termMarks->first(fn($m) => $m->assessmentTemplate && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId);
                        if ($termTotalMark) return $termTotalMark->score;
                        
                        // Priority 2: Sum component marks if no TERM_TOTAL
                        return $termMarks->isNotEmpty() ? $termMarks->sum('score') : null;
                    })->filter(fn($score) => $score !== null);
                    
                    return $termScores->isNotEmpty() ? $termScores->avg() : 0;
                })->filter(fn($avg) => $avg > 0);

                $average = $studentAverages->isNotEmpty() ? round($studentAverages->avg(), 1) : 0;
                
                return (object)[
                    'subject_name' => $subject->name,
                    'average' => $average,
                ];
            });
        });

        if ($request->ajax() && $request->has('fetch_academic_excellence')) {
            return response()->json([
                'subjectAverages' => $subjectAverages
            ]);
        }
        
        // Key Metrics (Cached)
        $stats = \Illuminate\Support\Facades\Cache::remember("admin_dashboard_stats{$cacheSuffix}", $cacheTtl, function() use ($divisionId) {
            $studentQuery = \App\Models\Student::where('is_active', true);
            $staffQuery = \App\Models\User::role(['Super Admin', 'Principal', 'Teacher', 'Accountant', 'HR Manager', 'Librarian', 'Staff', 'IT / System Admin', 'Registrar', 'General Manager']);
            
            if ($divisionId) {
                $studentQuery->whereHas('enrollments', function($q) use ($divisionId) {
                    $q->whereNull('end_date')
                      ->whereHas('section.gradeLevel', fn($sq) => $sq->where('division_id', $divisionId));
                });
                $staffQuery->whereHas('employee', fn($q) => $q->where('division_id', $divisionId));
            }

            return [
                'total_students' => $studentQuery->count(),
                'total_staff' => $staffQuery->count(),
                'today_attendance' => 0,
                'pending_actions' => 0,
            ];
        });
        
        // Today's Attendance Rate (Cached)
        if ($academicYear) {
            $attendanceRate = \Illuminate\Support\Facades\Cache::remember("admin_dashboard_attendance_rate{$cacheSuffix}", $cacheTtl, function() use ($academicYear, $divisionId) {
                $attendanceQuery = StudentAttendance::whereDate('attendance_date', Carbon::today());
                
                if ($divisionId) {
                    $attendanceQuery->whereHas('section.gradeLevel', fn($q) => $q->where('division_id', $divisionId));
                }

                $todayAttendance = $attendanceQuery->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();
                
                $totalToday = array_sum($todayAttendance);
                if ($totalToday > 0) {
                    $presentToday = ($todayAttendance['present'] ?? 0) + ($todayAttendance['late'] ?? 0);
                    return round(($presentToday / $totalToday) * 100, 1);
                }
                return 0;
            });
            $stats['today_attendance'] = $attendanceRate;
        }
        
        // Recent Activity (Live)
        $recentActivity = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();
        
        // Student breakdown by Grade Level (Cached)
        $studentsByGrade = \Illuminate\Support\Facades\Cache::remember("admin_dashboard_students_by_grade{$cacheSuffix}", $cacheTtl, function() use ($divisionId) {
            $query = \App\Models\StudentEnrollment::whereNull('end_date')
                ->whereHas('student', fn($q) => $q->where('is_active', true))
                ->join('sections', 'student_enrollments.section_id', '=', 'sections.id')
                ->join('grade_levels', 'sections.grade_level_id', '=', 'grade_levels.id');
                
            if ($divisionId) {
                $query->where('grade_levels.division_id', $divisionId);
            }

            return $query->selectRaw('grade_levels.name as grade_name, count(*) as count')
                ->groupBy('grade_levels.name', 'grade_levels.sort_order')
                ->orderBy('grade_levels.sort_order')
                ->get();
        });
        
        // Sections without attendance marked today (Cached short-term)
        $sectionsMissingAttendance = \Illuminate\Support\Facades\Cache::remember("admin_dashboard_missing_attendance{$cacheSuffix}", 300, function() use ($academicYear, $divisionId) {
            $sectionQuery = \App\Models\Section::where('is_active', true)
                ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id));
                
            $attendanceQuery = StudentAttendance::whereDate('attendance_date', Carbon::today());

            if ($divisionId) {
                $sectionQuery->whereHas('gradeLevel', fn($q) => $q->where('division_id', $divisionId));
                $attendanceQuery->whereHas('section.gradeLevel', fn($q) => $q->where('division_id', $divisionId));
            }

            $allActiveSections = $sectionQuery->count();
            $sectionsWithAttendance = $attendanceQuery->distinct('section_id')->count('section_id');
            
            return max(0, $allActiveSections - $sectionsWithAttendance);
        });
        
        // Gender breakdown (Cached)
        $genderBreakdown = \Illuminate\Support\Facades\Cache::remember("admin_dashboard_gender_breakdown{$cacheSuffix}", $cacheTtl, function() use ($divisionId) {
            $query = Student::where('is_active', true);
            
            if ($divisionId) {
                $query->whereHas('enrollments', function($q) use ($divisionId) {
                    $q->whereNull('end_date')
                      ->whereHas('section.gradeLevel', fn($sq) => $sq->where('division_id', $divisionId));
                });
            }

            return $query->selectRaw('gender, count(*) as count')
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();
        });
        
        $executionTime = round(microtime(true) - $start, 4);
        
        // System Health (Live)
        $systemHealth = \Illuminate\Support\Facades\Cache::remember('system_health_status', 10, function() {
            try {
                \Illuminate\Support\Facades\DB::connection()->getPdo();
                $dbStatus = 'Online';
            } catch (\Exception $e) { $dbStatus = 'Offline'; }

            $queueDriver = config('queue.default');
            $queueStatus = 'Online';
            if ($queueDriver !== 'sync') {
                try { \Illuminate\Support\Facades\Queue::connection()->size(); } 
                catch (\Exception $e) { $queueStatus = 'Offline'; }
            }

            try {
                \Illuminate\Support\Facades\Cache::put('health_check', 'ok', 1);
                $cacheStatus = \Illuminate\Support\Facades\Cache::get('health_check') === 'ok' ? 'Online' : 'Offline';
            } catch (\Exception $e) { $cacheStatus = 'Offline'; }

            return ['database' => $dbStatus, 'queue' => $queueStatus, 'cache' => $cacheStatus];
        });

        $divisions = \App\Models\Division::where('is_active', true)->orderBy('sort_order')->get();
        $selectedDivision = $divisionId ? $divisions->where('id', $divisionId)->first() : null;

        return view('admin.dashboard', compact(
            'stats', 'recentActivity', 'studentsByGrade', 'sectionsMissingAttendance', 
            'genderBreakdown', 'academicYear', 'executionTime', 'systemHealth', 
            'divisions', 'selectedDivision', 'subjectAverages', 'terms', 'selectedTermId',
            'gradeLevels', 'selectedGradeLevelId'
        ));
    }
}
