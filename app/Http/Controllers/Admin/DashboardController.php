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
    public function index()
    {
        $start = microtime(true);
        // PERFORMANCE: Use cached active academic year
        $academicYear = \App\Helpers\CachedData::activeAcademicYear();
        $cacheTtl = 3600; // 1 hour
        
        // Key Metrics (Cached)
        $stats = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_stats', $cacheTtl, function() {
            // PERFORMANCE: Efficient count
            return [
                'total_students' => \App\Models\Student::where('is_active', true)->count(),
                'total_staff' => \App\Models\User::role(['Super Admin', 'Principal', 'Teacher', 'Accountant', 'HR Manager', 'Librarian', 'Staff', 'IT / System Admin', 'Registrar', 'General Manager'])->count(),
                'today_attendance' => 0,
                'pending_actions' => 0,
            ];
        });
        
        // Today's Attendance Rate (Cached)
        if ($academicYear) {
            $attendanceRate = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_attendance_rate', $cacheTtl, function() use ($academicYear) {
                $todayAttendance = StudentAttendance::whereDate('attendance_date', Carbon::today())
                    ->selectRaw('status, count(*) as count')
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
        
        // Recent Activity (Live - for real-time visibility)
        $recentActivity = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();
        
        // Student breakdown by Grade Level (Cached)
        $studentsByGrade = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_students_by_grade', $cacheTtl, function() {
            return \App\Models\StudentEnrollment::whereNull('end_date')
                ->whereHas('student', fn($q) => $q->where('is_active', true))
                ->join('sections', 'student_enrollments.section_id', '=', 'sections.id')
                ->join('grade_levels', 'sections.grade_level_id', '=', 'grade_levels.id')
                ->selectRaw('grade_levels.name as grade_name, count(*) as count')
                ->groupBy('grade_levels.name', 'grade_levels.sort_order')
                ->orderBy('grade_levels.sort_order')
                ->get();
        });
        
        // Sections without attendance marked today (Cached short-term)
        $sectionsMissingAttendance = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_missing_attendance', 300, function() use ($academicYear) {
            // PERFORMANCE: Use cached data for total active sections
            $allActiveSections = \App\Models\Section::where('is_active', true)
                ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
                ->count();
            
            $sectionsWithAttendance = StudentAttendance::whereDate('attendance_date', Carbon::today())
                ->distinct('section_id')
                ->count('section_id');
            
            return max(0, $allActiveSections - $sectionsWithAttendance);
        });
        
        // Gender breakdown (Cached)
        $genderBreakdown = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_gender_breakdown', $cacheTtl, function() {
            return Student::where('is_active', true)
                ->selectRaw('gender, count(*) as count')
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();
        });
        
        $executionTime = round(microtime(true) - $start, 4);
        \Illuminate\Support\Facades\Log::info("Dashboard load time: {$executionTime}s");
        
        // System Health (Live, but with very short cache to prevent abuse)
        $systemHealth = \Illuminate\Support\Facades\Cache::remember('system_health_status', 10, function() {
            try {
                \Illuminate\Support\Facades\DB::connection()->getPdo();
                $dbStatus = 'Online';
            } catch (\Exception $e) {
                $dbStatus = 'Offline';
            }

            $queueDriver = config('queue.default');
            $queueStatus = 'Online';
            if ($queueDriver !== 'sync') {
                try {
                    // This is a more robust check for database-like queues.
                    // For Redis, you'd check the Redis connection.
                    \Illuminate\Support\Facades\Queue::connection()->size();
                } catch (\Exception $e) {
                    $queueStatus = 'Offline';
                }
            }

            try {
                \Illuminate\Support\Facades\Cache::put('health_check', 'ok', 1);
                $cacheStatus = \Illuminate\Support\Facades\Cache::get('health_check') === 'ok' ? 'Online' : 'Offline';
            } catch (\Exception $e) {
                $cacheStatus = 'Offline';
            }

            return [
                'database' => $dbStatus,
                'queue' => $queueStatus,
                'cache' => $cacheStatus,
            ];
        });

        return view('admin.dashboard', compact('stats', 'recentActivity', 'studentsByGrade', 'sectionsMissingAttendance', 'genderBreakdown', 'academicYear', 'executionTime', 'systemHealth'));
    }
}
