<?php

use Illuminate\Support\Facades\DB;

// Fix enrollment dates to match admission dates
$updated = DB::statement("
    UPDATE student_enrollments se
    JOIN students s ON se.student_id = s.id
    SET se.enrollment_date = s.admission_date
");

echo "Enrollment dates have been synced with admission dates.\n";
