<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentAttendance;
use App\Models\AcademicYear;
use App\Models\StudentEnrollment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Get summary of attendance for all active sections.
     * OPTIMIZED: Uses direct subquery for enrolled counts instead of nested whereHas.
     */
    public function getActiveSectionsSummary(?AcademicYear $academicYear): Collection
    {
        $today = Carbon::today()->format('Y-m-d');
        $academicYearId = $academicYear?->id;
        
        // 1. Get sections with gradeLevel - simple query first
        $sections = Section::with('gradeLevel')
            ->where('is_active', true)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->get();
        
        if ($sections->isEmpty()) {
            return collect();
        }
        
        $sectionIds = $sections->pluck('id');
        
        // 2. Get enrolled counts per section in ONE efficient query
        $enrolledCounts = StudentEnrollment::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->select('section_id', DB::raw('COUNT(DISTINCT student_id) as enrolled_count'))
            ->groupBy('section_id')
            ->pluck('enrolled_count', 'section_id');

        // 3. Fetch all attendance stats for today in ONE query
        $attendanceStats = StudentAttendance::whereIn('section_id', $sectionIds)
            ->whereDate('attendance_date', $today)
            ->select(
                'section_id',
                DB::raw('COUNT(*) as marked_count'),
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent"),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late"),
                DB::raw("SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused")
            )
            ->groupBy('section_id')
            ->get()
            ->keyBy('section_id');

        // 4. Merge all stats into sections
        return $sections->map(function($section) use ($enrolledCounts, $attendanceStats) {
            $stats = $attendanceStats->get($section->id);
            
            $section->enrolled_count = $enrolledCounts->get($section->id, 0);
            $section->marked_count = $stats ? $stats->marked_count : 0;
            $section->is_complete = $section->enrolled_count > 0 && $section->marked_count >= $section->enrolled_count;
            $section->today_stats = $stats ?: (object)[
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];
            
            return $section;
        })->sortBy(function($section) {
            return ($section->gradeLevel->sort_order ?? 99) . $section->name;
        })->values();
    }
}
