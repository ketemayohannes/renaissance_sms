<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user is a student
        if (!$user->hasRole('Student')) {
            return redirect()->route('dashboard');
        }

        $student = $user->student;

        if (!$student) {
            return view('student.no-profile'); // Handle case where user has role but no linked student profile
        }

        // Eager load necessary relationships
        $student->load(['currentEnrollment.section.gradeLevel', 'currentEnrollment.academicYear']);

        // Quick Stats Placeholder
        // 1. Attendance
        $attendanceStats = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'rate' => 0,
        ];
        
        // TODO: Calculate actual attendance stats from Attendance records
        // $attendanceRecords = ...

        // 2. Recent Grades (Placeholder)
        $recentGrades = [];

        return view('student.dashboard', compact('student', 'attendanceStats', 'recentGrades'));
    }
}
