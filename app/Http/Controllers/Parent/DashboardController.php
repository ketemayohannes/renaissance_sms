<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;

class DashboardController extends Controller
{
    public function index()
    {
        // Get linked students for the authenticated parent
        $user = auth()->user();
        $children = $user->linked_students->load(['currentEnrollment.section.gradeLevel']);

        // Compute performance summaries for each child
        $children->each(function ($child) {
            // Attendance Rate
            $attendanceCount = $child->attendance()->count();
            $presentCount = $child->attendance()->whereIn('status', ['present', 'late', 'Present', 'Late'])->count();
            $child->attendance_rate = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100, 1) : 100;

            // Average Score
            $averageScore = $child->marks()->avg('score');
            $child->average_score = $averageScore !== null ? round($averageScore, 1) : null;

            // Conduct Incident Count
            $child->conduct_incidents_count = $child->disciplinaryRecords()->count();
        });

        // Active notices targeting parents or all audiences
        $notices = Notice::active()
            ->whereIn('target_audience', ['Parent', 'All'])
            ->orderByDesc('publish_date')
            ->take(5)
            ->get();

        return view('parent.dashboard', [
            'children' => $children,
            'notices' => $notices,
        ]);
    }
}
