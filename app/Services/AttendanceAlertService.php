<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentAttendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceAlertService
{
    /**
     * Identify students with 3 or more consecutive absences in a section.
     */
    public function getAtRiskStudents(Section $section): Collection
    {
        // Get the last 10 attendance dates for this section
        $recentDates = StudentAttendance::where('section_id', $section->id)
            ->distinct()
            ->orderBy('attendance_date', 'desc')
            ->limit(10)
            ->pluck('attendance_date');

        if ($recentDates->count() < 3) {
            return collect();
        }

        // Pull every attendance row for this section within that recent window in
        // ONE query (newest first), then evaluate each student's last 3 in memory
        // — avoids a per-student query (N+1).
        $atRisk = StudentAttendance::where('section_id', $section->id)
            ->whereIn('attendance_date', $recentDates)
            ->orderBy('attendance_date', 'desc')
            ->get(['student_id', 'attendance_date', 'status'])
            ->groupBy('student_id')
            ->filter(function ($records) {
                $lastThree = $records->take(3);
                return $lastThree->count() === 3
                    && $lastThree->every(fn($record) => $record->status === 'absent');
            })
            ->keys();

        if ($atRisk->isEmpty()) {
            return collect();
        }

        return $section->students()->whereIn('students.id', $atRisk)->with('user')->get();
    }
}
