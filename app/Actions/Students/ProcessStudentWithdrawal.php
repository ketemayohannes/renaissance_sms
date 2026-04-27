<?php

namespace App\Actions\Students;

use App\Models\Student;
use App\Models\StudentStatusHistory;
use Illuminate\Support\Facades\DB;

class ProcessStudentWithdrawal
{
    /**
     * Process a student withdrawal action.
     *
     * @param Student $student
     * @param array $data Expected keys: new_status, reason, notes, effective_date
     * @return void
     */
    public static function run(Student $student, array $data)
    {
        DB::transaction(function() use ($student, $data) {
            $oldStatus = $student->is_active ? 'active' : 'inactive';
            
            // Create status history record
            StudentStatusHistory::create([
                'student_id' => $student->id,
                'old_status' => $oldStatus,
                'new_status' => $data['new_status'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'effective_date' => $data['effective_date'],
                'changed_by' => auth()->id(),
            ]);

            // Update student status
            $student->update(['is_active' => false]);

            // Close current enrollment
            $enrollment = $student->currentEnrollment;
            if ($enrollment) {
                $enrollment->update([
                    'status' => $data['new_status'],
                    'end_date' => $data['effective_date'],
                ]);
            }
        });
    }
}
