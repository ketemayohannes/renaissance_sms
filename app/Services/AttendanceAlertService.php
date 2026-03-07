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

        $studentIds = $section->students()->pluck('students.id');
        $atRisk = collect();

        foreach ($studentIds as $studentId) {
            // Check last 3 records for this student
            $lastThree = StudentAttendance::where('student_id', $studentId)
                ->where('section_id', $section->id)
                ->orderBy('attendance_date', 'desc')
                ->limit(3)
                ->pluck('status');

            if ($lastThree->count() === 3 && $lastThree->every(fn($status) => $status === 'absent')) {
                $atRisk->push($studentId);
            }
        }

        return $section->students()->whereIn('students.id', $atRisk)->with('user')->get();
    }
}
