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
        $cacheTtl = 3600;
        
        // Cache key includes division ID
        $cacheSuffix = $divisionId ? "_div_{$divisionId}" : "_all";
        
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
            'divisions', 'selectedDivision'
        ));
    }
}
