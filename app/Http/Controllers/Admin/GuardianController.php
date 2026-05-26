<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentGuardian;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GuardianController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $guardians = StudentGuardian::query()
            ->where('first_name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->distinct()
            ->latest()
            ->take(10)
            ->get();

        return response()->json($guardians);
    }

    public function index(Request $request)
    {
        $query = StudentGuardian::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                  });
            });
        }

        // Group by parent identity to combine siblings
        $guardians = $query->selectRaw('MIN(id) as id, first_name, father_name, grandfather_name, phone, email, photo, user_id, MIN(relationship) as relationship')
            ->groupBy('first_name', 'father_name', 'grandfather_name', 'phone', 'email', 'photo', 'user_id')
            ->latest('id')
            ->paginate(15);

        // Fetch all students for these grouped guardians to avoid N+1
        $phones = $guardians->pluck('phone')->filter()->unique();
        $emails = $guardians->pluck('email')->filter()->unique();

        $allLinkedStudents = Student::whereHas('guardians', function($q) use ($phones, $emails) {
            $q->whereIn('phone', $phones)->orWhereIn('email', $emails);
        })->with(['enrollments.section.gradeLevel', 'guardians'])->get();

        // Map students back to grouped guardians
        $guardians->getCollection()->transform(function($guardian) use ($allLinkedStudents) {
            $guardian->linked_students = $allLinkedStudents->filter(function($student) use ($guardian) {
                return $student->guardians->contains(function($g) use ($guardian) {
                    return ($guardian->phone && $g->phone === $guardian->phone) ||
                           ($guardian->email && $g->email === $guardian->email);
                });
            });
            return $guardian;
        });

        return view('admin.guardians.index', compact('guardians'));
    }

    public function show(StudentGuardian $guardian)
    {
        $guardian->load(['student.enrollments.section.gradeLevel', 'user']);
        
        // Find other students who might share this guardian (by phone or email)
        $otherStudents = [];
        if ($guardian->phone || $guardian->email) {
            $otherStudents = Student::whereHas('guardians', function($q) use ($guardian) {
                if ($guardian->phone) $q->where('phone', $guardian->phone);
                if ($guardian->email) $q->orWhere('email', $guardian->email);
            })
            ->where('id', '!=', $guardian->student_id)
            ->with(['enrollments.section.gradeLevel'])
            ->get();
        }

        return view('admin.guardians.show', compact('guardian', 'otherStudents'));
    }

    public function edit(StudentGuardian $guardian)
    {
        return view('admin.guardians.edit', compact('guardian'));
    }

    public function update(Request $request, StudentGuardian $guardian, StudentService $studentService)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::where('email', $value)->first();
                    if ($user && !$user->hasRole('Parent')) {
                        $fail('This email address belongs to a staff or administrative account and cannot be used for a guardian.');
                    }
                }
            ],
            'relationship' => 'required|string|max:50',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'is_emergency_contact' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($guardian->photo) {
                Storage::disk('public')->delete($guardian->photo);
            }
            $validated['photo'] = $request->file('photo')->store('guardians/photos', 'public');
        }

        $guardian->update($validated);
        $studentService->syncGuardianUser($guardian);

        return redirect()->route('admin.guardians.show', $guardian)
            ->with('success', 'Guardian information updated successfully.');
    }

    public function destroy(StudentGuardian $guardian)
    {
        if ($guardian->photo) {
            Storage::disk('public')->delete($guardian->photo);
        }
        
        $guardian->delete();

        return redirect()->route('admin.guardians.index')
            ->with('success', 'Guardian record removed successfully.');
    }

    public function createUser(Request $request, StudentGuardian $guardian, StudentService $studentService)
    {
        if (!$guardian->email) {
            return back()->with('error', 'Guardian must have an email address to create a user account.');
        }

        if ($guardian->user_id) {
            return back()->with('error', 'This guardian already has a user account.');
        }

        DB::beginTransaction();
        try {
            $password = $request->password ?? 'guardian123';
            $studentService->syncGuardianUser($guardian, $password);

            DB::commit();
            return back()->with('success', 'User account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create user account: ' . $e->getMessage());
        }
    }

    public function resetPassword(StudentGuardian $guardian)
    {
        if (!$guardian->user_id || !$guardian->user) {
            return back()->with('error', 'Guardian does not have a user account.');
        }

        $password = \Illuminate\Support\Str::random(10);
        $guardian->user->update([
            'password' => Hash::make($password),
            'temp_password' => $password,
        ]);

        return back()->with('success', 'Password reset successfully. New Password: ' . $password);
    }
}
