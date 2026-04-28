<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Display the teacher's weekly schedule.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all timetable entries for the teacher's assignments
        $timetableEntries = \App\Models\Timetable::with(['classPeriod', 'teacherAssignment.subject', 'teacherAssignment.section.gradeLevel'])
            ->whereHas('teacherAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->get();

        $daysMap = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];

        $schedule = [];
        
        foreach ($timetableEntries as $entry) {
            $dayName = $daysMap[$entry->day_of_week] ?? 'Unknown';
            
            // Format time safely
            $startTime = optional($entry->classPeriod->start_time)->format('H:i') ?? '--:--';
            $endTime = optional($entry->classPeriod->end_time)->format('H:i') ?? '--:--';
            
            $schedule[$dayName][] = [
                'time' => "{$startTime} - {$endTime}",
                'subject' => $entry->teacherAssignment->subject->name ?? 'N/A',
                'section' => ($entry->teacherAssignment->section->gradeLevel->name ?? '') . ' - ' . ($entry->teacherAssignment->section->name ?? ''),
                'room' => $entry->room_number ?? 'TBA',
                'sort_order' => $entry->classPeriod->sort_order ?? 0
            ];
        }

        // Sort each day by period order
        foreach ($schedule as $day => &$sessions) {
            usort($sessions, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
        }

        return view('teacher.schedule.index', compact('schedule'));
    }
}
