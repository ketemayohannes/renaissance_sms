<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/fix-enrollment-dates', function () {
    try {
        // Update all enrollment dates to match admission dates
        $updated = DB::update("
            UPDATE student_enrollments se
            JOIN students s ON se.student_id = s.id
            SET se.enrollment_date = s.admission_date
        ");
        
        // Get the updated student info
        $student = DB::select("
            SELECT s.first_name, s.father_name, s.grandfather_name, 
                   s.admission_date, se.enrollment_date
            FROM students s
            JOIN student_enrollments se ON s.id = se.student_id
            LIMIT 1
        ");
        
        return response()->json([
            'success' => true,
            'message' => 'Enrollment dates have been synced with admission dates',
            'updated_records' => $updated,
            'sample_student' => $student[0] ?? null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->name('fix.enrollment.dates');
