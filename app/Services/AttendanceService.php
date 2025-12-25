<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentAttendance;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Get summary of attendance for all active sections.
     */
    public function getActiveSectionsSummary(?AcademicYear $academicYear): Collection
    {
        $today = Carbon::today()->format('Y-m-d');
        
        // 1. Get sections with enrolled student count (optimized)
        $sections = Section::with('gradeLevel')
            ->where('is_active', true)
            ->when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->withCount(['students as enrolled_count' => function($query) use ($academicYear) {
                $query->whereHas('enrollments', function($q) use ($academicYear) {
                    $q->where('academic_year_id', $academicYear?->id)
                      ->where('status', 'active');
                });
            }])
            ->get();

        // 2. Fetch all matching attendance records for today in ONE query
        $attendanceStats = StudentAttendance::whereIn('section_id', $sections->pluck('id'))
            ->where('attendance_date', $today)
            ->selectRaw("
                section_id,
                COUNT(*) as marked_count,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
            ")
            ->groupBy('section_id')
            ->get()
            ->keyBy('section_id');

        // 3. Merge stats into sections
        return $sections->map(function($section) use ($attendanceStats) {
            $stats = $attendanceStats->get($section->id);
            
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
