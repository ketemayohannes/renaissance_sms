<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\BulkStudentIdsRequest;
use App\Http\Requests\Student\BulkTransferStudentsRequest;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentStatusHistory;
use App\Services\StudentService;
use Illuminate\Support\Facades\DB;

class StudentBulkActionController extends Controller
{
    public function __construct(protected StudentService $studentService) {}

    public function bulkDestroy(BulkStudentIdsRequest $request)
    {
        try {
            $count = $this->studentService->bulkDeleteStudents($request->ids);

            return redirect()->route('admin.students.index')->with('success', "$count students deleted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting students: '.$e->getMessage());
        }
    }

    public function bulkDeactivate(BulkStudentIdsRequest $request)
    {
        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->ids as $id) {
                $student = Student::findOrFail($id);
                if ($student->is_active) {
                    $student->update(['is_active' => false]);

                    StudentStatusHistory::create([
                        'student_id' => $student->id,
                        'old_status' => 'active',
                        'new_status' => 'inactive',
                        'reason' => 'Bulk Deactivation',
                        'notes' => 'Deactivated via bulk action.',
                        'effective_date' => now(),
                        'changed_by' => auth()->id(),
                    ]);

                    // Close current active enrollment as well
                    $enrollment = $student->currentActiveEnrollment;
                    if ($enrollment) {
                        $enrollment->update([
                            'status' => 'withdrawn',
                            'end_date' => now(),
                        ]);
                    }

                    $count++;
                }
            }
            DB::commit();

            return redirect()->route('admin.students.index')->with('success', "$count students deactivated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error deactivating students: '.$e->getMessage());
        }
    }

    public function bulkTransfer(BulkTransferStudentsRequest $request)
    {
        $newSection = Section::findOrFail($request->new_section_id);

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($request->ids as $id) {
                $student = Student::findOrFail($id);
                $currentEnrollment = $student->currentActiveEnrollment;

                // If already in that section, skip
                if ($currentEnrollment && $currentEnrollment->section_id == $request->new_section_id) {
                    continue;
                }

                if ($currentEnrollment) {
                    // Close the current enrollment
                    $currentEnrollment->update([
                        'end_date' => $request->transfer_date,
                        'status' => 'transferred',
                    ]);
                }

                // Create new enrollment in the target section
                $student->enrollments()->create([
                    'section_id' => $request->new_section_id,
                    'academic_year_id' => $newSection->academic_year_id,
                    'enrollment_date' => $request->transfer_date,
                    'status' => 'active',
                ]);

                $count++;
            }

            DB::commit();

            return redirect()->route('admin.students.index')->with('success', "$count students transferred successfully to {$newSection->gradeLevel->name} - {$newSection->name}.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Bulk transfer failed: '.$e->getMessage());
        }
    }
}
