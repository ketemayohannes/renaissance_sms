<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\LinkSiblingRequest;
use App\Models\Student;
use App\Services\StudentService;

class StudentSiblingController extends Controller
{
    public function __construct(protected StudentService $studentService) {}

    public function linkSibling(LinkSiblingRequest $request, Student $student)
    {
        $sibling = Student::findOrFail($request->sibling_id);
        $student->addSibling($sibling);

        // Auto-fill/sync guardian info from the first student to the newly linked sibling
        if ($student->guardians()->count() > 0) {
            $sibling->guardians()->delete();

            foreach ($student->guardians as $guardian) {
                $newGuardian = $sibling->guardians()->create([
                    'guardian_type' => $guardian->guardian_type,
                    'photo' => $guardian->photo,
                    'first_name' => $guardian->first_name,
                    'father_name' => $guardian->father_name,
                    'grandfather_name' => $guardian->grandfather_name,
                    'phone' => $guardian->phone,
                    'email' => $guardian->email,
                    'relationship' => $guardian->relationship,
                    'communication_preferences' => $guardian->communication_preferences,
                    'is_emergency_contact' => $guardian->is_emergency_contact,
                    'user_id' => $guardian->user_id,
                ]);
                $this->studentService->syncGuardianUser($newGuardian);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Sibling linked successfully.']);
        }

        return back()->with('success', 'Sibling linked successfully.');
    }

    public function unlinkSibling(Student $student, Student $sibling)
    {
        $student->removeSibling($sibling);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Sibling unlinked successfully.']);
        }

        return back()->with('success', 'Sibling unlinked successfully.');
    }
}
