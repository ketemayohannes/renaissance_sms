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
        // Placeholder schedule data for demonstration
        $schedule = [
            'Monday' => [
                ['time' => '08:30 - 09:15', 'subject' => 'Mathematics', 'section' => '9A', 'room' => '101'],
                ['time' => '10:00 - 10:45', 'subject' => 'Physics', 'section' => '10B', 'room' => 'Lab 2'],
            ],
            'Tuesday' => [
                ['time' => '09:15 - 10:00', 'subject' => 'Mathematics', 'section' => '9A', 'room' => '101'],
                ['time' => '11:00 - 11:45', 'subject' => 'Mathematics', 'section' => '9B', 'room' => '102'],
            ],
            'Wednesday' => [
                ['time' => '08:30 - 09:15', 'subject' => 'Physics', 'section' => '10B', 'room' => 'Lab 2'],
            ],
            'Thursday' => [
                ['time' => '10:00 - 10:45', 'subject' => 'Mathematics', 'section' => '9B', 'room' => '102'],
            ],
            'Friday' => [
                ['time' => '09:15 - 10:00', 'subject' => 'Physics', 'section' => '10B', 'room' => 'Lab 2'],
            ],
        ];

        return view('teacher.schedule.index', compact('schedule'));
    }
}
